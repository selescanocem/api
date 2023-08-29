
<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestTrabajador.php');
$api = new RestTrabajador();
switch($requestMethod) {
	case 'GET':      
		$idt = '';
		if ($_GET['id']){
			$idt = $_GET['id'];
			$api->listarPermisosTrabajadorId($idt);
			break;
		}		
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>

