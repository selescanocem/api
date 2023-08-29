<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestOrdenPago.php');
$api = new RestOrdenPago();
switch($requestMethod) {
	case 'GET':       
		$api->cargardatosregistroordenpago();
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>