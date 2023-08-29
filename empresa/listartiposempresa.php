<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestEmpresa.php');
$api = new RestEmpresa();
switch($requestMethod) {
	case 'GET':       
		$api->ListarTiposEmpresa();
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>