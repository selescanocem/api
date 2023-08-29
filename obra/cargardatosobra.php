<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestObra.php');
$api = new RestObra();
switch($requestMethod) {
	case 'GET':       
		$api->CargarDatosRegistrarObra();
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>