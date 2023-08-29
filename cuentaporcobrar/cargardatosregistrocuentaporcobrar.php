<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestCuentaPorCobrar.php');
$api = new RestCuentaPorCobrar();
switch($requestMethod) {
	case 'GET':       
		$api->cargardatosregistrocuentaporcobrar();
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>