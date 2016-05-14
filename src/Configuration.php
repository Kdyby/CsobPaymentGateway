<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway;




/**
 * @author Filip Procházka <filip@prochazka.su>
 * @see https://iplatebnibrana.csob.cz/keygen/
 */
class Configuration
{

	const VERSION_1_5 = '1.5';
	const VERSION_1_6 = '1.6';

	const DEFAULT_SANDBOX_URL = 'https://iapi.iplatebnibrana.csob.cz/api';
	const DEFAULT_PRODUCTION_URL = 'https://api.platebnibrana.csob.cz/api';

	CONST DEFAULT_CSOB_SANDBOX_CERT = 'mips_iplatebnibrana.csob.cz.pub';
	CONST DEFAULT_CSOB_PRODUCTION_CERT = 'mips_platebnibrana.csob.cz.pub';

	/**
	 * @var string
	 */
	private $url = self::DEFAULT_SANDBOX_URL;

	/**
	 * @var string
	 */
	private $version = self::VERSION_1_5;

	/**
	 * @var string
	 */
	private $merchantId;

	/**
	 * @var string
	 */
	private $shopName;

	/**
	 * @var string
	 */
	private $returnUrl;

	/**
	 * @var string
	 */
	private $returnMethod = Message\Request::POST;

	/**
	 * @var bool
	 */
	private $checkoutEnabled = FALSE;



	/**
	 * @param string $merchantId
	 * @param string $shopName
	 */
	public function __construct($merchantId, $shopName)
	{
		$this->merchantId = $merchantId;
		$this->shopName = $shopName;
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
	public function buildUrl()
	{
		return $this->url . '/v' . $this->version;
	}



	/**
	 * @param string $url
	 * @return Configuration
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
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
	 * @return string
	 */
	public function getReturnUrl()
	{
		return $this->returnUrl;
	}



	/**
	 * @param string $returnUrl
	 * @return Configuration
	 */
	public function setReturnUrl($returnUrl)
	{
		$this->returnUrl = $returnUrl;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getReturnMethod()
	{
		return $this->returnMethod;
	}



	/**
	 * @param string $returnMethod
	 * @return Configuration
	 */
	public function setReturnMethod($returnMethod)
	{
		if (!in_array($returnMethod, [Message\Request::GET, Message\Request::POST], TRUE)) {
			throw new InvalidArgumentException('Only Message\Request::POST or Message\Request::GET constants are allowed');
		}

		$this->returnMethod = $returnMethod;
		return $this;
	}



	/**
	 * @return bool
	 */
	public function isCheckoutEnabled()
	{
		return $this->checkoutEnabled;
	}



	/**
	 * @param bool $checkoutEnabled
	 * @return Configuration
	 */
	public function setCheckoutEnabled($checkoutEnabled)
	{
		$this->checkoutEnabled = $checkoutEnabled;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}



	/**
	 * @param string $version
	 * @return Configuration
	 */
	public function setVersion($version)
	{
		if (!in_array($version, [self::VERSION_1_5, self::VERSION_1_6], TRUE)) {
			throw new InvalidArgumentException('Only 1.5 and 1.6 eAPI versions are supported');
		}

		$this->version = $version;
		return $this;
	}



	/**
	 * @return string
	 */
	public static function getCsobSandboxCertPath()
	{
		return __DIR__ . '/../resources/' . self::DEFAULT_CSOB_SANDBOX_CERT;
	}



	/**
	 * @return string
	 */
	public static function getCsobProductionCertPath()
	{
		return __DIR__ . '/../resources/' . self::DEFAULT_CSOB_PRODUCTION_CERT;
	}



	/**
	 * @return IHttpClient|Http\GuzzleClient
	 */
	public static function createDefaultHttpClient()
	{
		return new Http\GuzzleClient();
	}

}
