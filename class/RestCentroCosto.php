<?php



class RestCentroCosto{

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



    public function ListarCentroCosto(){			
		$query = "SELECT idcentrocosto, UPPER(nombrecentrocostos) AS nombrecentrocostos, eliminado, total from CentroCostos where eliminado = 0";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaCentroCostos = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaCentroCostos[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaCentroCostos);
	}



	public function ListarItemXCentroCosto($idcentrocostos){			
		$query = "SELECT i.iditemcanal, UPPER(i.descripcion) AS descripcion, UPPER(i.UnidadMedida) AS UnidadMedida, i.montoitem, C.idconcepto, UPPER(CO.nombreconcepto) AS nombreconcepto, 
		i.idcanalcentrocostos, UPPER(C.nombrecanal) AS nombrecanal, CO.idcentrocostos, UPPER(CE.nombrecentrocostos) AS nombrecentrocostos
		FROM itemCanal i
		INNER JOIN CanalCentroCostos C ON i.idcanalcentrocostos = C.idcanal
		INNER JOIN ConceptoCentroCosto CO on C.idconcepto = CO.idconcepto
		INNER JOIN CentroCostos CE on CO.idcentrocostos = CE.idcentrocosto
		WHERE CO.idcentrocostos =".$idcentrocostos;

		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaItems = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {

			$ListaItems[] = $recorre;

		}		

		header('Content-Type: application/json');

		echo json_encode($ListaItems);

	}





	public function CargarDatosRegistrarItem($idcentrocostos){		

        $ListaRetorno = array();	
        $list = array();
		$query="SELECT idconcepto, UPPER(nombreconcepto) AS nombreconcepto from ConceptoCentroCosto where idcentrocostos = ".$idcentrocostos;
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaConceptos = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaConceptos['Conceptos'][] = $recorre;
		}
		if(count($ListaConceptos) == 0){
			$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
		}
        $query="SELECT idcanal, UPPER(nombrecanal) AS nombrecanal, CanalCentroCostos.idconcepto FROM CanalCentroCostos 
		WHERE CanalCentroCostos.idconcepto 
		IN (SELECT ConceptoCentroCosto.idconcepto FROM ConceptoCentroCosto WHERE ConceptoCentroCosto.idcentrocostos = ".$idcentrocostos.")";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaCanal = array();
        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaCanal['Canales'][] = $recorre;
		}
		if(count($ListaCanal) == 0){
			$ListaCanal = $this->llenar_lista_vacia('Canales', $ListaCanal, array('idcanal'=>"0" , 'nombrecanal' => "SELECCIONAR CANAL", 'idconcepto' => "0"));
		}
        $ListaRetorno = [$ListaConceptos,$ListaCanal];       
        $list ['ResponseService'] = $ListaRetorno;      
		header('Content-Type: application/json');
		echo json_encode($list);
	}

	

	public function ListarConceptosCentroCosto($idcentrocostos){			

		$query = "SELECT idconcepto,UPPER(nombreconcepto) AS nombreconcepto,montorelacionconcepto from ConceptoCentroCosto where idcentrocostos = ".$idcentrocostos;
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaConceptos = array();     
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaConceptos[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaConceptos);
	}

	

	public function ListarCanal($idcentrocostos){			
		$query = "SELECT C.idcanal, UPPER(C.nombrecanal) AS nombrecanal, C.idconcepto, UPPER(CO.nombreconcepto) AS nombreconcepto, CO.idcentrocostos, UPPER(CC.nombrecentrocostos) AS nombrecentrocostos, C.montorelacion
		FROM CanalCentroCostos C
		INNER JOIN ConceptoCentroCosto CO ON CO.idconcepto = C.idconcepto
		INNER JOIN CentroCostos CC ON CO.idcentrocostos = CC.idcentrocosto 
		WHERE CO.idcentrocostos = ".$idcentrocostos." AND C.eliminado=0;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaCanales = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaCanales[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaCanales);
    }


	public function BorrarCanal($data){
		$this->dbConnect->autocommit(false);
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

		$idcanal=$data->idcanal;
		$idconcepto=$data->idconcepto;
		$idcentro=$data->centrocostos;
		$monto=$data->montorelacion;

		$query = "UPDATE CanalCentroCostos SET eliminado=1 WHERE idcanal=?";
		$statement = $this->dbConnect->prepare($query);
		if($statement){
			if($statement->bind_param("i",$idcanal)){
				if($statement->execute()){
					$query = "UPDATE ConceptoCentroCosto SET montorelacionconcepto = montorelacionconcepto - ? WHERE idconcepto=?";
					$statement = $this->dbConnect->prepare($query);
					if($statement){
						if($statement->bind_param("di",$monto,$idconcepto)){
							if($statement->execute()){
								$query = "UPDATE CentroCostos SET total = total - ? WHERE idcentrocosto=?";
								$statement = $this->dbConnect->prepare($query);
								if($statement){
									if($statement->bind_param("di",$monto,$idcentro)){
										if($statement->execute()){
											$status = 1;
											$message = "Canal borrado correctamente.";
											$this->dbConnect->commit();
										}
										else{
											$status = 0;
											$message = "No se pudo borrar el canal. Codigo de error: {$this->dbConnect->errno}";
											$this->dbConnect->rollback();
										}
									}
								}
								else{
									$status = 0;
									$message = "No se pudo borrar el canal. Codigo de error: {$this->dbConnect->errno}";
									$this->dbConnect->rollback();
								}
							}
						}
					}
					else{
						$status = 0;
						$message = "No se pudo borrar el canal. Codigo de error: {$this->dbConnect->errno}";
						$this->dbConnect->rollback();
					}
				}
			}
		}
		else{
			$status = 0;
			$message = "No se pudo borrar el canal. Codigo de error: {$this->dbConnect->errno}";
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



    public function RegistrarCentroCostos($CentroCostosData){
        $nombrecentrocostos=$CentroCostosData["nombrecentrocostos"];
		$Query = "INSERT INTO CentroCostos
        VALUES(default, '".$nombrecentrocostos."',0,0)";
		if( mysqli_query($this->dbConnect, $Query)) {
			$messgae = "Centro Costos Registrado con exito.";
			$status = 1;			
		} else {
			$messgae = "Centro Costos No Creado.";
			$status = 0;			
        }        
		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $messgae
        );
		header('Content-Type: application/json');
		echo json_encode($Responde);
	}

	

	public function RegistrarItem($ItemData){
#		$this->dbConnect->autocommit(false);
        $descripcion=$ItemData["descripcion"];
        $unidadmedida=$ItemData["UnidadMedida"];
        $monto=$ItemData["montoitem"];
        $idcanalcentrocostos=$ItemData["idcanalcentrocostos"];
		$Query = "INSERT INTO itemCanal(iditemcanal, descripcion, UnidadMedida, montoitem, idcanalcentrocostos, eliminado)
		VALUES (default,?,?,?,?,0)";
		$statement=$this->dbConnect->prepare($Query);
		$statement->bind_param("ssdi",$descripcion,$unidadmedida,$monto,$idcanalcentrocostos);
		if( $statement->execute()) {
			$query = "UPDATE CanalCentroCostos SET montorelacion = montorelacion + ".$monto." WHERE CanalCentroCostos.idcanal =".$idcanalcentrocostos.";";
			if (!mysqli_query($this->dbConnect, $query)){
				$messgae = "Item No Registrado";
				$status = 0;
			}
			$query = "UPDATE ConceptoCentroCosto SET montorelacionconcepto = montorelacionconcepto + ".$monto." WHERE ConceptoCentroCosto.idconcepto = (SELECT idconcepto FROM CanalCentroCostos WHERE idcanal=".$idcanalcentrocostos.");";
			if (!mysqli_query($this->dbConnect, $query)){
				$messgae = "Item No Registrado";
				$status = 0;
			}
			$query = "UPDATE CentroCostos SET total = total +".$monto." WHERE CentroCostos.idcentrocosto = (SELECT idcentrocostos FROM ConceptoCentroCosto WHERE idconcepto = (SELECT idconcepto FROM CanalCentroCostos WHERE idcanal=".$idcanalcentrocostos."));";
			if (!mysqli_query($this->dbConnect, $query)){
				$messgae = "Item No Registrado";
				$status = 0;
			}

			if(!isset($status)){
				$messgae = "Item Registrado con exito.";
				$status = 1;
			}			
		} else {
			$messgae = "Item No Registrado.";
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

    

    public function RegistrarConceptoCentroCosto($ConceptoData){

        $nombreconcepto=$ConceptoData["nombreconcepto"];
        $montorelacionconcepto=$ConceptoData["montorelacionconcepto"];
        $idcentrocostos=$ConceptoData["idcentrocostos"];
		$Query = "INSERT INTO ConceptoCentroCosto
        VALUES(default, '".$nombreconcepto."',".$montorelacionconcepto.",".$idcentrocostos.",0)";
		if( mysqli_query($this->dbConnect, $Query)) {
			$messgae = "Concepto Registrado con exito.";
			$status = 1;			
		} else {
			$messgae = "Concepto No Registrado.";
			$status = 0;			
        }
		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $messgae
        );
		header('Content-Type: application/json');
		echo json_encode($Responde);
	}



	public function RegistrarCanal($CanalData){

		$nombrecanal = $CanalData->nombrecanal;
        $montorelacion=$CanalData->montorelacion;
        $idconcepto=$CanalData->idconcepto;


		$Query = "INSERT INTO CanalCentroCostos
        VALUES(default, ?,?,?,0)";
		$statement = $this->dbConnect->prepare($Query);
		$statement->bind_param("sdi", $nombrecanal,$montorelacion, $idconcepto);
		if($statement->execute()){
			$Query="UPDATE ConceptoCentroCosto SET montorelacionconcepto = montorelacionconcepto + ? WHERE ConceptoCentroCosto.idconcepto = ?;";
			$statement = $this->dbConnect->prepare($Query);
			if(!$statement){
				$messgae = "Error en el servidor. Codigo de error: {$this->dbConnect->errno}";
				$status=0;
			}
			if($statement->bind_param("di", $montorelacion, $idconcepto)){
				if(!$statement->execute()){
					$messgae = "Error al actualizar montos de concepto. Código de error {$this->dbConnect->errno}";
					$status=0;
				}
				else{
					$Query = "UPDATE CentroCostos SET total = total + ?  WHERE CentroCostos.idcentrocosto = (SELECT idcentrocostos FROM ConceptoCentroCosto WHERE idconcepto = ?);";
					$statement = $this->dbConnect->prepare($Query);
					if(!$statement){
						$messgae = "Error en el servidor. Codigo de error: {$this->dbConnect->errno}";
						$status=0;
					}
					if($statement->bind_param("di", $montorelacion, $idconcepto)){
						if(!$statement->execute()){
							$messgae = "Error al actualizar montos de centro de costos. Código de error {$this->dbConnect->errno}";
							$status=0;
						}
						else{
							$messgae = "Canal Registrado con exito.";
							$status = 1;
						}
					}
					else{
						$messgae = "Error en el servidor. Codigo de error: {$this->dbConnect->errno}";
						$status=0;
					}				
				}
			}
			else{
				$messgae = "Error en el servidor. Codigo de error: {$this->dbConnect->errno}";
				$status=0;
			}			
		} else {
			$messgae = "Canal No Registrado. Codigo de error: {$this->dbConnect->errno}";
			$status = 0;			
        }

        
		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $messgae
        );
		header('Content-Type: application/json');
		echo json_encode($Responde);
	}
	private function llenar_lista_vacia($nombrelista, $lista, $valoreslista){
		$lista[$nombrelista][] = $valoreslista;
		return $lista;
	}

}

?>