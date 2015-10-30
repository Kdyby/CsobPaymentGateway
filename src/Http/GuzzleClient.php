<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway\Http;

use GuzzleHttp;
use Kdyby;
use Nette;
use Psr\Http\Message\ResponseInterface;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class GuzzleClient implements Kdyby\CsobPaymentGateway\IHttpClient
{

	/**
	 * @var array|callable[]
	 */
	public $onRequest = [];

	/**
	 * @var array|callable[]
	 */
	public $onResponse = [];

	/**
	 * @var GuzzleHttp\Client
	 */
	private $client;



	/**
	 * @param array $config Default options for Guzzle client
	 */
	public function __construct(array $config = [])
	{
		$this->client = new GuzzleHttp\Client($config);
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
		$request = new GuzzleHttp\Psr7\Request($method, $url, $headers, $body);

		foreach ($this->onRequest as $callback) {
			call_user_func($callback, $request);
		}

		$response = $this->client->send($request);

		foreach ($this->onResponse as $callback) {
			call_user_func($callback, $response);
		}

		return $response;
	}

}
