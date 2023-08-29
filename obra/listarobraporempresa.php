<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestObra.php');
$api = new RestObra();
switch($requestMethod) {
	case 'GET':
		$idObra='';
		if ($_GET['id']){
			$idObra = $_GET['id'];
			$api->ListarObrasPorEmpresa($idObra);
			break;
		}
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>