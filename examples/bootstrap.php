<?php

use Kdyby\CsobPaymentGateway\Certificate\PrivateKey;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Configuration;
use Kdyby\CsobPaymentGateway\Message\Signature;
use Psr\Http;



require_once __DIR__ . '/../vendor/autoload.php';

function formatHttpMessage(Http\Message\MessageInterface $message = NULL)
{
	if ($message === NULL) {
		return '';
	}

	$dump = '';
	$direction = '? ';
	$headers = $message->getHeaders();

	if ($message instanceof Http\Message\RequestInterface) {
		$direction = '> ';

		$uri = $message->getUri();
		$path = $uri->getPath();
		$path .= ($uri->getQuery() ? '?' . $uri->getQuery() : '');
		$path .= ($uri->getFragment() ? '#' . $uri->getFragment() : '');
		$dump .= $direction . htmlspecialchars($message->getMethod());
		$dump .= ' ' . htmlspecialchars($path) . ' HTTP/' . $message->getProtocolVersion() . "\n";

	} elseif ($message instanceof Http\Message\ResponseInterface) {
		$direction = '< ';

		$dump .= $direction . 'HTTP/ ' . $message->getProtocolVersion();
		$dump .= ' ' . htmlspecialchars($message->getStatusCode()) . ' ' . $headers[''] . "\n";
	}

	foreach ($headers as $key => $val) {
		foreach ((array) $val as $item) {
			$dump .= $direction . htmlspecialchars($key) . ': ' . htmlspecialchars($item) . "\n";
		}
	}

	if ($body = (string) $message->getBody()) {
		$dump .= "\n" . htmlspecialchars($body) . "\n\n";
	}

	return $dump;
}

$config = new Configuration('A1029DTmM7', 'Shopping at ...');
$config->setCheckoutEnabled(TRUE);

$httpClient = Configuration::createDefaultHttpClient();
$client = new Client(
	$config,
	new Signature(
		new PrivateKey(__DIR__ . '/keys/rsa_A1029DTmM7.key', NULL),
		new PublicKey(__DIR__ . '/../resources/mips_iplatebnibrana.csob.cz.pub')
	),
	$httpClient
);

/** @var \Kdyby\CsobPaymentGateway\Message\Request $lastRequest */
$lastRequest = $lastHttpResponse = $lastHttpRequest = NULL;
$client->onRequest[] = function ($request) use (&$lastRequest) {
	$lastRequest = $request;
};
$httpClient->onRequest[] = function ($request) use (&$lastHttpRequest) {
	$lastHttpRequest = $request;
};
$httpClient->onResponse[] = function ($response) use (&$lastHttpResponse) {
	$lastHttpResponse = $response;
};

$selfUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . ltrim(dirname($_SERVER['DOCUMENT_URI']), '/');
