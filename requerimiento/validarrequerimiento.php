<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestRequerimiento.php');
$api = new RestRequerimiento();
switch($requestMethod) {
    case 'POST':
        // Takes raw data from the request
        $data = file_get_contents('php://input');
        // Converts it into a PHP object
        //$data = json_decode($json);
        
        $api->ValidarRequerimiento($data);
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>