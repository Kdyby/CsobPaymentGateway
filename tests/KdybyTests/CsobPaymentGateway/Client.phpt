<?php

/**
 * Client
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use GuzzleHttp\Psr7\Response;
use Kdyby\CsobPaymentGateway\Certificate\PrivateKey;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\Http\GuzzleClient;
use Kdyby\CsobPaymentGateway\IHttpClient;
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
			$this->mockHttpClient()
		);
	}


	public function testPaymentProcess()
	{
		$payment = $this->client->createPayment(15000001)
			->setDescription('Test payment')
			->setReturnUrl('https://example.com/process-payment-response')
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



	public function testRedirectFromPaygate()
	{
		$client = new Client(
			new Configuration('A1029DTmM7', 'Test shop'),
			$signature = \Mockery::mock(Signature::class),
			$this->mockHttpClient()
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



	/**
	 * @return \Mockery\Mock|IHttpClient
	 */
	private function mockHttpClient()
	{
		$guzzle = new GuzzleClient();

		$client = \Mockery::mock(IHttpClient::class)->shouldDeferMissing();
		$client->shouldReceive('request')->andReturnUsing(function ($method, $url, $headers, $body) use ($guzzle) {
			$parsedUrl = parse_url($url);
			$path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';

			if ($path === '/api/v1.5/payment/init' && $method === 'POST') {
				$decodedBody = json_decode($body, TRUE);
				$targetFile = __DIR__ . '/api-data/init_' . $decodedBody['merchantId'] . '_' . $decodedBody['orderNo'] . '.json';

			} elseif (strpos($path, '/api/v1.5/payment/status') === 0 && $method === 'GET') {
				list(,,,,, $merchantId, $payId) = explode('/', $path);
				$targetFile = __DIR__ . '/api-data/status_' . $merchantId . '_' . $payId . '.json';

			} else {
				throw new \LogicException(sprintf('Unexpected %s to endpoint %s', $method, $path));
			}

			if (!file_exists($targetFile)) {
				$response = $guzzle->request($method, $url, $headers, $body);
				$data = [
					'status' => $response->getStatusCode(),
					'headers' => $response->getHeaders(),
					'body' => $response->getBody()->getContents(),
				];
				file_put_contents($targetFile, json_encode($data));

			} else {
				$data = json_decode(file_get_contents($targetFile), TRUE);
				$response = new Response($data['status'], $data['headers'], $data['body']);
			}

			return $response;
		});

		return $client;
	}



	protected function tearDown()
	{
		\Mockery::close();
	}

}



\run(new ClientTest());
