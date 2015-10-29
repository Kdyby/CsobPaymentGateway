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
<div class="container">
<h2>preparing payment data ...</h2>
<pre>
<?php
$payment = new \Kdyby\CsobPaymentGateway\Payment($_POST['merchant_id'], (int) $_POST['order_no'], $_POST['customer_id']);
$payment->setReturnUrl($_POST['return_url']);
$payment->setReturnMethod('GET');
$payment->setDescription($_POST['description']);
$payment->addCartItem('Shopping at ...', 100 * $_POST['total_amount'], 1, $_POST['goods_desc']);
$payment->addCartItem('Shipping', 100 * $_POST['shipping_amount'], 1, 'PPL');

echo htmlspecialchars(json_encode($payment->toArray(), JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE));
?>
</pre>

<h2>processing payment/init response:</h2>
<pre>
<?php

$payId = NULL;
try {
	$response = $client->paymentInit($payment);
	echo htmlspecialchars(json_encode($response->toArray(), JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE));

	$payId = $response->getPayId();

} catch (\Exception $e) {
	echo "payment/init failed, reason: \n\n";
	echo htmlspecialchars((string) $e, ENT_QUOTES);
}

?>
</pre>

<h2>http communication:</h2>
<pre>
<?php
echo formatHttpMessage($lastHttpRequest);
echo formatHttpMessage($lastHttpResponse);
?>
</pre>

<?php if ($payId): ?>
	<h2>actions</h2>
	<a href="<?=htmlspecialchars($client->paymentProcess($payId), ENT_QUOTES);?>">payment/process</a><br/>
	<a href="payment.php?action=status&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/status</a><br/>
	<a href="payment.php?action=close&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/close</a><br/>
	<a href="payment.php?action=reverse&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/reverse</a><br/>
	<a href="payment.php?action=refund&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/refund</a><br/>
<?php endif; ?>

<br/>
<a href="index.php">new FORM payment/init</a><br/>

</div>
</body>
</html>
