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

		$request = Message\Request::paymentProcess($data);

		if ($this->logger) {
			$this->logger->info($request->getEndpointName(), ['request' => $request->toArray()]);
		}

		foreach ($this->onRequest as $callback) {
			call_user_func($callback, $request);
		}

		$data['signature'] = $this->signature->simpleSign($data);

		return new Message\RedirectResponse($this->buildUrl($request->getEndpoint(), $data));
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
	 * @param int $totalAmount In hundredth of currency units
	 * @return Message\Response
	 */
	public function paymentClose($paymentId, $totalAmount = NULL)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		if ($totalAmount !== NULL) {
			if (!is_integer($totalAmount)) {
				throw new InvalidArgumentException('Amount must be an integer. For example 100.25 must be passed as 10025.');
			}

			$data['totalAmount'] = $totalAmount;
		}

		return $this->processRequest(Message\Request::paymentClose($data));
	}



	/**
	 * Please note, that when you wanna provide 100.25 CZK,
	 * you should pass here the number 10025
	 *
	 * @param string $paymentId
	 * @param int $amount In hundredth of currency units
	 * @return Message\Response
	 */
	public function paymentRefund($paymentId, $amount = NULL)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		if ($amount !== NULL) {
			if (!is_integer($amount)) {
				throw new InvalidArgumentException('Amount must be an integer. For example 100.25 must be passed as 10025.');
			}

			$data['amount'] = $amount;
		}

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

		$response = Message\Response::createFromArray($data);

		// todo: call onResponse?

		if ($this->logger) {
			$logParams = $data;
			unset($logParams['signature']);
			$this->logger->info('payment/process', ['response' => $logParams]);
		}

		$response->verify($this->signature);

		$this->handleInvalidPaymentStatus($response);

		return $response;
	}



	/**
	 * @param Message\Request $request
	 * @throws ApiException
	 * @throws PaymentException
	 * @throws SigningException
	 * @return Message\Response
	 */
	public function processRequest(Message\Request $request)
	{
		$response = NULL;
		try {
			foreach ($this->onRequest as $callback) {
				call_user_func($callback, $request);
			}

			$httpResponse = $this->sendHttpRequest($request);

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

			$this->handleInvalidPaymentStatus($response);

			foreach ($this->onResponse as $callback) {
				call_user_func($callback, $request, $response);
			}

			$this->logRequest($request, $response);

			return $response;

		} catch (\Exception $e) {
			$this->logRequest($request, $response, $e);

			if ($e instanceof Exception) {
				foreach ($this->onError as $callback) {
					call_user_func($callback, $request, $e, $response);
				}
			}

			throw $e;
		}
	}



	protected function handleInvalidPaymentStatus(Message\Response $response)
	{
		switch ($response->getPaymentStatus()) {
			case Payment::STATUS_CANCELED:
				throw PaymentCanceledException::fromResponse($response->toArray(), $response);
			case Payment::STATUS_DECLINED:
				throw PaymentDeclinedException::fromResponse($response->toArray(), $response);
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

		try {
			return $this->httpClient->request($request->getMethod(), $url, $headers, $body);

		} catch (\Exception $httpException) {
			throw new ApiException($httpException->getMessage(), 0, $httpException);
		}
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



	protected function logRequest(Message\Request $request, Message\Response $response = NULL, \Exception $exception = NULL)
	{
		if (!$this->logger) {
			return;
		}

		$context = [
			'request' => $request->toArray(),
			'response' => $response ? $response->toArray() : NULL
		];
		$responseMsg = $response ? $response->getResultMessage() : NULL;

		unset($context['request']['signature']);
		unset($context['response']['signature']);

		if ($exception === NULL) {
			$this->logger->info($request->getEndpointName() . ($responseMsg ? ': ' . $responseMsg : NULL), $context);

		} else {
			$context['exception'] = [
				'type' => get_class($exception),
				'code' => $exception->getCode(),
				'message' => $exception->getMessage(),
			];

			$this->logger->error($request->getEndpointName() . ($responseMsg ? ': ' . $responseMsg : NULL), $context);
		}
	}

}
