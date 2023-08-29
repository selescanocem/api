<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestCuentaPorPagar.php');
$api = new RestCuentaPorPagar();
switch($requestMethod) {
    case 'PUT':
        // Takes raw data from the request
        $json = file_get_contents('php://input');
        // Converts it into a PHP object
        $data = json_decode($json);
        $api->RegistrarComprobanteCuentaPorPagar($data);
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>