<?php

/**
 * Client: return checkout
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class ClientReceiveCheckoutTest extends CsobTestCase
{

	public function testReceiveCheckout()
	{
		$data = [
			'payId' => 'fb425174783f9AK',
			'dttm' => '20151109153917',
			'signature' => 'signature',
		];

		$returnResponse = $this->client->receiveCheckout($data);
		Assert::same('fb425174783f9AK', $returnResponse->getPayId());
	}

}



\run(new ClientReceiveCheckoutTest());
