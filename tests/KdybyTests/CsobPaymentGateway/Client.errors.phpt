<?php

/**
 * Client generic errors
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\ApiException;
use Kdyby\CsobPaymentGateway\InternalErrorException;
use Kdyby\CsobPaymentGateway\InvalidParameterException;
use Kdyby\CsobPaymentGateway\MerchantBlockedException;
use Kdyby\CsobPaymentGateway\Message\Request;
use Kdyby\CsobPaymentGateway\MissingParameterException;
use Kdyby\CsobPaymentGateway\PaymentNotFoundException;
use Kdyby\CsobPaymentGateway\PaymentNotInValidStateException;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientErrorsTest extends CsobTestCase
{

	/**
	 * @var int
	 */
	private $invalidOrderNo = 15000006;



	public function testHttpCodes()
	{
		Assert::throws(function () {
			$this->client->processRequest(new Request(Request::GET, 'payment/400', []));
		}, ApiException::class, 'Payment is probably in wrong state or request is broken', ApiException::S400_BAD_REQUEST);

		Assert::throws(function () {
			$this->client->processRequest(new Request(Request::GET, 'payment/403', []));
		}, ApiException::class, 'Payment is probably in wrong state or request is broken', ApiException::S403_FORBIDDEN);

		Assert::throws(function () {
			$this->client->processRequest(new Request(Request::GET, 'payment/404', []));
		}, ApiException::class, 'Payment is probably in wrong state or request is broken', ApiException::S404_NOT_FOUND);

		Assert::throws(function () {
			$this->client->processRequest(new Request(Request::GET, 'payment/429', []));
		}, ApiException::class, 'Too Many Requests', ApiException::S429_TOO_MANY_REQUESTS);

		Assert::throws(function () {
			$this->client->processRequest(new Request(Request::GET, 'payment/503', []));
		}, ApiException::class, 'Service is down for maintenance', ApiException::S503_SERVICE_UNAVAILABLE);
	}



	public function testResultCodes()
	{
		Assert::throws(function () {
			$this->client->paymentStatus('internalError');
		}, InternalErrorException::class, 'Internal error');

		Assert::throws(function () {
			$this->client->paymentStatus('paymentNotFound');
		}, PaymentNotFoundException::class, 'Payment not found');

		Assert::throws(function () {
			$this->client->paymentStatus('paymentInvalid');
		}, PaymentNotInValidStateException::class, 'Payment not in valid state');

		Assert::throws(function () {
			$this->client->paymentStatus('merchantBlocked');
		}, MerchantBlockedException::class, 'Merchant blocked');
	}



	public function testMissingParameter()
	{
		$data = $this->createPayment(15000005)->toArray();
		unset($data['description']);

		Assert::throws(function () use ($data) {
			$this->client->processRequest(Request::paymentInit($data));
		}, MissingParameterException::class, 'Missing parameter description');
	}



	public function testInvalidParameter()
	{
		foreach ($this->invalidParameterProvider() as list($paymentData, $exceptionMessage)) {
			Assert::throws(function () use ($paymentData) {
				$this->client->processRequest(Request::paymentInit($paymentData));
			}, InvalidParameterException::class, $exceptionMessage);
		}
	}



	private function invalidParameterProvider()
	{

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['orderNo'] = 12345678901;
		yield [$data, 'Invalid length of orderNo parameter'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['dttm'] = 'NaN';
		yield [$data, 'Invalid format of dttm parameter'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['payOperation'] = 'nothing';
		yield [$data, 'Invalid payOperation parameter, value not allowed'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['payMethod'] = 'cash';
		yield [$data, 'Invalid payMethod parameter, value not allowed'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['totalAmount'] = 0;
		yield [$data, 'Invalid \'totalAmount\' parameter, must be positive'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['currency'] = 'BTC';
		yield [$data, 'Invalid currency parameter, value not allowed'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['description'] = 'This description is far too long: ' . str_repeat('x', 255);
		yield [$data, 'Invalid length of description parameter'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['returnUrl'] .= '?x=' . str_repeat('x', 300);
		yield [$data, 'Invalid length of returnUrl parameter'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['returnMethod'] = 'HEAD';
		yield [$data, 'Invalid returnMethod parameter, value not allowed'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['cart'][0]['name'] = 'Name longer than 20 characters';
		yield [$data, 'Invalid length of cart/name parameter'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['cart'][0]['quantity'] = 0;
		yield [$data, 'Invalid \'cart/quantity\' parameter, must be >= 1'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['cart'][0]['description'] = 'This description is far too long: ' . str_repeat('x', 30);
		yield [$data, 'Invalid length of cart/description parameter'];

		$data = $this->createPayment($this->invalidOrderNo++)->toArray();
		$data['cart'][2] = [
			'name' => 'Test item 3',
			'quantity' => 1,
			'amount' => 1 * 100,
		];
		yield [$data, 'Invalid \'cart\' parameter, wrong size'];
	}



	private function createPayment($orderNo)
	{
		return $this->client->createPayment($orderNo)
			->setDescription('Test payment')
			->setReturnUrl('https://kdyby.org/process-payment-response')
			->addCartItem('Test item 1', 42 * 100, 1)
			->addCartItem('Test item 2', 158 * 100, 2);
	}

}



\run(new ClientErrorsTest());
