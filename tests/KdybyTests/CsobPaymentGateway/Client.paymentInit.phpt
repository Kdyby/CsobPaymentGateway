<?php

/**
 * Client: payment/init
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\InvalidParameterException;
use Kdyby\CsobPaymentGateway\Message\PaymentResponse;
use Kdyby\CsobPaymentGateway\Message\RedirectResponse;
use Kdyby\CsobPaymentGateway\Message\Signature;
use Kdyby\CsobPaymentGateway\Payment;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientPaymentInitTest extends CsobTestCase
{

	public function testPaymentInit()
	{
		$payment = $this->client->createPayment(15000001)
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		$response = $this->client->paymentInit($payment);
		Assert::type(PaymentResponse::class, $response);
		Assert::notSame(NULL, $response->getPayId());
		Assert::same(0, $response->getResultCode());
		Assert::same('OK', $response->getResultMessage());
		Assert::same(Payment::STATUS_REQUESTED, $response->getPaymentStatus());
	}



	public function testDuplicatePayment()
	{
		$payment = $this->client->createPayment(123456)
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		Assert::throws(function () use ($payment) {
			$this->client->paymentInit($payment);
		}, InvalidParameterException::class, 'Invalid paymentInit request: authorized trx for merchantId A1029DTmM7 and orderNo 123456 already exists');
	}

}



\run(new ClientPaymentInitTest());
