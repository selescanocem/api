<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestTrabajador.php');
$api = new RestTrabajador();
switch($requestMethod) {
	case 'POST':	
		$api->RegistrarTrabajador($_POST);
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>