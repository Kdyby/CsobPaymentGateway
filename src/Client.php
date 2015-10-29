<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway;

use Bitbang\Http;
use Kdyby\CsobPaymentGateway\Certificate\PrivateKey;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Psr\Log\LoggerInterface;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Client
{

	const DTTM_FORMAT = 'YmdHis';

	/**
	 * @var array|callable[]|\Closure[]
	 */
	public $onRequest = [];

	/**
	 * @var array|callable[]|\Closure[]
	 */
	public $onResponse = [];

	/**
	 * @var array|callable[]|\Closure[]
	 */
	public $onError = [];

	/**
	 * @var Configuration
	 */
	private $config;

	/**
	 * @var PrivateKey
	 */
	private $privateKey;

	/**
	 * @var PublicKey
	 */
	private $publicKey;

	/**
	 * @var Http\IClient
	 */
	private $httpClient;

	/**
	 * @var LoggerInterface
	 */
	private $logger;



	public function __construct(Configuration $config, PrivateKey $privateKey, PublicKey $publicKey, Http\IClient $httpClient)
	{
		$this->config = $config;
		$this->privateKey = $privateKey;
		$this->publicKey = $publicKey;
		$this->httpClient = $httpClient;
	}



	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger = NULL)
	{
		$this->logger = $logger;
	}



	/**
	 * This is a factory method, not an api call.
	 * @param integer $orderNo
	 * @param string $customerId
	 * @return Payment
	 */
	public function createPayment($orderNo, $customerId = NULL)
	{
		$payment = new Payment($this->config->getMerchantId(), $orderNo, $customerId);
		$payment->setReturnMethod($this->config->getReturnMethod());
		$payment->setReturnUrl($this->config->getReturnUrl());
		return $payment;
	}



	/**
	 * Api call for payment/init
	 *
	 * @param Payment $payment
	 * @return Message\Response
	 */
	public function paymentInit(Payment $payment)
	{
		$data = $payment->toArray();
		$signatureString = Helpers::arrayToSignatureString($data, [
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
		]);

		$data['signature'] = $this->privateKey->sign($signatureString);

		return $this->sendRequest(Message\Request::paymentInit($data));
	}



	/**
	 * RedirectResponse factory for payment/process
	 * @param string $paymentId
	 * @return Message\RedirectResponse
	 */
	public function paymentProcess($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];
		$data['signature'] = $this->simpleSign($data);

		return new Message\RedirectResponse($this->buildUrl('payment/process', $data, ['merchantId', 'payId', 'dttm', 'signature']));
	}



	/**
	 * @param string $paymentId
	 * @return Message\Response
	 */
	public function paymentStatus($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::paymentStatus($data));
	}



	/**
	 * @param string $paymentId
	 * @return Message\Response
	 */
	public function paymentReverse($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::paymentReverse($data));
	}



	/**
	 * @param string $paymentId
	 * @return Message\Response
	 */
	public function paymentClose($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::paymentClose($data));
	}



	/**
	 * @param string $paymentId
	 * @return Message\Response
	 */
	public function paymentRefund($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::paymentRefund($data));
	}



	/**
	 * @param string $paymentId
	 * @return Message\Response
	 */
	public function paymentRecurrent($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::paymentRecurrent($data));
	}



	/**
	 * @param string $customerId
	 * @return Message\Response
	 */
	public function customerInfo($customerId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'customerId' => $customerId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::customerInfo($data));
	}



	/**
	 * @param array $data
	 * @return Message\Response
	 */
	public function receiveResponse(array $data)
	{
		if (empty($data)) {
			throw new InvalidArgumentException('Expected at least partial response from gateway, nothing was given.');
		}

		$data += array_fill_keys([
			'payId',
			'dttm',
			'resultCode',
			'resultMessage',
			'paymentStatus',
			'authCode',
			'merchantData',
			'signature'
		], NULL);

		return Message\Response::createFromArray($data)->verify($this->publicKey);
	}



	/**
	 * @param Message\Request $request
	 * @throws HttpClientException
	 * @throws PaymentApiException
	 * @return Message\Response
	 */
	public function sendRequest(Message\Request $request)
	{
		$response = NULL;
		try {
			try {
				$httpRequest = $this->requestToHttpRequest($request);
				foreach ($this->onRequest as $callback) {
					call_user_func($callback, $request);
				}

				$httpResponse = $this->httpClient->request($httpRequest);

			} catch (Http\IException $e) {
				throw new HttpClientException($e->getMessage(), 0, $e);
			}

			$decoded = @json_decode($responseBody = $httpResponse->getBody(), TRUE);
			if ($decoded === NULL) {
				throw new HttpClientException(sprintf('API returned invalid json %s', $responseBody));
			}

			if (!isset($decoded['resultCode'])) {
				throw new HttpClientException(sprintf('The "resultCode" key is missing in response %s', $responseBody));
			}

			$response = Message\Response::createWithRequest($decoded, $request);

			if ($decoded['resultCode'] !== PaymentApiException::OK) {
				throw PaymentApiException::fromResponse($decoded, $response);
			}

			if (empty($decoded['signature'])) {
				throw new HttpClientException(sprintf('The "signature" key is missing or empty in response %s', $responseBody));
			}

			$response->verify($this->publicKey);

			foreach ($this->onResponse as $callback) {
				call_user_func($callback, $response);
			}

			return $response;

		} catch (\Exception $e) {
			foreach ($this->onError as $callback) {
				call_user_func($callback, $e, $response);
			}
			throw $e;
		}
	}



	/**
	 * @param Message\Request $request
	 * @return Http\Request
	 */
	protected function requestToHttpRequest(Message\Request $request)
	{
		$data = $request->toArray();

		if (empty($data['signature'])) {
			$data['signature'] = $this->simpleSign($data);
		}

		$url = $this->buildUrl($request->getEndpoint(), $data, $request->getUrlParams());

		$headers = [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json;charset=UTF-8',
		];

		$body = $request->isMethodGet() ? NULL : json_encode($data);

		return new Http\Request($request->getMethod(), $url, $headers, $body);
	}



	/**
	 * @param $endpoint
	 * @param array $data
	 * @param array $urlParams
	 * @return string
	 */
	protected function buildUrl($endpoint, array $data, array $urlParams = [])
	{
		$url = $this->config->getUrl() . '/' . $endpoint;

		foreach ($urlParams as $key) {
			if (empty($data[$key])) {
				throw new InvalidArgumentException(sprintF('Missing key %s for the assembly of url', $key));
			}

			$url .= '/' . urlencode($data[$key]);
		}

		return $url;
	}



	/**
	 * @param array $data
	 * @return string
	 */
	protected function simpleSign(array $data)
	{
		return $this->privateKey->sign(Helpers::arrayToSignatureString($data));
	}



	/**
	 * @return bool|string
	 */
	protected function formatDatetime()
	{
		return date(self::DTTM_FORMAT);
	}

}
