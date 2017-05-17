<?php

/**
 * CheckoutRequest
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby;
use Kdyby\CsobPaymentGateway\CheckoutRequest;
use Kdyby\CsobPaymentGateway\InvalidArgumentException;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class CheckoutRequestTest extends Tester\TestCase
{

	public function testCheckoutRequest()
	{
		$request = new CheckoutRequest('ee4c7266dca71AK', CheckoutRequest::ONECLICK_CHECKBOX_HIDE);

		Assert::same('ee4c7266dca71AK', $request->getPaymentId());
		Assert::same(CheckoutRequest::ONECLICK_CHECKBOX_HIDE, $request->getOneclickPaymentCheckbox());
		Assert::false($request->getDisplayOmnibox());
		Assert::null($request->getReturnCheckoutUrl());

		$request->setDisplayOmnibox(TRUE);
		Assert::true($request->getDisplayOmnibox());

		$request->setReturnCheckoutUrl('https://kdyby.org');
		Assert::same('https://kdyby.org', $request->getReturnCheckoutUrl());

		Assert::throws(function () use ($request) {
			$request->setOneclickPaymentCheckbox(42);
		}, InvalidArgumentException::class, 'Only CheckoutRequest::ONECLICK_CHECKBOX_* constants are allowed');
	}

}



\run(new CheckoutRequestTest());
