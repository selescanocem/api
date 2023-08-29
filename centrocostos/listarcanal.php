<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestCentroCosto.php');
$api = new RestCentroCosto();
switch($requestMethod) {
	case 'GET':
        $id = '';        
		if($_GET['idconcepto']) {
			$id = $_GET['idconcepto'];			
			$api->ListarCanal($id);
			break;
		}
		break;
		
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>