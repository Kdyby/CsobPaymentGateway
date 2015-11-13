<?php

/**
 * Client: receive response
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Payment;
use Kdyby\CsobPaymentGateway\PaymentCanceledException;
use Kdyby\CsobPaymentGateway\PaymentDeclinedException;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientReceiveResponseTest extends CsobTestCase
{

	public function testReceiveResponseToClearing()
	{
		$returnData = [
			'payId' => 'fb425174783f9AK',
			'dttm' => '20151109153917',
			'resultCode' => 0,
			'resultMessage' => 'OK',
			'paymentStatus' => Payment::STATUS_TO_CLEARING,
			'signature' => 'signature',
			'authCode' => '637413',
		];

		$returnResponse = $this->client->receiveResponse($returnData);
		Assert::same('fb425174783f9AK', $returnResponse->getPayId());
		Assert::same(0, $returnResponse->getResultCode());
		Assert::same('OK', $returnResponse->getResultMessage());
		Assert::same(Payment::STATUS_TO_CLEARING, $returnResponse->getPaymentStatus());
		Assert::same('637413', $returnResponse->getAuthCode());
	}



	public function testReceiveResponseApproved()
	{
		$returnData = [
			'payId' => 'fb425174783f9AK',
			'dttm' => '20151109153917',
			'resultCode' => 0,
			'resultMessage' => 'OK',
			'paymentStatus' => Payment::STATUS_APPROVED,
			'signature' => 'signature',
			'authCode' => '637413',
		];

		$returnResponse = $this->client->receiveResponse($returnData);
		Assert::same('fb425174783f9AK', $returnResponse->getPayId());
		Assert::same(0, $returnResponse->getResultCode());
		Assert::same('OK', $returnResponse->getResultMessage());
		Assert::same(Payment::STATUS_APPROVED, $returnResponse->getPaymentStatus());
		Assert::same('637413', $returnResponse->getAuthCode());
	}



	public function testReceiveResponseCanceled()
	{
		$returnData = [
			'payId' => 'fb425174783f9AK',
			'dttm' => '20151109153917',
			'resultCode' => 0,
			'resultMessage' => 'OK',
			'paymentStatus' => Payment::STATUS_CANCELED,
			'signature' => 'signature',
		];

		Assert::throws(function () use ($returnData) {
			$this->client->receiveResponse($returnData);
		}, PaymentCanceledException::class, 'OK');
	}



	public function testReceiveResponseDeclined()
	{
		$returnData = [
			'payId' => 'fb425174783f9AK',
			'dttm' => '20151109153917',
			'resultCode' => 0,
			'resultMessage' => 'OK',
			'paymentStatus' => Payment::STATUS_DECLINED,
			'signature' => 'signature',
		];

		Assert::throws(function () use ($returnData) {
			$this->client->receiveResponse($returnData);
		}, PaymentDeclinedException::class, 'OK');
	}

}



\run(new ClientReceiveResponseTest());
