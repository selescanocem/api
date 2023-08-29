<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestProveedor.php');
$api = new RestProveedor();
switch($requestMethod) {
	case 'GET':       
        if($_GET['idproveedor']){
            $api->CargarDatosProveedor($_GET['idproveedor']);
            break;
        }
        break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>