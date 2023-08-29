<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestAlmacen.php');
$api = new RestAlmacen();
switch($requestMethod) {
	case 'GET':       
		if($_GET['idalmacen'] && $_GET['iddetalle']){
			$api->CargarDetalleEntradasAlmacen($_GET['idalmacen'], $_GET['iddetalle']);
		}
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>