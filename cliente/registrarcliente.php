<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestCliente.php');
$api = new RestCliente();
switch($requestMethod) {
	case 'POST':	
		$api->Registrar($_POST);
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>