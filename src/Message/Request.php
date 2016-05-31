<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway\Message;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Request
{

	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';

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



	public function __construct($method, $endpoint, array $data)
	{
		$this->method = $method;
		$this->endpoint = $endpoint;
		$this->data = $data;
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
	 * @return string
	 */
	public function getEndpointName()
	{
		list($name) = explode('/:', $this->endpoint, 2);
		return $name;
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
		return new static(self::POST, 'payment/init', $data);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentProcess(array $data)
	{
		return new static(self::GET, 'payment/process/:merchantId/:payId/:dttm/:signature', $data);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentStatus(array $data)
	{
		return new static(self::GET, 'payment/status/:merchantId/:payId/:dttm/:signature', $data);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentReverse(array $data)
	{
		return new static(self::PUT, 'payment/reverse', $data);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentClose(array $data)
	{
		return new static(self::PUT, 'payment/close', $data);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentRefund(array $data)
	{
		return new static(self::PUT, 'payment/refund', $data);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentRecurrent(array $data)
	{
		return new static(self::POST, 'payment/recurrent', $data);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentOneclickInit(array $data)
	{
		return new static(self::POST, 'payment/oneclick/init', $data);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentOneclickStart(array $data)
	{
		return new static(self::POST, 'payment/oneclick/start', $data);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function customerInfo(array $data)
	{
		return new static(self::GET, 'customer/info/:merchantId/:customerId/:dttm/:signature', $data);
	}

	/**
	 * @param array $data
	 * @return Request
	 */
	public static function echoGET(array $data)
	{
		return new static(self::GET, 'echo/:merchantId/:dttm/:signature', $data);
	}

	/**
	 * @param array $data
	 * @return Request
	 */
	public static function echoPOST(array $data)
	{
		return new static(self::POST, 'echo', $data);
	}
}
