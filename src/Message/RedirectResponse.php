<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\CsobClient\Message;

/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RedirectResponse
{

	/**
	 * @var string
	 */
	private $url;



	public function __construct($url)
	{
		$this->url = $url;
	}



	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

}
