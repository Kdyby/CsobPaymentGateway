<?php

/**
 * Client: logging
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\Message\Signature;
use Kdyby\CsobPaymentGateway\Payment;
use Psr\Log\LoggerInterface;
use Tester;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientLoggingTest extends CsobTestCase
{

	/**
	 * @var \Mockery\MockInterface|LoggerInterface
	 */
	private $logger;



	protected function setUp()
	{
		parent::setUp();

		$this->logger = \Mockery::mock(LoggerInterface::class);
		$this->client->setLogger($this->logger);

		Tester\Environment::$checkAssertions = FALSE;
	}



	public function testRequestResponse()
	{
		$payment = $this->client->createPayment(15000001)
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		$this->logger->shouldReceive('info')->once()
			->with('payment/init: OK', \Mockery::type('array'));
		$this->logger->shouldReceive('error')->never();

		$this->client->paymentInit($payment);
	}



	public function testError()
	{
		$this->logger->shouldReceive('info')->never();
		$this->logger->shouldReceive('error')->once()
			->with('payment/status', \Mockery::on(function ($context) {
				return is_array($context) && isset($context['exception']);
			}));

		try {
			$this->client->paymentStatus('thisPaymentDoesntExist');
		} catch (\Exception $e) {}
	}



	public function testPaymentProcess()
	{
		$this->logger->shouldReceive('info')->once()
			->with('payment/process', \Mockery::on(function ($context) {
				return is_array($context) && isset($context['request']);
			}));

		try {
			$this->client->paymentProcess('thisPaymentDoesntExist');
		} catch (\Exception $e) {}
	}



	public function testReceiveResponse()
	{
		$this->logger->shouldReceive('info')->once()
			->with('payment/process', \Mockery::on(function ($context) {
				return is_array($context) && isset($context['response']);
			}));

		$client = new Client(
			new Configuration('A1029DTmM7', 'Test shop'),
			$signature = \Mockery::mock(Signature::class),
			new HttpClientMock()
		);
		$client->setLogger($this->logger);
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
		$client->receiveResponse($returnData);
	}

}



\run(new ClientLoggingTest());
