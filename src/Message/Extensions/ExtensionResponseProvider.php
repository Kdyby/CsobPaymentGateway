<?php

namespace Kdyby\CsobPaymentGateway\Message\Extensions;



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ExtensionResponseProvider
{

	private static $extensions = [
		MaskClnRPExtensionResponse::EXTENSION_NAME => MaskClnRPExtensionResponse::class,
		TrxDatesExtensionResponse::EXTENSION_NAME => TrxDatesExtensionResponse::class,
	];



	private function __construct()
	{
	}



	/**
	 * @param string $extensionName
	 * @return string
	 */
	public static function getResponseClass($extensionName)
	{
		if (isset(self::$extensions[$extensionName])) {
			return self::$extensions[$extensionName];

		} else {
			return NULL;
		}
	}

}
