<?php

/**
 * Client: events
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Certificate\PrivateKey;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\Exception;
use Kdyby\CsobPaymentGateway\Message\Request;
use Kdyby\CsobPaymentGateway\Message\Response;
use Kdyby\CsobPaymentGateway\Message\Signature;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientEventsTest extends CsobTestCase
{

	private $onRequestCalled;
	private $onResponseCalled;
	private $onErrorCalled;



	protected function setUp()
	{
		parent::setUp();
		$this->onRequestCalled = $this->onResponseCalled = $this->onErrorCalled = FALSE;

		$this->client->onRequest[] = function (Request $request) {
			$this->onRequestCalled = TRUE;
		};
		$this->client->onResponse[] = function (Request $request, Response $response) {
			$this->onResponseCalled = TRUE;
		};
		$this->client->onError[] = function (Request $request, Exception $e, Response $response = NULL) {
			$this->onErrorCalled = TRUE;
		};
	}



	public function testRequestResponse()
	{
		$payment = $this->client->createPayment(15000001)
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		$this->client->paymentInit($payment);

		Assert::true($this->onRequestCalled);
		Assert::true($this->onResponseCalled);
		Assert::false($this->onErrorCalled);
	}



	public function testError()
	{
		try {
			$this->client->paymentStatus('thisPaymentDoesntExist');
		} catch (\Exception $e) {}

		Assert::true($this->onRequestCalled);
		Assert::false($this->onResponseCalled);
		Assert::true($this->onErrorCalled);
	}

}



\run(new ClientEventsTest());
