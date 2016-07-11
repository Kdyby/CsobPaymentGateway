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
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class CheckoutRequest
{

	const ONECLICK_CHECKBOX_HIDE = 0;
	const ONECLICK_CHECKBOX_SHOW_UNCHECKED = 1;
	const ONECLICK_CHECKBOX_SHOW_CHECKED = 2;


	/**
	 * @var string
	 */
	private $paymentId;

	/**
	 * Příznak, zda zobrazovat a pokud ano, tak jak předvyplnit checkbox pro uložení karty pro budoucí platby na klik.
	 * Povolené hodnoty 0 (nezobrazeno), 1 (zobrazeno a nezaškrtnuto), 2 (zobrazeno a zaškrtnuto).
	 *
	 * @var int
	 */
	private $oneclickPaymentCheckbox;

	/**
	 * Příznak, zda zobrazovat v desktop iframe verzi platební brány omnibox. Povolené hodnoty true (zobrazen omnibox),
	 * false (místo omniboxu zobrazeny tři pole pro zadání čísla karty, expirace a cvc). Default hodnota je false.
	 *
	 * @var bool
	 */
	private $displayOmnibox = FALSE;

	/**
	 * URL pro návrat zpět na eshop na stránku checkoutu, nepovinný parametr, hodnota parametru musí být URL encoded
	 * (tak, aby se správně přenesla v GET požadavku na server platební brány).
	 *
	 * @var string|NULL
	 */
	private $returnCheckoutUrl;


	/**
	 * @param string $paymentId
	 * @param int $oneclickPaymentCheckbox
	 */
	public function __construct($paymentId, $oneclickPaymentCheckbox)
	{
		$this->paymentId = $paymentId;
		$this->setOneclickPaymentCheckbox($oneclickPaymentCheckbox);
	}


	/**
	 * @return string
	 */
	public function getPaymentId()
	{
		return $this->paymentId;
	}


	/**
	 * @return int
	 */
	public function getOneclickPaymentCheckbox()
	{
		return $this->oneclickPaymentCheckbox;
	}


	/**
	 * @param int $oneclickPaymentCheckbox
	 * @return CheckoutRequest
	 */
	public function setOneclickPaymentCheckbox($oneclickPaymentCheckbox)
	{
		if (!in_array($oneclickPaymentCheckbox, [self::ONECLICK_CHECKBOX_HIDE, self::ONECLICK_CHECKBOX_SHOW_CHECKED, self::ONECLICK_CHECKBOX_SHOW_UNCHECKED], TRUE)) {
			throw new InvalidArgumentException('Only CheckoutRequest::ONECLICK_CHECKBOX_* constants are allowed');
		}

		$this->oneclickPaymentCheckbox = $oneclickPaymentCheckbox;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function getDisplayOmnibox()
	{
		return $this->displayOmnibox;
	}


	/**
	 * @param bool $displayOmnibox
	 * @return CheckoutRequest
	 */
	public function setDisplayOmnibox($displayOmnibox)
	{
		$this->displayOmnibox = (bool) $displayOmnibox;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getReturnCheckoutUrl()
	{
		return $this->returnCheckoutUrl;
	}


	/**
	 * @param string $returnCheckoutUrl
	 * @return CheckoutRequest
	 */
	public function setReturnCheckoutUrl($returnCheckoutUrl)
	{
		$this->returnCheckoutUrl = (string) $returnCheckoutUrl;
		return $this;
	}

}
