<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestRequerimiento.php');
$api = new RestRequerimiento();
switch($requestMethod) {
	case 'POST':	
		$json = file_get_contents('php://input');
		$data = json_decode($json);
        $api->DesestimarItemRequerimiento($data);
        break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>