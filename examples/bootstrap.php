<?php

use Kdyby\CsobPaymentGateway\Certificate\PrivateKey;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Kdyby\CsobPaymentGateway\Client;
use Kdyby\CsobPaymentGateway\Configuration;
use Bitbang\Http;



require_once __DIR__ . '/../vendor/autoload.php';

function formatHttpMessage(Http\Message $message = NULL)
{
	if ($message === NULL) {
		return '';
	}

	$dump = '';
	$direction = '? ';
	$headers = $message->getHeaders();

	if ($message instanceof Http\Request) {
		$direction = '> ';

		$url = Http\Helpers::parseUrl($message->getUrl());
		$path = '/' . ltrim($url['path'], '/');
		$path .= (!empty($url['query']) ? '?' . http_build_query($url['query'], NULL, '&') : '');
		$path .= (!empty($url['fragment']) ? '#' . $url['fragment'] : '');
		$dump .= $direction . htmlspecialchars($message->getMethod()) . ' ' . htmlspecialchars($path) . ' HTTP/1.1' . "\n";
		$dump .= $direction . 'Host: ' . htmlspecialchars($url['host']) . "\n";

	} elseif ($message instanceof Http\Response) {
		$direction = '< ';

		$dump .= $direction . 'HTTP/1.1 ' . htmlspecialchars($message->getCode()) . ' ' . $headers[''] . "\n";
	}

	foreach ($headers as $key => $val) {
		foreach ((array) $val as $item) {
			$dump .= $direction . htmlspecialchars($key) . ': ' . htmlspecialchars($item) . "\n";
		}
	}

	if ($body = $message->getBody()) {
		$dump .= "\n" . htmlspecialchars($body) . "\n\n";
	}

	return $dump;
}

$config = new Configuration('A1029DTmM7', 'Shopping at ...');
$httpClient = Configuration::createDefaultHttpClient();
$client = new Client(
	$config,
	new PrivateKey(__DIR__ . '/keys/rsa_A1029DTmM7.key', NULL),
	new PublicKey(__DIR__ . '/../resources/mips_iplatebnibrana.csob.cz.pub'),
	$httpClient
);

/** @var \Kdyby\CsobPaymentGateway\Message\Request $lastRequest */
$lastRequest = $lastHttpResponse = $lastHttpRequest = NULL;
$client->onRequest[] = function ($request) use (&$lastRequest) {
	$lastRequest = $request;
};
$httpClient->onRequest(function ($request) use (&$lastHttpRequest) {
	$lastHttpRequest = $request;
});
$httpClient->onResponse(function ($response) use (&$lastHttpResponse) {
	$lastHttpResponse = $response;
});

$selfUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . ltrim(dirname($_SERVER['DOCUMENT_URI']) . '/payment-return.php', '/');
