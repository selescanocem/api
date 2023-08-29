<?php


$requestMethod = $_SERVER["REQUEST_METHOD"];
switch($requestMethod) {
	case 'GET':       
		$date = date('Y-m', strtotime('+1 month'));
        echo $date;
		break;
	default:
	header("HTTP/1.0 405 Method Not Allowed");
	break;
}



?>