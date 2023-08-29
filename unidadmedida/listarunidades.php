<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestUnidadMedida.php');
$api = new RestUnidadMedida();
switch($requestMethod) {
	case 'GET':       
		$api->ListarUnidades();
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>