<?php

namespace Kdyby\CsobPaymentGateway\Message;


class CustomerResponse extends Response
{

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
	public function getCustomerId()
	{
		return array_key_exists('customerId', $this->data) ? (string) $this->data['customerId'] : NULL;
	}



	/**
	 * @return int
	 */
	public function getResultCode()
	{
		return array_key_exists('resultCode', $this->data) ? (int) $this->data['resultCode'] : NULL;
	}



	/**
	 * @return string
	 */
	public function getResultMessage()
	{
		return array_key_exists('resultMessage', $this->data) ? $this->data['resultMessage'] : NULL;
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return [
			'customerId' => $this->getCustomerId(),
			'dttm' => $this->getDateTime(),
			'resultCode' => $this->getResultCode(),
			'resultMessage' => $this->getResultMessage(),
		];
	}



	/**
	 * @return array
	 */
	public function getSignaturePriorities()
	{
		return [
			'customerId',
			'dttm',
			'resultCode',
			'resultMessage',
		];
	}

}
