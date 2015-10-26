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



class SigningException extends \RuntimeException implements Exception
{

}


class MissingExtensionException extends \RuntimeException implements Exception
{

}



class IOException extends \RuntimeException implements Exception
{

}
