<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestAlmacen.php');
$api = new RestAlmacen();
switch($requestMethod) {
	case 'GET':       
		if($_GET['idalmacen']){
			$api->ListarOperaciones($_GET['idalmacen']);
		}
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>