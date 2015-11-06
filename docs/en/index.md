Quickstart
==========

This extension provides a client library for ČSOB Payment Gateway.

- [CSOB payment gateway wiki](https://github.com/csob/paymentgateway/wiki)
- [CSOB eAPI 1.5](https://github.com/csob/paymentgateway/wiki/eAPI-1.5)


Installation
-----------

The best way to install Kdyby/CsobPaymentGateway is using [Composer](http://getcomposer.org/):

```sh
$ composer require kdyby/csob-payment-gateway
```

You also need to choose a PSR7 compatible HTTP Client.
This library contains a default `GuzzleClient`, but Guzzle is not installed by default.

It is recommended to just use Guzzle, so you should also install it using Composer.

```sh
$ composer require guzzlehttp/guzzle
```

If you don't wanna use Guzzle, that's fine, you're just gonna have implement your own `Kdyby\CsobPaymentGateway\IHttpClient`.


Setup sandbox
-------------

```php
use Kdyby\CsobPaymentGateway\Certificate;
use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Message\Signature;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\Http\GuzzleClient;

$client = new Client(
    new Configuration('123456', 'example.org'),
    new Signature(
        new Certificate\PrivateKey(__DIR__ . '/private/sandbox/csob.key', 'password'),
        new Certificate\PublicKey(Configuration::getCsobSandboxCertPath())
    ),
    new GuzzleClient()
);
```


Processing a payment
--------------------

First, you have to configure a `Payment` object and initialize the payment over CSOB API.
Then you can redirect the user to the gateway.

**WARNING:** Please note, that all the prices are in hundredths of currency units.
It means that when you wanna init a payment for 100.25 CZK, you should pass here the integer 10025.


```php
$payment = $client->createPayment(123)
    ->setDescription('Order 123')
    ->addCartItem('Item 1', 10 * 100, 1)
    ->addCartItem('Item 2', 11 * 100, 1)
    ->setReturnUrl('https://example.org/return');

$paymentResponse = $client->paymentInit($payment);

// redirect to payment
header('Location: ' . $client->paymentProcess($paymentResponse->getPayId())->getUrl());
```

After the user pays (or not) the gateway should redirect to the provided `returnUrl`, where you can handle the response.

```php
use Kdyby\CsobPaymentGateway\Payment;

// payment validation after successful payment
$response = $client->receiveResponse($_POST);
if($response->getPaymentStatus() === Payment::STATUS_APPROVED) {
    // profit!
}
```

Please refer to [the CSOB documentation](https://github.com/csob/paymentgateway/wiki/eAPI-1.5#return-url---n%C3%A1vrat-do-e-shopu-) and learn what states you should to check,
they should be all available as `Payment::STATUS_*` constants.



Going to production
-------------------

When you've completed integrating the gateway into your application, you have to tell the client, that you wanna use production settings.

- change the `url` of api endpoint in `Configuration`
- change path to `PrivateKey` certificate to your production certificate
- change path to `PublicKey` certificate to the production one

It could look like this

```php
$config = new Configuration('123456', 'example.org');
$config->setUrl(Configuration::DEFAULT_PRODUCTION_URL);

$client = new Client(
    $config,
    new Signature(
        new Certificate\PrivateKey(__DIR__ . '/private/production/csob.key', 'password'),
        new Certificate\PublicKey(Configuration::getCsobProductionCertPath())
    ),
    new GuzzleClient()
);
```
