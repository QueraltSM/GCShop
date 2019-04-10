<?php
include_once 'presentation.class.php';
View::start('GCShop');
View::navigation();
if (isset($_POST["register"])) Product::registerProduct();
else View::showProductRegistrationForm();
View::end();