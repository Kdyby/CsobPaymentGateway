<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Message\Signature;
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
	 * @var Signature
	 */
	private $signature;

	/**
	 * @var IHttpClient
	 */
	private $httpClient;

	/**
	 * @var LoggerInterface
	 */
	private $logger;



	public function __construct(Configuration $config, Signature $signature, IHttpClient $httpClient)
	{
		$this->config = $config;
		$this->signature = $signature;
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
	 *
	 * @param integer $orderNo
	 * @param string $customerId
	 * @return Payment
	 */
	public function createPayment($orderNo = NULL, $customerId = NULL)
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
		if ($payment->getOriginalPayId()) {
			throw new InvalidArgumentException('You should use paymentRecurrent when origPayId is provided.');
		}

		$data = $payment->toArray();
		$data['signature'] = $this->signature->signPayment($data);

		return $this->processRequest(Message\Request::paymentInit($data));
	}



	/**
	 * RedirectResponse factory for payment/process
	 *
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
		$data['signature'] = $this->signature->simpleSign($data);

		return new Message\RedirectResponse($this->buildUrl('payment/process/:merchantId/:payId/:dttm/:signature', $data));
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

		return $this->processRequest(Message\Request::paymentStatus($data));
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

		return $this->processRequest(Message\Request::paymentReverse($data));
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

		return $this->processRequest(Message\Request::paymentClose($data));
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

		return $this->processRequest(Message\Request::paymentRefund($data));
	}



	/**
	 * @param Payment $payment
	 * @return Message\Response
	 */
	public function paymentRecurrent(Payment $payment)
	{
		if (!$payment->getOriginalPayId()) {
			throw new InvalidArgumentException('The origPayId is required for recurrent payment.');
		}

		$data = $payment->toArray();
		$data['signature'] = $this->signature->signPayment($data);

		return $this->processRequest(Message\Request::paymentRecurrent($data));
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

		return $this->processRequest(Message\Request::customerInfo($data));
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
			'signature',
		], NULL);

		return Message\Response::createFromArray($data)->verify($this->signature);
	}



	/**
	 * @param Message\Request $request
	 * @throws ApiException
	 * @throws PaymentException
	 * @return Message\Response
	 */
	public function processRequest(Message\Request $request)
	{
		$response = NULL;
		try {
			foreach ($this->onRequest as $callback) {
				call_user_func($callback, $request);
			}

			try {
				$httpResponse = $this->sendHttpRequest($request);

			} catch (\Exception $e) {
				throw new ApiException($e->getMessage(), 0, $e);
			}

			switch ($httpResponse->getStatusCode()) {
				case ApiException::S400_BAD_REQUEST:
				case ApiException::S403_FORBIDDEN:
				case ApiException::S404_NOT_FOUND:
					throw new ApiException('Payment is probably in wrong state or request is broken', $httpResponse->getStatusCode());
				case ApiException::S429_TOO_MANY_REQUESTS:
					throw new ApiException('Too Many Requests', $httpResponse->getStatusCode());
				case ApiException::S503_SERVICE_UNAVAILABLE:
					throw new ApiException('Service is down for maintenance', $httpResponse->getStatusCode());
			}

			$decoded = @json_decode($responseBody = $httpResponse->getBody(), TRUE);
			if ($decoded === NULL) {
				throw new ApiException(sprintf('API returned invalid json %s', $responseBody), $httpResponse->getStatusCode());
			}

			if (!isset($decoded['resultCode'])) {
				throw new ApiException(sprintf('The "resultCode" key is missing in response %s', $responseBody), $httpResponse->getStatusCode());
			}

			$response = new Message\Response($decoded, $request);

			if ($decoded['resultCode'] === PaymentException::INTERNAL_ERROR) {
				throw InternalErrorException::fromResponse($decoded, $response);
			}

			if (empty($decoded['signature'])) {
				throw new ApiException(sprintf('The "signature" key is missing or empty in response %s', $responseBody), $httpResponse->getStatusCode());
			}

			$response->verify($this->signature);

			switch ($decoded['resultCode']) {
				case PaymentException::MISSING_PARAMETER:
					throw MissingParameterException::fromResponse($decoded, $response);
				case PaymentException::INVALID_PARAMETER:
					throw InvalidParameterException::fromResponse($decoded, $response);
				case PaymentException::MERCHANT_BLOCKED:
					throw MerchantBlockedException::fromResponse($decoded, $response);
				case PaymentException::SESSION_EXPIRED:
					throw SessionExpiredException::fromResponse($decoded, $response);
				case PaymentException::PAYMENT_NOT_FOUND:
					throw PaymentNotFoundException::fromResponse($decoded, $response);
				case PaymentException::PAYMENT_NOT_IN_VALID_STATE:
					throw PaymentNotInValidStateException::fromResponse($decoded, $response);
			}

			foreach ($this->onResponse as $callback) {
				call_user_func($callback, $response);
			}

			return $response;

		} catch (Exception $e) {
			foreach ($this->onError as $callback) {
				call_user_func($callback, $e, $response);
			}
			throw $e;
		}
	}



	/**
	 * @param Message\Request $request
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	protected function sendHttpRequest(Message\Request $request)
	{
		$data = $request->toArray();

		if (empty($data['signature'])) {
			$data['signature'] = $this->signature->simpleSign($data);
		}

		$url = $this->buildUrl($request->getEndpoint(), $data);

		$headers = [
			'Connection' => 'keep-alive',
			'Expect' => '',
			'Content-Type' => 'application/json',
			'Accept' => 'application/json;charset=UTF-8',
		];

		$body = $request->isMethodGet() ? NULL : json_encode($data);

		return $this->httpClient->request($request->getMethod(), $url, $headers, $body);
	}



	/**
	 * @param $endpoint
	 * @param array $data
	 * @return string
	 */
	protected function buildUrl($endpoint, array $data)
	{
		$endpoint = preg_replace_callback('~\\:(?P<name>[a-z0-9]+)~i', function ($m) use ($data) {
			if (empty($data[$m['name']])) {
				throw new InvalidArgumentException(sprintF('Missing key %s for the assembly of url', $m['name']));
			}
			return urlencode($data[$m['name']]);
		}, $endpoint);

		return $this->config->getUrl() . '/' . $endpoint;
	}



	/**
	 * @return bool|string
	 */
	protected function formatDatetime()
	{
		return date(self::DTTM_FORMAT);
	}

}
