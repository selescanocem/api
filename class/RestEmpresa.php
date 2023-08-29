<?php

class RestEmpresa{

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

    public function Listar(){			
		$ListaRetorno = array();	
        $list = array();

		$query = "SELECT E.idempresa, UPPER(E.nombreempresa) As nombreempresa, E.eliminado, E.RUC, UPPER(E.Direccion) As Direccion, UPPER(TE.nombre) AS tipoempresa, E.presupuesto FROM Empresa E INNER JOIN TipoEmpresa TE ON E.tipoempresa = TE.idtipoempresa;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaEmpresas = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaEmpresas['Empresas'][] = $recorre;
		}		

		if(count($ListaEmpresas) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaEmpresas['Empresas'] = array();
        }

		$query ="SELECT idtipoempresa, nombre FROM TipoEmpresa;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaTipoEmpresas = array();

        $ListaTipoEmpresas = $this->llenar_lista_vacia("TipoEmpresas",$ListaTipoEmpresas, array("idtipoempresa" => "0", "nombre" => "TODOS"));
        while($recorre = mysqli_fetch_assoc($respuestaQuery)){
            $ListaTipoEmpresas['TipoEmpresas'][] = $recorre;
        }

        if(count($ListaTipoEmpresas) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaTipoEmpresas['TipoEmpresas'] = array();
        }

		$ListaRetorno = [$ListaEmpresas, $ListaTipoEmpresas];
        $list ['ResponseService'] = $ListaRetorno;

		header('Content-Type: application/json');
		echo json_encode($list);
	}
	
	public function ListarTiposEmpresa(){			

		$query = "SELECT TipoEmpresa.idtipoempresa, UPPER(TipoEmpresa.nombre) As nombre FROM TipoEmpresa WHERE TipoEmpresa.idtipoempresa!=3;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaTipoEmpresas = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaTipoEmpresas[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaTipoEmpresas);
    }

    public function Registrar($EmpresaData){
		
		#Registrando nueva contratacion
		$registrado = true;
		#Inicializando el autocommit de la conexion en falso
		$this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
		#obteniendo datos de nueva contratacion
		$nombreempresa = $EmpresaData->nombreempresa;
		$tipoempresa = $EmpresaData->tipoempresa;
		$RUC = $EmpresaData->RUC;
		$Direccion = $EmpresaData->Direccion;

		#inicio de query
		$query = "INSERT INTO Empresa VALUES(DEFAULT,?,0,?,?,?,0.00);";
		$statement = $this->dbConnect->prepare($query);
		$statement->bind_param("siss",$nombreempresa, $tipoempresa, $RUC, $Direccion);
		if($statement->execute()){
			$query =  "SELECT LAST_INSERT_ID()";
			$respuesta_query = mysqli_query($this->dbConnect, $query);
			$lastId=$respuesta_query->fetch_row();
			$idcontratacion=$lastId[0];
			$status=$idcontratacion;
			$message= "Empresa registrada correctamente.";
			$this->dbConnect->commit();
		}
		else{
			$status = 0;
			$message="Empresa no registrada. Codigo de error {$this->dbConnect->errno}";
		}

		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
	}
	
	public function Editar($empresaData){
		$this->dbConnect->autocommit(false);
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
		$idempresa=$empresaData->idempresa;
		$nombreempresa = $empresaData->nombreempresa;
		$tipoempresa = $empresaData->tipoempresa;
		$RUC = $empresaData->RUC;
		$Direccion = $empresaData->Direccion;

		$query = "UPDATE Empresa SET Empresa.nombreempresa = ?, Empresa.tipoempresa = ?, Empresa.RUC = ?, Empresa.Direccion = ? WHERE idempresa=?;";
		$statement = $this->dbConnect->prepare($query);
		$statement->bind_param("sissi",$nombreempresa, $tipoempresa, $RUC, $Direccion, $idempresa);

		if($statement->execute()){
			$status=1;
			$message="Empresa editada correctamente";
			$this->dbConnect->commit();
		}
		else{
			$statut=0;
			$message="Error, no se pudo editar empresa. Codigo de error {$this->dbConnect->errno}";
			$this->dbConnect->rollback();
		}

		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
	}

	public function Borrar($empresaData){


		$this->dbConnect->autocommit(false);
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
		$idempresa=$empresaData->idempresa;
		$query = "UPDATE Empresa SET eliminado=True WHERE idempresa=".$idempresa.";";
		if(mysqli_query($this->dbConnect, $query)){
			$status=1;
			$message="Empresa deshabilitada correctamente";
			$this->dbConnect->commit();
		}
		else{
			$status=0;
			$message="Error, no se pudo borrar empresa. Codigo de error {$this->dbConnect->errno}";
			$this->dbConnect->rollback();
		}

		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
	}

	private function llenar_lista_vacia($nombrelista, $lista, $valoreslista){
		$lista[$nombrelista][] = $valoreslista;
		return $lista;
	}

}
?>