<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestTipoTrabajador.php');
$api = new RestTipoTrabajador();
switch($requestMethod) {
    case 'GET':       
        if($_GET['id']){
            $api->CargarDatosAsignacion($_GET['id']);
		    break;
        }
        break;		
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>