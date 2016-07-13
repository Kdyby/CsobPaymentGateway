<?php

namespace Kdyby\CsobPaymentGateway\Message;

use Kdyby\CsobPaymentGateway\Message\Extensions\ExtensionResponseProvider;
use Kdyby\CsobPaymentGateway\Message\Extensions\IExtensionResponse;
use Kdyby\CsobPaymentGateway\SigningException;



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
abstract class Response
{

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var Request|NULL
	 */
	protected $request;



	public function __construct(array $data, Request $request = NULL)
	{
		$this->data = $data;
		$this->request = $request;
	}



	/**
	 * @return Request|NULL
	 */
	public function getRequest()
	{
		return $this->request;
	}



	/**
	 * @return IExtensionResponse[]
	 */
	public function getExtensions()
	{
		$extensions = array_key_exists('extensions', $this->data) ? $this->data['extensions'] : [];
		$responses = [];

		foreach ($extensions as $extension) {
			$extensionName = array_key_exists('extension', $extension) ? $extension['extension'] : NULL;
			$responseClass = ExtensionResponseProvider::getResponseClass($extensionName);

			if ($responseClass !== NULL) {
				$responses[$extensionName] = new $responseClass($extension);
			}
		}

		return $responses;
	}



	/**
	 * @param Signature $signature
	 * @throws SigningException
	 * @return static
	 */
	public function verify(Signature $signature)
	{
		if ($signature->verifyResponse($this->data, $this->data['signature'], $this->getSignaturePriorities()) !== TRUE) {
			throw SigningException::fromResponse($this);
		}

		return $this;
	}



	/**
	 * @return array
	 */
	abstract protected function getSignaturePriorities();



	/**
	 * @return array
	 */
	abstract public function toArray();

}
