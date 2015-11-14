<?php

/**
 * Client: payment/recurrent
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\InvalidParameterException;
use Kdyby\CsobPaymentGateway\OperationNotAllowedException;
use Kdyby\CsobPaymentGateway\Payment;
use Kdyby\CsobPaymentGateway\PaymentNotFoundException;
use Kdyby\CsobPaymentGateway\PaymentNotInValidStateException;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientPaymentRecurrentTest extends CsobTestCase
{

	public function testPaymentRecurrent()
	{
		$payment = $this->client->createPayment(15000001)
			->setOriginalPayId('eb54f88be59c2AK')
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		$response = $this->client->paymentRecurrent($payment);
		Assert::notSame(NULL, $response->getPayId());
		Assert::same(0, $response->getResultCode());
		Assert::same('OK', $response->getResultMessage());
		Assert::same(Payment::STATUS_TO_CLEARING, $response->getPaymentStatus());
	}



	public function testPaymentRecurrentNotAuthorized()
	{
		$payment = $this->client->createPayment(15000001)
			->setOriginalPayId('ee4c7266dca71AK')
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		Assert::throws(function () use ($payment) {
			$this->client->paymentRecurrent($payment);
		}, PaymentNotInValidStateException::class, 'orig payment not authorized');
	}



	public function testPaymentRecurrentNotFound()
	{
		$payment = $this->client->createPayment(15000001)
			->setOriginalPayId('nonExistentId')
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		Assert::throws(function () use ($payment) {
			$this->client->paymentRecurrent($payment);
		}, PaymentNotFoundException::class, 'orig payment not found');
	}



	public function testPaymentRecurrentNotATemplate()
	{
		$payment = $this->client->createPayment(15000001)
			->setOriginalPayId('912aeb8706eceAK')
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		Assert::throws(function () use ($payment) {
			$this->client->paymentRecurrent($payment);
		}, OperationNotAllowedException::class, 'orig payment not recurrent payment template');
	}



	public function testPaymentRecurrentExceedsOriginalAmount()
	{
		Tester\Environment::skip('Not yet sure how it should work');

		$payment = $this->client->createPayment(15000001)
			->setOriginalPayId('6a37894a1f822AK')
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 264 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		Assert::throws(function () use ($payment) {
			$this->client->paymentRecurrent($payment);
		}, InvalidParameterException::class, 'payment/recurrent amount exceeds origin payment amount');
	}

}



\run(new ClientPaymentRecurrentTest());
