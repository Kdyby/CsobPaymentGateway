<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway\Message;

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



	public function __construct(array $data, Request $request = NULL)
	{
		$this->data = $data;
		$this->request = $request;
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
		return array_key_exists('payId', $this->data) ? $this->data['payId'] : NULL;
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
		return array_key_exists('resultCode', $this->data) ? (int) $this->data['resultCode'] : NULL;
	}



	/**
	 * @return string
	 */
	public function getResultMessage()
	{
		return array_key_exists('resultMessage', $this->data) ? $this->data['resultMessage'] : NULL;
	}



	/**
	 * @return int
	 */
	public function getPaymentStatus()
	{
		return array_key_exists('paymentStatus', $this->data) ? (int) $this->data['paymentStatus'] : NULL;
	}



	/**
	 * @return string|NULL
	 */
	public function getAuthCode()
	{
		return array_key_exists('authCode', $this->data) ? (string) $this->data['authCode'] : NULL;
	}



	/**
	 * @return string|NULL
	 */
	public function getCardToken()
	{
		return array_key_exists('cardToken', $this->data) ? $this->data['cardToken'] : NULL;
	}



	/**
	 * @return string|NULL
	 */
	public function getMerchantData()
	{
		return array_key_exists('merchantData', $this->data) ? base64_decode($this->data['merchantData']) : NULL;
	}



	/**
	 * @param Signature $signature
	 * @throws SigningException
	 * @return Response
	 */
	public function verify(Signature $signature)
	{
		if ($signature->verifyResponse($this->data, $this->data['signature']) !== TRUE) {
			throw SigningException::fromResponse($this);
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
	 * @return Response
	 */
	public static function createFromArray(array $decoded)
	{
		return new static($decoded, NULL);
	}

}
