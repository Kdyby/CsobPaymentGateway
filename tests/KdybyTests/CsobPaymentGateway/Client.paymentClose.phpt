<?php

/**
 * Client: payment/close
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

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
class ClientPaymentCloseTest extends CsobTestCase
{

	public function testPaymentClose()
	{
		$response = $this->client->paymentClose('ee4c7266dca71AK');
		Assert::same('ee4c7266dca71AK', $response->getPayId());
		Assert::same(0, $response->getResultCode());
		Assert::same('OK', $response->getResultMessage());
		Assert::same(Payment::STATUS_TO_CLEARING, $response->getPaymentStatus());
	}



	public function testPartialClose()
	{
		$response = $this->client->paymentClose('e1ea517e561e4AK', 200 * 100);
		Assert::same('e1ea517e561e4AK', $response->getPayId());
		Assert::same(0, $response->getResultCode());
		Assert::same('OK', $response->getResultMessage());
		Assert::same(Payment::STATUS_TO_CLEARING, $response->getPaymentStatus());
	}



	public function testPartialCloseExceedsApprovedAmount()
	{
		Assert::throws(function () {
			$this->client->paymentClose('3699bae1bae60AK', 500 * 100);
		}, InvalidParameterException::class, 'Invalid amount of payment/close');
	}

}



\run(new ClientPaymentCloseTest());
