<?php

/**
 * Client: payment/refund
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
class ClientPaymentRefundTest extends CsobTestCase
{

	public function testPaymentRefund()
	{
		$response = $this->client->paymentRefund('eb54f88be59c2AK');
		Assert::same('eb54f88be59c2AK', $response->getPayId());
		Assert::same(0, $response->getResultCode());
		Assert::same('OK', $response->getResultMessage());
		Assert::same(Payment::STATUS_CLEARED, $response->getPaymentStatus()); // refund is async

		$response = $this->client->paymentStatus('eb54f88be59c2AK');
		Assert::same('eb54f88be59c2AK', $response->getPayId());
		Assert::same(0, $response->getResultCode());
		Assert::same('OK', $response->getResultMessage());
		Assert::same(Payment::STATUS_REFUND_REQUESTED, $response->getPaymentStatus());
	}



	public function testPartialRefund()
	{
		$response = $this->client->paymentRefund('ee4c7266dca71AK', 50 * 100);
		Assert::same('ee4c7266dca71AK', $response->getPayId());
		Assert::same(0, $response->getResultCode());
		Assert::same('OK', $response->getResultMessage());
		Assert::same(Payment::STATUS_CLEARED, $response->getPaymentStatus());
	}

}



\run(new ClientPaymentRefundTest());
