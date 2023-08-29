<?php

class RestTrabajador{

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
	
	Public function ListarTrabajadorporId($idTrabajador){
		$query = "SELECT t.idtrabajador, CONCAT(t.nombretrabajador,' ',t.apellidotrabajador) as Nombre, t.dnitrabajador, tt.nombretipotrabajador, t.firmadigital
		FROM Trabajador t 
		inner join TipoTrabajador as tt on t.idtipotrabajador = tt.idtipotrabajador
		where t.eliminado!= 1 AND t.idtrabajador=".$idTrabajador.";";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaTrabajadores = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaTrabajadores[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaTrabajadores);
	}

	Public Function cargardatostrabajador($idtrabajador){
		$query = "SELECT t.idtrabajador, (CASE WHEN t.nombretrabajador IS NULL THEN '' ELSE t.nombretrabajador END) AS nombretrabajador,
		(CASE WHEN t.apellidotrabajador IS NULL THEN '' ELSE t.apellidotrabajador END) AS apellidotrabajador,
		(CASE WHEN t.dnitrabajador IS NULL THEN '' ELSE t.dnitrabajador END) AS dnitrabajador,
		(CASE WHEN t.correotrabajador IS NULL THEN '' ELSE t.correotrabajador END) AS correotrabajador,
		(CASE WHEN t.celulartrabajador IS NULL THEN '' ELSE t.celulartrabajador END) AS celulartrabajador,
		(CASE WHEN t.usuario IS NULL THEN '' ELSE t.usuario END) AS usuario,
		(CASE WHEN t.clave IS NULL THEN '' ELSE t.clave END) AS clave,
		(CASE WHEN t.idtipotrabajador IS NULL THEN '' ELSE t.idtipotrabajador END) AS idtipotrabajador,
		(CASE WHEN t.firmadigital IS NULL THEN '' ELSE t.firmadigital END) AS firmadigital, t.eliminado, t.idempresa FROM Trabajador t where t.eliminado!= 1 AND t.idtrabajador=".$idtrabajador.";";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $Datostrabajador = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$Datostrabajador[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($Datostrabajador);
	}
	
	public function modificartrabajador($datatrabajador){
		$this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
		#obteniendo datos de nuevo articulo

		$idtrabajador = $datatrabajador->idtrabajador;
        $nombretrabajador = $datatrabajador->nombretrabajador;
		$apellidotrabajador = $datatrabajador->apellidotrabajador;
		$dnitrabajador = $datatrabajador->dnitrabajador;
		$correotrabajador =$datatrabajador->correotrabajador;
		$celulartrabajador =$datatrabajador->celulartrabajador;
		$usuario = $datatrabajador->usuario;
		$clave =$datatrabajador->clave;
		$idtipotrabajador= $datatrabajador->idtipotrabajador;
		$firmadigital =$datatrabajador->firmadigital;
		$idempresa= $datatrabajador->idempresa;

        $query = "UPDATE Trabajador SET nombretrabajador=?, apellidotrabajador=?, dnitrabajador=?, correotrabajador=?, celulartrabajador=?, usuario=?, clave=?, idtipotrabajador=?, firmadigital=?, idempresa=? WHERE Trabajador.idtrabajador=?";
        $statement = $this->dbConnect->prepare($query);
        if($statement){
            if($statement->bind_param("sssssssisii", $nombretrabajador, $apellidotrabajador, $dnitrabajador, $correotrabajador, $celulartrabajador, $usuario, $clave, $idtipotrabajador, $firmadigital, $idempresa, $idtrabajador)){
                if($statement->execute()){
                    $status=1;
                    $message="Trabajador modificado correctamente";
                    $this->dbConnect->commit();
                }
                else{
                    $status=0;
                    $message="Trabajador no modificado. Código de error: {$this->dbConnect->errno}";
                    $this->dbConnect->rollback();
                }
            }
            else{
                $status=0;
                $message="Trabajador no modificado. Código de error: {$this->dbConnect->errno}";
                $this->dbConnect->rollback();
            }
        }
        else{
            $status=0;
            $message="Trabajador no modificado. Código de error: {$this->dbConnect->errno}";
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


	public function ListarResidentes(){			

		

		$query = "SELECT Trabajador.idtrabajador, CONCAT(Trabajador.nombretrabajador,' ',Trabajador.apellidotrabajador) as Nombre
		FROM Trabajador	WHERE Trabajador.idtipotrabajador = (SELECT idtipotrabajador FROM TipoTrabajador WHERE nombretipotrabajador='RESIDENTE' LIMIT 1);";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaTrabajadores = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaTrabajadores[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaTrabajadores);
	}

    public function ListarTrabajadores(){			

		$ListaRetorno = array();	
        $list = array();

		$query = "SELECT t.idtrabajador, UPPER(CONCAT(t.nombretrabajador,' ',t.apellidotrabajador)) as Nombre, t.dnitrabajador, UPPER(tt.nombretipotrabajador) AS nombretipotrabajador, t.eliminado, UPPER(CASE 
        WHEN e.nombreempresa IS NULL THEN '-'
        ELSE e.nombreempresa END) as Empresa, t.idtipotrabajador
		FROM Trabajador t 
		inner join TipoTrabajador as tt on t.idtipotrabajador = tt.idtipotrabajador
        LEFT join Empresa as e on t.idempresa = e.idempresa 
		where t.eliminado  != 1";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaTrabajadores = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaTrabajadores['Trabajadores'][] = $recorre;
		}		

		if(count($ListaTrabajadores) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaTrabajadores['Trabajadores'] = array();
        }

		$query ="SELECT idtipotrabajador, UPPER(nombretipotrabajador) AS nombretipotrabajador FROM TipoTrabajador;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaTipoTrabajador = array();

        $ListaTipoTrabajador = $this->llenar_lista_vacia("TiposTrabajador",$ListaTipoTrabajador, array("idtipotrabajador" => "0", "nombretipotrabajador" => "TODOS"));
        while($recorre = mysqli_fetch_assoc($respuestaQuery)){
            $ListaTipoTrabajador['TiposTrabajador'][] = $recorre;
        }

        if(count($ListaTipoTrabajador) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaTipoTrabajador['TiposTrabajador'] = array();
        }

		$ListaRetorno = [$ListaTrabajadores, $ListaTipoTrabajador];
        $list ['ResponseService'] = $ListaRetorno;

		header('Content-Type: application/json');
		echo json_encode($list);

	}
	
    public function ValidarInicioSesion($usr,$pwd){
		$Query = '';
		$messgae = "";
		$usrData = array();
		if(empty($usr) == false and empty($pwd) == false){
			$Query = "SELECT * FROM Trabajador WHERE usuario = '".$usr."' and clave = '".$pwd."'";
			if (isset($Query)){
				$resultData = mysqli_query($this->dbConnect, $Query);				
			while( $empRecord = mysqli_fetch_assoc($resultData) ) 
			{
				$usrData[] = $empRecord;				
			}
			}else{
				$messgae = "Error Consulta";
				$status = 0;
				$usrData = array(
					'Estado' => $status,
					'Mensaje' => $messgae
				);	
			}
		}else{
			$messgae = "Falta Datos";
			$status = 0;
			$usrData = array(
				'Estado' => $status,
				'Mensaje' => $messgae
			);
		}
		header('Content-Type: application/json');
		echo json_encode($usrData);
	}

	public function listarPermisosTrabajadorId($id){
		$Query = '';
		$messgae = "";
		$usrData = array();

		if(empty($id) == false){
			$Query = "SELECT t.idtrabajador,p.idpermiso,p.formulariopermiso, p.nombrepermiso, p.iconopermiso FROM Trabajador t
			inner join TipoTrabajador tt on t.idtipotrabajador = tt.idtipotrabajador
			inner join DetallePermiso d on t.idtipotrabajador = d.idtipotrabajador
			inner join Permiso p on d.idpermiso = p.idpermiso WHERE t.idtrabajador = '".$id."' order by p.nombrepermiso";
			if (isset($Query)){
				$resultData = mysqli_query($this->dbConnect, $Query);				
				while( $empRecord = mysqli_fetch_assoc($resultData)) 
				{
					$usrData[] = $empRecord;				
				}
			}else{
				$messgae = "Error Consulta";
				$status = 0;
				$usrData = array(
					'Estado' => $status,
					'Mensaje' => $messgae
				);	
			}
		}else{
			$messgae = "Falta Datos";
			$status = 0;
			$usrData = array(
				'Estado' => $status,
				'Mensaje' => $messgae
			);
		}
		header('Content-Type: application/json');
		echo json_encode($usrData);
	}

	public function cargardatosregistrotrabajador(){
		$ListaRetorno = array();	
        $list = array();
		$query = "SELECT idtipotrabajador, UPPER(nombretipotrabajador) AS nombretipotrabajador FROM TipoTrabajador where TipoTrabajador.eliminado!= 1 ;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaTiposTrabajador = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaTiposTrabajador['TiposTrabajador'][] = $recorre;
        }
        #if(count($ListaEstados) == 0 ){
            #$ListaEstados = $this->llenar_lista_vacia('EstadosObra', $ListaEstados, array("idestadoobra" => "0", "descripcion" => "SELECCIONAR ESTADO"));
		#}
		
		$query = "SELECT idempresa, nombreempresa FROM Empresa WHERE Empresa.eliminado=0 AND Empresa.tipoempresa=1;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaEmpresas = array();
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaEmpresas['Empresas'][] = $recorre;
		}

        $ListaRetorno = [$ListaTiposTrabajador, $ListaEmpresas];
        $list ['ResponseService'] = $ListaRetorno;       
		header('Content-Type: application/json');
		echo json_encode($list);
		
	}

	public function RegistrarTrabajador($TrabajadorData){
	
        $nombretrabajador=$TrabajadorData["nombretrabajador"];
        $apellidotrabajador=$TrabajadorData["apellidotrabajador"];
        $dnitrabajador=$TrabajadorData["dnitrabajador"];
        $correotrabajador=$TrabajadorData["correotrabajador"];
        $celulartrabajador=$TrabajadorData["celulartrabajador"];
        $usuariotrabajador=$TrabajadorData["usuario"];
        $clavetrabajador=$TrabajadorData["clave"];
        $idtipotrabajador=$TrabajadorData["idtipotrabajador"];
        $firmadigitaltrabajador=$TrabajadorData["firmadigital"];
	$idempresa=$TrabajadorData["idempresa"];
        
		$Query = "INSERT INTO Trabajador
        VALUES(default, '".$nombretrabajador."', '".$apellidotrabajador."', '".$dnitrabajador."', '".$correotrabajador."'
		, '".$celulartrabajador."', '".$usuariotrabajador."', '".$clavetrabajador."', ".$idtipotrabajador."
		, '".$firmadigitaltrabajador."',0,$idempresa)";
        
		if( mysqli_query($this->dbConnect, $Query)) {
			$messgae = "Trabajador Registrado con exito.";
			$status = 1;			
		} else {
			$messgae = "Trabajador No Creado.";
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