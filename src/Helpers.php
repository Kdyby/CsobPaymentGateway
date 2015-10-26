<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobClient;

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
			if (!isset($data[$key]) || $data[$key] === NULL) {
				continue;
			}
			$value = $data[$key];
			if ($value === TRUE) {
				$str .= 'true';

			} elseif ($value === FALSE) {
				$str .= 'false';

			} elseif (is_array($value)) {
				$str .= self::arrayToSignatureString($value, array_keys($value));

			} else {
				$str .= (string) $data[$key];
			}

			$str .= '|';
		}

		return rtrim($str, '|');
	}

}
