<?php

namespace Kdyby\CsobPaymentGateway\Message\Extensions;



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
interface IExtensionResponse
{

	/**
	 * @return string
	 */
	public function getExtension();

}
