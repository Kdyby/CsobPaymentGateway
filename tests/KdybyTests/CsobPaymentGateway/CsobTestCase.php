<?php

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Certificate\PrivateKey;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\Message\Signature;
use Tester;



class CsobTestCase extends Tester\TestCase
{

	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @var Configuration
	 */
	protected $configuration;



	protected function setUp()
	{
		parent::setUp();
		$this->client = new Client(
			$this->configuration = new Configuration('A1029DTmM7', 'Test shop'),
			$signature = \Mockery::mock(Signature::class, [
				new PrivateKey(__DIR__ . '/../../../examples/keys/rsa_A1029DTmM7.key', NULL),
				new PublicKey(Configuration::getCsobSandboxCertPath())
			])->shouldDeferMissing(),
			new HttpClientMock()
		);
		$signature->shouldReceive('verifyResponse')->andReturn(TRUE);
	}



	protected function tearDown()
	{
		\Mockery::close();
	}

}
