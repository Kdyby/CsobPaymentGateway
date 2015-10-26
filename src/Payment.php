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
class Payment
{

	const REQUESTED = 1;
	const PENDING = 2;
	const CANCELED = 3;
	const APPROVED = 4;
	const REVERSED = 5;
	const DECLINED = 6;
	const TO_CLEARING = 7;
	const CLEARED = 8;
	const REFUND_REQUESTED = 9;
	const REFUNDED = 10;

}
