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
class Payment
{

	const STATUS_REQUESTED = 1;
	const STATUS_PENDING = 2;
	const STATUS_CANCELED = 3;
	const STATUS_APPROVED = 4;
	const STATUS_REVERSED = 5;
	const STATUS_DECLINED = 6;
	const STATUS_TO_CLEARING = 7;
	const STATUS_CLEARED = 8;
	const STATUS_REFUND_REQUESTED = 9;
	const STATUS_REFUNDED = 10;

	const CURRENCY_CZK = 'CZK';
	const CURRENCY_EUR = 'EUR';
	const CURRENCY_USD = 'USD';
	const CURRENCY_GBP = 'GBP';

	const OPERATION_PAYMENT = 'payment';
	const OPERATION_PAYMENT_RECURRENT = 'recurrentPayment';

	const PAY_METHOD_CARD = 'card';

	const LANGUAGE_CZ = 'CZ';
	const LANGUAGE_EN = 'EN';
	const LANGUAGE_DE = 'DE';
	const LANGUAGE_SK = 'SK';

	/**
	 * ID obchodníka přiřazené platební bránou
	 *
	 * @var string
	 */
	private $merchantId;

	/**
	 * Referenční číslo objednávky využívané pro párování plateb, které bude uvedeno také na výpisu z banky. Numerická hodnota, maximální délka 10 číslic.
	 *
	 * @var string
	 */
	private $orderNo;

	/**
	 * payID dříve vytvořené šablony pro opakovanou platbu
	 * @var string
	 */
	private $origPayId;

	/**
	 * Datum a čas odeslání požadavku ve formátu YYYYMMDDHHMMSS
	 *
	 * @var \DateTime
	 */
	private $dttm;

	/**
	 * Typ platební operace.
	 * Povolené hodnoty: self::OPERATION_*
	 *
	 * @var string
	 */
	private $payOperation = self::OPERATION_PAYMENT;

	/**
	 * Typ implicitní platební metody, která bude nabídnuta zákazníkovi.
	 * Povolené hodnoty: self::PAY_METHOD_*
	 *
	 * @var string
	 */
	private $payMethod = self::PAY_METHOD_CARD;

	/**
	 * Celková cena v setinách základní měny.
	 * Tato hodnota bude zobrazena na platební bráně jako celková částka k zaplacení
	 *
	 * @var integer
	 */
	private $totalAmount = 0;

	/**
	 * Kód měny.
	 * Povolené hodnoty: self::CURRENCY_*
	 *
	 * @var string
	 */
	private $currency = self::CURRENCY_CZK;

	/**
	 * Indikuje, zda má být platba automaticky zahrnuta do uzávěrky a proplacena.
	 *
	 * @var bool
	 */
	private $closePayment = TRUE;

	/**
	 * URL adresa, na kterou bude klient přesměrován zpět do e-shopu po dokončení platby. Maximální délka 300 znaků.
	 *
	 * @var string
	 */
	private $returnUrl;

	/**
	 * Metoda návratu na URL adresu e-shopu.
	 * Povolené hodnoty POST, GET.
	 * Doporučená metoda je POST.
	 *
	 * @var string
	 */
	private $returnMethod = Message\Request::POST;

	/**
	 * Seznam položek nákupu, který bude zobrazen na platební bráně.
	 * Obsahuje položky Item.
	 *
	 * @var array
	 */
	private $cart = [];

	/**
	 * Stručný popis nákupu pro 3DS stránku:
	 * V případě ověření klienta na straně vydavatelské banky nelze zobrazit detail košíku jako na platební bráně.
	 * Do banky se proto posílá tento stručný popis nákupu. Maximální délka 255 znaků.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Libovolná pomocná data, která budou vrácena ve zpětném redirectu z platební brány na stránku obchodníka.
	 * Mohou sloužit např pro udržení kontinuity procesu na e-shopu, musí být kódována v BASE64.
	 * Maximální délka po zakódování 255 znaků.
	 *
	 * @var string
	 */
	private $merchantData;

	/**
	 * Jednoznačné ID zákazníka, který přiděluje e-shop. Maximální délka 50 znaků.
	 * Používá se při uložení karty a jejím následném použití při další návštěvě tohoto e-shopu
	 *
	 * @var string
	 */
	private $customerId;

	/**
	 * Preferovaná jazyková mutace, která se zobrazí zákazníkovi na platební bráně.
	 * Defaultně je mutace česká.
	 * Povolené hodnoty: self::LANGUAGE_*
	 *
	 * @var string
	 */
	private $language = self::LANGUAGE_CZ;



	public function __construct($merchantId, $orderNo = NULL, $customerId = NULL)
	{
		if (empty($merchantId)) {
			throw new InvalidArgumentException('The merchantId is required.');
		}

		$this->merchantId = $merchantId;
		$this->setOrderNo($orderNo);
		$this->setCustomerId($customerId);
		$this->dttm = new \DateTime();
	}



	/**
	 * @return string
	 */
	public function getMerchantId()
	{
		return $this->merchantId;
	}



	/**
	 * @param string $orderNo
	 * @return Payment
	 */
	public function setOrderNo($orderNo)
	{
		if ($orderNo !== NULL && (!is_integer($orderNo) || strlen($orderNo) > 10)) {
			throw new InvalidArgumentException('The orderNo is required. It should be strictly integer, with maximum length of 10 digits.');
		}

		$this->orderNo = $orderNo;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getOrderNo()
	{
		return $this->orderNo;
	}



	/**
	 * @return string
	 */
	public function getOriginalPayId()
	{
		return $this->origPayId;
	}



	/**
	 * @param string $origPayId
	 * @return Payment
	 */
	public function setOriginalPayId($origPayId)
	{
		$this->origPayId = $origPayId;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}



	/**
	 * @param string $currency
	 * @return Payment
	 */
	public function setCurrency($currency)
	{
		if (!in_array($currency, [self::CURRENCY_CZK, self::CURRENCY_EUR, self::CURRENCY_GBP, self::CURRENCY_USD], TRUE)) {
			throw new InvalidArgumentException('Only Payment::CURRENCY_* constants are allowed');
		}

		$this->currency = $currency;
		return $this;
	}



	/**
	 * @param \DateTime $dttm
	 * @return Payment
	 */
	public function setDttm(\DateTime $dttm)
	{
		$this->dttm = $dttm;
		return $this;
	}



	/**
	 * @param string $payOperation
	 * @return Payment
	 */
	public function setPayOperation($payOperation)
	{
		if (!in_array($payOperation, [self::OPERATION_PAYMENT, self::OPERATION_PAYMENT_RECURRENT], TRUE)) {
			throw new InvalidArgumentException('Only Payment::OPERATION_* constants are allowed');
		}

		$this->payOperation = $payOperation;
		return $this;
	}



	/**
	 * @param string $payMethod
	 * @return Payment
	 */
	public function setPayMethod($payMethod)
	{
		if (!in_array($payMethod, [self::PAY_METHOD_CARD], TRUE)) {
			throw new InvalidArgumentException('Only Payment::PAY_METHOD_* constants are allowed');
		}

		$this->payMethod = $payMethod;
		return $this;
	}



	/**
	 * @param boolean $closePayment
	 * @return Payment
	 */
	public function setClosePayment($closePayment)
	{
		$this->closePayment = (bool) $closePayment;
		return $this;
	}



	/**
	 * @param string $returnUrl
	 * @return Payment
	 */
	public function setReturnUrl($returnUrl)
	{
		$this->returnUrl = $returnUrl;
		return $this;
	}



	/**
	 * @param string $returnMethod
	 * @return Payment
	 */
	public function setReturnMethod($returnMethod)
	{
		$this->returnMethod = $returnMethod;
		return $this;
	}



	/**
	 * @param string $description
	 * @return Payment
	 */
	public function setDescription($description)
	{
		if (strlen($description) > 255) {
			throw new UnexpectedValueException('The description cannot be longer than 255 characters.');
		}

		$this->description = $description;
		return $this;
	}



	/**
	 * @param string $merchantData
	 * @return Payment
	 */
	public function setMerchantData($merchantData)
	{
		$this->merchantData = $merchantData;
		return $this;
	}



	/**
	 * @param string $customerId
	 * @return Payment
	 */
	public function setCustomerId($customerId)
	{
		if (strlen($customerId) > 50) {
			throw new UnexpectedValueException('The customer id cannot be longer than 50 characters.');
		}

		$this->customerId = $customerId;
		return $this;
	}



	/**
	 * @param string $language
	 * @return Payment
	 */
	public function setLanguage($language)
	{
		if (!in_array($language, [self::LANGUAGE_CZ, self::LANGUAGE_DE, self::LANGUAGE_EN, self::LANGUAGE_SK], TRUE)) {
			throw new InvalidArgumentException('Only Payment::LANGUAGE_* constants are allowed');
		}

		$this->language = $language;
		return $this;
	}



	/**
	 * Please note, that when you wanna provide 100.25 CZK,
	 * you should pass here the number 10025
	 *
	 * @param string $name Maximum of 20 chars
	 * @param integer $price In hundredth of currency units
	 * @param integer $quantity At least 1
	 * @param string $description Maximum of 40 chars
	 * @return Payment
	 */
	public function addCartItem($name, $price, $quantity = 1, $description = NULL)
	{
		if (!is_integer($price)) {
			throw new InvalidArgumentException('Price must be an integer. For example 100.25 must be passed as 10025.');
		}

		if (!is_numeric($quantity) || $quantity < 1) {
			throw new InvalidArgumentException('Quantity must be numeric and larger than zero.');
		}

		if (strlen($name) > 20) {
			throw new InvalidArgumentException('Name cannot be longer than 20 characters.');
		}

		$item = [
			'name' => $name,
			'quantity' => $quantity,
			'amount' => $price,
		];

		if ($description !== NULL) {
			if (strlen($description) > 40) {
				throw new InvalidArgumentException('Description cannot be longer than 40 characters.');
			}

			$item['description'] = $description;
		}

		$this->cart[] = $item;
		$this->totalAmount += $price;

		return $this;
	}



	/**
	 * Cleans the cart.
	 */
	public function removeCartItems()
	{
		$this->cart = [];
		$this->totalAmount = 0;
	}



	/**
	 * @return array
	 */
	public function getCartItems()
	{
		return $this->cart;
	}



	/**
	 * Returns whole integer in hundredth of currency units.
	 *
	 * @return int
	 */
	public function getTotalAmount()
	{
		return $this->totalAmount;
	}



	/**
	 * Returns structure that is required by the API.
	 *
	 * @return array
	 */
	public function toArray()
	{
		if (empty($this->origPayId)) {
			return $this->toPaymentArray();

		} else {
			return $this->toRecurrentArray();
		}
	}



	/**
	 * @return array
	 */
	private function toPaymentArray()
	{
		if (count($this->cart) < 1) {
			throw new UnexpectedValueException('The cart must contain at least one item.');
		}

		if (empty($this->returnUrl)) {
			throw new UnexpectedValueException('The returnUrl is required.');
		}

		if (empty($this->description)) {
			throw new UnexpectedValueException('The description is required.');
		}

		if (empty($this->orderNo)) {
			throw new UnexpectedValueException('The orderNo is required.');
		}

		$data = [
			'merchantId' => $this->merchantId,
			'orderNo' => $this->orderNo,
			'dttm' => $this->dttm->format(Client::DTTM_FORMAT),
			'payOperation' => $this->payOperation,
			'payMethod' => $this->payMethod,
			'totalAmount' => $this->totalAmount,
			'currency' => $this->currency,
			'closePayment' => $this->closePayment,
			'returnUrl' => $this->returnUrl,
			'returnMethod' => $this->returnMethod,
			'cart' => $this->cart,
			'description' => $this->description,
		];

		if ($this->merchantData !== NULL) {
			$data['merchantData'] = base64_encode($this->merchantData);
			if (strlen($data['merchantData']) > 255) {
				throw new UnexpectedValueException('Merchant data cannot be longer than 255 characters after base64 encoding.');
			}
		}

		if ($this->customerId !== NULL) {
			$data['customerId'] = $this->customerId;
		}

		$data['language'] = $this->language;

		return $data;
	}



	/**
	 * @return array
	 */
	private function toRecurrentArray()
	{
		if (empty($this->orderNo)) {
			throw new UnexpectedValueException('The orderNo is required.');
		}

		if (empty($this->origPayId)) {
			throw new UnexpectedValueException('The origPayId is required.');
		}

		$data = [
			'merchantId' => $this->merchantId,
			'origPayId' => $this->origPayId,
			'orderNo' => $this->orderNo,
			'dttm' => $this->dttm->format(Client::DTTM_FORMAT),
		];

		if ($this->totalAmount !== 0) {
			$data['totalAmount'] = $this->totalAmount;
			$data['currency'] = $this->currency;
		}

		if (!empty($this->description)) {
			$data['description'] = $this->description;
		}

		return $data;
	}

}
