<?php
include_once 'presentation.class.php';
View::start('GCShop');
View::navigation();
if (isset($_POST["search"])) Product::showProducts();
else if (isset($_POST['final_payment'])) Product::finalPayment();
else if (isset($_POST['buy'])) Product::buy();
else View::buyView();
View::end();