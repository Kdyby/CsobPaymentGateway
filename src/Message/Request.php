<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobClient\Message;

use Bitbang\Http;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Request
{

	const GET = Http\Request::GET;
	const POST = Http\Request::POST;
	const PUT = Http\Request::PUT;

	const CURRENCY_CZK = 'CZK';
	const CURRENCY_EUR = 'EUR';
	const CURRENCY_USD = 'USD';
	const CURRENCY_GBP = 'GBP';

	const OPERATION_PAYMENT = 'payment';
	const OPERATION_PAYMENT_RECURRENT = 'recurrentPayment';

	const PAY_METHOD = 'card';

	const LANGUAGE_CZ = 'CZ';
	const LANGUAGE_EN = 'EN';
	const LANGUAGE_DE = 'DE';
	const LANGUAGE_SK = 'SK';

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



	public function __construct($method, $endpoint, array $data, array $urlParams = [], array $responseVerifyOrder = [])
	{
		$this->method = $method;
		$this->endpoint = $endpoint;
		$this->data = $data;
		$this->urlParams = $urlParams;
		$this->responseVerifyOrder = $responseVerifyOrder;
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
	public function getUrlParams()
	{
		return $this->urlParams;
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
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
