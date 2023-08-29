<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestProveedor.php');
$api = new RestProveedor();

switch($requestMethod) {

    case 'POST':     
        $json = file_get_contents('php://input');       
        $data = json_decode($json);
        $api->ModificarCuentaBancaria($data);
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>