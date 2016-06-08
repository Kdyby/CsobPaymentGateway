<?php

namespace KdybyTests\CsobPaymentGateway;

use GuzzleHttp\Psr7\Response;
use Kdyby\CsobPaymentGateway\Http\GuzzleClient;
use Kdyby\CsobPaymentGateway\IHttpClient;
use Psr\Http\Message\ResponseInterface;



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class HttpClientMock implements IHttpClient
{

	/**
	 * @var GuzzleClient
	 */
	private $guzzle;



	public function __construct()
	{
		$this->guzzle = new GuzzleClient();
	}



	/**
	 * @param string $method
	 * @param string $url
	 * @param array $headers
	 * @param string $body
	 * @return ResponseInterface
	 */
	public function request($method, $url, $headers, $body)
	{
		$targetFile = $this->resolveTargetFile($method, $url, $headers, $body);

		if (!file_exists($targetFile)) {
			$response = $this->guzzle->request($method, $url, $headers, $body);
			$data = [
				'status' => $response->getStatusCode(),
				'headers' => $response->getHeaders(),
			];

			$responseBody = $response->getBody()->getContents();
			$data['body'] = json_decode($responseBody, TRUE);
			if (json_last_error() !== JSON_ERROR_NONE) {
				$data['body'] = $responseBody;
			}

			file_put_contents($targetFile, json_encode($data, JSON_PRETTY_PRINT));

		} else {
			$data = json_decode(file_get_contents($targetFile), TRUE);
			$response = new Response($data['status'], $data['headers'], json_encode($data['body']));
		}

		return $response;
	}



	private function resolveTargetFile($method, $url, $headers, $body)
	{
		$parsedUrl = parse_url($url);
		$path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';

		$pieces = explode('/', $path);
		$resource = $pieces[3];
		$action = isset($pieces[4]) ? $pieces[4] : NULL;
		$endpoint = $resource . ($action ? '/' . $action : '');

		$decodedBody = json_decode($body, TRUE);

		switch ($endpoint) {
			case 'payment/init':
				return __DIR__ . '/api-data/init_' . $decodedBody['merchantId'] . '_' . $decodedBody['orderNo'] . '.json';

			case 'payment/recurrent':
				return __DIR__ . '/api-data/recurrent_' . $decodedBody['merchantId'] . '_' . $decodedBody['origPayId'] . '.json';

			case 'payment/oneclick':
				if ($pieces[5] === 'init') {
					return __DIR__ . '/api-data/oneclickInit_' . $decodedBody['merchantId'] . '_' . $decodedBody['origPayId'] . '.json';
				} else {
					return __DIR__ . '/api-data/oneclickStart_' . $decodedBody['merchantId'] . '_' . $decodedBody['payId'] . '.json';
				}

			case 'payment/close':
			case 'payment/reverse':
			case 'payment/refund':
				return __DIR__ . '/api-data/' . $action . '_' . $decodedBody['merchantId'] . '_' . $decodedBody['payId'] . '.json';

			case 'payment/status': // payment/status/:merchantId/:payId
				return __DIR__ . '/api-data/' . $action . '_' . $pieces[5] . '_' . $pieces[6] . '.json';

			case 'customer/info': // customer/info/:merchantId/:customerId
				return __DIR__ . '/api-data/customer_' . $pieces[5] . '_' . $pieces[6] . '.json';

			case 'payment/400':
			case 'payment/403':
			case 'payment/404':
			case 'payment/429':
			case 'payment/503':
				return __DIR__ . '/api-data/error_' . $action . '.json';

			default:
				throw new \LogicException(sprintf('Unexpected %s to endpoint %s', $method, $endpoint));
		}
	}

}
