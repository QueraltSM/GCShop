<?php
include_once 'presentation.class.php';
View::start('GCShop');
View::navigation();

$datos = DB::execute_sql("SELECT * FROM productos");

View::proveedorView($datos);

View::end();