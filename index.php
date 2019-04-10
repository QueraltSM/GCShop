<?php
include_once 'presentation.class.php';
View::start('GCShop');
View::navigation();

if (!isset($_SESSION['products'])) $_SESSION['products'] = array();

$user = User::getLoggedUser();

if (isset($_GET["admin_show_inform"])) getAdminView($_GET["admin_show_inform"]); // admin 
else View::showSearch();

// Admin View
function getAdminView($show){
    if ($show == "ventas") View::getSales();
    else if ($show == "stock") View::getStock();
}
function getAccount($idcliente) {
    $inst = DB::execute_sql("SELECT nombre,cuenta FROM usuarios WHERE id=?",array($idcliente));
    $datos =  DB::sqlFetch($inst);
    $data = array();
    foreach($datos as $registro){
        $data[0] = $registro['nombre'];
        $data[1] = $registro['cuenta'];
    }
    return $data;
}
View::end();