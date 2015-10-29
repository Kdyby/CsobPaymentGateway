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
 */
class Helpers
{

	/**
	 * @param array $data
	 * @param array $arrayKeys
	 * @return string
	 */
	public static function arrayToSignatureString(array $data, array $arrayKeys = [])
	{
		$str = '';
		foreach ($arrayKeys ?: array_keys($data) as $key) {
			if (!array_key_exists($key, $data) || $data[$key] === NULL) {
				continue; // can skip
			}

			$value = $data[$key];
			if ($value === TRUE) {
				$str .= 'true';

			} elseif ($value === FALSE) {
				$str .= 'false';

			} elseif ($value instanceof \DateTime) {
				$str .= $value->format(Client::DTTM_FORMAT);

			} elseif (is_array($value)) {
				$str .= self::arrayToSignatureString($value);

			} else {
				$str .= (string) $data[$key];
			}

			$str .= '|';
		}

		return rtrim($str, '|');
	}

}
