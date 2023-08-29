<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestCentroCosto.php');
$api = new RestCentroCosto();
switch($requestMethod) {
	case 'GET':
        $id = '';        
		if($_GET['idcentrocostos']) {
			$id = $_GET['idcentrocostos'];			
			$api->ListarConceptosCentroCosto($id);
			break;
		}
		break;
		
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>