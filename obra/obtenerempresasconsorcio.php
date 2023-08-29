<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestObra.php');
$api = new RestObra();
switch($requestMethod) {
    case 'GET':       
        if($_GET['idconsorcio']){
            $api->obtenerEmpresasConsorcio($_GET['idconsorcio']);          
            break;
        }
        break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>