<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway\Message;

use Kdyby;
use Kdyby\CsobPaymentGateway\Certificate\PrivateKey;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Kdyby\CsobPaymentGateway\Client;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Signature
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
	 * @var array
	 */
	private $paymentPriorities = [
		'merchantId',
		'origPayId',
		'orderNo',
		'dttm',
		'payOperation',
		'payMethod',
		'totalAmount',
		'currency',
		'closePayment',
		'returnUrl',
		'returnMethod',
		'cart',
		'description',
		'merchantData',
		'customerId',
		'language',
	];

	/**
	 * @var array
	 */
	private $responsePriorities = [
		'payId',
		'customerId',
		'dttm',
		'resultCode',
		'resultMessage',
		'paymentStatus',
		'authCode',
		'cardToken',
	];



	public function __construct(PrivateKey $privateKey, PublicKey $publicKey)
	{
		$this->privateKey = $privateKey;
		$this->publicKey = $publicKey;
	}



	/**
	 * @param array $paymentPriorities
	 * @return Signature
	 */
	public function setPaymentPriorities(array $paymentPriorities)
	{
		$this->paymentPriorities = $paymentPriorities;
	}



	/**
	 * @param array $responsePriorities
	 * @return Signature
	 */
	public function setResponsePriorities(array $responsePriorities)
	{
		$this->responsePriorities = $responsePriorities;
	}



	/**
	 * @param array $data
	 * @return string
	 */
	public function simpleSign(array $data)
	{
		return $this->privateKey->sign(self::arrayToSignatureString($data));
	}



	/**
	 * @param array $data
	 * @return string
	 */
	public function signPayment(array $data)
	{
		return $this->privateKey->sign(self::arrayToSignatureString($data, $this->paymentPriorities));
	}



	/**
	 * @param array $data
	 * @param string $signature
	 * @return bool
	 */
	public function verifyResponse(array $data, $signature)
	{
		return $this->publicKey->verify(self::arrayToSignatureString($data, $this->responsePriorities), $signature);
	}



	/**
	 * @param array $data
	 * @param array $arrayKeys
	 * @return string
	 */
	public static function arrayToSignatureString(array $data, array $arrayKeys = [])
	{
		$str = '';
		foreach ($arrayKeys ?: array_keys($data) as $key) {
			if (!array_key_exists($key, $data) || $data[$key] === NULL) {
				continue; // can skip
			}

			$value = $data[$key];
			if ($value === TRUE) {
				$str .= 'true';

			} elseif ($value === FALSE) {
				$str .= 'false';

			} elseif ($value instanceof \DateTime) {
				$str .= $value->format(Client::DTTM_FORMAT);

			} elseif (is_array($value)) {
				$str .= self::arrayToSignatureString($value);

			} else {
				$str .= (string) $data[$key];
			}

			$str .= '|';
		}

		return rtrim($str, '|');
	}

}
