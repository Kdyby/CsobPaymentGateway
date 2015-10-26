<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobClient;

interface Exception
{

}



class InvalidArgumentException extends \InvalidArgumentException implements Exception
{

}



class SigningException extends \RuntimeException implements Exception
{

}



class MissingExtensionException extends \RuntimeException implements Exception
{

}



class IOException extends \RuntimeException implements Exception
{

}



class HttpClientException extends \RuntimeException implements Exception
{

}



class PaymentApiException extends \RuntimeException implements Exception
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
	 * @param array $data
	 * @param Message\Response $response
	 * @return PaymentApiException
	 */
	public static function fromResponse(array $data, Message\Response $response)
	{
		$e = new static($data['resultMessage'], (int) $data['resultCode']);
		$e->response = $response;
		return $e;
	}

}
