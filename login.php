<?php 
include_once 'business.class.php';
include_once 'presentation.class.php';
View::start('GCShop');
View::navigation();
if(isset($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
    $passw = $_POST['contraseña'];
    if (User::login($nombre, $passw) == true) header('Location: index.php');
    else View::userError();
} else View::userLogin(); 
View::end();