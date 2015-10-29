# Example

This example will help you understand the payment gateway integration faster and in practical terms.
Should you have a problem with your implementation, please study this example before submitting an issue.

Use this example for the integration environment to see how the payment gateway communicates.
In order to talk to the gateway, please place your private key for message signing into directory 'keys'.
The gateway's public key for response signature verification (mips_iplatebnibrana.csob.cz.pub) is already provided in the same directory.
Then setup `$merchantId` and `$privateKey` in `setup.php`.

Please note this code is intended for demonstration purposes only and should not be deployed on production systems.
When building your own production implementation, please make sure that the directory in which you store your private key for message signing is secured.


## SECURITY WARNING

Having keys placed in directory under document root is a serious security risk.
That's why there is a `www/` directory


## Installation:

1. copy content of this directory into php server
2. make sure that only the directory `www/` is a document root for your webserver
3. place you private key for message signing into directory 'keys'
4. setup `$merchantId` and `$privateKey` in `setup.php`


## Copyright

This example is based on the official CSOB Payment Gateway integration example
https://github.com/csob/paymentgateway/tree/master/eshop-integration/eAPI/v1.5/php/example
