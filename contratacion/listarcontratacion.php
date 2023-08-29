<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestContratacion.php');
$api = new RestContratacion();
switch($requestMethod) {
	case 'GET':       
		$api->ListarContrataciones();
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>