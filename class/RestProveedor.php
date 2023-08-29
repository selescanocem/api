<?php

class RestProveedor{

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

	public function CargarDatosProveedor($idproveedor){
        $query = "SELECT rucproveedor, razonsocial, direccionproveedor, contactoproveedor, telefonoproveedor FROM Proveedor WHERE Proveedor.idproveedor=".$idproveedor.";";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $DetalleObra = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$DetalleObra[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($DetalleObra);
    }

	public function CargarDatosCuentasProveedor($idproveedor){
		$ListaRetorno = array();	
        $list = array();

        $query = "SELECT E.nombreentidad, E.identidad  FROM CuentaBancaria C INNER JOIN EntidadBancaria E ON E.identidad = C.identidad WHERE idproveedor = {$idproveedor} GROUP BY E.identidad;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaEntidades = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaEntidades['Entidades'][] = $recorre;
		}		
		if(count($ListaEntidades) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaEntidades['Entidades'] = array();
        }


		$query ="SELECT idcuenta, identidad, nrocuentabancaria from CuentaBancaria  where idproveedor = {$idproveedor}";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaCuentas = array();

        #$ListaEntidades = $this->llenar_lista_vacia("Cuentas",$ListaCuentas, array("idcuenta" => "0", "nro" => "TODAS"));
        while($recorre = mysqli_fetch_assoc($respuestaQuery)){
            $ListaCuentas['Cuentas'][] = $recorre;
        }

        if(count($ListaCuentas) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaCuentas['Cuentas'] = array();
        }

        $ListaRetorno = [$ListaEntidades, $ListaCuentas];
        $list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);
	}

	public function EditarProveedor($proveedorData){
		$this->dbConnect->autocommit(false);
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
		$idproveedor = $proveedorData->idproveedor;
		$rucproveedor=$proveedorData->rucproveedor;
		$razonsocial = $proveedorData->razonsocial;
		$direccionproveedor = $proveedorData->direccionproveedor;
		$telefonoproveedor = $proveedorData->telefonoproveedor;
		$contactoproveedor = $proveedorData->contactoproveedor;

		$query = "UPDATE Proveedor SET rucproveedor = ?, razonsocial = ?, direccionproveedor = ?, telefonoproveedor = ?, contactoproveedor=? WHERE idproveedor=?;";
		$statement = $this->dbConnect->prepare($query);
		$statement->bind_param("sssssi",$rucproveedor, $razonsocial, $direccionproveedor, $telefonoproveedor, $contactoproveedor,$idproveedor );

		if($statement->execute()){
			$status=1;
			$message="Proveedor editado correctamente";
			$this->dbConnect->commit();
		}
		else{
			$statut=0;
			$message="Error, no se pudo editar proveedor. Codigo de error {$this->dbConnect->errno}";
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

	public function ListarEntidades(){			



		$query = "SELECT * FROM EntidadBancaria;";



		$respuestaQuery = mysqli_query($this->dbConnect, $query);



        $ListaEntidad = array();

        

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {

			$ListaEntidad [] = $recorre;

		}		

		header('Content-Type: application/json');

		echo json_encode($ListaEntidad);

    	}

    public function ListarProveedores(){			

		$query = "SELECT * FROM Proveedor where Proveedor.eliminado!= 1 ;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaTiposTrabajador = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaTiposTrabajador[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaTiposTrabajador);
    }
    
    public function RegistrarCuentaBancaria($cuentadata){
        		#Registrando nueva contratacion
		#Inicializando el autocommit de la conexion en falso
		$this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
        #obteniendo datos de nueva contratacion
        #{"idcuenta":0,"identidad":1,"idproveedor":1,"nrocuentabancaria":145155155}
		$identidad = $cuentadata->identidad;
		$idproveedor = $cuentadata->idproveedor;
		$nrocuentabancaria = $cuentadata->nrocuentabancaria;


		#inicio de query
		$query = "INSERT INTO CuentaBancaria VALUES(DEFAULT,?,?,?);";
		$statement = $this->dbConnect->prepare($query);
		$statement->bind_param("iis",$identidad, $idproveedor, $nrocuentabancaria);
		if($statement->execute()){
			$query =  "SELECT LAST_INSERT_ID()";
			$respuesta_query = mysqli_query($this->dbConnect, $query);
			$lastId=$respuesta_query->fetch_row();
            $idcuenta=$lastId[0];
            $status = $idcuenta;
			$message="Cuenta bancaria registrada correctamente.";
		}
		else{
			$status = 0;
			$message="Cuenta Bancaria no registrada";
		}
		

		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
    }

	public function ModificarCuentaBancaria($cuentadata){
		#Registrando nueva contratacion
		#Inicializando el autocommit de la conexion en falso
		$this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
		#obteniendo datos de nueva contratacion
		#{"idcuenta":0,"identidad":1,"idproveedor":1,"nrocuentabancaria":145155155}
		$idcuenta = $cuentadata->idcuenta;
		$identidad = $cuentadata->identidad;
		$idproveedor = $cuentadata->idproveedor;
		$nrocuentabancaria = $cuentadata->nrocuentabancaria;


		#inicio de query
		$query = "UPDATE CuentaBancaria SET identidad=?, idproveedor=?, nrocuentabancaria=? WHERE idcuenta=?";
		$statement = $this->dbConnect->prepare($query);
		$statement->bind_param("iisi",$identidad, $idproveedor, $nrocuentabancaria,$idcuenta);
		if($statement->execute()){
			$status = 1;
			$message="Cuenta bancaria modificada correctamente.";
		}
		else{
			$status = 0;
			$message="Cuenta Bancaria no modificada";
		}


		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
	}


    public function RegistrarProveedor($proveedorData){
        $rucproveedor = $proveedorData["rucproveedor"];
        $razonsocial = $proveedorData["razonsocial"];
        $direccionproveedor = $proveedorData["direccionproveedor"];
        $contactoproveedor = $proveedorData["contactoproveedor"];
        $telefonoproveedor = $proveedorData["telefonoproveedor"];
        
        $query = "INSERT INTO Proveedor VALUES(DEFAULT, ?,?,?,?,?, 0)";
        $statement = $this->dbConnect->prepare($query);
        $statement->bind_param("sssss",$rucproveedor,$razonsocial,$direccionproveedor,$contactoproveedor,$telefonoproveedor);

        if($statement->execute()){
            $messgae = "Proveedor Registrado con exito.";
			$status = 1;		
        } else{
            $messgae = "Proveedor No Creado.";
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

    public function ListarCuentas($idproveedor){
		$ListaRetorno = array();	
        $list = array();

        $query = "SELECT C.idcuenta, C.identidad, C.nrocuentabancaria, E.nombreentidad  FROM CuentaBancaria C INNER JOIN EntidadBancaria E ON E.identidad = C.identidad WHERE idproveedor = ".$idproveedor;
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaCuentas = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaCuentas['Cuentas'][] = $recorre;
		}		
		if(count($ListaCuentas) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaCuentas['Cuentas'] = array();
        }


		$query ="SELECT identidad, nombreentidad FROM EntidadBancaria;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaEntidades = array();

        $ListaEntidades = $this->llenar_lista_vacia("Entidades",$ListaEntidades, array("identidad" => "0", "nombreentidad" => "TODAS"));
        while($recorre = mysqli_fetch_assoc($respuestaQuery)){
            $ListaEntidades['Entidades'][] = $recorre;
        }

        if(count($ListaEntidades) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaEntidades['Entidades'] = array();
        }

        $ListaRetorno = [$ListaCuentas, $ListaEntidades];
        $list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);
    }

	private function llenar_lista_vacia($nombrelista, $lista, $valoreslista){
		$lista[$nombrelista][] = $valoreslista;
		return $lista;
	}

}

?>