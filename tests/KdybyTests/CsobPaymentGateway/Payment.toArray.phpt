<?php

/**
 * Payment: toArray()
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby;
use Kdyby\CsobPaymentGateway\Payment;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class PaymentToArrayTest extends Tester\TestCase
{

	public function testPayment()
	{
		$payment = new Payment('A1029DTmM7', 15000001);
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');
		$payment->setReturnUrl('https://example.com/process-payment-response');

		$payment->addCartItem('Test item 1', 4200, 1);
		$payment->addCartItem('Test item 2', 15800, 2);

		Assert::same([
			'merchantId' => 'A1029DTmM7',
			'orderNo' => 15000001,
			'dttm' => '20151111120000',
			'payOperation' => $payment::OPERATION_PAYMENT,
			'payMethod' => $payment::PAY_METHOD_CARD,
			'totalAmount' => 20000,
			'currency' => $payment::CURRENCY_CZK,
			'closePayment' => TRUE,
			'returnUrl' => 'https://example.com/process-payment-response',
			'returnMethod' => 'POST',
			'cart' => [
				[
					'name' => 'Test item 1',
					'quantity' => 1,
					'amount' => 4200,
				],
				[
					'name' => 'Test item 2',
					'quantity' => 2,
					'amount' => 15800,
				],
			],
			'description' => 'Test payment',
			'language' => $payment::LANGUAGE_CZ,
		], $payment->toArray());
	}



	public function testPaymentWithMerchantData()
	{
		$payment = new Payment('A1029DTmM7', 15000001);
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');
		$payment->setReturnUrl('https://example.com/process-payment-response');

		$payment->addCartItem('Test item 1', 4200, 1);
		$payment->addCartItem('Test item 2', 15800, 2);

		$payment->setMerchantData($merchantData = json_encode(['foo' => 'bar']));

		$data = $payment->toArray();
		Assert::true(isset($data['merchantData']));
		Assert::same(base64_encode($merchantData), $data['merchantData']);
	}



	public function testPaymentWithCustomerId()
	{
		$payment = new Payment('A1029DTmM7', 15000001, 123456);
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');
		$payment->setReturnUrl('https://example.com/process-payment-response');

		$payment->addCartItem('Test item 1', 4200, 1);
		$payment->addCartItem('Test item 2', 15800, 2);

		$data = $payment->toArray();
		Assert::true(isset($data['customerId']));
		Assert::same(123456, $data['customerId']);
	}



	public function testEmptyCart()
	{
		$payment = new Payment('A1029DTmM7', 15000001);
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');
		$payment->setReturnUrl('https://example.com/process-payment-response');

		Assert::throws(function () use ($payment) {
			$payment->toArray();
		}, Kdyby\CsobPaymentGateway\UnexpectedValueException::class, 'The cart must contain at least one item.');
	}



	public function testEmptyReturnUrl()
	{
		$payment = new Payment('A1029DTmM7', 15000001);
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');

		$payment->addCartItem('Test item 1', 4200, 1);
		$payment->addCartItem('Test item 2', 15800, 2);

		Assert::throws(function () use ($payment) {
			$payment->toArray();
		}, Kdyby\CsobPaymentGateway\UnexpectedValueException::class, 'The returnUrl is required.');
	}



	public function testEmptyDescription()
	{
		$payment = new Payment('A1029DTmM7', 15000001);
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setReturnUrl('https://example.com/process-payment-response');

		$payment->addCartItem('Test item 1', 4200, 1);
		$payment->addCartItem('Test item 2', 15800, 2);

		Assert::throws(function () use ($payment) {
			$payment->toArray();
		}, Kdyby\CsobPaymentGateway\UnexpectedValueException::class, 'The description is required.');
	}



	public function testEmptyOrderNo()
	{
		$payment = new Payment('A1029DTmM7');
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');
		$payment->setReturnUrl('https://example.com/process-payment-response');

		$payment->addCartItem('Test item 1', 4200, 1);
		$payment->addCartItem('Test item 2', 15800, 2);

		Assert::throws(function () use ($payment) {
			$payment->toArray();
		}, Kdyby\CsobPaymentGateway\UnexpectedValueException::class, 'The orderNo is required.');
	}



	public function testPaymentMerchantDataTooLong()
	{
		$payment = new Payment('A1029DTmM7', 15000001);
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');
		$payment->setReturnUrl('https://example.com/process-payment-response');

		$payment->addCartItem('Test item 1', 4200, 1);
		$payment->addCartItem('Test item 2', 15800, 2);

		$payment->setMerchantData($merchantData = str_repeat('foo', 1e6));

		Assert::throws(function () use ($payment) {
			$payment->toArray();
		}, Kdyby\CsobPaymentGateway\UnexpectedValueException::class, 'Merchant data cannot be longer than 255 characters after base64 encoding.');
	}



	public function testRecurrentPayment()
	{
		$payment = new Payment('A1029DTmM7', 15000002);
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');
		$payment->setReturnUrl('https://example.com/process-payment-response');

		$payment->setPayOperation($payment::OPERATION_PAYMENT_RECURRENT);
		$payment->setOriginalPayId(15000001);

		Assert::same([
			'merchantId' => 'A1029DTmM7',
			'origPayId' => 15000001,
			'orderNo' => 15000002,
			'dttm' => '20151111120000',
			'description' => 'Test payment',
		], $payment->toArray());
	}



	public function testRecurrentPaymentWithAmount()
	{
		$payment = new Payment('A1029DTmM7', 15000002);
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');
		$payment->setReturnUrl('https://example.com/process-payment-response');

		$payment->setPayOperation($payment::OPERATION_PAYMENT_RECURRENT);
		$payment->setOriginalPayId(15000001);

		$payment->addCartItem('Test item 1', 4200, 1);
		$payment->addCartItem('Test item 2', 15800, 2);

		Assert::same([
			'merchantId' => 'A1029DTmM7',
			'origPayId' => 15000001,
			'orderNo' => 15000002,
			'dttm' => '20151111120000',
			'totalAmount' => 20000,
			'currency' => $payment::CURRENCY_CZK,
			'description' => 'Test payment',
		], $payment->toArray());
	}



	public function testRecurrentEmptyOrderNo()
	{
		$payment = new Payment('A1029DTmM7');
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');
		$payment->setReturnUrl('https://example.com/process-payment-response');

		$payment->setPayOperation($payment::OPERATION_PAYMENT_RECURRENT);
		$payment->setOriginalPayId(15000001);

		Assert::throws(function () use ($payment) {
			$payment->toArray();
		}, Kdyby\CsobPaymentGateway\UnexpectedValueException::class, 'The orderNo is required.');
	}

}



\run(new PaymentToArrayTest());
