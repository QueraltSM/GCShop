<?php
include_once 'presentation.class.php';
View::start('GCShop');
View::navigation();
if (isset($_POST["modify"])) Product::modifyProduct();
else View::showProduct();
View::end();