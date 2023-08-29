<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestProveedor.php');
$api = new RestProveedor();
switch($requestMethod) {
	case 'POST':	
        $api->RegistrarProveedor($_POST);
        break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>