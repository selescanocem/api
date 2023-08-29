<?php

class RestCliente{

	private $dbConnect = false;
    public function __construct(){
		include('../config/config.php');
        if(!$this->dbConnect){
			$conn = new mysqli($host, $user, $password, $database);
            if($conn->connect_error){
                die("Error failed to connect to MySQL: " . $conn->connect_error);
            }else{
                $this->dbConnect = $conn;
            }
        }
    }

    public function Listar(){			

		$query = "SELECT * FROM Cliente WHERE eliminado!=1;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaTrabajadores = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaTrabajadores[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaTrabajadores);
    }

    public function Registrar($ClienteData){
	
        $nombrecliente=$ClienteData["nombrecliente"];
        $direccioncliente=$ClienteData["direccioncliente"];
        $telefonocliente=$ClienteData["telefonocliente"];
		
		$query = "INSERT INTO Cliente VALUES(DEFAULT, ?, ?, ?, ?)";
		$activo=0;

		$statement=$this->dbConnect->prepare($query);
		$statement->bind_param("sssi", $nombrecliente,$direccioncliente,$telefonocliente,$activo);

		if($statement->execute()) {
			$messgae = "Cliente Registrado con exito.";
			$status = 1;			
		} else {
			$messgae = "Cliente No Creado.";
			$status = 0;			
        }
		
		$statement->close();

		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $messgae
        );
        
		header('Content-Type: application/json');
		echo json_encode($Responde);
    }

    public function Actualizar($ClienteData){
	
        $nombrecliente=$ClienteData["nombrecliente"];
        $direccioncliente=$ClienteData["direccioncliente"];
        $telefonocliente=$ClienteData["telefonocliente"];
        
		$Query = "INSERT INTO Cliente
        VALUES(default, '".$nombrecliente."','".$direccioncliente."','".$telefonocliente."',1,0)";
        
		if( mysqli_query($this->dbConnect, $Query)) {
			$messgae = "Cliente Registrado con exito.";
			$status = 1;			
		} else {
			$messgae = "Cliente No Creado.";
			$status = 0;			
        }
        
		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $messgae
        );
        
		header('Content-Type: application/json');
		echo json_encode($Responde);
    }

    public function Borrar($idCliente){
		$Query = "UPDATE Cliente SET eliminado=1";
        
		if( mysqli_query($this->dbConnect, $Query)) {
			$messgae = "Cliente Eliminado con exito.";
			$status = 1;			
		} else {
			$messgae = "Cliente No Borrado.";
			$status = 0;			
        }
        
		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $messgae
        );
        
		header('Content-Type: application/json');
		echo json_encode($Responde);
    }
}
?>