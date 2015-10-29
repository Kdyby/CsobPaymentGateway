<?php
require_once __DIR__ . '/../bootstrap.php';
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>

<h2>payment/<?=htmlspecialchars($_GET['action'])?></h2>
<?php

try {
	$payId = $_GET['pay_id'];

	switch ($_GET['action']) {
		case 'status':
			$response = $client->paymentStatus($payId);
			break;
		case 'close':
			$response = $client->paymentClose($payId);
			break;
		case 'reverse':
			$response = $client->paymentReverse($payId);
			break;
		case 'refund':
			$response = $client->paymentRefund($payId);
			break;
		default:
			echo 'unknown action "', htmlspecialchars($_GET['action']), "\"\n";
			exit;
	}

	echo "<pre>", htmlspecialchars(json_encode($response->toArray(), JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE)), "</pre>\n";

} catch (\Exception $e) {
	echo "payment/init failed, reason: \n\n";
	echo htmlspecialchars((string) $e, ENT_QUOTES);
}

?>

<h2>http communication:</h2>
<pre>
<?php
echo formatHttpMessage($lastHttpRequest);
echo formatHttpMessage($lastHttpResponse);
?>
</pre>

<h2>actions</h2>
<a href="payment.php?action=status&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/status</a><br/>
<a href="payment.php?action=close&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/close</a><br/>
<a href="payment.php?action=reverse&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/reverse</a><br/>
<a href="payment.php?action=refund&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/refund</a><br/>

<br/>
<a href="index.php">new FORM payment/init</a><br/>

</body>
</html>
