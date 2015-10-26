<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobClient;

use Kdyby\CsobClient\Certificate\PrivateKey;
use Kdyby\CsobClient\Certificate\PublicKey;



/**
 * @author Filip Procházka <filip@prochazka.su>
 * @see https://iplatebnibrana.csob.cz/keygen/
 */
class Configuration
{

	const DEFAULT_PRODUCTION_URL = 'https://api.platebnibrana.csob.cz/api/v1.5';
	const DEFAULT_SANDBOX_URL = 'https://iapi.iplatebnibrana.csob.cz/api/v1.5';

	CONST DEFAULT_CSOB_SANDBOX_CERT = 'mips_iplatebnibrana.csob.cz.pub';
	CONST DEFAULT_CSOB_PRODUCTION_CERT = 'mips_platebnibrana.csob.cz.pub';

	/**
	 * @var string
	 */
	private $url = self::DEFAULT_SANDBOX_URL;

	/**
	 * @var string
	 */
	private $merchantId;

	/**
	 * @var string
	 */
	private $shopName;

	/**
	 * @var PrivateKey
	 */
	private $privateKey;

	/**
	 * @var PublicKey
	 */
	private $publicKey;



	/**
	 * @param string $merchantId
	 * @param string $shopName
	 * @param PrivateKey $privateKey
	 * @param PublicKey $publicKey
	 */
	public function __construct($merchantId, $shopName, PrivateKey $privateKey, PublicKey $publicKey)
	{
		$this->merchantId = $merchantId;
		$this->shopName = $shopName;
		$this->privateKey = $privateKey;
		$this->publicKey = $publicKey;
	}



	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}



	/**
	 * @return string
	 */
	public function getMerchantId()
	{
		return $this->merchantId;
	}



	/**
	 * @return string
	 */
	public function getShopName()
	{
		return $this->shopName;
	}



	/**
	 * @return PrivateKey
	 */
	public function getPrivateKey()
	{
		return $this->privateKey;
	}



	/**
	 * @return PublicKey
	 */
	public function getPublicKey()
	{
		return $this->publicKey;
	}



	/**
	 * @param string $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}

}
