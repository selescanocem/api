<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestCuentaPorPagar.php');
$api = new RestCuentaPorPagar();
switch($requestMethod) {
	case 'GET':       
		$api->ListarCuentasPorPagar();
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>