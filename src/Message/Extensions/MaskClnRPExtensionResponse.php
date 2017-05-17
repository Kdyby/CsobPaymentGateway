<?php

namespace Kdyby\CsobPaymentGateway\Message\Extensions;

use Kdyby\CsobPaymentGateway\Message\Response;



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class MaskClnRPExtensionResponse extends Response implements IExtensionResponse
{

	const EXTENSION_NAME = 'maskClnRP';



	/**
	 * @return string
	 */
	public function getExtension()
	{
		return self::EXTENSION_NAME;
	}



	/**
	 * @return \DateTime
	 */
	public function getDateTime()
	{
		return \DateTime::createFromFormat('YmdHis', $this->data['dttm']);
	}



	/**
	 * @return string
	 */
	public function getMaskedCln()
	{
		return (string) $this->data['maskedCln'];
	}




	/**
	 * @return string
	 */
	public function getLongMaskedCln()
	{
		return (string) $this->data['longMaskedCln'];
	}



	/**
	 * @return \DateTime
	 */
	public function getExpiration()
	{
		return \DateTime::createFromFormat('d/m/y', '01/' . $this->data['expiration'])
			->setTime(0, 0);
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return [
			'extension' => $this->getExtension(),
			'dttm' => $this->getDateTime(),
			'maskedCln' => $this->getMaskedCln(),
			'expiration' => $this->getExpiration(),
			'longMaskedCln' => $this->getLongMaskedCln(),
		] + $this->data;
	}



	/**
	 * @return array
	 */
	public function getSignaturePriorities()
	{
		return [
			'extension',
			'dttm',
			'maskedCln',
			'expiration',
			'longMaskedCln',
		];
	}

}
