<?php

/**
 * Extensions: trxDates
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Message\Extensions\TrxDatesExtensionResponse;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class TrxDatesExtensionTest extends CsobTestCase
{

	public function testExtension()
	{
		$response = $this->client->paymentStatus('TODO'); // TODO
		Assert::same($response->getPayId(), 'TODO');
		Assert::count(1, $response->getExtensions());
		Assert::true(isset($response->getExtensions()[TrxDatesExtensionResponse::EXTENSION_NAME]));

		/** @var TrxDatesExtensionResponse $extensionResponse */
		$extensionResponse = $response->getExtensions()[TrxDatesExtensionResponse::EXTENSION_NAME];

		Assert::type(TrxDatesExtensionResponse::class, $extensionResponse);
		Assert::same(TrxDatesExtensionResponse::EXTENSION_NAME, $extensionResponse->getExtension());
		Assert::same('', $extensionResponse->getCreatedDate());
		Assert::same('', $extensionResponse->getAuthDate());
		Assert::same('', $extensionResponse->getSettlementDate());
	}

}



\run(new TrxDatesExtensionTest());
