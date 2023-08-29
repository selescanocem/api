<?php



class RestCuentaPorPagar{



	private $dbConnect = false;
    public function __construct(){
		include('../config/config.php');
        if(!$this->dbConnect){
			$conn = new mysqli($host, $user, $password, $database);
			$conn->set_charset('utf8');
            if($conn->connect_error){
                die("Error failed to connect to MySQL: " . $conn->connect_error);
            }else{
				$conn->autocommit(false);
                $this->dbConnect = $conn;
            }
        }
    }

    public function autorizarCuentaPorPagar($data){
        $idcuentaporpagar = $data->idcuentaporpagar;
        $query="UPDATE CuentaPorPagar SET autorizado=true WHERE idcuentaporpagar=?";
        $statement=$this->dbConnect->prepare($query);
        if($statement && $statement->bind_param("i", $idcuentaporpagar)){
            if($statement->execute()){
                $status=1;
                $message="Cuenta Autorizada correctamente.";

            }
            else{
                $status=0;
                $message="Cuenta no autorizada. Mensaje de error {$this->dbConnect->errno}";
            }
        }
        else{
            $status=0;
            $message="Cuenta no autorizada. Mensaje de error {$this->dbConnect->errno}";

        }
        $Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
    }

    public function ListarCuentasPorPagar(){			

		$ListaRetorno = array();	
		$list = array();

		$query = "SELECT cp.idcuentaporpagar, cp.idcontratacion, c.codigoContratacion, cp.concepto as conceptocuentaporpagar,
        cp.importetotal as importecuentaporpagar, cp.fechalimite, DATEDIFF(fechalimite,now()) as diaspendientes,
        (CASE WHEN fechacierre IS NULL THEN 'PENDIENTE' ELSE fechacierre END) AS fechacierre, 
        cp.idestadocuentaporpagar, ep.descripcion, cp.idobra,
        cp.idtipocuentaporpagar, UPPER(top.nombre) AS nombre, idempresa,importependiente as pendiente
        from CuentaPorPagar cp
        INNER JOIN Contratacion c on c.idcontratacion = cp.idcontratacion
        INNER JOIN EstadoPago ep on ep.idestadopago = cp.idestadocuentaporpagar
        INNER JOIN tipoordenpago top on top.idtipoorden = cp.idtipocuentaporpagar
        ORDER BY cp.idtipocuentaporpagar ASC,diaspendientes ASC;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaOrdenes = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaOrdenes['CuentasPorPagar'][] = $recorre;
		}		
		
		if(count($ListaOrdenes) == 0){			
			$ListaOrdenes['CuentasPorPagar'] = array();
		}

		$query = "SELECT idtipoorden, nombre FROM tipoordenpago;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaTipoOrdenes = array();

		$ListaTipoOrdenes = $this->llenar_lista_vacia("Tipos",$ListaTipoOrdenes, array("idtipoorden" => "0", "nombre" => "TODOS"));
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaTipoOrdenes['Tipos'][] = $recorre;
		}

		$query = "SELECT idempresa, nombreempresa, presupuesto FROM Empresa WHERE Empresa.tipoempresa=1;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaEmpresas = array();

		$ListaEmpresas = $this->llenar_lista_vacia("Empresas",$ListaEmpresas, array("idempresa" => "0", "nombreempresa" => "SELECCIONE", "presupuesto"=>"0.0"));
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaEmpresas['Empresas'][] = $recorre;
		}


		$ListaRetorno = [$ListaOrdenes, $ListaTipoOrdenes, $ListaEmpresas];

		$list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);
	}

    public function RegistrarComprobanteCuentaPorPagar($data){
        $idcuentaporpagar=$data->idcuentaporpagar;
        $tipocomprobante=$data->tipoComprobante;
        $numerocomprobante=$data->numerocomprobante;

        $query="UPDATE CuentaPorPagar SET tipoComprobante=?, comprobante=true, numerocomprobante=? WHERE idcuentaporpagar=?;";
        $statement=$this->dbConnect->prepare($query);
        if($statement && $statement->bind_param("isi", $tipocomprobante, $numerocomprobante,$idcuentaporpagar)){
            if($statement->execute()){
                $status=1;
                $message="Comprobante registrado correctamente.";
            }
            else{
                $status=0;  
                $message="Error al registrar comprobante. Código de error {$this->dbConnect->errno}";

            }
        }
        else{
            $status=0;
            $message="Error al registrar comprobante. Código de error {$this->dbConnect->errno}";
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