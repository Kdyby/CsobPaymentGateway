<?php

namespace Kdyby\CsobPaymentGateway\Message\Extensions;

use Kdyby\CsobPaymentGateway\Message\Response;



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class TrxDatesExtensionResponse extends Response implements IExtensionResponse
{

	const EXTENSION_NAME = 'trxDates';



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
	 * @return \DateTime
	 */
	public function getCreatedDate()
	{
		return \DateTime::createFromFormat('Y-M-D\TH:i:s.uZ', $this->data['createdDate'], new \DateTimeZone('UTC'));
	}



	/**
	 * @return \DateTime|NULL
	 */
	public function getAuthDate()
	{
		return array_key_exists('authDate', $this->data)
			? \DateTime::createFromFormat('ymdHis', $this->data['authDate'])
			: NULL;
	}



	/**
	 * @return \DateTime|NULL
	 */
	public function getSettlementDate()
	{
		return array_key_exists('settlementDate', $this->data)
			? \DateTime::createFromFormat('Ymd', $this->data['settlementDate'])->setTime(0, 0)
			: NULL;
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return [
			'extension' => $this->getExtension(),
			'dttm' => $this->getDateTime(),
			'createdDate' => $this->getCreatedDate(),
			'authDate' => $this->getAuthDate(),
			'settlementDate' => $this->getSettlementDate(),
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
			'createdDate',
			'authDate',
			'settlementDate',
		];
	}

}
