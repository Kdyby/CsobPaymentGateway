<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway;

use Kdyby;
use Nette;
use Psr\Http\Message\ResponseInterface;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
interface IHttpClient
{

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $headers
	 * @param string $body
	 * @return ResponseInterface
	 */
	public function request($method, $url, $headers, $body);

}
