<?php
    
    class RestUnidadMedida{
        
    
    private $dbConnect = false;
    public function __construct(){
		include('../config/config.php');
        if(!$this->dbConnect){
            $conn = new mysqli($host, $user, $password, $database);
            $conn->set_charset('utf8');
            if($conn->connect_error){
                die("Error failed to connect to MySQL: " . $conn->connect_error);
            }else{
                $this->dbConnect = $conn;
            }
        }
    }

    public function ListarUnidades(){			
        $query = "SELECT * FROM UnidadMedida;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaUnidad = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaUnidad [] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaUnidad);
    }

    public function RegistrarUnidadMedida($descripcion){
        $query="INSERT INTO UnidadMedida VALUES(DEFAULT, UPPER(?), 0);";
        $statement=$this->dbConnect->prepare($query);
        if($statement){
            if($statement->bind_param("s",$descripcion)){
                if($statement->execute()){
                    $messgae="Unidad registrada correctamente.";
                    $status=1;
                }
                else{
                    $messgae="Error al registrar unidad.";
                    $status=0;
                    if($this->dbConnect->errno == 1062){
                        $messgae= $messgae."La unidad ya existe, ingrese otra descripción.";
                    }
                }
            }
            else{
                $messgae="Error al registrar unidad. Código de error {$this->dbConnect->errno}";
                $status=0;
            }
        }
        else{
            $messgae="Error al registrar unidad. Código de error {$this->dbConnect->errno}";
            $status=0;
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