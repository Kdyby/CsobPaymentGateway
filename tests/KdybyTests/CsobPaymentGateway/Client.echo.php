<?php

/**
 * Client customer/info
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


/**
 * @author Petr Soukup <soukup@simplia.cz>
 */
class ClientEchoTest extends CsobTestCase {

    public function testEchoGET() {
        $response = $this->client->echoGET();
        Assert::same(0, $response->getResultCode());
    }

    public function testEchoPOST() {
        $response = $this->client->echoPOST();
        Assert::same(0, $response->getResultCode());
    }

}


\run(new ClientEchoTest());
