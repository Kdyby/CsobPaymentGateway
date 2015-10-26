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
class PublicKey
{

	/**
	 * @var string
	 */
	private $publicPath;



	public function __construct($bankPublicKeyPath)
	{
		$this->publicPath = $bankPublicKeyPath;
	}



	public function verify($text, $signatureBase64)
	{
		if (!function_exists('openssl_get_privatekey')) {
			throw new MissingExtensionException('OpenSSL extension in PHP is required. Please install or enable it.');
		}

		$publicKey = @file_get_contents($this->publicPath);
		if ($publicKey === FALSE) {
			throw new IOException(sprintf('The public key was not found at %s', $this->publicPath));
		}

		$publicKeyId = openssl_get_publickey($publicKey);
		if ($publicKeyId === FALSE) {
			throw new SigningException(sprintf('Public key %s couldn\'t be opened.', $this->publicPath));
		}

		$signature = base64_decode($signatureBase64);
		$res = openssl_verify($text, $signature, $publicKeyId);
		openssl_free_key($publicKeyId);

		if ($res == -1) {
			throw new SigningException(sprintf('Verification of signature "%s" failed: %s', $text, openssl_error_string()));
		}

		return $res == 1;
	}

}
