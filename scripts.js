$(document).ready(function() {
    $("#search").keyup(function() {
        if($("#search").val().length > 2){
            $.ajax({
                url: "update_search.php",
                type: "POST",
                data: "search="+$("#search").val(),
                dataType: "json",
                success: function(data){
                    if(data.search){
                        $("#updateProducts").empty(); //vaciamos tabla
                        $("#updateProducts").append("<tr><th>Producto</th><th>Precio</th><th>Imagen</th></tr>");
                        for(var i = 0; i < data.name.length; i++){
                            $("#updateProducts").append("<tr>");
                            $("#updateProducts").append("<td>"+data.name[i]+"</td>");
                            $("#updateProducts").append("<td>"+data.price[i]+"</td>");
                            $("#updateProducts").append("<td><img src=data:image/jpg;base64,"+data.image[i]+"></td>");
                            $("#updateProducts").append("</tr>");
                        }
                    }
                },
                error: function(data){
                    $("#updateProducts").append("error");
                }
            });
        }
    }).delay(100);
});

function showMessage(texto){
   $dialogo=$('<div></div>');
   $dialogo.text(texto);
   $dialogo.dialog({
      width:200,
      modal: true
   });
}

function validateModifyForm() {
    var code = document.getElementById("codigo").value;
    var name = document.getElementById("nombre").value;
    var type = document.getElementById("tipo").value;
    var description = document.getElementById("descripcion").value;
    var price = document.getElementById("precio").value;
    var stock = document.getElementById("stock").value;
    var image = document.getElementById("imageID").files[0].size;
    var message = "";
    
    if (code.length > 0 && (code.length < 2 || code.length > 16)) message += "Código debe tener entre 2 y 16 caracteres\n";
    if (name.length > 0 && (name.length < 2 || name.length > 32)) message += "Nombre debe tener entre 2 y 32 caracteres\n";
    if (type.length > 0 && (type.length < 1 || type.length > 16)) message += "Tipo debe tener entre 1 y 16 caracteres\n";
    if (description.length > 0 && (description.length < 12 || description.length > 1024)) message += "Descripción debe tener entre 12 y 1024 caracteres\n"; 
    if (price.length > 0 && (Math.sign(Number(price)) === -1 || Math.sign(Number(price)) === 0 || isNaN(price))) message += "Precio debe ser mayor a 0€\n";  
    if (stock.length > 0 && (Math.sign(Number(stock)) === -1 || Math.sign(Number(stock)) === 0 || isNaN(stock))) message += "Stock debe ser mayor a 0€\n";
    if (image > 0 && (image > (60 * 1024))) message += "Imagen debe ser de máximo 60K octetos\n";
    
    if (message.length === 0) return true;
    else showMessage(message);
    return false;
}

function validateRegisterForm() {
    var code = document.getElementById("codigo_p").value;
    var name = document.getElementById("nombre_p").value;
    var type = document.getElementById("tipo_p").value;
    var description = document.getElementById("descripcion_p").value;
    var price = document.getElementById("precio_p").value;
    var stock = document.getElementById("stock_p").value;
    var image = document.getElementById("imageID_p").files[0].size;
    var message = "";
    
    if (code.length < 2 || code.length > 16) message += "Código debe tener entre 2 y 16 caracteres\n";
    if (name.length < 2 || name.length > 32) message += "Nombre debe tener entre 2 y 32 caracteres\n";
    if (type.length < 1 || type.length > 16) message += "Tipo debe tener entre 1 y 16 caracteres\n";
    if (description.length < 12 || description.length > 1024) message += "Descripción debe tener entre 12 y 1024 caracteres\n"; 
    if (Math.sign(Number(price)) === -1 || Math.sign(Number(price)) === 0 || isNaN(price)) message += "Precio debe ser mayor a 0€\n";  
    if (Math.sign(Number(stock)) === -1 || Math.sign(Number(stock)) === 0 || isNaN(stock)) message += "Stock debe ser mayor a 0€\n";
    if (image > (60 * 1024)) message += "Imagen debe ser de máximo 60K octetos\n";
    
    if (message.length === 0) return true;
    else showMessage(message);
    return false;
}

function addAjax(id,units) {
    $.ajax({
        url: "update_cart_quantity.php",
        type: "POST",
        data: JSON.stringify({"id":id, "units":units}),
        contentType: "application/json;charset=utf-8",
        dataType: "json",
        async: false,
        success: function(res){
            if(res.added){
                document.getElementById("cesta_label").innerHTML = "Mi cesta (" + res.value + ")";
            } else{
                error(res.message);
            }
        }
    }); 
}

function addShoppingList(id){
    var unitsSelected = document.getElementById("units");
    var units = unitsSelected.options[unitsSelected.selectedIndex].value;
    addAjax(id,units);
}