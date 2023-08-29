<?php

$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestTrabajador.php');
$api = new RestTrabajador();
switch($requestMethod) {
	case 'GET':
        $usr = '';
        $pwd = '';	
		if($_GET['usr'] and $_GET['pwd']) {
			$usr = $_GET['usr'];
			$pwd = $_GET['pwd'];
			$api->ValidarInicioSesion($usr,$pwd);
			break;
        }
		
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>