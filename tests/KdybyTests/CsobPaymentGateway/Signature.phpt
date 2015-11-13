<?php

/**
 * Signature
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby;
use Kdyby\CsobPaymentGateway\Certificate\PrivateKey;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Kdyby\CsobPaymentGateway\Message\Signature;
use Kdyby\CsobPaymentGateway\Payment;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class SignatureTest extends Tester\TestCase
{

	/**
	 * @var PrivateKey
	 */
	private $privateKey;

	/**
	 * @var PublicKey
	 */
	private $publicKey;

	/**
	 * @var Signature
	 */
	private $signature;


	protected function setUp()
	{
		$this->privateKey = new PrivateKey(__DIR__ . '/../../../examples/keys/rsa_A1029DTmM7.key', NULL);
		$this->publicKey = new PublicKey(__DIR__ . '/../../../examples/keys/rsa_A1029DTmM7.pub');
		$this->signature = new Signature($this->privateKey, $this->publicKey);
	}



	public function testPaymentSignature()
	{
		$payment = new Payment('A1029DTmM7', 15000001);
		$payment->setDttm(new \DateTime('2015-11-11 12:00:00'));
		$payment->setDescription('Test payment');
		$payment->setReturnUrl('https://example.com/process-payment-response');

		$payment->addCartItem('Test item 1', 4200, 1);
		$payment->addCartItem('Test item 2', 15800, 2);

		$expected = $this->privateKey->sign('A1029DTmM7|15000001|20151111120000|payment|card|20000|CZK|true|https://example.com/process-payment-response|POST|Test item 1|1|4200|Test item 2|2|15800|Test payment|CZ');
		$signatureString = $this->signature->signPayment($payment->toArray());
		Assert::same($expected, $signatureString);
	}



	public function testResponseSignature()
	{
		$data = [
			'payId' => 'abcdefghijklmno',
			'dttm' => '20151111120002',
			'resultCode' => 0,
			'resultMessage' => 'OK',
			'paymentStatus' => 1,
			'merchantData' => 'eyJmb29JZCI6IDEyMzQ1Nn0=',
		];

		$signatureString = $this->privateKey->sign('abcdefghijklmno|20151111120002|0|OK|1|eyJmb29JZCI6IDEyMzQ1Nn0=');
		Assert::true($this->signature->verifyResponse($data, $signatureString));
	}

}



\run(new SignatureTest());
