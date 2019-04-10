<?php
include_once 'presentation.class.php';
View::start('GCShop');
View::navigation();
//if (isset($_POST["search"])) Product::showProducts();
View::showProductP();
View::end();