<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestTrabajador.php');
$api = new RestTrabajador();
switch($requestMethod) {
    case 'GET':
        if($_GET['idtrabajador']){
            $api->cargardatostrabajador($_GET['idtrabajador']);
            break;
        }       
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>