<?php
include_once 'business.class.php';
class View {
    public static function start($title){
        $html = "<!DOCTYPE html>
                <html>
                    <head>
                        <meta charset=\"utf-8\">
                        <link rel=\"stylesheet\" type=\"text/css\" href=\"estilos.css\">
                        <script src=\"http://code.jquery.com/jquery-1.11.2.js\"></script>
                        <script src=\"http://code.jquery.com/ui/1.11.4/jquery-ui.js\"></script>
                        <title>$title</title>
                    </head>
                <body>";

        User::session_start();
        echo $html;
    }
    
    public static function navigation(){
        echo '<nav class="toolbar"><img class="logotipo" src="logo.png">';
        $res = User::getLoggedUser();
        $busqu = self::showSearchForm();
        echo "<ul>";
        echo '<li><a href = "index.php">Inicio</a></li>';
        $products_in_basket = Product::getProductsInBasket();
        echo "<li><a href = \"basket.php\"><div id=\"cesta_label\">Mi cesta ($products_in_basket)</div></a></li>";
        if ($res) {
            switch($res['tipo']) {
                case 1:
                        echo '<li><a href="index.php?admin_show_inform=ventas">Ventas</a></li>';
                        echo '<li><a href="index.php?admin_show_inform=stock">Stock</a></li>';
                        echo '<li><a href="logout.php">Cerrar sesión</a></li>';
                        echo "<li> $busqu </li>";
                    echo "</ul>";
                    break;
                case 2:
                        echo '<li><a href="createProduct.php">Registrar producto</a></li>';
                        echo '<li><a href="proveedor.php">Modificar producto</a></li>';
                        echo '<li><a href="logout.php">Cerrar sesión</a></li>';
                        echo "<li> $busqu </li>";
                    echo "</ul>";
                    break;
                case 4:
                    echo '<li><a href="logout.php">Cerrar sesión</a></li>';
                    echo "<li> $busqu </li>";
                    echo "</ul>";
            }              
        } else {
            echo '<li><a href="login.php">Iniciar sesión</a></li>';  
            echo "</ul>";
        }        
        echo '</nav>';
    }
    public static function end(){
        echo '<script src="scripts.js"></script></body></html>';
    }
    
    public static function userError(){
        echo "<h1>Usuario y/o contraseña incorrectos</h1>";
    }
    
    public static function userLogin(){
        echo "<div>
            <form method = \"post\">
              <input type=\"text\" name=\"nombre\" placeholder =\"Nombre de usuario\" required><br>
              <input type=\"password\" name=\"contraseña\" placeholder =\"Contraseña\" required><br>
              <input type=\"submit\" value=\"Iniciar sesión\">
            </form>
        </div>";
    }
    
    public static function getProducts() {
        if (count($_SESSION['products']) == 0) {
            echo "<h1>No hay artículos</h1>";
        } else {
            echo "<h1>Mi cesta</h1>";
            echo '<form method="post" class="login_form">';
            echo '<table class= "principal"><tr>';
            echo "<th>Nombre</th>";
            echo "<th>Tipo</th>";
            echo "<th>Descripción</th>";
            echo "<th>Imagen</th>";
            echo "<th>Unidades</th>";
            echo "<th>Precio total</th>";
            echo "<th>Operación</th>";
            echo "</tr>";
            
            for($i = 0 ; $i < count($_SESSION['products']) ; $i++) {
                $idproducto = $_SESSION['products'][$i];
                $units = $_SESSION['products'][++$i];
                $var = array($idproducto);
                $inst = DB::execute_sql("SELECT nombre,tipo,descripcion,precio,imagen FROM productos WHERE id=?",$var);
                $datos =  DB::sqlFetch($inst);
        
                foreach($datos as $registro){
                    echo "<tr>";
                    echo "<td>{$registro['nombre']}</td>";
                    echo "<td>{$registro['tipo']}</td>";
                    echo "<td>{$registro['descripcion']}</td>";
                    $imgb64 = base64_encode($registro['imagen']);
                    echo "<td><img src='data:image/jpeg;base64,$imgb64'></td>";
                    echo "<td>$units</td>";
                    $precio = $units * $registro['precio'];
                    echo "<td>$precio €</td>";
                    echo "<td><a href='basket.php?buy=$idproducto'>Comprar</a><br>
                    <a href='basket.php?delete=$idproducto'>Eliminar</a></td></tr>";
                }
            } 
            echo '</table><br><br></form>';
        }
    }
    
    public static function proveedorView($datos){
        echo "  <table class = 'principal'>
            <tr>
                <td class = 'toolbar'></td>
            </tr>
            <tr>
                <td>
                    <table id = 'secundaria'>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Imagen</th>
                        </tr>";
                        foreach($datos as $registro){
                            echo "<tr>";
                            echo "<td><a href = 'modifyProduct.php?id=$registro[id]'>{$registro['nombre']}</a></td>"; 
                            echo "<td>{$registro['tipo']}</td>";
                            echo "<td>{$registro['descripcion']}</td>";
                            echo "<td>{$registro['precio']}</td>";
                            $imgb64 = base64_encode($registro['imagen']);
                            echo "<td><img src='data:image/jpeg;base64,$imgb64'></td>";
                            echo "</tr>";
                        }
                    echo "</table>
                </td>
            </tr>
        </table>";
    }
    
    public static function showProduct(){
        $product = Product::getProduct($_GET["id"]);
        $stock = intval($product[5]);
        echo "<h1>$product[1]</h1>";
        echo '<form method="post" enctype="multipart/form-data">';
        echo "<table class = 'principal'><tr><td>";
        echo "<label for='codigo'>Código:</label>
        <input type='text' name='codigo' id='codigo' 
        placeholder='$product[0]'><br>";
        echo "<label for='nombre'>Nombre:</label>
        <input type='text' name='nombre' id='nombre' 
        placeholder='$product[1]'><br>";
        echo "<label for='tipo'>Tipo:</label>
        <input type='text' name='tipo' id='tipo' 
        placeholder='$product[2]'><br>";
        echo "<label for='descripcion'>Descripción:</label>
        <input type='text' name='descripcion' id='descripcion' 
        placeholder='$product[3]'><br>";
        echo "<label for='precio'>Precio:</label>
        <input type='text' name='precio' id='precio' 
        placeholder='$product[4]'><br>";
        echo "<label for='stock'>Stock:</label>
        <input type='text' name='stock' id='stock' placeholder='$stock'><br>";
        echo "<label for='fileUpload'>Imagen:</label>";
        echo '<input type="file" name="fileUpload" id="imageID" />';
    	echo '<br><br><input class="w3-input" type="submit" value="Modificar" name="modify" onclick="return validateModifyForm();" />
    	</table></form>';
    }
    
    public static function getSales() {
        $datos = DB::execute_sql('SELECT idcliente,fechaventa,total FROM facturaventas;');
        echo "<h1>Facturas de ventas</h1>";
        echo "<table class = 'principal'>";
        echo "<th>Nombre del cliente</th>";
        echo "<th>Nombre de usuario</th>";
        echo "<th>Fecha de la venta</th>";
        echo "<th>Total</th>";
        echo "</tr>";
        foreach($datos as $registro){
            echo "<tr>";
            $data = getAccount($registro['idcliente']);
            echo "<td>{$data[0]}</td>";
            echo "<td>{$data[1]}</td>";
            $date =date("d-m-Y",time($registro['fechaventa']));
            echo "<td>{$date}</td>";
            echo "<td>{$registro['total']}</td>";
            echo "</tr>";
        }
        echo '</table>';   
    }
    
    public static function getStock(){
        $datos = DB::execute_sql("SELECT * FROM productos;");
        echo "<h1>Productos con stock</h1>";
        echo "<table class = 'principal'>";
        echo "<th>Artículo</th>";
        echo "<th>Stock</th>";
        echo "<th>Valor total de la venta</th>";
        echo "</tr>";
        foreach($datos as $registro){
            if ($registro['stock'] > 0.0) {
                echo "<tr>";
                echo "<td>{$registro['nombre']}</td>";
                $stock = intval($registro['stock']);
                echo "<td>{$stock}</td>";
                $totalVenta = $registro['precio']*$registro['stock'];
                echo "<td>{$totalVenta}€</td>";
                echo "</tr>";   
            }
        }
        echo '</table>';       
    }
    
    public static function showProducts() {
        $datos = DB::execute_sql("SELECT * FROM productos");
        echo " <table class = 'principal'>
                    <tr>
                        <td>
                            <table id = 'secundaria'>";
                             foreach($datos as $registro){
                                    echo "<tr>";
                                    echo "<td>{$registro['nombre']}</td>"; 
                                    echo "<td>{$registro['precio']}</td>";
                                    $imgb64 = base64_encode($registro['imagen']);
                                    echo "<td><img src='data:image/jpeg;base64,$imgb64'></td>";
                                    echo "</tr>";
                                }
                            echo "</table>
                        </td>
                    </tr>
                </table>";
    }
    
    public static function showProductRegistrationForm(){
        echo "<h1>Registra un producto</h1>";
        echo "<table class = 'principal'><tr><td>";
    	echo '<form enctype="multipart/form-data" action="createProduct.php" class="w3-container" method="post">';
        echo "<label for='codigo'>Código:</label>
        <input class='w3-input' type='text' id='codigo_p' name='codigo' placeholder='código' required><br>";
        echo "<label for='nombre'>Nombre:</label>
        <input class='w3-input' type='text' id='nombre_p' name='nombre' placeholder='nombre' required><br>";
        echo "<label for='tipo'>Tipo:</label>
        <input class='w3-input' type='text' id='tipo_p' name='tipo' placeholder='tipo' required><br>";
        echo "<label for='descripcion'>Descripción:</label>
        <input class='w3-input' type='text' id='descripcion_p' name='descripcion' placeholder='descripción' required><br>";
        echo "<label for='precio'>Precio:</label>
        <input class='w3-input' type='text' id='precio_p' name='precio' placeholder='precio' required><br>";
        echo "<label for='stock'>Stock:</label>
        <input class='w3-input' type='text' id='stock_p' name='stock' placeholder='stock' required><br>";
        echo "<input id='imageID_p' name='fileUpload' type='file' required/><br>";
    	echo '<br><input class="w3-input" type="submit" name="register" value="Registrar" onclick="return validateRegisterForm();" />
    	</form></table>';
    }
    
    public static function showProductP() {
        $id = $_GET['product_id'];
        $datos = DB::execute_sql("SELECT nombre,tipo,descripcion,precio,imagen,stock FROM productos WHERE id={$_GET['product_id']}");
        echo '<form method="post" class="login_form">';
        echo '<table class = "principal">';
        echo "<th>Nombre</th>";
        echo "<th>Tipo</th>";
        echo "<th>Descripción</th>";
        echo "<th>Precio</th>";
        echo "<th>Imagen</th>";
        echo "<th>Cesta</th>";
        echo "</tr>";
        foreach($datos as $registro){
            echo "<tr>";
            echo "<td>{$registro['nombre']}</td>";
            echo "<td>{$registro['tipo']}</td>";
            echo "<td>{$registro['descripcion']}</td>";
            echo "<td>{$registro['precio']}</td>";
            $imgb64 = base64_encode($registro['imagen']);
            echo "<td><img src='data:image/jpeg;base64,$imgb64'></td>";
            $stock = intval($registro['stock']);
        }
            if ($stock == 0) {
                echo "<td>Producto agotado</td>";
            } else {
                echo "<td><label for='units'>Cantidad:</label><select id='units'>";
                for ($i = 1; $i <= $stock; $i++) {
                    echo "<option value='$i'>$i</option>";
                }
                echo "</select><br><br><br>"; 
                echo "<input type='submit' value='Añadir' onclick=\"addShoppingList('".$id."');\">";
            }
            echo "</td></tr></form></table><br><br>";    
    }
    
    public static function buyView(){
        echo "<h1>Pedido</h1>";
        echo '<div class="flexbox-container"><div>';
    	echo '<form method="post">';
        echo "<label for='direccionEntrega'>Dirección de entrega:</label>
        <input type='text' name='direccionEntrega' required placeholder='Avenida Escaleritas nº 85 3º puerta 5'><br>";
        echo '<input type="radio" name="way_to_pay" value="card" id="card" required />
        <label for="card">Pago con tarjeta</label>
        <input type="radio" name="way_to_pay" value="paypal" id="paypal" />
        <label for="paypal">Paypal</label> 
        <input type="submit" value="Pagar" name="buy" class="button_save_modify_search"></form></div>';
    }
    
    public static function pay_with_card() {
        echo "<h1>Pago con Tarjeta</h1>";
        echo '<div class="flexbox-container"><div>';
    	echo '<form method="post">';
        echo "<label for='cardNumber'>Número de tarjeta:</label>
        <input type='text' name='cardNumber' 
        pattern='(\d{4}\s{1}){3}\d{4}' required placeholder='1234 5678 9012 3456'><br>
        <label for='expiryDate'>Fecha en la que expira:</label>
        <input type='text' name='expiryDate' 
        pattern='(0[1-9]|[12]\d|3[01])/(\d{4}$)' required placeholder='12/2019'><br>
        <input type='submit' value='Finalizar pago' name='final_payment' class='button_save_modify_search'>";
    	echo '</div></form>';      
    }
    
    public static function pay_with_paypal() {
        echo "<h1>Pago con Paypal</h1>";
        echo '<div class="flexbox-container"><div>';
    	echo '<form method="post">';
        echo "<label for='cardNumber'>Email:</label>
        <input type='text' name='email' 
        pattern = '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$' 
        required placeholder='Email'><br>
        <label for='password'>Contraseña:</label>
        <input type='password' name='password' required><br>
        <input type='submit' value='Finalizar pago' name='final_payment' 
        class='button_save_modify_search'>";
    	echo '</div>';
        echo '</form>';    
    }
    
    public static function deleteProductView($res){
        if ($res) {
            echo "<h1>Artículo eliminado</h1>"; 
        } else {
            echo "<h1>Artículo no eliminado</h1>"; 
        }
    }
    
    public static function addProductView($res){
        if ($res) {
        echo "<h1>Artículo añadido</h1>"; 
        } else {
            echo "<h1>Artículo no añadido</h1>"; 
        }
    }
    
    public static function showSearchForm() {
        $tipos = array("Todos");
        $inst = DB::execute_sql('SELECT tipo FROM productos;');
        $datos =  DB::sqlFetch($inst);
        foreach($datos as $registro){
            if (!in_array($registro['tipo'], $tipos)) {
                array_push($tipos,$registro['tipo']);
            }
        }
        echo "  <div id = 'busqueda'>
                    <div id = busquedaint><form name='form' method='post'>";
                        echo '<select id="tipo">';
                        foreach ($tipos as $tipo) {
                            echo "<option value='$tipo'>$tipo</option>";
                        }
                    echo "<input  id = \"search\" type=\"text\" name=\"nombre\" placeholder =\"Búsqueda por nombre\"></select></div>
                    <div id = busquedaint><input  type=\"submit\" value=\"Buscar\" name=\"search\"></div></div>";
    }
    
    
    public static function showSearch() {
        $inst = DB::execute_sql('SELECT * FROM productos;');
        $datos =  DB::sqlFetch($inst);
        echo "  <table id = 'updateProducts' class = 'principal'>
                            <tr>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Imagen</th>
                            </tr>";
                            foreach($datos as $registro){
                                echo "<tr>";
                                echo "<td><a href = 'product.php?product_id=$registro[id]'>{$registro['nombre']}</a></td>"; 
                                echo "<td>{$registro['precio']}</td>";
                                $imgb64 = base64_encode($registro['imagen']);
                                echo "<td><img src='data:image/jpeg;base64,$imgb64'></td>";
                                echo "</tr>";
                            }
                    echo "</td>
                </tr>
            </table>";
    }
    public static function showProductsView($datos){
        if ($datos != null) {
        echo "<table class = 'principal'><tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Imagen</th></tr>";
                foreach($datos as $registro){
                    $id = $registro['id'];
                    echo "<tr>";
                    echo "<td><a href = 'product.php?product_id=$id'>{$registro['nombre']}</td>";
                    echo "<td>{$registro['descripcion']}</td>";
                    echo "<td>{$registro['precio']}</td>";
                    $imgb64 = base64_encode($registro['imagen']);
                    echo "<td><img src='data:image/jpeg;base64,$imgb64'></a></td>";
                    echo "</tr>";
                }
        echo "</table>";
        } else {
            echo "<h1>No existe ningún producto<br>con esas características</h1>";
        }
    }
    public static function imgBadSizeView(){
        echo "<h2><br><br><br>El producto no se ha registrado<br>
                El tamaño de la imagen debe ser menor de 60K</h2>";
    }
    public static function imgBadFormatView(){
        echo "echo <h2><br><br><br>El producto no se ha registrado<br>
            El formato de la imagen debe ser jpg</h2>";
    }
    public static function productoModificado($res, $nombre){
        if($res) echo "<h1>$nombre ha sido modificado</h1>";
        else echo "<h1>$nombre no ha sido modificado</h1>";
    }
    
    public static function loginMsgView(){
        echo "<h1>Tienes que iniciar sesión para<br>comprar el artículo</h1>";
    }
    public static function productoRegistrado($res, $db){
        if ($res) {
            $db-> commit();  
            echo "<h1>El producto se ha registrado</h1>";
        } else echo "<h1>El producto no se ha registrado</h1>";
    }

    public static function pedidoOk(){
        echo "<h1>Pedido se ha realizado</h1>";
    }
    public static function pedidoNoOk(){
        echo "<h1>Pedido no se ha podido realizado</h1>"; 
    }
}
