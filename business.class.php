<?php
include_once 'data_access.class.php';
include_once 'presentation.class.php';
class User{
    public static function session_start(){
        if(session_status () === PHP_SESSION_NONE){
            session_start();
        }
    }
    
    public static function getLoggedUser(){ //Devuelve un array con los datos del cuenta o false
        self::session_start();
        if(!isset($_SESSION['user'])) return false;
        return $_SESSION['user'];
    }
    
    public static function login($usuario,$pass){ //Devuelve verdadero o falso según
        self::session_start();
        if(DB::user_exists($usuario, $pass, $res)){
            $_SESSION['user']=$res[0]; //Almacena datos del usuario en la sesión
            return true;
        }
        return false;
    }
    
    public static function logout(){
        self::session_start();
        unset($_SESSION['user']);
    }
    
    public static function checkUserType() {
        if (!isset($_SESSION['user'])) View::loginMsgView();
        else {
            $idproducto = $_GET['buy'];
            header("Location: buy.php?product_id=$idproducto");
            exit;
        }
    }
}

class Product{
    public static function showProducts(){
        $nombre = $_POST['nombre'];
        $tipo = $_POST['tipo'];
        $var = null;
        if ($nombre != "" && $tipo != "Todos") {
            $sql = "SELECT id,nombre,tipo,descripcion,precio,imagen FROM productos WHERE nombre LIKE ? AND tipo = ?";
            $var =array("%$nombre%",$tipo);
        } else if ($nombre != "" && $tipo == "Todos") {
            $sql = "SELECT id,nombre,tipo,descripcion,precio,imagen FROM productos WHERE nombre LIKE ?";
            $var = array("%$nombre%");
        } else if ($nombre == "" && $tipo != "Todos") {
            $sql = "SELECT id,nombre,tipo,descripcion,precio,imagen FROM productos WHERE tipo = ?";
            $var =array($tipo);
        } else {
            $sql = "SELECT id,nombre,tipo,descripcion,precio,imagen FROM productos";
        }
        $inst = DB::execute_sql($sql,$var);
        $datos =  DB::sqlFetch($inst);
        View::showProductsView($datos);
    }
    
    public static function executeUpdate($nombre,$valor) {
        $id=$_GET["id"];
        $res = DB::execute_sql("UPDATE productos SET $nombre = ? WHERE id = ?",array($valor,$id));
        View::productoModificado($res, $nombre);
    }
    
    public static function registerProduct() {
        $db = new PDO("sqlite:./datos.db");
        $db->exec('PRAGMA foreign_keys = ON;');
        $db-> beginTransaction();
        $image_base64 = base64_encode(file_get_contents($_FILES['fileUpload']['tmp_name']));
        $image = base64_decode($image_base64);
        $res = DB::execute_sql("INSERT INTO productos (codigo, nombre, tipo, 
        descripcion, precio, stock, imagen) VALUES (?,?,?,?,?,?,?)", 
        array($_POST['codigo'], $_POST['nombre'], $_POST['tipo'], 
        $_POST['descripcion'],$_POST['precio'],$_POST['stock'],$image));
        View::productoRegistrado($res, $db);
    }
    
    public static function getProduct($id){
        $inst = DB::execute_sql("SELECT * FROM productos WHERE id = ?",array($id)); 
        $datos =  DB::sqlFetch($inst);
        foreach($datos as $registro){ 
            $codigo=$registro['codigo'];
            $nombre=$registro['nombre'];
            $tipo=$registro['tipo'];
            $descripcion=$registro['descripcion'];
            $precio=$registro['precio'];
            $stock=$registro['stock'];
            $imgb64 = base64_encode($registro['imagen']);
            return array($codigo, $nombre, $tipo, $descripcion, $precio, $stock, $imgb64);
        }
    }
    
    public static function modifyProduct() {
        if ($_POST['codigo']) self::executeUpdate("codigo",$_POST['codigo']);
        if ($_POST['nombre']) self::executeUpdate("nombre",$_POST['nombre']);
        if ($_POST['tipo']) self::executeUpdate("tipo",$_POST['tipo']);
        if ($_POST['descripcion']) self::executeUpdate("descripcion",$_POST['descripcion']);
        if ($_POST['precio']) self::executeUpdate("precio",$_POST['precio']);
        if ($_POST['stock']) self::executeUpdate("stock",$_POST['stock']);
        if (file_exists ($_FILES['fileUpload']['name'])) {
            $image_base64 = base64_encode(file_get_contents($_FILES['fileUpload']['tmp_name']));
            $image = base64_decode($image_base64);
            self::executeUpdate("imagen",$image); 
        }
    }
    
    public static function deleteProduct() {
        $index = array_search($_GET['delete'], $_SESSION['products']);
        if($index !== false){
            $unidades = $_SESSION['products'][$index+1];
            unset($_SESSION['products'][$index]);
            unset($_SESSION['products'][$index+1]);
            $_SESSION['products'] = array_merge($_SESSION['products']);
            Product::updateProductStockDelete($_GET['delete'],$unidades);  
        } 
    }
    
    public static function updateProductStockDelete($id,$unidades) {
        $datos = DB::execute_sql("SELECT stock FROM productos WHERE id = ?",array($id));
        
        foreach($datos as $registro){ 
            $stock = $registro['stock'];
        }
        $new_stock = $stock + $unidades;
        $res = DB::execute_sql("UPDATE productos SET stock = ? WHERE id = ?",array($new_stock,$id));
        View::deleteProductView($res);
    }
    
    public static function updateProductStockAdd($id,$unidades){
        $datos = DB::execute_sql("SELECT stock FROM productos WHERE id = ?",array($id));
        foreach($datos as $registro){ 
            $stock = $registro['stock'];
        }
        $new_stock = $stock - $unidades;
        $res = DB::execute_sql("UPDATE productos SET stock = ? WHERE id = ?",array($new_stock,$id));
        return true;
    }

    public static function getProductsInBasket() {
        $count = 0;
        if (isset($_SESSION['products'])) {
            for ($i = 1; $i<count($_SESSION['products']); $i+=2) {
                $count += $_SESSION['products'][$i];
            }    
        }
        return $count;
    }
    
    public static function addProductShoppingList ($id,$units) {
        $index = array_search($id, array_values($_SESSION['products']));
        if ($index !== false) {
            $units_index = $index + 1;
            $update_units = $_SESSION['products'][$units_index] + $units;
            $_SESSION['products'][$units_index] = $update_units;
        } else {
            array_push($_SESSION['products'],$id,$units);
        }
        return Product::updateProductStockAdd($id,$units);
    }
    
    public static function getBillID($idcliente,$fechaventa,$total) {
        $datos = DB::execute_sql("SELECT id FROM facturaventas WHERE idcliente=? AND fechaventa = ? AND total = ?",array($idcliente,$fechaventa,$total));
        foreach($datos as $registro){
            return $registro['id'];
        }    
    }

    public static function updateBasket($id) {
        $index = array_search($id, $_SESSION['products']);
        if($index !== false){
            unset($_SESSION['products'][$index]);
            unset($_SESSION['products'][$index+1]);
            View::pedidoOk();  
        } else {
            View::pedidoNoOk();  
        }
    }  
    
    public static function setBillDetails($idcliente,$fechaventa,$total, $precio, $unidades) {
        $idfactura = self::getBillID($idcliente,$fechaventa,$total);
        $idproducto = $_GET['product_id'];
        $insert = new PDO("sqlite:./datos.db");
        $insert-> beginTransaction();
        $res = DB::execute_sql("INSERT INTO detallefacturaventas (idfacturaventas, idproducto, precio, unidades) VALUES (?,?,?,?)", 
            array($idfactura, $idproducto, $precio, $unidades));
        if ($res) {
            $insert-> commit();
            Product::updateBasket($_GET['product_id']);
        } else {
            View::pedidoNoOk();
        }
    }

    public static function getTotalPrice($id) {
        $units_index = array_search($id, $_SESSION['products']) + 1;
        $datos = DB::execute_sql("SELECT precio FROM productos WHERE id=?",array($id));
        foreach($datos as $registro){
            $price = $registro['precio'];
        }
        $index = array_search($_GET['product_id'], array_values($_SESSION['products']));
        $units_index = $index + 1;
        $units = $_SESSION['products'][$units_index];
        return array($price,$units);
    }
    
    public static function finalPayment() {
        $idcliente = User::getLoggedUser();
        $fechaventa = time();
        $precio = Product::getTotalPrice($_GET['product_id'])[0];
        $unidades =  Product::getTotalPrice($_GET['product_id'])[1];
        $total = $precio * $unidades;
        $insert = new PDO("sqlite:./datos.db");
        $insert-> beginTransaction();
        $res = DB::execute_sql("INSERT INTO facturaventas (idcliente, fechaventa, total) VALUES (?,?,?)", 
        array($idcliente['id'], $fechaventa, $total));
        if ($res) {
            $insert-> commit();
            Product::setBillDetails($idcliente['id'],$fechaventa,$total,$precio,$unidades);
        } else {
            View::pedidoNoOk();
        }
    }
    
    public static function buy() {
        if ($_POST['way_to_pay'] == 'paypal') View::pay_with_paypal();
        else View::pay_with_card();
    }
}