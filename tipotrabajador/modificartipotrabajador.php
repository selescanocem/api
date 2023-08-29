<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestTipoTrabajador.php');
$api = new RestTipoTrabajador();
switch($requestMethod) {
	case 'PUT':	
		$json = file_get_contents('php://input');
		$data = json_decode($json);
        $api->modificartipotrabajador($data);
        break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>