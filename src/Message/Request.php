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
	private $responseVerifyOrder;

	/**
	 * @var array
	 */
	private $requestSignatureOrder;



	public function __construct($method, $endpoint, array $data, array $responseVerifyOrder = [], array $requestSignatureOrder = [])
	{
		$this->method = $method;
		$this->endpoint = $endpoint;
		$this->data = $data;
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
		return new static(
			self::POST,
			'payment/init',
			$data,
			['payId', 'dttm', 'resultCode', 'resultMessage', 'paymentStatus', 'authCode', 'cardToken'],
			[
				'merchantId',
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
				'language'
			]
		);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentStatus(array $data)
	{
		return new static(
			self::GET,
			'payment/status/:merchantId/:payId/:dttm/:signature',
			$data,
			['payId', 'dttm', 'resultCode', 'resultMessage', 'paymentStatus', 'authCode', 'cardToken']
		);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentReverse(array $data)
	{
		return new static(
			self::PUT,
			'payment/reverse/:merchantId/:payId/:dttm/:signature',
			$data,
			['payId', 'dttm', 'resultCode', 'resultMessage', 'paymentStatus', 'authCode', 'cardToken']
		);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentClose(array $data)
	{
		return new static(
			self::PUT,
			'payment/close/:merchantId/:payId/:dttm/:signature',
			$data,
			['payId', 'dttm', 'resultCode', 'resultMessage', 'paymentStatus', 'authCode', 'cardToken']
		);
	}



	/**
	 * @param array $data
	 * @return Request
	 */
	public static function paymentRefund(array $data)
	{
		return new static(
			self::PUT,
			'payment/refund/:merchantId/:payId/:dttm/:signature',
			$data,
			['payId', 'dttm', 'resultCode', 'resultMessage', 'paymentStatus', 'authCode', 'cardToken']
		);
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
		return new static(
			self::GET,
			'customer/info/:merchantId/:customerId/:dttm/:signature',
			$data,
			['customerId', 'dttm', 'resultCode', 'resultMessage']
		);
	}

}
