<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestAlmacen.php');
$api = new RestAlmacen();
switch($requestMethod) {
    case 'POST':
        // Takes raw data from the request
        $data = file_get_contents('php://input');
        $api->RegistrarUnidadMedida($data);
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>