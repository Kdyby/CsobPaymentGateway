<?php

/**
 * Payment: parameter validation
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
class PaymentValidationTest extends Tester\TestCase
{

	public function testPaymentFields()
	{
		// merchantId
		Assert::throws(function () {
			new Payment('');
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'The merchantId is required.');

		// orderNo
		Assert::throws(function () {
			new Payment('A1029DTmM7', 'abcdef');
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'The orderNo is required. It should be strictly integer, with maximum length of 10 digits.');

		Assert::throws(function () {
			new Payment('A1029DTmM7', '12948' );
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'The orderNo is required. It should be strictly integer, with maximum length of 10 digits.');

		Assert::throws(function () {
			new Payment('A1029DTmM7', 12481247814287);
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'The orderNo is required. It should be strictly integer, with maximum length of 10 digits.');

		// customerId
		Assert::throws(function () {
			new Payment('A1029DTmM7', 12948, str_repeat('x', 64));
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'The customer id cannot be longer than 50 characters.');

		// payOperation
		Assert::throws(function () {
			$payment = new Payment('A1029DTmM7', 15000001);
			$payment->setPayOperation('no-op');
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'Only Payment::OPERATION_* constants are allowed');

		// payMethod
		Assert::throws(function () {
			$payment = new Payment('A1029DTmM7', 15000001);
			$payment->setPayMethod('cash');
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'Only Payment::PAY_METHOD_* constants are allowed');

		// currency
		Assert::throws(function () {
			$payment = new Payment('A1029DTmM7', 15000001);
			$payment->setCurrency('BTC');
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'Only Payment::CURRENCY_* constants are allowed');

		// description
		Assert::throws(function () {
			$payment = new Payment('A1029DTmM7', 15000001);
			$payment->setDescription(str_repeat('x', 300));
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'The description cannot be longer than 255 characters.');

		// language
		Assert::throws(function () {
			$payment = new Payment('A1029DTmM7', 15000001);
			$payment->setLanguage('ES');
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'Only Payment::LANGUAGE_* constants are allowed');
	}



	public function testCartItems()
	{
		Assert::throws(function () {
			$payment = new Payment('A1029DTmM7', 15000001);
			$payment->addCartItem('Test item with a way too long name', 4200, 1);
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'Name cannot be longer than 20 characters.');

		Assert::throws(function () {
			$payment = new Payment('A1029DTmM7', 15000001);
			$payment->addCartItem('Test item 1', 42.00, 1);
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'Price must be an integer. For example 100.25 must be passed as 10025.');

		Assert::throws(function () {
			$payment = new Payment('A1029DTmM7', 15000001);
			$payment->addCartItem('Test item 1', 4200, -1);
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'Quantity must be numeric and larger than zero.');

		Assert::throws(function () {
			$payment = new Payment('A1029DTmM7', 15000001);
			$payment->addCartItem('Test item 1', 4200, 1, str_repeat('x', 42));
		}, Kdyby\CsobPaymentGateway\InvalidArgumentException::class, 'Description cannot be longer than 40 characters.');
	}

}



\run(new PaymentValidationTest());
