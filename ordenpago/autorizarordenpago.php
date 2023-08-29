<?php
    $requestMethod = $_SERVER["REQUEST_METHOD"];
    include('../class/RestOrdenPago.php');
    $api = new RestOrdenPago();
    switch($requestMethod) {
        case 'PUT':
            // Takes raw data from the request
            $json = file_get_contents("php://input");
            // Converts it into a PHP object
            $data = json_decode($json);
            $api->autorizarPago($data);
            break;
        default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
    }
?>