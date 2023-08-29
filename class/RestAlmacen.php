<?php
    
    class RestAlmacen{
        
    
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

    public function cargardatosregistrar(){
        $ListaRetorno = array();	
		$list = array();
		$query = "SELECT idobra, codigoObra FROM Obra;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaObras = array();

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
				$ListaObras['Obras'][]  = $recorre;
		}

		
		$ListaRetorno = [$ListaObras];

		$list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);
    }

    public function ListarArticulos(){

        $ListaRetorno = array();	
        $list = array();
        $query = "SELECT A.idarticulo, A.nombrearticulo, UM.descripcion AS Unidad, A.eliminado FROM Articulo A INNER JOIN UnidadMedida UM ON A.Unidad = UM.idunidad;";
        $respuesta_query = mysqli_query($this->dbConnect, $query);
        $ListaArticulos = array();

        while( $recorre = mysqli_fetch_assoc($respuesta_query) ) {
			$ListaArticulos['Articulos'][] = $recorre;
		}		

        if(count($ListaArticulos) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaArticulos['Articulos'] = array();
        }

		$query ="SELECT idunidad, UPPER(Descripcion) AS Descripcion FROM UnidadMedida;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaUnidades = array();

        $ListaUnidades = $this->llenar_lista_vacia("UnidadesMedida",$ListaUnidades, array("idunidad" => "0", "Descripcion" => "TODAS"));
        
        while($recorre = mysqli_fetch_assoc($respuestaQuery)){
            $ListaUnidades['UnidadesMedida'][] = $recorre;
        }

        if(count($ListaUnidades) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaUnidades['UnidadesMedida'] = array();
        }

		$ListaRetorno = [$ListaArticulos, $ListaUnidades];
        $list ['ResponseService'] = $ListaRetorno;

		header('Content-Type: application/json');
		echo json_encode($list);

    }

    public function ListarUnidades(){			
        $query = "SELECT idunidad, Descripcion FROM UnidadMedida;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaUnidad = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaUnidad [] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaUnidad);
    }
        
    public function ListarAlmacenes($idtipotrabajador, $idtrabajador){			
        include('../config/config.php');

        $extra = $idtipotrabajador==$ALMACENERO?" WHERE Trabajador.idtrabajador = ".$idtrabajador.";" : ";";

		$query = "SELECT idalmacen, UPPER(nombrealmacen) AS nombrealmacen, Almacen.eliminado, Obra.idobra, UPPER(Obra.codigoObra) AS codigoObra, UPPER(Trabajador.nombretrabajador) AS encargado FROM Almacen INNER JOIN Obra ON Almacen.idobra = Obra.idobra INNER JOIN Trabajador ON Almacen.idtrabajador = Trabajador.idtrabajador".$extra;

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaAlmacenes = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaAlmacenes[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaAlmacenes);
    }

    public function ListarArticulosDisponibles($idalmacen){
        $query = "SELECT A.idarticulo, A.nombrearticulo, UM.descripcion AS Unidad, A.eliminado FROM Articulo A INNER JOIN UnidadMedida UM ON A.Unidad = UM.idunidad WHERE idarticulo NOT IN (SELECT idarticulo FROM DetalleProductoAlmacen WHERE idalmacen=".$idalmacen.");";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaProductosDisponibles = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaProductosDisponibles[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaProductosDisponibles);
    }

    public function RegistrarAlmacen($almacenData){
        $this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
		#obteniendo datos de nuevo almacen
        $nombrealmacen = $almacenData->nombrealmacen;
        $idobra = $almacenData->idobra;
        $encargado = $almacenData->encargado;

        $arr = explode(' ',trim($encargado));

        $nombre = $arr[0];
        $apellido = $arr[1];

        $cred = $nombre.$apellido;

        $query = "INSERT INTO Trabajador VALUES(DEFAULT, ?, '', NULL, NULL, NULL, ?, ?, 5, NULL, 0,0)";
        $statement = $this->dbConnect->prepare($query);
        $statement->bind_param("sss",$encargado,$cred, $cred);
        if($statement->execute()){
            $query =  "SELECT LAST_INSERT_ID()";
            $respuesta_query = mysqli_query($this->dbConnect, $query);
            $lastId=$respuesta_query->fetch_row();
            $idalmacenero=$lastId[0];

            #inicio de query
            $query = "INSERT INTO Almacen VALUES(DEFAULT, ?,0,?,?);";
            $statement = $this->dbConnect->prepare($query);
            $statement->bind_param("sii",$nombrealmacen, $idobra,$idalmacenero);
            if($statement->execute()){
                $query =  "SELECT LAST_INSERT_ID()";
                $respuesta_query = mysqli_query($this->dbConnect, $query);
                $lastId=$respuesta_query->fetch_row();
                $idalmacen=$lastId[0];
                $status=$idalmacen;
                $message="Almacen registrado correctamente.";
                $this->dbConnect->commit();
            }
            else{
                $this->dbConnect->rollback();
                $status = 0;
                $message="Almacen no registrado. Código de error: {$this->dbConnect->errno}";
            }
        }
        else{
            $this->dbConnect->rollback();
            $status=0;
            $message="Error. Código: {$this->dbConnect->errno}";
        }
		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
    }

    public function RegistrarDetalleAlmacenProducto($detalledata){
        $this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 

        $query = "INSERT INTO DetalleProductoAlmacen VALUES(DEFAULT, ?, ?, 0.0)";
        $statement = $this->dbConnect->prepare($query);
        if($statement){
            foreach ($detalledata as $nuevodetalle) {
                 $idarticulo = $nuevodetalle->idarticulo;
                 $idalmacen = $nuevodetalle->idalmacen;
                if($statement->bind_param("ii", $idarticulo, $idalmacen)){
                    if($statement->execute()){
                        $status= 1;
                        $message ="Articulos asignados correctamente.";
                        $this->dbConnect->commit();
                    }
                    else{
                        $status= 0;
                        $message ="No se pudo registrar detalle. Código de error: {$this->dbConnect->errno}";
                        $this->dbConnect->rollback();
                    }
                }
                else{
                    $status= 0;
                    $message ="No se pudo registrar detalle. Código de error: {$this->dbConnect->errno}";
                    $this->dbConnect->rollback();
                }
            }
        }
        else{
            $status= 0;
            $message ="No se pudo registrar detalle. Código de error: {$this->dbConnect->errno}";
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

    public function RegistrarArticulo($articuloData){
        $this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
		#obteniendo datos de nuevo articulo

        $nombrearticulo = $articuloData->nombrearticulo;
        $Unidad = $articuloData->Unidad;

        $query = "INSERT INTO Articulo VALUES(DEFAULT, ? , ?, 0);";
        $statement = $this->dbConnect->prepare($query);
        if($statement){
            if($statement->bind_param("si", $nombrearticulo, $Unidad)){
                if($statement->execute()){
                    $status=1;
                    $message="Articulo creado correctamente";
                    $this->dbConnect->commit();
                }
                else{
                    $status=0;
                    $coderror= $this->dbConnect->errno;
                    $message="";
                    $this->dbConnect->rollback();
                    if($coderror == 1062){
                        $message= "Error, el artículo ya existe con esta unidad de medida, seleccione otra.";
                    }
                    else{
                        $message= "Articulo no creado. Código de error: {$coderror}";
                    }
                }
            }
            else{
                $status=0;
                $message="Articulo no creado. Código de error: {$this->dbConnect->errno}";
                $this->dbConnect->rollback();
            }
        }
        else{
            $status=0;
            $message="Articulo no creado. Código de error: {$this->dbConnect->errno}";
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

    public function ListarInventarioAlmacen($idalmacen){
        $ListaRetorno = array();	
        $list = array();

        $query = "SELECT d.iddetalle, a.idarticulo, a.nombrearticulo, u.Descripcion, d.stockactual,(CASE WHEN O1.entradas IS NULL THEN 0 ELSE O1.entradas END) AS entradas, 
        (CASE WHEN O2.salidas IS NULL THEN 0 ELSE O2.salidas END) AS salidas from DetalleProductoAlmacen d
        Inner join Articulo a on d.idarticulo = a.idarticulo
        Inner Join UnidadMedida u on a.Unidad = u.idunidad
        LEFT JOIN (SELECT iddetalle, COUNT(tipooperacion) AS entradas FROM OperacionAlmacen GROUP BY iddetalle, tipooperacion HAVING tipooperacion=1) O1 ON O1.iddetalle = d.iddetalle
        LEFT JOIN (SELECT iddetalle, COUNT(tipooperacion) AS salidas FROM OperacionAlmacen GROUP BY iddetalle, tipooperacion HAVING tipooperacion=2) O2 ON O2.iddetalle = d.iddetalle WHERE idalmacen=".$idalmacen.";";

        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaInventario = array();
        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
            $ListaInventario['Inventario'][] = $recorre;
        }		

        if(count($ListaInventario) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaInventario['Inventario'] = array();
        }


        #mover aqui
        $query ="SELECT idunidad, Descripcion FROM UnidadMedida;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaUnidades = array();

        $ListaUnidades = $this->llenar_lista_vacia("UnidadesMedida",$ListaUnidades, array("idunidad" => "0", "Descripcion" => "TODOS"));
        while($recorre = mysqli_fetch_assoc($respuestaQuery)){
            $ListaUnidades['UnidadesMedida'][] = $recorre;
        }

        if(count($ListaUnidades) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaUnidades['UnidadesMedida'] = array();
        }

        $ListaRetorno = [$ListaInventario, $ListaUnidades];
        $list ['ResponseService'] = $ListaRetorno;

        header('Content-Type: application/json');
        echo json_encode($list);

    }

    public function ListarOperaciones($idalmacen){
        $ListaRetorno = array();	
        $list = array();

        $query = "SELECT O.idoperacion,A.nombrearticulo, U.Descripcion AS Unidad, T.descripcion AS tipooperacion , O.fechaoperacion, O.preciounitario, O.StockActual, O.cantidadoperacion,
        (O.StockActual + O.cantidadoperacion) AS nuevacantidad   FROM OperacionAlmacen O 
               INNER JOIN DetalleProductoAlmacen D ON O.iddetalle = D.iddetalle INNER JOIN Articulo A ON D.idarticulo = A.idarticulo 
               INNER JOIN UnidadMedida U ON A.Unidad = U.idunidad INNER JOIN TipoOperacionAlmacen T ON T.idtipo = O.tipooperacion WHERE D.idalmacen=".$idalmacen.";";

        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaOperaciones = array();
        
        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
            $ListaOperaciones ['Operaciones'][] = $recorre;
        }		

        if(count($ListaOperaciones) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaOperaciones['Operaciones'] = array();
        }

        $query ="SELECT idtipo, descripcion FROM TipoOperacionAlmacen;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaTipoOperaciones = array();

        $ListaTipoOperaciones = $this->llenar_lista_vacia("TipoOperaciones",$ListaTipoOperaciones, array("idtipo" => "0", "descripcion" => "TODOS"));
        while($recorre = mysqli_fetch_assoc($respuestaQuery)){
            $ListaTipoOperaciones['TipoOperaciones'][] = $recorre;
        }

        if(count($ListaTipoOperaciones) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaTipoOperaciones['TipoOperaciones'] = array();
        }

        $ListaRetorno = [$ListaOperaciones, $ListaTipoOperaciones];
        $list ['ResponseService'] = $ListaRetorno;

        header('Content-Type: application/json');
        echo json_encode($list);

    }

    public function RegistrarOperacion($dataoperacion){
        $this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
		#obteniendo datos de nuevo articulo

        $iddetalle = $dataoperacion->iddetalle;
        $StockActual = $dataoperacion->StockActual;
        $cantidadoperacion = $dataoperacion->cantidadoperacion;
        $tipooperacion = $dataoperacion->tipooperacion;
        $preciounitario = $dataoperacion->preciounitario;
        $cantidadrestante=$cantidadoperacion;

        
        if($tipooperacion == 2){ #Cambiar por la constante
            $cantidadrestante=NULL;
            $idoperacion = $dataoperacion->idoperacion;
        }

        $query = "INSERT INTO OperacionAlmacen VALUES(DEFAULT, ? , CURDATE(), ?, ?, ? , ?, ?);";

        $statement = $this->dbConnect->prepare($query);
        if($statement){
            if($statement->bind_param("iddidd", $iddetalle, $StockActual, $cantidadoperacion,$tipooperacion, $preciounitario, $cantidadrestante)){
                if($statement->execute()){
                    
                    $query =  "SELECT LAST_INSERT_ID()";
                    $respuesta_query = mysqli_query($this->dbConnect, $query);
                    $lastId=$respuesta_query->fetch_row();
                    $idcurrentoperacion=$lastId[0];

                    $query = "UPDATE DetalleProductoAlmacen SET stockactual= stockactual + ? WHERE iddetalle=?";
                    $statement = $this->dbConnect->prepare($query);
                    if($statement){
                        if($statement->bind_param("di",$cantidadoperacion, $iddetalle)){
                            if($statement->execute()){
                                $query_idobra="SELECT Obra.idobra FROM DetalleProductoAlmacen D RIGHT JOIN Almacen A ON D.idalmacen = A.idalmacen INNER JOIN Obra ON A.idobra = Obra.idobra WHERE D.iddetalle=".$iddetalle." LIMIT 1;";
                                $respuesta_query = mysqli_query($this->dbConnect, $query_idobra);
                                $datos=$respuesta_query->fetch_row();
                                $idobra=$datos[0];
                                
                                $numerror=0;

                                if($tipooperacion==2){
                                    if($this->RegistrarCosto($numerror, $idobra,$cantidadoperacion * $preciounitario,$idcurrentoperacion)){
                                        if($this->actualizarcantidadrestanteentrada($numerror,$idoperacion, $cantidadoperacion)){
                                            $status=1;
                                            $message="Operacion registrada correctamente";
                                            $this->dbConnect->commit();
                                        }
                                        else{
                                            $status=0;
                                            $message="Error actualizando datos articulo. Código de error: {$numerror}";
                                            $this->dbConnect->rollback();
                                        }
                                        
                                    }
                                    else{
                                        $status=0;
                                        $message="Error registrando costo. Código de error: {$numerror}";
                                        $this->dbConnect->rollback();
                                    }
                                }
                                else{
                                    if($this->RegistrarGasto($numerror,$idobra,$cantidadoperacion * $preciounitario,$idcurrentoperacion)){
                                        $status=1;
                                        $message="Operacion registrada correctamente";
                                        $this->dbConnect->commit();
                                    }
                                    else{
                                        $status=0;
                                        $message="Error registrando gasto. Código de error: {$numerror}";
                                        $this->dbConnect->rollback();
                                    }
                                }

                                
                            }
                            else{
                                $status=0;
                                $message="Operacion no registrada. Código de error: {$this->dbConnect->errno}";
                                $this->dbConnect->rollback();
                            }
                        }
                        else{
                            $status=0;
                            $message="Operacion no registrada. Código de error: {$this->dbConnect->errno}";
                            $this->dbConnect->rollback();
                        }
                    }
                    else{
                        $status=0;
                        $message="Operacion no registrada. Código de error: {$this->dbConnect->errno}";
                        $this->dbConnect->rollback();
                    }
                    
                }
                else{
                    $status=0;
                    $message="Operacion no registrada. Código de error: {$this->dbConnect->errno}";
                    $this->dbConnect->rollback();
                }
            }
            else{
                $status=0;
                $message="Operacion no registrada. Código de error: {$this->dbConnect->errno}";
                $this->dbConnect->rollback();
            }
        }
        else{
            $status=0;
            $message="Operacion no registrada. Código de error: {$this->dbConnect->errno}";
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

    private function RegistrarGasto(&$codigoError,$idobra, $cantidad,$idoperacion){
        //CAMBIADO CURDATE() POR NOW()
        $query="INSERT INTO Gasto(idgasto, idobra, fechagasto, cantidadgasto, idoperacion) VALUES(DEFAULT,?,NOW(),?,?)";
        $statement = $this->dbConnect->prepare($query);
        if($statement){
            if($statement->bind_param("idi", $idobra, $cantidad, $idoperacion)){
                if($statement->execute()){
                    return True;
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
            return False;
        }
    }

    private function RegistrarCosto(&$codigoError,$idobra, $cantidad,$idoperacion){
        //CAMBIADO CURDATE() POR NOW()
        $query="INSERT INTO Costo(idcosto, idobra, fechacosto, cantidadcosto, idoperacion) VALUES(DEFAULT,?,NOW(),ABS(?),?)";
        $statement = $this->dbConnect->prepare($query);
        if($statement){
            if($statement->bind_param("idi", $idobra, $cantidad,$idoperacion)){
                if($statement->execute()){
                    return True;
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
            return False;
        }
    }

    private function actualizarcantidadrestanteentrada(&$codigoError,$idoperacion, $cantidadoperacion){
        $query = "UPDATE OperacionAlmacen SET cantidadrestante=cantidadrestante- ABS(?) WHERE idoperacion=?";
        $statement = $this->dbConnect->prepare($query);
        if($statement){
            if($statement->bind_param("di", $cantidadoperacion, $idoperacion)){
                if($statement->execute()){
                    return True;
                }
                else{
                    $codigoError=$this->dbConnect->errno;
                    return False;
                }
            }
            else{
                $codigoError=$this->dbConnect->errno;
                return False;
            }
        }
        else{
            $codigoError=$this->dbConnect->errno;
            return False;
        }
    }


    public function CargarDetalleProductoAlmacen($idalmacen, $iddetalle){
        $ListaRetorno = array();	
		$list = array();
        $query = "SELECT D.iddetalle, D.idarticulo, AA.nombrearticulo, UPPER(U.Descripcion) AS Unidad, D.stockactual FROM DetalleProductoAlmacen D 
        INNER JOIN Almacen A ON A.idalmacen = D.idalmacen INNER JOIN Articulo AA ON AA.idarticulo = D.idarticulo INNER JOIN UnidadMedida U ON U.idunidad = AA.Unidad WHERE D.idalmacen=".$idalmacen." AND D.iddetalle=".$iddetalle.";";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaDetalle = array();
        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
            $ListaDetalle['DetalleProductoAlmacen'][]  = $recorre;
        }

        if(count($ListaDetalle) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaDetalle['DetalleProductoAlmacen'] = array();
        }

        $query = "SELECT idtipo, UPPER(descripcion) as descripcion  FROM TipoOperacionAlmacen;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaTipoOperacion = array();

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
				$ListaTipoOperacion['TiposOperacion'][]  = $recorre;
		}

        $ListaRetorno = [$ListaDetalle, $ListaTipoOperacion];

		$list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);

    }

    public function CargarDetalleEntradasAlmacen($idalmacen, $iddetalle){
        $ListaRetorno = array();	
		$list = array();
        $query = "SELECT O.idoperacion,D.iddetalle, D.stockactual, O.fechaoperacion, AA.nombrearticulo, UPPER(U.Descripcion) AS Unidad, O.cantidadrestante, O.preciounitario FROM OperacionAlmacen O INNER JOIN DetalleProductoAlmacen D ON O.iddetalle=D.iddetalle 
        INNER JOIN Articulo AA ON AA.idarticulo = D.idarticulo INNER JOIN UnidadMedida U ON U.idunidad = AA.Unidad WHERE tipooperacion=1 AND D.idalmacen=".$idalmacen." AND D.iddetalle=".$iddetalle.";";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaDetalle = array();
        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
            $ListaDetalle['DetalleEntradasAlmacen'][]  = $recorre;
        }

        if(count($ListaDetalle) == 0){
            #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
            $ListaDetalle['DetalleEntradasAlmacen'] = array();
        }

        $query = "SELECT idtipo, UPPER(descripcion) as descripcion  FROM TipoOperacionAlmacen;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaTipoOperacion = array();

        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
				$ListaTipoOperacion['TiposOperacion'][]  = $recorre;
		}

        

        $ListaRetorno = [$ListaDetalle, $ListaTipoOperacion];

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