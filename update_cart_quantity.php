<?php
include_once 'presentation.class.php';
View::start('GCShop');
View::navigation();
$res = new stdClass();
$res->added=false; 
$res->message=''; 
$res->value=0; 
try {
    $datoscrudos = file_get_contents("php://input"); //Leemos los datos
    $datos = json_decode($datoscrudos);
    $id = $datos->id;
    $units = $datos->units;
    $res->added=Product::addProductShoppingList($id,$units);
    $res->value=Product::getProductsInBasket();
} catch(Exception $e){ 
    $res->message="Se ha producido una excepción en el servidor: ".$e->getMessage(); 
}
header('Content-type: application/json');
echo json_encode($res);
View::end();