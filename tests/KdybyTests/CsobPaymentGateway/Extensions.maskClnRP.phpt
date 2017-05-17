<?php

/**
 * Extensions: trxDates
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby\CsobPaymentGateway\Message\Extensions\MaskClnRPExtensionResponse;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jiří Pudil <me@jiripudil.cz>
 */
class MaskClnRPExtensionTest extends CsobTestCase
{

	public function testExtension()
	{
		$response = $this->client->paymentStatus('TODO'); // TODO
		Assert::same($response->getPayId(), 'TODO');
		Assert::count(1, $response->getExtensions());
		Assert::true(isset($response->getExtensions()[MaskClnRPExtensionResponse::EXTENSION_NAME]));

		/** @var MaskClnRPExtensionResponse $extensionResponse */
		$extensionResponse = $response->getExtensions()[MaskClnRPExtensionResponse::EXTENSION_NAME];

		Assert::type(MaskClnRPExtensionResponse::class, $extensionResponse);
		Assert::same(MaskClnRPExtensionResponse::EXTENSION_NAME, $extensionResponse->getExtension());
		Assert::same('****0202', $extensionResponse->getMaskedCln());
		Assert::equal(new \DateTime('2020-12-01'), $extensionResponse->getExpiration());
		Assert::same('516844****0202', $extensionResponse->getLongMaskedCln());
	}

}



\run(new MaskClnRPExtensionTest());
