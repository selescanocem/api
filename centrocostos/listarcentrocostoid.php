<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestCentroCosto.php');
$api = new RestCentroCosto();
switch($requestMethod) {
    case 'GET':
        if($_GET['id']){
            $api->ListarCentroCostoPorId($_GET['id']);
            break;
        }       
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>