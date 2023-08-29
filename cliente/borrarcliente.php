<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestCliente.php');
$api = new RestCliente();
switch($requestMethod) {
    case 'DELETE':	
        $delete = file_get_contents("php://input");
        if ($delete['idcliente']){
            //$api->Borrar($delete['idcliente']); //FALTA DEFINIR LOS BORRADOS
            break;
        }
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>