<?php

include('../correo/MailSender.php');

class RestContratacion{


	private $dbConnect = false;

    public function __construct(){

		include('../config/config.php');

        if(!$this->dbConnect){

			$conn = new mysqli($host, $user, $password, $database);
            if($conn->connect_error){

                die("Error failed to connect to MySQL: " . $conn->connect_error);

            }else{

				$conn->set_charset('utf8');
				$conn->autocommit(false);
                $this->dbConnect = $conn;

            }

        }

    }


    public function CargarDatosRegistrarContratacion($idrequerimiento){
		$ListaRetorno = array();	
		$list = array();
		$query = "SELECT i.iditem, UPPER(i.nombreitem) as nombreitem, UPPER(i.unidaditem) as unidaditem, i.cantidaditem,i.cantidadpendiente, i.observacionitem, i.iditemcentrocostos, 0 as precio, i.estadoitem
			from ItemRequerimiento i 
			where i.idrequerimiento = ".$idrequerimiento." and i.estadoitem!=2 and i.estadoitem!=4;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaDetalle = array();

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
				$ListaDetalle['DetalleRequerimiento'][]  = $recorre;
		}
		if(count($ListaDetalle) == 0){
			$ListaDetalle['DetalleRequerimiento']=array();
		}

		$query = "SELECT idtipocontratacion, UPPER(nombretipocontratacion) as nombretipocontratacion  FROM TipoContratacion;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaTipoContrato = array();

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
				$ListaTipoContrato['TiposContrato'][]  = $recorre;
		}

		$query = "SELECT idtipopagocontratacion, UPPER(nombre) AS nombre  from TipoPagoContratacion where eliminado = 0;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaForma = array();

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
				$ListaForma ['FormasPago'][]  = $recorre;
		}

		$query ="SELECT Moneda.idmoneda, CONCAT(Moneda.simbolomoneda,' ',UPPER(Moneda.nombremoneda)) AS nombre FROM Moneda;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$Listamonedas = array();

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
				$Listamonedas ['Moneda'][]  = $recorre;
		}

		$ListaRetorno = [$ListaDetalle, $ListaTipoContrato,$ListaForma,$Listamonedas];

		$list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);
    }    

	public function Obteneritemrequerimiento($idrequerimiento){
		$query = "SELECT i.iditem, UPPER(i.nombreitem) as nombreitem, UPPER(i.unidaditem) as unidaditem, i.cantidaditem,i.cantidadpendiente, UPPER(i.observacionitem) AS observacionitem, i.iditemcentrocostos, 0 as precio, i.estadoitem
			from ItemRequerimiento i 
			where i.idrequerimiento = ".$idrequerimiento." and i.estadoitem!=2 and i.estadoitem!=4;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaDetalle = array();

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
				$ListaDetalle[]  = $recorre;
		}
		header('Content-Type: application/json');
		echo json_encode($ListaDetalle);
	}

	public function ListarContrataciones(){
		$ListaRetorno = array();	
		$list = array();

		$query = "SELECT C.idcontratacion, C.fechacontratacion, UPPER(TC.nombretipocontratacion) AS nombretipocontratacion, UPPER(O.codigoObra) AS codigoObra, 
		UPPER(O.nomenclatura) AS nomenclatura,UPPER(C.codigoContratacion) AS codigoContratacion, UPPER(TPC.nombre) AS nombre, UPPER(P.razonsocial) AS razonsocial, 
		UPPER(EB.nombreentidad) AS entidadbancaria, CB.nrocuentabancaria AS cuentabancaria, C.totalcontrato, C.comprobante,
		(CASE WHEN TipoComprobante.nombretipo IS NULL THEN '' ELSE TipoComprobante.nombretipo END) AS nombretipo ,(CASE WHEN C.nrocomprobante IS NULL THEN '' ELSE C.nrocomprobante END) 
		AS nrocomprobante, EstadoContratacion.nombre
		FROM Contratacion C INNER JOIN TipoContratacion TC ON C.tipocontratacion=TC.idtipocontratacion INNER JOIN Obra O ON C.idobra=O.idobra 
		INNER JOIN TipoPagoContratacion TPC ON C.formapago = TPC.idtipopagocontratacion INNER JOIN Proveedor P ON C.idproveedor = P.idproveedor 
		LEFT JOIN CuentaBancaria CB ON CB.idcuenta = C.idcuenta LEFT JOIN EntidadBancaria EB ON EB.identidad = CB.identidad 
		LEFT JOIN TipoComprobante ON C.idtipocomprobante = TipoComprobante.idtipocomprobante INNER JOIN EstadoContratacion ON C.idestadocontratacion=EstadoContratacion.idestado ORDER BY C.fechacontratacion;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaContrataciones = array();
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaContrataciones['Contrataciones'][] = $recorre;
		}

		if(count($ListaContrataciones) == 0){
			#$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
			$ListaContrataciones['Contrataciones'] = array();
		}

		$query = "SELECT idtipocontratacion, UPPER(nombretipocontratacion) AS nombretipocontratacion FROM TipoContratacion;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaTipoContrataciones = array();

		$ListaTipoContrataciones = $this->llenar_lista_vacia("TipoContrataciones",$ListaTipoContrataciones, array("idtipocontratacion" => "0", "nombretipocontratacion" => "SELECCIONE"));
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaTipoContrataciones['TipoContrataciones'][] = $recorre;
		}

		if(count($ListaTipoContrataciones) == 0){
			#$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
			$ListaTipoContrataciones['TipoContrataciones'] = array();
		}

		$ListaRetorno = [$ListaContrataciones, $ListaTipoContrataciones];

		$list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);
	}

	public function BuscarContratacion($desde, $hasta, $tipo){

		$extra = $tipo=="0"? ";": " AND C.tipocontratacion=".$tipo.";";

		$query = "SELECT C.idcontratacion,UPPER(C.codigoContratacion) AS codigoContratacion , C.fechacontratacion, UPPER(TC.nombretipocontratacion) AS nombretipocontratacion, UPPER(O.codigoObra) AS codigoObra, UPPER(O.nomenclatura) AS nomenclatura , UPPER(TPC.nombre) AS nombre, UPPER(P.razonsocial) AS razonsocial, UPPER(EB.nombreentidad) AS entidadbancaria, CB.nrocuentabancaria AS cuentabancaria, C.totalcontrato
		FROM Contratacion C INNER JOIN TipoContratacion TC ON C.tipocontratacion=TC.idtipocontratacion INNER JOIN Obra O ON C.idobra=O.idobra 
		INNER JOIN TipoPagoContratacion TPC ON C.formapago = TPC.idtipopagocontratacion INNER JOIN Proveedor P ON C.idproveedor = P.idproveedor 
		LEFT JOIN CuentaBancaria CB ON CB.idcuenta = C.idcuenta LEFT JOIN EntidadBancaria EB ON EB.identidad = CB.identidad WHERE C.fechacontratacion BETWEEN CAST('".$desde."' AS DATE) AND CAST('".$hasta."' AS DATE)".$extra;

		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaContrataciones = array();
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaContrataciones[] = $recorre;
		}


		header('Content-Type: application/json');
		echo json_encode($ListaContrataciones);
	}

	public function ListarTIpoComprobante(){
		$query = "SELECT idtipocomprobante, nombretipo FROM TipoComprobante;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaTipoComprobantes = array();
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaTipoComprobantes[] = $recorre;
		}


		header('Content-Type: application/json');
		echo json_encode($ListaTipoComprobantes);
	}

	public function listardetracciones(){
		$query = "SELECT * FROM PorcentajesDetraccion";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaDetraccion = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaDetraccion[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaDetraccion);
	}


	public function registrarContratacion($contrataciondata){
		#Registrando nueva contratacion
		$registrado = true;
		#Inicializando el autocommit de la conexion en falso
		$this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
		#obteniendo datos de nueva contratacion
		$idrequerimiento = $contrataciondata->idrequerimiento;
		$tipocontratacion = $contrataciondata->tipocontratacion;
		$idobra = $contrataciondata->idobra;
		$formapago = $contrataciondata->formapago;
		$idproveedor = $contrataciondata->idproveedor;
		#$entidadbancaria = $contrataciondata->entidadbancaria;
		#$cuentabancaria = $contrataciondata->cuentabancaria;
		$idcuenta = $contrataciondata->idcuenta;
		$totalcontrato = $contrataciondata->totalcontrato;
		$idmoneda = $contrataciondata->idmoneda;

		$ventagravada = $contrataciondata->ventagravada;
		$ventaNogravada = $contrataciondata->ventaNogravada;
		$igv = $contrataciondata->igv;

		$detraccioncont = $contrataciondata->detraccion;
		$porcentajedetraccion = $contrataciondata->porcentajedetraccion;
		$pagaproveedor = $contrataciondata->pagaproveedor;
		$montoconcredito = $contrataciondata->montoconcredito;
		$montopendiente = $contrataciondata->montopendiente;
		$montoneto = $contrataciondata->montoneto;
		$credito = $contrataciondata->credito;
		$porcentajecredito = $contrataciondata->porcentajecredito;
		$fechapagocredito = $contrataciondata->fechapagocredito;

		$codigoObra = $contrataciondata->codigoObra;
		$idlogistico = $contrataciondata->idlogistico;

		
		$orden= "";
		$codOrden = "";
		switch ($tipocontratacion) {
			case 1:
				$orden = "Orden de compra";
				$codOrden ="OC";
				break;
			case 2:
				$orden = "Orden de servicio";
				$codOrden ="OS";
				break;
			default:
				$orden = "Contratacion";
				$codOrden ="C";
				break;
		}

		$anio = date("y");

		#inicio de query
		$query = "INSERT INTO Contratacion VALUES(DEFAULT, ?,CURDATE(),?,?,?,?,?,0,?,?,?,?,?, NULL,?,?,?,?,?,?,?,?,STR_TO_DATE(? , '%Y-%m-%d'),1,NULL, NULL,0,?, '');";
		$statement = $this->dbConnect->prepare($query);
		if(!$statement){
			echo $this->dbConnect->error;
		}
		$statement->bind_param("iiiiidiidddddidddidss",$idrequerimiento, $tipocontratacion, $idobra, $formapago, $idproveedor,$totalcontrato,$idcuenta,$idmoneda,$ventagravada,$ventaNogravada,$igv,$detraccioncont,$porcentajedetraccion,$pagaproveedor,$montoconcredito,$montopendiente,$montoneto,$credito,$porcentajecredito,$fechapagocredito,$idlogistico);
		if($statement->execute()){
			$query =  "SELECT LAST_INSERT_ID()";
			$respuesta_query = mysqli_query($this->dbConnect, $query);
			$lastId=$respuesta_query->fetch_row();
			$idcontratacion=$lastId[0];

			$query =  "SELECT COUNT(*) FROM Contratacion WHERE idobra =".$idobra." AND tipocontratacion=".$tipocontratacion.";";
            $respuesta_query = mysqli_query($this->dbConnect, $query);
            $conteo=$respuesta_query->fetch_row();
            $totalfilas=$conteo[0];

			#str_pad($idrequerimiento, 6, "-", STR_PAD_LEFT);
            $strcorrelativo = str_pad($totalfilas, 5, "0", STR_PAD_LEFT);
            #echo $strcorrelativo;
			$codigoContratacion = "{$codOrden}-{$codigoObra}-{$anio}-{$strcorrelativo}";
			//echo $codigoContratacion;
			//CONCAT('".$codOrden."-','".$codigoObra."-','".$anio."-','".$strcorrelativo."')
            $correlativoquery = "UPDATE Contratacion SET Contratacion.codigoContratacion = '".$codigoContratacion."' WHERE Contratacion.idcontratacion = ".$idcontratacion.";";
            #echo $correlativoquery;
            if (mysqli_query($this->dbConnect, $correlativoquery)){

			$listaitems = $contrataciondata->ListaItemContratacion;
			foreach ($listaitems as $itemactual) {
					$iditemrequerimiento = $itemactual->iditem;
					$nombreitem= $itemactual->nombreitem;
					$unidadmedida = $itemactual->unidadmedida;
					$cantidad = $itemactual->cantidad;
					$precio = $itemactual->precio;

					#$itemrequerimiento = $itemactual->iditem;
					$estado = $itemactual->estado;
					$gravado = $itemactual->gravado;
					$detraccion = $itemactual->detraccion;
					$porcentajedetraccion=$itemactual->porcentajedetraccion;

					$query = "INSERT INTO ItemContratacion VALUES(DEFAULT,?,?,?,?,?,?,?,?,?)";
					$statement = $this->dbConnect->prepare($query);
					$statement->bind_param("ssddiiiid", $nombreitem, $unidadmedida, $cantidad, $precio, $idcontratacion,$gravado,$detraccion, $porcentajedetraccion,$iditemrequerimiento);
					if(!$statement->execute()){
						$registrado=false;
						break;
					}
					$query = "UPDATE ItemRequerimiento SET cantidadpendiente =GREATEST(0,cantidadpendiente-".$cantidad."), estadoitem=".$estado." WHERE iditem=".$iditemrequerimiento.";";
					if(! mysqli_query($this->dbConnect, $query)){
						$registrado=false;
						break;
					}
				}
			}
			else{
				$registrado=false;
			}
			if($registrado){
				$query = "SELECT COUNT(*) FROM ItemRequerimiento WHERE idrequerimiento=".$idrequerimiento." AND ItemRequerimiento.estadoitem!=2;";
				$respuesta_query = mysqli_query($this->dbConnect, $query);
				$conteorestantes=$respuesta_query->fetch_row();
				$restantes=(int)$conteorestantes[0];
				$registradoordenpago=true;
				if($restantes==0){
					$query = "UPDATE RequerimientoObra SET estado=2 WHERE idrequerimiento=".$idrequerimiento.";";
					if(!mysqli_query($this->dbConnect, $query)){
						$this->dbConnect->rollback();
						$status=0;
						$message="Error al registrar item de ".$orden.".".$orden." no registrada";
					}
				}
				else{
					$query = "UPDATE RequerimientoObra SET estado=4 WHERE idrequerimiento=".$idrequerimiento.";";
					if(!mysqli_query($this->dbConnect, $query)){
						$this->dbConnect->rollback();
						$status=0;
						$message="Error al registrar item de ".$orden.".".$orden." no registrada";
					}
				}

				//TODO: Crear ordenes de pago
				$fechapagomonto = date('Y-m-d', strtotime('+2 week'));
				if($detraccioncont>0 && !$pagaproveedor){
					//generar el pago de la detraccion
					$date = date('Y-m', strtotime('+1 month')); //Añadiendo un mes al mes actual para agarrar el quinto día

					//Registrando pago, y en caso falle hacer rollback y devolver el mensaje correspondiente
					if(!$this->registrarPago($detraccioncont, $date.'-05', $idcontratacion, 'PAGO DETRACCION ORDEN '.$codigoContratacion.".", 1, $identidad, $idmoneda, $formapago, $idobra)){
						$status=0;
						$message="Error al crear la orden de pago de detraccion. Comuniquese con el proveedor.";
						$registradoordenpago=false;
						$this->dbConnect->rollback();
					}
					else{
						$this->dbConnect->commit();
					}
				}
				if($credito){
					//agarrar de $montoconcredito
					if($montoconcredito>0){
						if(!$this->registrarPago($montoconcredito,$fechapagomonto,$idcontratacion,"PAGO DE ORDEN ".$codigoContratacion.".", 2, $identidad, $idmoneda, $formapago, $idobra)){
							$status=0;
							$message="Error al crear la orden de pago de credito. Comuniquese con el proveedor.";
							$registradoordenpago=false;
							$this->dbConnect->rollback();
						}
						else{
							$this->dbConnect->commit();
						}
					}
					if(!$this->registrarPago($montopendiente,$fechapagocredito,$idcontratacion, "PAGO CREDITO ".$codigoContratacion.".", 3, $identidad, $idmoneda, $formapago, $idobra)){
						$status=0;
						$message="Error al crear la orden de pago del monto pendiente. Comuniquese con el proveedor.";
						$registradoordenpago=false;
						$this->dbConnect->rollback();
					}
					else{
						$this->dbConnect->commit();
					}
				}
				else{
					//agarrar de $montopendiente
					if(!$this->registrarPago($montoneto,$fechapagomonto, $idcontratacion, "PAGO DE ORDEN ".$codigoContratacion.".", 4, $identidad, $idmoneda, $formapago, $idobra)){
						$status=0;
						$message="Error al crear la orden de pago. Comuniquese con el proveedor.";
						$registradoordenpago=false;
						$this->dbConnect->rollback();
					}
					else{
						$this->dbConnect->commit();
					}
				}
				

				if($registradoordenpago){
					$this->dbConnect->commit();
					$status=$idcontratacion;
					$message= $orden." registrada. ";
					try{
						$sender = new MailSender();
						$query ="SELECT nombretrabajador, correotrabajador FROM Trabajador WHERE idtipotrabajador=4;";
						$respuesta_query = mysqli_query($this->dbConnect, $query);
						$correos=array();
						while($recorre = mysqli_fetch_assoc($respuesta_query)){
							//TODO: VER ESTO MAÑANA URGENTE
							$correos[$recorre['nombretrabajador']] = $recorre['correotrabajador'];
						}
	
						if($sender->enviarCorreoContratacion($correos)){
						//echo 'Correo enviado satisfactoriamente';
						}
						else{
							//echo 'Falló el envío de correo';
						}
					}
					catch(Exception $e){
	
					}
				}
				else{
					$this->dbConnect->rollback();
				}
			}
			else{
				$this->dbConnect->rollback();
				$status=0;
				$message="Error al registrar item de ".$orden.".".$orden." no registrada";
			}
		}
		else{
			$status = 0;
			$message=$orden." no registrada";
		}
		

		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
	}

	public function registrarComprobanteContratacion($contrataciondata){
		$this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
		

		$idcontratacion=$contrataciondata->idcontratacion;
		$idtipocomprobante=$contrataciondata->idtipocomprobante;
		$nrocomprobante = $contrataciondata->nrocomprobante;

		$query = "UPDATE Contratacion SET idtipocomprobante = ? , nrocomprobante=?, comprobante=true WHERE idcontratacion=?";
		$statement = $this->dbConnect->prepare($query);
		if(!$statement){
			$status=0;
			$message="Error, no se pudo completar la accion. Codigo de error: {$this->dbConnect->errno}";
			$this->dbConnect->rollback();
		}
		$statement->bind_param("isi",$idtipocomprobante,$nrocomprobante,$idcontratacion);
		if(!$statement->execute()){
			$status=0;
			$message="Error, no se pudo completar la accion. Codigo de error: {$this->dbConnect->errno}";
			$this->dbConnect->rollback();
		}
		$status=1;
		$message="Comprobante registrado correctamente.";
		$this->dbConnect->commit();

		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
	}


	private function registrarPago($montopago, $fechalimite, $idcontratacion,$concepto, $idtipopago, $identidad, $idmoneda, $idforma, $idobra){
		$query="SELECT idempresa,Proveedor.rucproveedor, Proveedor.razonsocial, CuentaBancaria.nrocuentabancaria, EntidadBancaria.nombreentidad FROM Contratacion INNER JOIN Obra ON Contratacion.idobra=Obra.idobra 
		INNER JOIN Empresa ON Obra.operariotributario=Empresa.idempresa INNER JOIN Proveedor ON Contratacion.idproveedor = Proveedor.idproveedor LEFT JOIN CuentaBancaria ON Contratacion.idcuenta = CuentaBancaria.idcuenta
		LEFT JOIN EntidadBancaria ON EntidadBancaria.identidad = CuentaBancaria.identidad WHERE idcontratacion={$idcontratacion};";
		$respuesta_query = mysqli_query($this->dbConnect, $query);
		$rowempresa=$respuesta_query->fetch_row();
		$idempresa=$rowempresa[0];
		$docproveedor = $rowempresa[1];
		$razonproveedor = $rowempresa[2];
		$nrocuenta = $rowempresa[3];
		$identidad = $rowempresa[4];

		//idcuentaporpagar, idcontratacion, concepto, importetotal, importependiente, nrodocumento, razonsocial, identidadbancaria, numerocuenta, idmoneda, fechacreacion, fechalimite, fechacierre, idestadocuentaporpagar, idtipocuentaporpagar, idempresa, idformapago, idobra, comprobante
		$query="INSERT INTO CuentaPorPagar VALUES(DEFAULT, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), STR_TO_DATE(? , '%Y-%m-%d'), NULL, 1, ?, ?, ?, ?);";
		$statement = $this->dbConnect->prepare($query);
		if(!$statement){
			echo $this->dbConnect->error;
			return false;
		}
		$statement->bind_param("isddssisisiiii",$idcontratacion,$concepto,$montopago, $montopago, $docproveedor, $razonproveedor, $identidad, $nrocuenta, $idmoneda,$fechalimite, $idtipopago, $idempresa, $idforma, $idobra );
		#$statement->bind_param("isddsii",$idcontratacion,$concepto,$montopago,$montopago,$fechalimite,$idtipopago,$idempresa);
		if(!$statement->execute()){
			#echo $this->dbConnect->errno;
			#echo $this->dbConnect->error;
			$this->dbConnect->rollback();
			return false;
		}
		return true;

	}
	

	public function ListarTipoOperaciones(){
		$query = "SELECT idtipo, nombre FROM TipoOperacionPorcentaje WHERE eliminado = 0";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaTipoOperaciones = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaTipoOperaciones[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaTipoOperaciones);
	}



	public function CargarDatosReporte($idcontratacion){

		$ListaRetorno = array();	
        $list = array();

		$query = "SELECT Proveedor.rucproveedor, Proveedor.contactoproveedor, Proveedor.telefonoproveedor, Empresa.nombreempresa, Empresa.idempresa, Contratacion.totalcontrato AS totalcontratacion, 
		Empresa.RUC AS rucempresa, Empresa.Direccion AS direccionempresa, Moneda.simbolomoneda AS prefijomoneda, UPPER(Contratacion.codigoContratacion) AS codigoContratacion,
        Contratacion.ventagravada, Contratacion.ventaNogravada, Contratacion.igv, Contratacion.detraccion, Contratacion.montoconcredito, Contratacion.montopendiente,
        Contratacion.montoneto FROM Contratacion 
		INNER JOIN Proveedor ON Contratacion.idproveedor = Proveedor.idproveedor INNER JOIN RequerimientoObra ON Contratacion.idrequerimiento = RequerimientoObra.idrequerimiento 
		INNER JOIN Obra ON RequerimientoObra.idobra = Obra.idobra INNER JOIN Empresa ON Obra.operariotributario = Empresa.idempresa 
        INNER JOIN Moneda ON Contratacion.idmoneda = Moneda.idmoneda WHERE Contratacion.idcontratacion=".$idcontratacion.";";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $dataObra = mysqli_fetch_assoc($respuestaQuery);
        $listaData= array();
        $listaData['Data'][]= $dataObra;

		$query="SELECT IC.nombreitem, IC.unidadmedida, IC.precio, IC.cantidad, ROUND((IC.precio * IC.cantidad),2) AS costoadquisicion  FROM ItemContratacion IC WHERE IC.idcontratacion=".$idcontratacion.";";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaItems = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaItems['Items'][] = $recorre;
		}
		$ListaRetorno = [ $listaData,$ListaItems];       
        $list ['ResponseService'] = $ListaRetorno;      
		header('Content-Type: application/json');
		echo json_encode($list);
	}

	public function CargarDatosReporteMovil($idcontratacion){

		$ListaRetorno = array();	
        $list = array();

		$query = "SELECT Empresa.nombreempresa, Empresa.RUC AS rucempresa, Empresa.Direccion AS direccionempresa, Proveedor.razonsocial AS nombreproveedor, Proveedor.rucproveedor, Proveedor.contactoproveedor,Proveedor.telefonoproveedor,
		TipoContratacion.nombretipocontratacion, Moneda.simbolomoneda AS prefijomoneda, UPPER(Contratacion.codigoContratacion) AS codigoContratacion, 
		(CASE WHEN CuentaBancaria.nrocuentabancaria IS NULL THEN '' ELSE CuentaBancaria.nrocuentabancaria END) AS cuentaorden, (CASE WHEN EntidadBancaria.nombreentidad IS NULL THEN '' ELSE EntidadBancaria.nombreentidad END) AS entidadorden,
        Contratacion.totalcontrato AS totalcontratacion, Contratacion.ventagravada, Contratacion.ventaNogravada, Contratacion.igv, Contratacion.detraccion, Contratacion.montoconcredito, Contratacion.montopendiente,
        Contratacion.montoneto, TipoPagoContratacion.nombre AS formadepago, Obra.nomenclatura, Contratacion.fechacontratacion FROM Contratacion 
		LEFT JOIN Proveedor ON Contratacion.idproveedor = Proveedor.idproveedor LEFT JOIN RequerimientoObra ON Contratacion.idrequerimiento = RequerimientoObra.idrequerimiento 
		LEFT JOIN Obra ON RequerimientoObra.idobra = Obra.idobra LEFT JOIN Empresa ON Obra.operariotributario = Empresa.idempresa LEFT JOIN TipoPagoContratacion ON TipoPagoContratacion.idtipopagocontratacion=Contratacion.formapago
        LEFT JOIN Moneda ON Contratacion.idmoneda = Moneda.idmoneda LEFT JOIN TipoContratacion ON Contratacion.tipocontratacion = TipoContratacion.idtipocontratacion 
        LEFT JOIN CuentaBancaria ON Contratacion.idcuenta=CuentaBancaria.idcuenta LEFT JOIN EntidadBancaria ON CuentaBancaria.identidad = EntidadBancaria.identidad
        WHERE Contratacion.idcontratacion=".$idcontratacion.";";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $dataObra = mysqli_fetch_assoc($respuestaQuery);
        $listaData= array();
        $listaData['Data'][]= $dataObra;

		$query="SELECT IC.nombreitem, IC.unidadmedida, IC.precio, IC.cantidad, ROUND((IC.precio * IC.cantidad),2) AS costoadquisicion  FROM ItemContratacion IC WHERE IC.idcontratacion=".$idcontratacion.";";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaItems = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaItems['Items'][] = $recorre;
		}
		$ListaRetorno = [ $listaData,$ListaItems];       
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