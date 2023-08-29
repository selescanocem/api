<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestObra.php');
$api = new RestObra();
switch($requestMethod) {
	case 'POST':	
		$json = file_get_contents('php://input');
		$data = json_decode($json);
        $api->modificarobra($data);
        break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>