<?php
    $requestMethod = $_SERVER["REQUEST_METHOD"];
    include('../class/RestCuentaPorCobrar.php');
    $api = new RestCuentaPorCobrar();
    switch($requestMethod) {
        case 'POST':
            // Takes raw data from the request
            $json = file_get_contents("php://input");
            // Converts it into a PHP object
            $data = json_decode($json);
            $api->registrarCuentaPorCobrar($data);
            break;
        default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
    }
?>