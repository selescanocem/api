<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestAlmacen.php');
$api = new RestAlmacen();
switch($requestMethod) {
	case 'GET':       
		if($_GET['idtipotrabajador'] && $_GET['idtrabajador']){
			$api->ListarAlmacenes($_GET['idtipotrabajador'], $_GET['idtrabajador']);
		}
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>