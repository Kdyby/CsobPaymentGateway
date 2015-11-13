<?php

/**
 * Client customer/info
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Certificate\PrivateKey;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\InvalidParameterException;
use Kdyby\CsobPaymentGateway\Message\RedirectResponse;
use Kdyby\CsobPaymentGateway\Message\Signature;
use Kdyby\CsobPaymentGateway\Payment;
use Kdyby\CsobPaymentGateway\PaymentException;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientCustomerInfoTest extends Tester\TestCase
{

	/**
	 * @var Client
	 */
	private $client;



	protected function setUp()
	{
		parent::setUp();
		$this->client = new Client(
			new Configuration('A1029DTmM7', 'Test shop'),
			new Signature(
				new PrivateKey(__DIR__ . '/../../../examples/keys/rsa_A1029DTmM7.key', NULL),
				new PublicKey(Configuration::getCsobSandboxCertPath())
			),
			new HttpClientMock()
		);
	}



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



	protected function tearDown()
	{
		\Mockery::close();
	}

}



\run(new ClientCustomerInfoTest());
