<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway\Message;

use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Kdyby\CsobPaymentGateway\Helpers;
use Kdyby\CsobPaymentGateway\SigningException;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Response
{

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var array
	 */
	private $verifyKeysOrder;



	public function __construct(array $data, Request $request = NULL, array $verifyKeysOrder = [])
	{
		$this->data = $data;
		$this->request = $request;
		$this->verifyKeysOrder = $verifyKeysOrder;
	}



	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}



	/**
	 * @return string
	 */
	public function getPayId()
	{
		return $this->data['payId'];
	}



	/**
	 * @return \DateTime
	 */
	public function getDateTime()
	{
		return \DateTime::createFromFormat('YmdHis', $this->data['dttm']);
	}



	/**
	 * @return int
	 */
	public function getResultCode()
	{
		return (int) $this->data['resultCode'];
	}



	/**
	 * @return string
	 */
	public function getResultMessage()
	{
		return $this->data['resultMessage'];
	}



	/**
	 * @return int
	 */
	public function getPaymentStatus()
	{
		return (int) $this->data['paymentStatus'];
	}



	/**
	 * @return string|NULL
	 */
	public function getAuthCode()
	{
		return array_key_exists('authCode', $this->data) ? $this->data['authCode'] : NULL;
	}



	/**
	 * @return string|NULL
	 */
	public function getMerchantData()
	{
		return array_key_exists('merchantData', $this->data) ? base64_decode($this->data['merchantData']) : NULL;
	}



	/**
	 * @param PublicKey $publicKey
	 * @throws SigningException
	 * @return Response
	 */
	public function verify(PublicKey $publicKey)
	{
		$response = $this->data;
		unset($response['signature']);

		$string = Helpers::arrayToSignatureString($response, $this->verifyKeysOrder ?: array_keys($response));

		if ($publicKey->verify($string, $this->data['signature']) !== TRUE) {
			throw new SigningException('Result signature is incorrect.');
		}

		return $this;
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return [
			'payId' => $this->getPayId(),
			'dttm' => $this->getDateTime(),
			'resultCode' => $this->getResultCode(),
			'resultMessage' => $this->getResultMessage(),
			'paymentStatus' => $this->getPaymentStatus(),
			'authCode' => $this->getAuthCode(),
			'merchantData' => $this->getMerchantData(),
		] + $this->data;
	}



	/**
	 * @param array $decoded
	 * @param Request $request
	 * @return Response
	 */
	public static function createWithRequest(array $decoded, Request $request)
	{
		return new static($decoded, $request, $request->getResponseVerifyKeysOrder());
	}



	/**
	 * @param array $decoded
	 * @param array $verifyKeysOrder
	 * @return Response
	 */
	public static function createFromArray(array $decoded, array $verifyKeysOrder = [])
	{
		return new static($decoded, NULL, $verifyKeysOrder);
	}

}
