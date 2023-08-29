<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestTipoTrabajador.php');
$api = new RestTipoTrabajador();
switch($requestMethod) {
    case 'POST':	
        if($_POST['idtipotrabajador']){
            $api->LimpiarDetallePermiso($_POST['idtipotrabajador']);
            break;
        }
        break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>