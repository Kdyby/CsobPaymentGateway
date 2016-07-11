<?php

/**
 * Client: payment/checkout
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\CheckoutRequest;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\Message\RedirectResponse;
use Kdyby\CsobPaymentGateway\NotSupportedException;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientPaymentCheckoutTest extends CsobTestCase
{

	public function testDisabled()
	{
		$request = new CheckoutRequest('ee4c7266dca71AK', CheckoutRequest::ONECLICK_CHECKBOX_HIDE);

		Assert::throws(function () use ($request) {
			$this->client->paymentCheckout($request);
		}, NotSupportedException::class, 'payment/checkout is not enabled; enable it in your config');
	}


	public function testCheckout()
	{
		$this->configuration->setCheckoutEnabled(TRUE);
		$request = new CheckoutRequest('ee4c7266dca71AK', CheckoutRequest::ONECLICK_CHECKBOX_HIDE);
		$response = $this->client->paymentCheckout($request);

		Assert::type(RedirectResponse::class, $response);
		Assert::match(Configuration::DEFAULT_SANDBOX_URL . '/v1.5/payment/checkout/A1029DTmM7/ee4c7266dca71AK/%d%/%A%', $response->getUrl());
	}


	public function testCheckoutWithReturnUrl()
	{
		$this->configuration->setCheckoutEnabled(TRUE);
		$request = new CheckoutRequest('ee4c7266dca71AK', CheckoutRequest::ONECLICK_CHECKBOX_HIDE);
		$request->setReturnCheckoutUrl('foo');

		$response = $this->client->paymentCheckout($request);
		Assert::type(RedirectResponse::class, $response);
		Assert::match(Configuration::DEFAULT_SANDBOX_URL . '/v1.5/payment/checkout/A1029DTmM7/ee4c7266dca71AK/%d%/foo/%A%', $response->getUrl());
	}


	public function testCheckout16()
	{
		$this->configuration->setCheckoutEnabled(TRUE);
		$this->configuration->setVersion(Configuration::VERSION_1_6);
		$request = new CheckoutRequest('ee4c7266dca71AK', CheckoutRequest::ONECLICK_CHECKBOX_HIDE);
		$response = $this->client->paymentCheckout($request);

		Assert::type(RedirectResponse::class, $response);
		Assert::match(Configuration::DEFAULT_SANDBOX_URL . '/v1.6/payment/checkout/A1029DTmM7/ee4c7266dca71AK/%d%/0/false/%A%', $response->getUrl());
	}

}



\run(new ClientPaymentCheckoutTest());
