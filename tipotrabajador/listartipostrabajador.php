<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestTipoTrabajador.php');
$api = new RestTipoTrabajador();
switch($requestMethod) {
	case 'GET':       
		$api->ListarTiposTrabajador();
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>