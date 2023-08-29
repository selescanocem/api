<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestContratacion.php');
$api = new RestContratacion();
switch($requestMethod) {
    case 'GET':
        if(isset($_GET['fechadesde']) && isset($_GET['fechahasta']) && isset($_GET['tipocontrato'])){
            $api->BuscarContratacion($_GET['fechadesde'], $_GET['fechahasta'] ,$_GET['tipocontrato']);
		    break;
        }
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>