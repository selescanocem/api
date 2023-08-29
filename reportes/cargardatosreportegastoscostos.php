<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestReportes.php');
$api = new RestReportes();
switch($requestMethod) {
    case 'GET':
        if($_GET['idobra']){
            $api->CargarDatosReporteGastosCostos($_GET['idobra']);
		    break;
        }
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>