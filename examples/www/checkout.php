<?php
require_once __DIR__ . '/../bootstrap.php';
$payId = $_GET['payId'];
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="bootstrap.min.css">
</head>

<body>
<div class="container">
	<h2>payment/checkout</h2>
	<p>Keep in mind that the checkout operation must be enabled by the bank for your merchant for this to work.</p>
	<?php
	$checkoutRequest = new \Kdyby\CsobPaymentGateway\CheckoutRequest($payId, \Kdyby\CsobPaymentGateway\CheckoutRequest::ONECLICK_CHECKBOX_HIDE);
	$checkoutRequest->setReturnCheckoutUrl($selfUrl . 'checkout.php');
	?>
	<iframe width="500" height="600" src="<?=htmlspecialchars($client->paymentCheckout($checkoutRequest), ENT_QUOTES);?>"></iframe>

	<h2>actions</h2>
	<a href="payment.php?action=status&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/status</a><br/>
	<a href="payment.php?action=close&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/close</a><br/>
	<a href="payment.php?action=reverse&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/reverse</a><br/>
	<a href="payment.php?action=refund&pay_id=<?=htmlspecialchars($payId, ENT_QUOTES);?>">payment/refund</a><br/>

	<br/>
	<a href="index.php">new FORM payment/init</a>
</div>
</body>
</html>
