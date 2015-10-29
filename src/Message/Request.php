<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway\Message;

use Bitbang\Http;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Request
{

	const GET = Http\Request::GET;
	const POST = Http\Request::POST;
	const PUT = Http\Request::PUT;

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var string
	 */
	private $endpoint;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var array
	 */
	private $urlParams;

	/**
	 * @var array
	 */
	private $responseVerifyOrder;

	/**
	 * @var array
	 */
	private $requestSignatureOrder;



	public function __construct($method, $endpoint, array $data, array $urlParams = [], array $responseVerifyOrder = [], array $requestSignatureOrder = [])
	{
		$this->method = $method;
		$this->endpoint = $endpoint;
		$this->data = $data;
		$this->urlParams = $urlParams;
		$this->responseVerifyOrder = $responseVerifyOrder;
		$this->requestSignatureOrder = $requestSignatureOrder;
	}



	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}



	/**
	 * @return bool
	 */
	public function isMethodGet()
	{
		return $this->method === self::GET;
	}



	/**
	 * @return string
	 */
	public function getEndpoint()
	{
		return $this->endpoint;
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}



	/**
	 * @return array
	 */
	public function getUrlParams()
	{
		return $this->urlParams;
	}



	/**
	 * @return array
	 */
	public function getResponseVerifyKeysOrder()
	{
		return $this->responseVerifyOrder;
	}



	/**
	 * @return array
	 */
	public function getRequestSignatureKeysOrder()
	{
		return $this->requestSignatureOrder;
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentInit(array $data)
	{
		return new static(self::POST, 'payment/init', $data, []);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentStatus(array $data)
	{
		return new static(self::GET, 'payment/status', $data, ['merchantId', 'payId', 'dttm', 'signature']);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentReverse(array $data)
	{
		return new static(self::PUT, 'payment/reverse', $data, ['merchantId', 'payId', 'dttm', 'signature']);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentClose(array $data)
	{
		return new static(self::PUT, 'payment/close', $data, ['merchantId', 'payId', 'dttm', 'signature']);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentRefund(array $data)
	{
		return new static(self::PUT, 'payment/refund', $data, ['merchantId', 'payId', 'dttm', 'signature']);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentRecurrent(array $data)
	{
		return new static(self::POST, 'payment/recurrent', $data, []);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function customerInfo(array $data)
	{
		return new static(self::GET, 'customer/info', $data, ['merchantId', 'customerId', 'dttm', 'signature']);
	}

}
