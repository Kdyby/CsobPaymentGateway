<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$privateKey = new Kdyby\CsobPaymentGateway\Certificate\PrivateKey($privatePath = __DIR__ . '/../keys/rsa_A1029DTmM7.key', NULL);
$publicKey = new Kdyby\CsobPaymentGateway\Certificate\PublicKey($publicPath = __DIR__ . '/../keys/rsa_A1029DTmM7.pub');

?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="bootstrap.min.css">
</head>

<body>

<div class="container">

<h2>sign</h2>
<pre>
<?php

$text = 'some text to sign';
echo 'signing text "' . htmlspecialchars($text) . '" using private key ' . htmlspecialchars(basename($privatePath)), "\n";

$signature = $privateKey->sign($text);
echo 'signature is "' . htmlspecialchars($signature) . "\"\n";

?>
</pre>

<h2>verify</h2>
<pre>
<?php

echo 'verifying signature using public key ' . htmlspecialchars(basename($publicPath)) . "\n";
echo 'result is: ' . ($publicKey->verify($text, $signature) ? 'ok' : 'failed') . "\n";

?>
</pre>

</div>
</body>
</html>
