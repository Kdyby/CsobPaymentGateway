<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobPaymentGateway;

interface Exception
{

}


interface ExceptionWithResponse
{

	/**
	 * @return Message\Response
	 */
	public function getResponse();

}



class InvalidArgumentException extends \InvalidArgumentException implements Exception
{

}



class UnexpectedValueException extends \UnexpectedValueException implements Exception
{

}



class SigningException extends \RuntimeException implements Exception, ExceptionWithResponse
{

	/**
	 * @var Message\Response
	 */
	private $response;



	/**
	 * @return Message\Response
	 */
	public function getResponse()
	{
		return $this->response;
	}



	/**
	 * @param Message\Response $response
	 * @return SigningException
	 */
	public static function fromResponse(Message\Response $response)
	{
		$e = new static('Result signature is incorrect.');
		$e->response = $response;
		return $e;
	}

}



class MissingExtensionException extends \RuntimeException implements Exception
{

}



class IOException extends \RuntimeException implements Exception
{

}



class ApiException extends \RuntimeException implements Exception
{

	const S400_BAD_REQUEST = 400;
	const S403_FORBIDDEN = 403;
	const S404_NOT_FOUND = 404;
	const S405_METHOD_NOT_ALLOWED = 405;
	const S429_TOO_MANY_REQUESTS = 429;
	const S503_SERVICE_UNAVAILABLE = 503;

}



class PaymentException extends \RuntimeException implements Exception, ExceptionWithResponse
{

	const OK = 0; // operace proběhla korektně, transakce založena, stav aktualizován apod
	const MISSING_PARAMETER = 100; // {name} chybějící povinný parametr
	const INVALID_PARAMETER = 110; // {name} chybný formát parametru
	const MERCHANT_BLOCKED = 120; // obchodnik nemá povoleny platby
	const SESSION_EXPIRED = 130; // vypršela platnost požadavku
	const PAYMENT_NOT_FOUND = 140; // platba nenalezena
	const PAYMENT_NOT_IN_VALID_STATE = 150; // nesprávný stav platby, operaci nelze provést
	const CUSTOMER_NOT_FOUND = 800; // zákazník identifikovaný pomocí customerId nenalezen
	const CUSTOMER_HAS_NO_SAVED_CARDS = 810;
	const CUSTOMER_FOUND_SAVED_CARDS = 820;
	const INTERNAL_ERROR = 900; // interní chyba ve zpracování požadavku

	/**
	 * @var Message\Response
	 */
	private $response;



	/**
	 * @return Message\Response
	 */
	public function getResponse()
	{
		return $this->response;
	}



	/**
	 * @param array $data
	 * @param Message\Response $response
	 * @return PaymentException
	 */
	public static function fromResponse(array $data, Message\Response $response)
	{
		$e = new static($data['resultMessage'], (int) $data['resultCode']);
		$e->response = $response;
		return $e;
	}

}



class MissingParameterException extends PaymentException
{

}



class InvalidParameterException extends PaymentException
{

}



class MerchantBlockedException extends PaymentException
{

}



class SessionExpiredException extends PaymentException
{

}



class PaymentNotFoundException extends PaymentException
{

}



class PaymentNotInValidStateException extends PaymentException
{

}



class InternalErrorException extends PaymentException
{

}
