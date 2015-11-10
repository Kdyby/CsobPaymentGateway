<?php

/**
 * Client
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Certificate\PrivateKey;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\InvalidParameterException;
use Kdyby\CsobPaymentGateway\Message\RedirectResponse;
use Kdyby\CsobPaymentGateway\Message\Signature;
use Kdyby\CsobPaymentGateway\Payment;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientTest extends Tester\TestCase
{

	/**
	 * @var Client
	 */
	private $client;



	protected function setUp()
	{
		parent::setUp();
		$this->client = new Client(
			new Configuration('A1029DTmM7', 'Test shop'),
			new Signature(
				new PrivateKey(__DIR__ . '/../../../examples/keys/rsa_A1029DTmM7.key', NULL),
				new PublicKey(Configuration::getCsobSandboxCertPath())
			),
			new HttpClientMock()
		);
	}



	public function testPaymentProcess()
	{
		$payment = $this->client->createPayment(15000001)
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		$response = $this->client->paymentInit($payment);
		Assert::notSame(NULL, $response->getPayId());
		Assert::same(0, $response->getResultCode());
		Assert::same('OK', $response->getResultMessage());
		Assert::same(Payment::STATUS_REQUESTED, $response->getPaymentStatus());

		$processResponse = $this->client->paymentProcess($response->getPayId());
		Assert::type(RedirectResponse::class, $processResponse);
		Assert::match(Configuration::DEFAULT_SANDBOX_URL . '/payment/process/%A%', $processResponse->getUrl());

		$statusResponse = $this->client->paymentStatus($response->getPayId());
		Assert::same($response->getPayId(), $statusResponse->getPayId());
		Assert::same(0, $statusResponse->getResultCode());
		Assert::same('OK', $statusResponse->getResultMessage());
		Assert::same(Payment::STATUS_REQUESTED, $response->getPaymentStatus());
	}



	public function testInitDuplicatePayment()
	{
		Assert::throws(function () {
			$payment = $this->client->createPayment(123456)
				->setDescription('Test payment')
				->setReturnUrl('https://kdyby.org/process-payment-response')
				->addCartItem('Test item 1', 42 * 100, 1)
				->addCartItem('Test item 2', 158 * 100, 2);

			$this->client->paymentInit($payment);

		}, InvalidParameterException::class, 'Invalid paymentInit request: authorized trx for merchantId A1029DTmM7 and orderNo 123456 already exists');
	}



	public function testRedirectFromPaygate()
	{
		$client = new Client(
			new Configuration('A1029DTmM7', 'Test shop'),
			$signature = \Mockery::mock(Signature::class),
			new HttpClientMock()
		);
		$signature->shouldReceive('verifyResponse')->with(\Mockery::type('array'), 'signature')->andReturn(TRUE);

		$returnData = [
			'payId' => 'fb425174783f9AK',
			'dttm' => '20151109153917',
			'resultCode' => 0,
			'resultMessage' => 'OK',
			'paymentStatus' => Payment::STATUS_TO_CLEARING,
			'signature' => 'signature',
			'authCode' => 637413,
		];
		$returnResponse = $client->receiveResponse($returnData);
		Assert::same('fb425174783f9AK', $returnResponse->getPayId());
		Assert::same(0, $returnResponse->getResultCode());
		Assert::same('OK', $returnResponse->getResultMessage());
		Assert::same(Payment::STATUS_TO_CLEARING, $returnResponse->getPaymentStatus());
		Assert::same(637413, $returnResponse->getAuthCode());
	}



	protected function tearDown()
	{
		\Mockery::close();
	}

}



\run(new ClientTest());
