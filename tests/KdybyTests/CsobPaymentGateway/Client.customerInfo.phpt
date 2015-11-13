<?php

/**
 * Client customer/info
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\PaymentException;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientCustomerInfoTest extends CsobTestCase
{

	public function testCustomerInfo()
	{
		$response = $this->client->customerInfo('1234');
		Assert::same(PaymentException::CUSTOMER_FOUND_SAVED_CARDS, $response->getResultCode());
		Assert::same('Customer found, found saved card(s)', $response->getResultMessage());

		$response = $this->client->customerInfo('4123');
		Assert::same(PaymentException::CUSTOMER_HAS_NO_SAVED_CARDS, $response->getResultCode());
		Assert::same('Customer found, no saved card(s)', $response->getResultMessage());

		$response = $this->client->customerInfo('doesNotExist');
		Assert::same(PaymentException::CUSTOMER_NOT_FOUND, $response->getResultCode());
		Assert::same('Customer not found', $response->getResultMessage());
	}

}



\run(new ClientCustomerInfoTest());
