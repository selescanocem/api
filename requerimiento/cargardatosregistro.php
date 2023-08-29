<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestRequerimiento.php');
$api = new RestRequerimiento();
switch($requestMethod) {
	case 'GET':       

		if($_GET['idtrabajador']){
			$api->CargarDatosRegistroRequerimiento($_GET['idtrabajador']);
			break;
		}
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>