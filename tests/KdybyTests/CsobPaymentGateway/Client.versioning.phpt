<?php

/**
 * Client: versioning
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\Message\RedirectResponse;
use Kdyby\CsobPaymentGateway\Message\Response;
use Kdyby\CsobPaymentGateway\Payment;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientVersioningTest extends CsobTestCase
{

	public function testFunctional()
	{
		$this->configuration->setVersion(Configuration::VERSION_1_6);
		$this->initPayment();
	}



	public function testDefaultVersionUrl()
	{
		$response = $this->initPayment();

		$processResponse = $this->client->paymentProcess($response->getPayId());
		Assert::type(RedirectResponse::class, $processResponse);
		Assert::match(Configuration::DEFAULT_SANDBOX_URL . '/v1.5/payment/process/%A%', $processResponse->getUrl());
	}



	public function testForcedVersionUrl()
	{
		$this->configuration->setVersion(Configuration::VERSION_1_6);
		$response = $this->initPayment();

		$processResponse = $this->client->paymentProcess($response->getPayId());
		Assert::type(RedirectResponse::class, $processResponse);
		Assert::match(Configuration::DEFAULT_SANDBOX_URL . '/v1.6/payment/process/%A%', $processResponse->getUrl());
	}



	/**
	 * @return Response
	 */
	private function initPayment()
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

		return $response;
	}

}



\run(new ClientVersioningTest());
