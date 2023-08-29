<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestConsorcio.php');
$api = new RestConsorcio();
switch($requestMethod) {
	case 'GET':       
        $api->CargarDatosRegistroConsorcio();
        break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>