<?php

class RestTipoTrabajador{

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

    public function ListarTiposTrabajador(){			

		$query = "SELECT idtipotrabajador, upper(nombretipotrabajador) as nombretipotrabajador, eliminado FROM TipoTrabajador where TipoTrabajador.eliminado!= 1 ;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaTiposTrabajador = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaTiposTrabajador[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaTiposTrabajador);
	}
	

    public function LimpiarDetallePermiso($idtipotrabajador){
        $query = "DELETE FROM DetallePermiso WHERE DetallePermiso.idtipotrabajador=".$idtipotrabajador.";";
        if( mysqli_query($this->dbConnect, $query)) {
			$messgae = "Detalle eliminado con exito.";
			$status = 1;			
		} else {
			$messgae = "Detalle no eliminado.";
			$status = 0;			
        }
        
		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $messgae
        );
        
		header('Content-Type: application/json');
		echo json_encode($Responde);
    }

    public function RegistrarDetallePermiso($dataDetalle){
        $idtipotrabajador=$dataDetalle["idtipotrabajador"];
        $idpermiso=$dataDetalle["idpermiso"];

		$Query = "INSERT INTO DetallePermiso(iddetallepermiso, idpermiso, idtipotrabajador)
		VALUES (default,?,?)";
		$statement=$this->dbConnect->prepare($Query);
		$statement->bind_param("ii",$idpermiso,$idtipotrabajador);
		if( $statement->execute()) {
			$messgae = "Detalle Permiso registrado con exito.";
			$status = 1;			
		} else {
			$messgae = "Detalle Permiso No Registrado.";
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
	
	public function RegistrarTipoTrabajador($dataTipo){
		$nombretipotrabajador=$dataTipo["nombretipotrabajador"];
		$query = "INSERT INTO TipoTrabajador VALUES(DEFAULT, ?,0);";
		$statement = $this->dbConnect->prepare($query);
		$statement->bind_param("s",$nombretipotrabajador);
		if($statement->execute()){
			$messgae = "Tipo Permiso registrado con exito.";
			$status = 1;			
		} else {
			$messgae = "Tipo Permiso No Registrado.";
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

    public function CargarDatosAsignacion($idtipotrabajador){
        $ListaRetorno = array();	
        $list = array();
        $query = "SELECT Permiso.idpermiso, Permiso.nombrepermiso FROM DetallePermiso INNER JOIN Permiso ON DetallePermiso.idpermiso = Permiso.idpermiso WHERE DetallePermiso.idtipotrabajador=".$idtipotrabajador.";";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaPermisosExistentes = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaPermisosExistentes['PermisosExistentes'][] = $recorre;
		}
		
        
		if(count($ListaPermisosExistentes) == 0){
			#$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
			$ListaPermisosExistentes['PermisosExistentes'] = array();
		}
        $query="SELECT Permiso.idpermiso, Permiso.nombrepermiso FROM Permiso;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaPermisos = array();
        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaPermisos['Permisos'][] = $recorre;
		}
        
        if(count($ListaPermisos) == 0){
			$ListaPermisos = $this->llenar_lista_vacia('Permisos', $ListaPermisos, array('idpermiso'=>"0" , 'nombrepermiso' => "SELECCIONAR PERMISO"));
        }
        
        $ListaRetorno = [$ListaPermisosExistentes,$ListaPermisos];       
        $list ['ResponseService'] = $ListaRetorno;      
		header('Content-Type: application/json');
        echo json_encode($list);
        
        
	}

	public function ModificarTipoTrabajador($data){
		$idtipotrabajador=$data->idtipotrabajador;
		$nombretipotrabajador=$data->nombretipotrabajador;

		$query="UPDATE TipoTrabajador SET nombretipotrabajador=? WHERE idtipotrabajador=?;";
		$statement=$this->dbConnect->prepare($query);
		if($statement){
			if($statement->bind_param("si", $nombretipotrabajador,$idtipotrabajador)){
				if($statement->execute()){
					$message="Tipo Trabajador modificado correctamente.";
					$status=1;
				}
				else{
					$message="Error al modificar tipo trabajador. Código de error {$this->dbConnect->errno}";
					$status=0;
				}
			}
			else{
				$message="Error al modificar tipo trabajador. Código de error {$this->dbConnect->errno}";
				$status=0;
			}
		}
		else{
			$message="Error al modificar tipo trabajador. Código de error {$this->dbConnect->errno}";
			$status=0;
		}

		$statement->close();
		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
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