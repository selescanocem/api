<?php
$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/RestRequerimiento.php');
$api = new RestRequerimiento();
switch($requestMethod) {
	case 'POST':	
		$json = file_get_contents('php://input');
		$data = json_decode($json);
        foreach($data as $itemactual){
            $iditem = $itemactual->iditem;
            echo $iditem."\n";
        }
        #$api->DesestimarItemRequerimiento($data);
        break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}
?>