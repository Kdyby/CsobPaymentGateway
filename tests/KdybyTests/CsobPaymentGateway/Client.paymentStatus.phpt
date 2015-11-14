<?php

/**
 * Client: payment/status
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
class ClientPaymentStatusTest extends CsobTestCase
{

	public function testPaymentStatus()
	{
		$payment = $this->client->createPayment(15000001)
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);
		$initResponse = $this->client->paymentInit($payment);

		$response = $this->client->paymentStatus($initResponse->getPayId());
		Assert::same($response->getPayId(), $initResponse->getPayId());
		Assert::same(0, $response->getResultCode());
		Assert::same('OK', $response->getResultMessage());
		Assert::same(Payment::STATUS_REQUESTED, $response->getPaymentStatus());
	}

}



\run(new ClientPaymentStatusTest());
