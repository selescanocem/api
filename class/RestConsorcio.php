<?php

class RestConsorcio{

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

    public function ListarConsorcios(){			

		$query = "SELECT idconsorcio,nombreconsorcio,eliminado FROM Consorcio where Consorcio.eliminado!= 1;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaConsorcios = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaConsorcios[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaConsorcios);
    }

    public function CargarDatosRegistroConsorcio(){
        $ListaRetorno = array();	
        $list = array();
        $query = "SELECT idempresa, nombreempresa FROM Empresa WHERE Empresa.eliminado=0 AND Empresa.tipoempresa!=3;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaEmpresas = array();
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaEmpresas['Empresas'][] = $recorre;
		}

        $ListaRetorno = [$ListaEmpresas];
        $list ['ResponseService'] = $ListaRetorno;       
		header('Content-Type: application/json');
		echo json_encode($list);
    }

    public function registrarConsorcio($consorcioData){
        #Registrando un nuevo consorcio
        $registrado = true;
        $this->dbConnect->autocommit(false);
        $this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
        $consorcio = $consorcioData->nombreconsorcio;
        $stringrucconsorcio = $consorcioData->rucconsorcio;
        
        $rucconsorcio = $stringrucconsorcio!=""? $stringrucconsorcio:NULL;

        $query = "INSERT INTO Consorcio VALUES(DEFAULT, ?,0,?);";
        $statement = $this->dbConnect->prepare($query);
        $statement->bind_param("ss",$consorcio,$rucconsorcio);
        if($statement->execute()){
            $query =  "SELECT LAST_INSERT_ID()";
            $respuesta_query = mysqli_query($this->dbConnect, $query);
            $lastId=$respuesta_query->fetch_row();
            $idconsorcio=$lastId[0];


            $empresas = $consorcioData->listaempresas;
            foreach ($empresas as $empresaactual) {
                $idempresaactual= $empresaactual->idempresa;
                $query = "INSERT INTO DetalleConsorcio VALUES(DEFAULT,?,?)";
                $statement = $this->dbConnect->prepare($query);
                $statement->bind_param("ss", $idempresaactual, $idconsorcio);
                if(!$statement->execute()){
                    $registrado=false;
                    break;
                }
            }
            if($registrado){
                $idconsorcioempresa = 0;


                if( isset($rucconsorcio)){


                    $query = "INSERT INTO Empresa VALUES(DEFAULT,?,0,3,?,'')";
                    $statement = $this->dbConnect->prepare($query);
                    $statement->bind_param("ss", $consorcio, $rucconsorcio);
                    if($statement->execute()){
                        $query =  "SELECT LAST_INSERT_ID()";
                        $respuesta_query = mysqli_query($this->dbConnect, $query);
                        $lastId=$respuesta_query->fetch_row();
                        $idconsorcioempresa=$lastId[0];
                        $query = "INSERT INTO DetalleConsorcio VALUES(DEFAULT,?,?)";
                        $statement = $this->dbConnect->prepare($query);
                        $statement->bind_param("ss", $idconsorcioempresa, $idconsorcio);
                        if(!$statement->execute()){
                            $idconsorcioempresa=NULL;
                        }
                    }
                    else{
                        $idconsorcioempresa=NULL;
                    }
                }
                if(isset($idconsorcioempresa)){
                    $this->dbConnect->commit();
                    $status=$idconsorcio;
                    $message="Consorcio registrado";
                }
                else{
                    $this->dbConnect->rollback();
                    $status=0;
                    $message="Error al asignar empresas. Consorcio no registrado";
                }
            }
            else{
                $this->dbConnect->rollback();
                $status=0;
                $message="Error al asignar empresas. Consorcio no registrado";
            }
        }
        else{
            $status = 0;
            $message="Consorcio no registrado";
        }
        

        $Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
        );
        $this->dbConnect->autocommit(true);
        header('Content-Type: application/json');
		echo json_encode($Responde);

    }

}

?>