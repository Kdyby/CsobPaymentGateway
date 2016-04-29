<?php

/**
 * Client: payment/oneclick/*
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\NotSupportedException;
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
class ClientPaymentOneclickTest extends CsobTestCase
{

	protected function setUp()
	{
		parent::setUp();
		$this->configuration->setVersion(Configuration::VERSION_1_6);
	}



	public function testPaymentOneclick()
	{
		$payment = $this->client->createPayment(15000001)
			->setOriginalPayId('4ff025effb8d5BD')
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		$initResponse = $this->client->paymentOneclickInit($payment);
		Assert::notSame(NULL, $initResponse->getPayId());
		Assert::same(0, $initResponse->getResultCode());
		Assert::same('OK', $initResponse->getResultMessage());
		Assert::same(Payment::STATUS_REQUESTED, $initResponse->getPaymentStatus());

		$startResponse = $this->client->paymentOneclickStart($initResponse->getPayId());
		Assert::same($initResponse->getPayId(), $startResponse->getPayId());
		Assert::same(0, $startResponse->getResultCode());
		Assert::same('OK', $startResponse->getResultMessage());
		Assert::same(Payment::STATUS_PENDING, $startResponse->getPaymentStatus());
	}



	public function testPaymentOneclickNotAuthorized()
	{
		$payment = $this->client->createPayment(15000001)
			->setOriginalPayId('6bc16c0ea9c25BD')
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		Assert::throws(function () use ($payment) {
			$this->client->paymentOneclickInit($payment);
		}, PaymentNotInValidStateException::class, 'orig payment not authorized');
	}



	public function testPaymentOneclickNotFound()
	{
		$payment = $this->client->createPayment(15000001)
			->setOriginalPayId('nonExistentId')
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		Assert::throws(function () use ($payment) {
			$this->client->paymentOneclickInit($payment);
		}, PaymentNotFoundException::class, 'orig payment not found');
	}



	public function testPaymentOneclickNotATemplate()
	{
		$payment = $this->client->createPayment(15000001)
			->setOriginalPayId('52b7b2f93846fBD')
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		Assert::throws(function () use ($payment) {
			$this->client->paymentOneclickInit($payment);
		}, OperationNotAllowedException::class, 'orig payment not oneclick payment template');
	}



	public function testPaymentOneclickNotSupportedIn15()
	{
		$this->configuration->setVersion(Configuration::VERSION_1_5);
		$payment = $this->client->createPayment(15000001)
			->setOriginalPayId('52b7b2f93846fBD')
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);

		Assert::throws(function () use ($payment) {
			$this->client->paymentOneclickInit($payment);
		}, NotSupportedException::class, 'payment/oneclick is not supported in currently used eAPI version');
	}

}



\run(new ClientPaymentOneclickTest());
