<?php
include_once 'presentation.class.php';
View::start('GCShop');
View::navigation();
if (isset($_POST["search"])) Product::showProducts();
else if (isset($_GET['buy'])) User::checkUserType();
else if (isset($_GET['delete'])) Product::deleteProduct();
else View::getProducts();
View::end();