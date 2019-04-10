<?php
include_once 'presentation.class.php';
$res = new stdClass();
try {
    if(isset($_POST['search'])){
        $res -> search = true; //búsqueda correcta
        $sql = "SELECT nombre,precio,imagen FROM productos WHERE nombre LIKE ?;";
        $var = array("%{$_POST['search']}%");
        $inst = DB::execute_sql($sql,$var);
        $datos =  DB::sqlFetch($inst);
        foreach($datos as $dato){
            $res -> name[] = $dato['nombre'];
            $res -> price[] = $dato['precio'];
            $res -> image[] = base64_encode($dato['imagen']);
        }
    }else $res -> search = false; 
} catch(Exception $e){ 
    $res->message="Se ha producido una excepción en el servidor: ".$e->getMessage(); 
}
header('Content-type: application/json');
echo json_encode($res);