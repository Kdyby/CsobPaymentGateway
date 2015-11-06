Kdyby/CsobPaymentGateway
========================

[![Build Status](https://travis-ci.org/Kdyby/CsobPaymentGateway.svg?branch=master)](https://travis-ci.org/Kdyby/CsobPaymentGateway)
[![Downloads this Month](https://img.shields.io/packagist/dm/kdyby/csob-payment-gateway.svg)](https://packagist.org/packages/kdyby/csob-payment-gateway)
[![Latest stable](https://img.shields.io/packagist/v/kdyby/csob-payment-gateway.svg)](https://packagist.org/packages/kdyby/csob-payment-gateway)


Requirements
------------

Kdyby/CsobPaymentGateway requires PHP 5.4.


Installation
------------

The best way to install Kdyby/CsobPaymentGateway is using  [Composer](http://getcomposer.org/):

```sh
$ composer require kdyby/csob-payment-gateway
```

Basic payment example
---------------------

```php
<?php
use Kdyby\CsobPaymentGateway\Certificate;
use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Message\Signature;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\Http\GuzzleClient;

$client = new Client(
    new Configuration('123456', 'example.org'),
    new Signature(
        new Certificate\PrivateKey('csob.key', 'password'),
        new Certificate\PublicKey(Configuration::getCsobSandboxCertPath())
    ),
    new GuzzleClient()
);

$payment = $client->createPayment('ORD0001')
    ->setDescription('Order 0001')
    ->addCartItem('Item 1', 10 * 100, 1)
    ->addCartItem('Item 2', 11 * 100, 1)
    ->setReturnUrl('https://example.org/return');

$paymentResponse = $client->paymentInit($payment);

// redirect to payment
header('Location: ' . $client->paymentProcess($paymentResponse->getPayId())->getUrl());

// payment validation after successful payment
$response = $client->receiveResponse($_POST);
if($response->getPaymentStatus() == 'PAID') {
    // profit!
}
```


Documentation
-------------

- [CSOB payment gateway wiki](https://github.com/csob/paymentgateway/wiki)
- [CSOB eAPI 1.5](https://github.com/csob/paymentgateway/wiki/eAPI-1.5)
- [Client documentation](https://github.com/Kdyby/CsobPaymentGateway/blob/master/docs/en/index.md).


-----

Homepage [http://www.kdyby.org](http://www.kdyby.org) and repository [http://github.com/Kdyby/CsobPaymentGateway](http://github.com/Kdyby/CsobPaymentGateway).
