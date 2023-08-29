<?php



    $con = new PDO('mysql:host=localhost;dbname=BD_ControlObras','userdbgroupbar','Barboza2020.');



    $con->exec("SET CHARACTER SET utf8");



	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



    $con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);



?>