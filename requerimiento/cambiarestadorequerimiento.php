<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestRequerimiento.php');
$api = new RestRequerimiento();
switch($requestMethod) {
    case 'PUT':
        $data = file_get_contents('php://input');

        $api->cambiarEstadoRequerimiento($data);

		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>