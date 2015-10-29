<?php

require_once __DIR__ . '/../bootstrap.php';

function paymentLog($string)
{
	$line = '[' . date('r') . '] ' . $string . "\n";
	file_put_contents(__DIR__ . '/log/payment.log', $line, FILE_APPEND | LOCK_EX);
}

try {
	$response = $client->receiveResponse($_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET);
	paymentLog(json_encode($response->toArray()));

	echo "<pre>", htmlspecialchars(json_encode($response->toArray(), JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE)), "</pre>\n";

} catch (\Exception $e) {
	paymentLog((string) $e);
}
