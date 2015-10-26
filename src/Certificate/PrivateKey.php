<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobClient\Certificate;

use Kdyby\CsobClient\IOException;
use Kdyby\CsobClient\MissingExtensionException;
use Kdyby\CsobClient\SigningException;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class PrivateKey
{

	/**
	 * @var string
	 */
	private $privatePath;

	/**
	 * @var string
	 */
	private $password;



	public function __construct($privateKeyPath, $password)
	{
		$this->privatePath = $privateKeyPath;
		$this->password = $password;
	}



	public function sign($text)
	{
		if (!function_exists('openssl_get_privatekey')) {
			throw new MissingExtensionException('OpenSSL extension in PHP is required. Please install or enable it.');
		}

		$privateKey = @file_get_contents($this->privatePath);
		if ($privateKey === FALSE) {
			throw new IOException(sprintf('The private key was not found at %s', $this->privatePath));
		}

		$privateKeyId = openssl_get_privatekey($privateKey, $this->password);
		if ($privateKeyId === FALSE) {
			throw new SigningException(sprintf('Private key %s couldn\'t be opened.', $this->privatePath));
		}

		$res = openssl_sign($text, $signature, $privateKeyId);
		$signature = base64_encode($signature);
		openssl_free_key($privateKeyId);

		if ($res !== TRUE) {
			throw new SigningException('Signing failed');
		}

		return $signature;
	}

}
