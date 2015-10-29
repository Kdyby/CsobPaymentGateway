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



	public function paymentInit()
	{

	}



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



	public function paymentStatus($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::paymentStatus($data));
	}



	public function paymentReverse($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::paymentReverse($data));
	}



	public function paymentClose($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::paymentClose($data));
	}



	public function paymentRefund($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::paymentRefund($data));
	}



	public function paymentRecurrent($paymentId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'payId' => $paymentId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::paymentRecurrent($data));
	}



	public function customerInfo($customerId)
	{
		$data = [
			'merchantId' => $this->config->getMerchantId(),
			'customerId' => $customerId,
			'dttm' => $this->formatDatetime(),
		];

		return $this->sendRequest(Message\Request::customerInfo($data));
	}



	public function receiveResponse(array $data)
	{
		if (empty($data)) {
			return NULL;
		}

		static $expectedData = [
			'payId',
			'dttm',
			'resultCode',
			'resultMessage',
			'paymentStatus',
			'authCode',
			'merchantData',
			'signature'
		];
		$data += array_fill_keys($expectedData, NULL);

		return Message\Response::createFromArray($data, $expectedData)->verify($this->publicKey);
	}



	/**
	 * @param Message\Request $request
	 * @return Message\Response
	 */
	public function sendRequest(Message\Request $request)
	{
		try {
			$httpRequest = $this->requestToHttpRequest($request);
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

		return $response->verify($this->publicKey);
	}



	protected function requestToHttpRequest(Message\Request $request)
	{
		$data = $request->toArray();

		if (empty($data['signature'])) {
			$data['signature'] = $this->simpleSign($data);
		}

		$url = $this->buildUrl($request->getEndpoint(), $data, $request->isMethodGet() ? $request->getUrlParams() : []);

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
				throw new InvalidArgumentException(sprintF('Missing key %s for the assembly of url'));
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
		return date('YmdHis');
	}

}
