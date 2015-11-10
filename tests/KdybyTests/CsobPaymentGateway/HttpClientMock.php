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
		$parsedUrl = parse_url($url);
		$path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';

		if ($path === '/api/v1.5/payment/init' && $method === 'POST') {
			$decodedBody = json_decode($body, TRUE);
			$targetFile = __DIR__ . '/api-data/init_' . $decodedBody['merchantId'] . '_' . $decodedBody['orderNo'] . '.json';

		} elseif (strpos($path, '/api/v1.5/payment/status') === 0 && $method === 'GET') {
			list(,,,,, $merchantId, $payId) = explode('/', $path);
			$targetFile = __DIR__ . '/api-data/status_' . $merchantId . '_' . $payId . '.json';

		} else {
			throw new \LogicException(sprintf('Unexpected %s to endpoint %s', $method, $path));
		}

		if (!file_exists($targetFile)) {
			$response = $this->guzzle->request($method, $url, $headers, $body);
			$data = [
					'status' => $response->getStatusCode(),
					'headers' => $response->getHeaders(),
					'body' => $response->getBody()->getContents(),
			];
			file_put_contents($targetFile, json_encode($data));

		} else {
			$data = json_decode(file_get_contents($targetFile), TRUE);
			$response = new Response($data['status'], $data['headers'], $data['body']);
		}

		return $response;
	}

}
