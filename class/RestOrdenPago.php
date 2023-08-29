<?php



class RestOrdenPago{



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

    public function ListarOrdenesPago(){			

		$ListaRetorno = array();	
		$list = array();

		$query = "SELECT OP.*, CPP.concepto, EP.descripcion FROM OrdenPago OP INNER JOIN CuentaPorPagar CPP ON OP.idcuentaporpagar = CPP.idcuentaporpagar INNER JOIN EstadoPago EP ON OP.estadopago=EP.idestadopago ORDER BY OP.estadopago ASC;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaOrdenes = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaOrdenes['OrdenesPago'][] = $recorre;
		}		
		
		if(count($ListaOrdenes) == 0){
			#$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
			$ListaOrdenes['OrdenesPago'] = array();
		}

		$ListaRetorno = [$ListaOrdenes];

		$list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);
	}

	public function GenerarOrdenPago($ordenpagodata){
        $this->dbConnect->autocommit(false);
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

        $idcuentaporpagar = $ordenpagodata->idcuentaporpagar;
        $parcial = $ordenpagodata->parcial;
        $importeOrdenPago = $ordenpagodata->importeOrdenPago;
        
        $query = "INSERT INTO OrdenPago VALUES(DEFAULT, ?,?, ?, '', null, null, null, null, 0);";
        $statement = $this->dbConnect->prepare($query);
        if($statement && $statement->bind_param("iid", $idcuentaporpagar, $parcial, $importeOrdenPago)){
            if($statement->execute()){
                $status=1;
                $message="Orden de pago creada correctamente.";
                $this->dbConnect->commit();
            }
            else{
                $status=0;
					$message="No se pudo crear la orden de pago. Código de error {$this->dbConnect->errno}";
					$this->dbConnect->rollback();
            }
        }
        else{
            $status=0;
			$message="No se pudo crear la orden de pago. Código de error {$this->dbConnect->errno}";
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


	public function cargardatosregistroordenpago(){
		$ListaRetorno = array();	
		$list = array();
		$query="SELECT identidad, nombreentidad FROM EntidadBancaria;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaEntidades = array();
		$ListaEntidades = $this->llenar_lista_vacia("Entidades",$ListaEntidades, array("identidad" => "0", "nombreentidad" => "SELECCIONE"));
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaEntidades['Entidades'][] = $recorre;
		}		

		$query="SELECT idmoneda, UPPER(nombremoneda) AS nombremoneda FROM Moneda;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaMonedas = array();

		$ListaMonedas = $this->llenar_lista_vacia("Monedas",$ListaMonedas, array("idmoneda" => "0", "nombremoneda" => "SELECCIONE"));
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaMonedas['Monedas'][] = $recorre;
		}

		
		$query="SELECT idtipoorden,UPPER(nombre) AS nombre FROM tipoordenpago where idtipoorden>4;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaTipoOrdenes = array();

		$ListaTipoOrdenes = $this->llenar_lista_vacia("Tipos",$ListaTipoOrdenes, array("idtipoorden" => "0", "nombre" => "SELECCIONE"));
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaTipoOrdenes['Tipos'][] = $recorre;
		}

		$query="SELECT idempresa,UPPER(nombreempresa) AS nombreempresa FROM Empresa WHERE tipoempresa=1;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaEmpresas = array();

		$ListaEmpresas = $this->llenar_lista_vacia("Empresas",$ListaEmpresas, array("idempresa" => "0", "nombreempresa" => "SELECCIONE"));
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaEmpresas['Empresas'][] = $recorre;
		}

		$query="SELECT idtipopagocontratacion, UPPER(nombre) AS nombre FROM TipoPagoContratacion;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaTipos = array();

		$ListaTipos = $this->llenar_lista_vacia("FormasPago",$ListaTipos, array("idtipopagocontratacion" => "0", "nombre" => "SELECCIONE"));
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaTipos['FormasPago'][] = $recorre;
		}

		$ListaRetorno = [$ListaEntidades,$ListaMonedas, $ListaTipoOrdenes, $ListaEmpresas,$ListaTipos];

		$list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);
	}

	public function registrarCuentaPorPagar($data){
		$this->dbConnect->autocommit(false);
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
		$conceptopago = $data->conceptopago;
		$importepago = $data->importepago;
		$documento = $data->documento;
		$razonsocial = $data->razonsocial;
		$identidad = $data->identidad;
		$numerocuenta = $data->numerocuenta;
		$idmoneda = $data->idmoneda;
		$fechalimite = $data->fechalimite;
		$idtipoorden = $data->idtipoorden;
		$idobra = $data->idobra;
		$idempresa = $data->idempresa;
		$formapago = $data->formapago;
		$query = "INSERT INTO CuentaPorPagar VALUES(DEFAULT, ?, ?,?, ?, ?, ?, ?, ?, CURDATE(), STR_TO_DATE(? , '%Y-%m-%d'), NULL, NULL, 1, '', '', NULL, '', 3, false, '-', false, ?, ?,?,?);";
		$statement=$this->dbConnect->prepare($query);
		if($statement){
			if($statement->bind_param("sddssisisiiii", $conceptopago,$importepago,$importepago,$documento,$razonsocial,$identidad,$numerocuenta,$idmoneda,$fechalimite,$idtipoorden,$idempresa,$idobra,$formapago)){
				if($statement->execute()){
					$status=1;
					$message="Orden registrada correctamente.";
					$this->dbConnect->commit();
				}
				else{
					$status=0;
					$message="No se pudo registrar la orden. Código de error {$this->dbConnect->errno}";
					$this->dbConnect->rollback();
				}
			}
			else{
				$status=0;
				$message="No se pudo registrar la orden. Código de error {$this->dbConnect->errno}";
				$this->dbConnect->rollback();
			}
		}
		else{
			$status=0;
			$message="No se pudo registrar la orden. Código de error {$this->dbConnect->errno}";
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

    public function confirmarPago($data){
        $this->dbConnect->autocommit(false);
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

        $idordenpago = $data->idordenpago;
		$fechapago = $data->fechapago;
		$numerotransaccion = $data->numerotransaccion;
		$idempresa=$data->idempresa;
		$importepago = $data->importepago;
		$idcontratacion = $data->idcontratacion;
		$idobra = $data->idobra;
		$fechavalor = $data->fechavalor;
		$ITF = $data->ITF;
		$referenciabanco = $data->referenciabanco;

		$idestadopago=$data->idestadopago;

		$bandera = $data->bandera;

		if($bandera==1){
			//QUERY PARA ACTUALIZAR ORDEN PAGO, PONERLA COMO PAGADA Y ASIGNAR DATOS COMO NUMERO DE TRANSACCIÓN O FECHA DE PAGO
			$query = "UPDATE OrdenPago SET idestadopago=?, pendiente=pendiente-?, fechapago=STR_TO_DATE(? , '%Y-%m-%d') , numerotransaccion=?, fechavalor=STR_TO_DATE(? , '%Y-%m-%d'), ITF=?, referenciabanco=? WHERE idordenpago=? ;";
			$statement = $this->dbConnect->prepare($query);
			$statement->bind_param("idsssdsi",$idestadopago,$importepago, $fechapago,$numerotransaccion,$fechavalor, $ITF, $referenciabanco,$idordenpago);
			if($statement->execute()){
				//QUERY PARA OBTENER LAS ÓRDENES DE PAGO PENDIENTES DE UNA CONTRATACIÓN
				$query = "SELECT COUNT(*) FROM OrdenPago WHERE OrdenPago.idcontratacion=".$idcontratacion." AND OrdenPago.idestadopago=1;";
				$respuesta_query = mysqli_query($this->dbConnect, $query);
				$conteorestantes=$respuesta_query->fetch_row();
				$restantes=(int)$conteorestantes[0];

				//SEGÚN SI EXISTEN ÓRDENES DE PAGO PENDIENTES, EL ESTADO DE LA CONTRATACIÓN SE VUELVE PROCESADA(3) O PARCIALMENTE PROCESADA(2)
				$nuevoestado = $restantes==0?3:2;

				//ACTUALIZANDO ESTADO CONTRATACIÓN SEGÚN SI TIENE PAGOS PENDIENTES
				$query = "UPDATE Contratacion SET Contratacion.idestadocontratacion=? WHERE idcontratacion=?";
				$statement = $this->dbConnect->prepare($query);
				$statement->bind_param("ii", $nuevoestado,$idcontratacion);
				if($statement->execute()){

					//AUMENTANDO GASTOS DE OBRA PARA LUEGO VER EL PRESUPUESTO RESTANTE
					$query = "UPDATE Obra SET gastos = gastos + ? WHERE idobra=?";
					$statement = $this->dbConnect->prepare($query);
					$statement->bind_param("di",$importepago,$idobra);
					if($statement->execute()){

						//OBTENIENDO SI LA CONTRATACION ES ORDEN DE COMPRA(1) O SI ES ORDEN DE SERVICIO(2), PARA SABER SI GENERAR COSTO
						$query="SELECT tipocontratacion FROM Contratacion WHERE idcontratacion={$idcontratacion};";
						$respuesta_query=mysqli_query($this->dbConnect, $query);
						$filacontratacion = $respuesta_query->fetch_row();
						$tipocontratacion = (int)$filacontratacion[0];

						//SI ES ORDEN DE SERVICIO GENERAMOS UN REGISTRO DE COSTO EN LA TABLA CORRESPONDIENTE
						if($tipocontratacion==2){
							$coderror=-1;
							#Estamos pasándole el IDordenpago a la función de registrar Egreso, debido a que es un egreso que provienede de una orden, por lo mismo, el idcuentaporpagar es null
							if($this->registrarCostoServicio($coderror,$idobra,$fechavalor, $importepago, $idcontratacion) && $this->registrarEgreso($coderror,$idempresa,$importepago, $idordenpago, null,$numerotransaccion,$referenciabanco,$ITF,$fechapago, $fechavalor)){
								$status=1;
								$message="Pago confirmado correctamente";
								$this->dbConnect->commit();
							}
							else{
								$status=0;
								$message="Error registrando costo. Código de error {$coderror}";
								$this->dbConnect->rollback();
							}
						}
						//SI NO ES ORDEN DE SERVICIO, AHÍ TERMINA EL FLUJO DE LA FUNCIÓN
						else{
							$coderror=-1;
							if($this->registrarEgreso($coderror,$idempresa,$importepago, $idordenpago, null,$numerotransaccion, $referenciabanco,$ITF, $fechapago, $fechavalor)){
								$status=1;
								$message="Pago confirmado correctamente";
								$this->dbConnect->commit();
							}else{
								$status=0;
								$message="Error registrando costo. Código de error {$coderror}";
								$this->dbConnect->rollback();
							}
						}
					}
					else{
						$status=0;
						$message="Error al generar gasto en registo de obra. Código de error: {$this->dbConnect->errno}";
						$this->dbConnect->rollback();
					}
				}
				else{
					$status=0;
					$message="Error al cambiar el estado a la contratación. Código de error: {$this->dbConnect->errno}";
					$this->dbConnect->rollback();
				}
			}
			else{
				$status=0;
				$message="Error, no se pudo confirmar pago. Código de error: {$this->dbConnect->errno}";
				$this->dbConnect->rollback();
			}
		}
		else{
			$query="UPDATE CuentaPorPagar SET idestado=?, pendiente=pendiente-?, fechapago=STR_TO_DATE(?,'%Y-%m-%d'), numoperacion=?, fechavalor=STR_TO_DATE(? , '%Y-%m-%d'), ITF=?, refbanco=? WHERE idcuentaporpagar=?";
			$statement=$this->dbConnect->prepare($query);
			if($statement){
				if($statement->bind_param("idsssdsi",$idestadopago,$importepago, $fechapago, $numerotransaccion, $fechavalor, $ITF, $referenciabanco, $idordenpago)){
					if($statement->execute()){
						$coderror=-1;
							#En este caso, el idordenpago va después porque en realidad es el ID CUENTA POR PAGAR, y como idordenpago le estamos pasando NULL a la función
							if($this->registrarEgreso($coderror,$idempresa,$importepago, null, $idordenpago,$numerotransaccion,$referenciabanco, $ITF, $fechapago, $fechavalor)){
								$status=1;
								$message="Pago confirmado correctamente";
								$this->dbConnect->commit();
							}else{
								$status=0;
								$message="Error registrando costo. Código de error {$coderror}";
								$this->dbConnect->rollback();
							}
					}
					else{
						$status=0;
						$message="Error al confirmar pago. Código de error: {$this->dbConnect->errno}";
						$this->dbConnect->rollback();
					}
				}
				else{
					$status=0;
					$message="Error al confirmar pago. Código de error: {$this->dbConnect->errno}";
					$this->dbConnect->rollback();
				}
			}
			else{
				$status=0;
				$message="Error al confirmar pago. Código de error: {$this->dbConnect->errno}";
				$this->dbConnect->rollback();
			}
		}

		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
    }

	private function registrarEgreso(&$codigoError, $idempresa, $importe, $idordenpago, $idcuenta, $numoperacion, $refbanco, $ITF, $fechapago, $fechavalor){
		$query="UPDATE Empresa SET presupuesto=presupuesto-? WHERE idempresa=?";
		$statement=$this->dbConnect->prepare($query);
		if($statement){
			if($statement->bind_param("di",$importe,$idempresa)){
				if($statement->execute()){
					$query="INSERT INTO Egreso VALUES(DEFAULT,?,?,?,?,?,?,STR_TO_DATE(? , '%Y-%m-%d'),STR_TO_DATE(? , '%Y-%m-%d'))";
					$statement=$this->dbConnect->prepare($query);
					if($statement){
						if($statement->bind_param("iidssdss", $idordenpago, $idcuenta, $importe,$numoperacion,$refbanco,$ITF,$fechapago, $fechavalor)){
							if($statement->execute()){
								return true;
							}
							else{
								$codigoError=$this->dbConnect->errno;
								return false;
							}
						}
						else{
							$codigoError=$this->dbConnect->errno;
							return false;
						}
					}
					else{
						$codigoError=$this->dbConnect->errno;
						return false;
					}
				}
				else{
					$codigoError=$this->dbConnect->errno;
					return false;
				}
			}
			else{
				$codigoError=$this->dbConnect->errno;
				return false;
			}
		}
		else{
			$codigoError=$this->dbConnect->errno;
			return false;
		}
	}

	public function autorizarPago($dataOrden){
		$this->dbConnect->autocommit(false);
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

        $idordenpago = $dataOrden->idordenpago;

		$query="UPDATE OrdenPago SET autorizado=1 WHERE idordenpago = ?";
		$statement = $this->dbConnect->prepare($query);
		if($statement){
			if($statement->bind_param("i",$idordenpago)){
				if($statement->execute()){
					$status=1;
					$message="Orden autorizada correctamente.";
					$this->dbConnect->commit();
				}
				else{
					$status=0;
					$message="Orden no autorizada. Código de error {$this->dbConnect->errno}";
					$this->dbConnect->commit();
				}
			}
			else{
				$status=0;
				$message="Orden no autorizada. Código de error {$this->dbConnect->errno}";
				$this->dbConnect->commit();
			}
		}
		else{
			$status=0;
			$message="Orden no autorizada. Código de error {$this->dbConnect->errno}";
			$this->dbConnect->commit();
		}
		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
	}

	private function registrarCostoServicio(&$codigoError,$idobra,$fechacosto,$cantidadcosto, $idcontratacion){
		$query = "INSERT INTO CostoServicio VALUES(DEFAULT, ?, STR_TO_DATE(? , '%Y-%m-%d'), ?, ?);";
		$statement = $this->dbConnect->prepare($query);
		if($statement){
			if($statement->bind_param("isdi", $idobra, $fechacosto,$cantidadcosto, $idcontratacion)){
				if($statement->execute()){
					return true;
				}
				else{
					$codigoError=$this->dbConnect->errno;
					return false;
				}
			}
			else{
				$codigoError=$this->dbConnect->errno;
				return false;
			}
		}
		else{
			$codigoError=$this->dbConnect->errno;
			return false;
		}
	}

	public function verDetalleOrdenPago($id){
		$query="SELECT idordenpago, OrdenPago.idcontratacion, C.codigoContratacion ,conceptopago, importepago, fechaorden, fechalimite, DATEDIFF(fechalimite,now()) as diaspendientes,(CASE WHEN fechapago 
		IS NULL THEN 'PENDIENTE' ELSE fechapago END) AS fechapago, OrdenPago.idestadopago, E.descripcion , C.idobra, (CASE WHEN C.idtipocomprobante IS NULL THEN 0 ELSE C.idtipocomprobante END)
		AS idtipocomprobante, (CASE WHEN C.nrocomprobante IS NULL THEN '-' ELSE C.nrocomprobante END) AS nrocomprobante,C.comprobante, (CASE WHEN TipoComprobante.nombretipo IS NULL THEN '-' ELSE TipoComprobante.nombretipo END) AS nombretipo,
		OrdenPago.idtipoorden, UPPER(tipoordenpago.nombre) AS nombre, OrdenPago.autorizado, 1 AS bandera FROM OrdenPago INNER JOIN Contratacion C ON OrdenPago.idcontratacion= C.idcontratacion 
		INNER JOIN EstadoPago E ON OrdenPago.idestadopago = E.idestadopago 
		LEFT JOIN TipoComprobante ON C.idtipocomprobante = TipoComprobante.idtipocomprobante LEFT JOIN tipoordenpago ON OrdenPago.idtipoorden = tipoordenpago.idtipoorden  WHERE idordenpago={$id};";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $detalle = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$detalle[] = $recorre;
		}	
		header('Content-Type: application/json');
		echo json_encode($detalle);
	}

	public function verDetalleCuentaPorPagar($id){
		$query="SELECT CPP.idcuentaporpagar AS idordenpago, '' AS CodigoContratacion,CPP.conceptopago, CPP.importepago, CPP.fechaorden, CPP.fechalimite, DATEDIFF(CPP.fechalimite,now()) as diaspendientes,(CASE WHEN fechapago 
		IS NULL THEN 'PENDIENTE' ELSE fechapago END) AS fechapago, CPP.idestado AS idestadopago, E.descripcion, '0' AS idobra, (CASE WHEN CPP.tipoComprobante IS NULL THEN 0 ELSE CPP.tipoComprobante END)
		AS idtipocomprobante, (CASE WHEN CPP.numerocomprobante IS NULL THEN '-' ELSE CPP.numerocomprobante END) AS nrocomprobante,CPP.comprobante, (CASE WHEN TipoComprobante.nombretipo IS NULL THEN '-' ELSE TipoComprobante.nombretipo END) AS nombretipo,
		CPP.idtipoorden, UPPER(tipoordenpago.nombre) AS nombre, CPP.autorizado, 0 AS bandera FROM CuentaPorPagar CPP
		INNER JOIN EstadoPago E ON CPP.idestado = E.idestadopago 
		LEFT JOIN TipoComprobante ON CPP.tipocomprobante = TipoComprobante.idtipocomprobante LEFT JOIN tipoordenpago ON CPP.idtipoorden = tipoordenpago.idtipoorden  WHERE idcuentaporpagar={$id};";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $detalle = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$detalle[] = $recorre;
		}	
		header('Content-Type: application/json');
		echo json_encode($detalle);
	}
	
	public function ListarOrdenesPagoMovil(){			

		$ListaRetorno = array();	
		$list = array();

		$query = "SELECT idordenpago, OrdenPago.idcontratacion, C.codigoContratacion,Moneda.simbolomoneda ,conceptopago, importepago, fechaorden, fechalimite, DATEDIFF(fechalimite,now()) as diaspendientes,(CASE WHEN fechapago 
		IS NULL THEN 'PENDIENTE' ELSE fechapago END) AS fechapago, OrdenPago.idestadopago, E.descripcion , C.idobra, (CASE WHEN C.idtipocomprobante IS NULL THEN 0 ELSE C.idtipocomprobante END)
		AS idtipocomprobante, (CASE WHEN C.nrocomprobante IS NULL THEN '-' ELSE C.nrocomprobante END) AS nrocomprobante,C.comprobante, (CASE WHEN TipoComprobante.nombretipo IS NULL THEN '-' ELSE TipoComprobante.nombretipo END) AS nombretipo,
		OrdenPago.idtipoorden, UPPER(tipoordenpago.nombre) AS nombre, OrdenPago.autorizado, 1 AS bandera,OrdenPago.idempresa FROM OrdenPago INNER JOIN Contratacion C ON OrdenPago.idcontratacion= C.idcontratacion 
		INNER JOIN EstadoPago E ON OrdenPago.idestadopago = E.idestadopago INNER JOIN Moneda ON C.idmoneda=Moneda.idmoneda
		LEFT JOIN TipoComprobante ON C.idtipocomprobante = TipoComprobante.idtipocomprobante LEFT JOIN tipoordenpago ON OrdenPago.idtipoorden = tipoordenpago.idtipoorden
		UNION ALL
		(SELECT idcuentaporpagar, '-' AS idcontratacion, '-' AS codigoContratacion, Moneda.simbolomoneda, conceptopago, importepago, fechaorden, fechalimite , DATEDIFF(fechalimite,now()) AS diaspendientes, 
		(CASE WHEN fechapago IS NULL THEN 'PENDIENTE' ELSE fechapago END) AS fechapago,idestado AS idestadopago, EstadoPago.descripcion, CuentaPorPagar.idobra, tipoComprobante, numerocomprobante,
		comprobante, TipoComprobante.nombretipo, CuentaPorPagar.idtipoorden, UPPER(tipoordenpago.nombre) AS nombre, autorizado, 0 AS bandera, CuentaPorPagar.idempresa FROM CuentaPorPagar
		INNER JOIN EstadoPago ON CuentaPorPagar.idestado=EstadoPago.idestadopago INNER JOIN Moneda ON CuentaPorPagar.idmoneda=Moneda.idmoneda
		INNER JOIN TipoComprobante ON CuentaPorPagar.tipoComprobante=TipoComprobante.idtipocomprobante INNER JOIN tipoordenpago ON CuentaPorPagar.idtipoorden=tipoordenpago.idtipoorden);";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaOrdenes = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaOrdenes['OrdenesPago'][] = $recorre;
		}		
		
		if(count($ListaOrdenes) == 0){
			#$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
			$ListaOrdenes['OrdenesPago'] = array();
		}

		$query = "SELECT idempresa, nombreempresa, presupuesto FROM Empresa WHERE tipoempresa=1;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaEmpresas = array();


		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaEmpresas['Empresa'][] = $recorre;
		}

		if(count($ListaEmpresas)==0){
			$ListaEmpresas = $this->llenar_lista_vacia("Empresa",$ListaEmpresas, array("idempresa" => "0", "nombreempresa" => "SELECCIONE", "presupuesto" => "0.0"));
		}


		$ListaRetorno = [$ListaOrdenes, $ListaEmpresas];

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