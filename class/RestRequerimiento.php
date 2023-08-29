<?php

    include('../correo/MailSender.php');
    class RestRequerimiento{
        
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
        
        public function listarRequerimientos(){

            $ListaRetorno = array();	
    		$list = array();

            $query =  "SELECT r.idrequerimiento, r.fecharequerimiento, r.idobra, o.nomenclatura, r.estado, er.nombre, r.centrocostos, cc.nombrecentrocostos, r.idconcepto, c.nombreconcepto,
             o.idresidente, CONCAT(t.nombretrabajador) as nombreresidente, UPPER(o.codigoObra) AS codigoObra, UPPER(r.codigoRequerimiento) AS codigoRequerimiento, r.servicio FROM RequerimientoObra r
            INNER join Obra o on r.idobra = o.idobra
            INNER join EstadoRequerimiento er on r.estado = er.idestado
            INNER join CentroCostos cc on r.centrocostos = cc.idcentrocosto
            INNER JOIN ConceptoCentroCosto c ON r.idconcepto = c.idconcepto
            INNER JOIN Trabajador t ON o.idresidente = t.idtrabajador WHERE r.eliminado=0;";
            $respuesta_query = mysqli_query($this->dbConnect, $query);
            $ListaRequerimientos = array();
            
            while($recorre = mysqli_fetch_assoc($respuesta_query)){
                $ListaRequerimientos['Requerimientos'][] = $recorre;
            }

            if(count($ListaRequerimientos) == 0){
                #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
                $ListaRequerimientos['Requerimientos'] = array();
            }

            $query = "SELECT idestado, UPPER(nombre) AS nombre FROM EstadoRequerimiento;";
            $respuestaQuery = mysqli_query($this->dbConnect, $query);
            $ListaEstadosRequerimientos = array();
            $ListaEstadosRequerimientos = $this->llenar_lista_vacia("EstadoRequerimientos",$ListaEstadosRequerimientos, array("idestado" => "0", "nombre" => "TODOS"));
            while($recorre = mysqli_fetch_assoc($respuestaQuery)){
                $ListaEstadosRequerimientos['EstadoRequerimientos'][] = $recorre;
            }

            if(count($ListaEstadosRequerimientos) == 0){
                #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
                $ListaEstadosRequerimientos['EstadoRequerimientos'] = array();
            }

            $ListaRetorno = [$ListaRequerimientos, $ListaEstadosRequerimientos];
            $list ['ResponseService'] = $ListaRetorno;

            header('Content-Type: application/json');
            echo json_encode($list);
        }

        public function listarRequerimientosxResidente($idresidente){

            $ListaRetorno = array();	
    		$list = array();
            

            $query = "SELECT r.idrequerimiento, r.fecharequerimiento, r.idobra, o.nomenclatura, r.estado, er.nombre, r.centrocostos, cc.nombrecentrocostos,
             r.idconcepto, c.nombreconcepto, UPPER(r.codigoRequerimiento) AS codigoRequerimiento,r.servicio, o.codigoobra FROM RequerimientoObra r
            INNER join Obra o on r.idobra = o.idobra
            INNER join EstadoRequerimiento er on r.estado = er.idestado
            INNER join CentroCostos cc on r.centrocostos = cc.idcentrocosto
            INNER JOIN ConceptoCentroCosto c ON r.idconcepto = c.idconcepto
            where o.idresidente = ".$idresidente.";";
            $respuesta_query = mysqli_query($this->dbConnect, $query);

            $ListaRequerimientos = array();
            while($recorre = mysqli_fetch_assoc($respuesta_query)){
                $ListaRequerimientos['Requerimientos'][] = $recorre;
            }
            if(count($ListaRequerimientos) == 0){
                #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
                $ListaRequerimientos['Requerimientos'] = array();
            }


            $query = "SELECT idestado, UPPER(nombre) AS nombre FROM EstadoRequerimiento;";
            $respuestaQuery = mysqli_query($this->dbConnect, $query);
            $ListaEstadosRequerimientos = array();
            $ListaEstadosRequerimientos = $this->llenar_lista_vacia("EstadoRequerimientos",$ListaEstadosRequerimientos, array("idestado" => "0", "nombre" => "TODOS"));
            while($recorre = mysqli_fetch_assoc($respuestaQuery)){
                $ListaEstadosRequerimientos['EstadoRequerimientos'][] = $recorre;
            }

            if(count($ListaEstadosRequerimientos) == 0){
                #$ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=>"0" , 'nombreconcepto' => "SELECCIONAR CONCEPTO"));
                $ListaEstadosRequerimientos['EstadoRequerimientos'] = array();
            }

            $ListaRetorno = [$ListaRequerimientos, $ListaEstadosRequerimientos];
            $list ['ResponseService'] = $ListaRetorno;

            header('Content-Type: application/json');
            echo json_encode($list);
        }


        /*public function registrarRequerimiento($requerimientoData){
            $idobra = $requerimientoData['idobra'];
            $fecharequerimiento = date("Y/m/d");
            $estado = $requerimientoData['estado'];
            $centrocostos = $requerimientoData['centrocostos'];
            $idconcepto = $requerimientoData['idconcepto'];
            $idresidente= $requerimientoData['idresidente'];

            $query="SELECT nombretrabajador FROM Trabajador WHERE idtrabajador = ".$idresidente." LIMIT 1;";
            $respuesta_query = mysqli_query($this->dbConnect, $query);
            $nombre=$respuesta_query->fetch_row();
            $nombreresidente=$nombre[0];            

            $query = "INSERT INTO RequerimientoObra VALUES (default, ?,?,?,?,0,?,?);";
            $statement = $this->dbConnect->prepare($query);
            
            $statement->bind_param("isiiis",$idobra, $fecharequerimiento, $estado, $centrocostos, $idconcepto,$nombreresidente);
            if($statement->execute()){
                $query =  "SELECT LAST_INSERT_ID()";
                $respuesta_query = mysqli_query($this->dbConnect, $query);
                $lastId=$respuesta_query->fetch_row();
                $idanex=$lastId[0];
                $messgae = "Requerimiento Registrado con exito.";
                $status = $idanex;			
            } else {
                $messgae = "Requerimiento No Registrado.";
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

        public function registrarItemRequerimiento($itemRequerimientoData){
            $nombreitem = $itemRequerimientoData['nombreitem'];
            $unidaditem = $itemRequerimientoData['unidaditem'];
            $cantidaditem = $itemRequerimientoData['cantidaditem'];
            $observacionitem = $itemRequerimientoData['observacionitem'];
            $iditemcentrocostos = $itemRequerimientoData['iditemcentrocostos'];
            $idrequerimiento = $itemRequerimientoData['idrequerimiento'];
            
            $query = "INSERT INTO `ItemRequerimiento` VALUES (default, ?,?,?,?,?,?,?,1);";
            $statement = $this->dbConnect->prepare($query);
            
            $statement->bind_param("ssdsiid",$nombreitem, $unidaditem, $cantidaditem, $observacionitem, $iditemcentrocostos,$idrequerimiento,$cantidaditem);
            if($statement->execute()){
                $query =  "SELECT LAST_INSERT_ID()";
                $respuesta_query = mysqli_query($this->dbConnect, $query);
                $lastId=$respuesta_query->fetch_row();
                $idanex=$lastId[0];
                $messgae = "Requerimiento Registrado con exito.";
                $status = $idanex;			
            } else {
                $messgae = "Requerimiento No Registrado.";
                $status = 0;			
            }
            $statement->close();

            $Responde = array(
                'Estado' => $status,
                'Respuesta' => $messgae
            );
            
            header('Content-Type: application/json');
            echo json_encode($Responde);    
        }*/

        public function registrarRequerimiento($requerimientoData){
            #Registrando nueva contratacion
		$registrado = true;
		#Inicializando el autocommit de la conexion en falso
		$this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
		#obteniendo datos de nueva contratacion
		$idobra = $requerimientoData->idobra;
        $fecharequerimiento = date("Y/m/d");
        $estado = $requerimientoData->estado;
        $centrocostos = $requerimientoData->centrocostos;
        $idconcepto = $requerimientoData->idconcepto;
        $idresidente= $requerimientoData->idresidente;
        $codigoObra = $requerimientoData->codigoObra;
        $servicio = $requerimientoData->servicio;

        $anio = date("y");

        $query="SELECT nombretrabajador FROM Trabajador WHERE idtrabajador = ".$idresidente." LIMIT 1;";
        $respuesta_query = mysqli_query($this->dbConnect, $query);
        $nombre=$respuesta_query->fetch_row();
        $nombreresidente=$nombre[0];    

		#inicio de query
        $query = "INSERT INTO RequerimientoObra VALUES (default, ?,?,?,?,0,?,?,NULL,?);";
        $statement = $this->dbConnect->prepare($query);
		$statement->bind_param("isiiiss",$idobra, $fecharequerimiento, $estado, $centrocostos, $idconcepto,$nombreresidente,$servicio);
		if($statement->execute()){
			$query =  "SELECT LAST_INSERT_ID()";
			$respuesta_query = mysqli_query($this->dbConnect, $query);
			$lastId=$respuesta_query->fetch_row();
            $idrequerimiento=$lastId[0];

            $query =  "SELECT COUNT(*) FROM RequerimientoObra WHERE idobra =".$idobra.";";
            $respuesta_query = mysqli_query($this->dbConnect, $query);
            $conteo=$respuesta_query->fetch_row();
            $totalfilas=$conteo[0];

            #str_pad($idrequerimiento, 6, "-", STR_PAD_LEFT);
            $strcorrelativo = str_pad($totalfilas, 5, "0", STR_PAD_LEFT);
            #echo $strcorrelativo;
            $correlativoquery = "UPDATE RequerimientoObra SET RequerimientoObra.codigoRequerimiento = CONCAT('RQ-','".$codigoObra."-','".$anio."-','".$strcorrelativo."') WHERE RequerimientoObra.idrequerimiento = ".$idrequerimiento.";";
            #echo $correlativoquery;
            if (mysqli_query($this->dbConnect, $correlativoquery)){
                $listaitems = $requerimientoData->ListaItems;
                foreach ($listaitems as $itemactual) {
                    $nombreitem = $itemactual->nombreitem;
                    $unidaditem = $itemactual->unidaditem;
                    $cantidaditem = $itemactual->cantidaditem;
                    $observacionitem = $itemactual->observacionitem;
                    $iditemcentrocostos = $itemactual->iditemcentrocostos;

                    $query = "INSERT INTO `ItemRequerimiento` VALUES (default, ?,?,?,?,?,?,?,1);";
                    $statement = $this->dbConnect->prepare($query);
                    $statement->bind_param("ssdsiid",$nombreitem, $unidaditem, $cantidaditem, $observacionitem, $iditemcentrocostos,$idrequerimiento,$cantidaditem);
                    if(!$statement->execute()){
                        $registrado=false;
                        break;
                    }
                    
                }
            }
            else{
                $registrado = false;
            }
			if($registrado){
				$this->dbConnect->commit();
				$status=$idrequerimiento;
                $message="Requerimento registrado.";
                
                try{
                    $sender = new MailSender();
                    $query ="SELECT nombretrabajador, correotrabajador FROM Trabajador WHERE idtipotrabajador=3;";
                    $respuesta_query = mysqli_query($this->dbConnect, $query);
                    $correos=array();
                    $datos = array($nombreresidente, $fecharequerimiento);
                    while($recorre = mysqli_fetch_assoc($respuesta_query)){
                        //TODO: VER ESTO MAÑANA URGENTE
                        $correos[$recorre['nombretrabajador']] = $recorre['correotrabajador'];
                    }

                    if($sender->enviarCorreoRequerimiento($correos, $datos)){
                        //echo 'Correo enviado correctamente';
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
				$status=0;
                $message="Error al registrar item de requerimiento. Requerimiento no registrado. Código de error {$this->dbConnect->errno}";
                
			}
		}
		else{
            $this->dbConnect->rollback();
			$status = 0;
			$message="Requerimiento no registrado. Código de error {$this->dbConnect->errno}";
		}
		

		$Responde = array(
			'Estado' => $status,
			'Respuesta' => $message
		);
		$this->dbConnect->autocommit(true);
		header('Content-Type: application/json');
		echo json_encode($Responde);
        }

        public function ValidarRequerimiento($idrequerimiento){
            $query =  "SELECT COUNT(*) FROM Contratacion WHERE idrequerimiento=".$idrequerimiento.";";
            $respuesta_query = mysqli_query($this->dbConnect, $query);
            $counter=$respuesta_query->fetch_row();
            $veces=intval($counter[0]);
            
            $status = +($veces>0);
            $message="Resultado";

            $Responde = array(
                'Estado' => $status,
                'Respuesta' => $message
            );
            $this->dbConnect->autocommit(true);
            header('Content-Type: application/json');
            echo json_encode($Responde);
        }

        public function DesestimarRequerimiento($idrequerimiento){
            $this->dbConnect->autocommit(false);
		    $this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
            $query = "UPDATE RequerimientoObra SET RequerimientoObra.estado = 3 WHERE idrequerimiento=?;";
            $statement = $this->dbConnect->prepare($query);
		    $statement->bind_param("i",$idrequerimiento);

            if($statement->execute()){
                $status=1;
                $message="Requerimiento desestimado correctamente.";
                $this->dbConnect->commit();
            }
            else{
                $status=0;
                $message="Error, no se pudo completar la accion. Codigo de error: {$this->dbConnect->errno}";
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

        public function DesestimarItemRequerimiento($data){
            $query = "UPDATE ItemRequerimiento SET estadoitem=4 WHERE iditem=?";
            $status=1;
            $message="Item desestimado correctamente";
            $statement = $this->dbConnect->prepare($query);
            $idrequerimiento = NULL;
            if(!$statement){
                $status=0;
                $message="Error al desestimar item. Código de error: {$this->dbConnect->errno}";
            }
            else{
                foreach($data as $itemactual){
                    $iditem = $itemactual->iditem;
                    $idrequerimiento = $itemactual->idrequerimiento;
                    if(!$statement->bind_param("i",$iditem)){
                        $status=0;
                        $message="Error al desestimar item. Código de error: {$this->dbConnect->errno}";
                        break;
                    }
                    if(!$statement->execute()){
                        $status=0;
                        $message="Error al desestimar item. Código de error: {$this->dbConnect->errno}";
                        break;
                    }
                }
            }
            if(isset($idrequerimiento)){
                $query = "SELECT COUNT(*) FROM ItemRequerimiento WHERE idrequerimiento=".$idrequerimiento." AND (ItemRequerimiento.estadoitem=1 OR ItemRequerimiento.estadoitem=3);";
                $respuesta_query = mysqli_query($this->dbConnect, $query);
                $conteorestantes=$respuesta_query->fetch_row();
                $restantes=(int)$conteorestantes[0];
                $registradoordenpago=true;
                if($restantes==0){
                    $query = "UPDATE RequerimientoObra SET estado=2 WHERE idrequerimiento=".$idrequerimiento.";";
                    if(!mysqli_query($this->dbConnect, $query)){
                        $this->dbConnect->rollback();
                    }
                }
            }
            $Responde = array(
                'Estado' => $status,
                'Respuesta' => $message
            );
            header('Content-Type: application/json');
            echo json_encode($Responde);
            
        }


        public function CargarDatosReporte($idrequerimiento){
        $ListaRetorno = array();	
        $list = array();

        $query = "SELECT nomenclatura AS nomenclaturaObra, residente, operariotributario, fecharequerimiento, UPPER(codigoRequerimiento) AS codigoRequerimiento, Empresa.nombreempresa, Empresa.RUC, Empresa.Direccion
        FROM RequerimientoObra INNER JOIN Obra ON RequerimientoObra.idobra = Obra.idobra INNER JOIN Empresa ON Obra.gestor=Empresa.idempresa WHERE RequerimientoObra.idrequerimiento =".$idrequerimiento.";";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $dataObra = mysqli_fetch_assoc($respuestaQuery);
        #$nombreObra = $dataObra[0];
        $listaData= array();
        $listaData['Data'][]= $dataObra;

        #$query = "SELECT residente FROM RequerimientoObra WHERE idrequerimiento=".$idrequerimiento.";";
		#$respuestaQuery = mysqli_query($this->dbConnect, $query);
        #$dataResidente = mysqli_fetch_assoc($respuestaQuery);
        #$nombreResidente = $dataResidente[0];

        #$listaData['Data'][] = $dataResidente;
        


        /* Query momentánea, usando vinculación con CANAL en lugar de ITEMCANAL */
        $query = "SELECT iditem AS itemID, UPPER(nombreitem) AS nombreInsumo, UPPER(unidaditem) AS unidadItem, cantidaditem AS cantidadItem, UPPER(observacionitem) AS observacionItem, CONCAT(ConceptoCentroCosto.idconcepto,'.',iditemcentrocostos) AS centrocostos 
        FROM ItemRequerimiento INNER JOIN CanalCentroCostos ON ItemRequerimiento.iditemcentrocostos = CanalCentroCostos.idcanal
        INNER JOIN ConceptoCentroCosto ON CanalCentroCostos.idconcepto=ConceptoCentroCosto.idconcepto 
         WHERE idrequerimiento =".$idrequerimiento.";";
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

        public function cambiarEstadoRequerimiento($idrequerimiento){
            $query_req = "UPDATE RequerimientoObra SET estado=2 WHERE RequerimientoObra.idrequerimiento=?";
            $query_itemreq= "UPDATE ItemRequerimiento SET estadoitem=2 WHERE ItemRequerimiento.idrequerimiento=?";
            $stmt_req = $this->dbConnect->prepare($query_req);
            $stmt_itemreq = $this->dbConnect->prepare($query_itemreq);
            if($stmt_req && $stmt_itemreq){
                if($stmt_req->bind_param("s",$idrequerimiento) && $stmt_itemreq->bind_param("s",$idrequerimiento)){
                    if($stmt_req->execute() && $stmt_itemreq->execute()){
                        $status=1;
                        $message="Requerimiento cerrado correctamente.";
                        $this->dbConnect->commit();
                    }
                    else{
                        $status=0;
                        $message="Error actualizando el estado de requerimiento. Código de error {$this->dbConnect->errno}.";
                        $this->dbConnect->rollback();
                    }
                }
                else{
                    $status=0;
                    $message="Error actualizando el estado de requerimiento. Código de error {$this->dbConnect->errno}.";
                    $this->dbConnect->rollback();
                }
            }
            else{
                $status=0;
                $message="Error actualizando el estado de requerimiento. Código de error {$this->dbConnect->errno}.";
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

        public function CargarDatosRegistroRequerimiento($idtrabajador){

        $ListaRetorno = array();	
        $list = array();

        $query = "SELECT idcentrocosto,nombrecentrocostos, total FROM CentroCostos LEFT JOIN Obra ON Obra.idcentrocostos = CentroCostos.idcentrocosto WHERE idresidente=".$idtrabajador." AND CentroCostos.eliminado=0;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);

		$ListaCentroCostos = array();

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaCentroCostos['Centros'][] = $recorre;
        }

        if(count($ListaCentroCostos) == 0){
            $ListaCentroCostos = $this->llenar_lista_vacia('Centros', $ListaCentroCostos, array('idcentrocosto'=> "0", 'nombrecentrocostos' => 'SELECCIONAR'));
        }


        $query="SELECT idconcepto, nombreconcepto FROM ConceptoCentroCosto WHERE idcentrocostos IN (SELECT idcentrocosto FROM CentroCostos LEFT JOIN Obra ON Obra.idcentrocostos = CentroCostos.idcentrocosto WHERE idresidente=".$idtrabajador.") AND ConceptoCentroCosto.eliminado=0;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaConceptos = array();



        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaConceptos['Conceptos'][] = $recorre;
        }

        if(count($ListaConceptos) == 0){
            $ListaConceptos = $this->llenar_lista_vacia('Conceptos', $ListaConceptos, array('idconcepto'=> "0", 'nombreconcepto' => 'SELECCIONAR', 'idcentrocostos' => "0"));
        }


        $query="SELECT idcanal, nombrecanal, idconcepto FROM CanalCentroCostos WHERE eliminado=0;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaCanales = array();



        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaCanales['Canales'][] = $recorre;
        }

        if(count($ListaCanales) == 0){
            $ListaCanales = $this->llenar_lista_vacia('Canales', $ListaCanales, array('idcanal'=> "0", 'nombrecanal' => 'SELECCIONAR', 'idconcepto' => "0"));
        }

        $query="SELECT iditemcanal, descripcion, idcanalcentrocostos FROM itemCanal WHERE eliminado=0;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaItems = array();

        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaItems['Items'][] = $recorre;
        }

        if(count($ListaItems) == 0){
            $ListaItems = $this->llenar_lista_vacia('Items', $ListaItems, array('iditemcanal'=> "0", 'descripcion' => 'SELECCIONAR', 'idcanalcentrocostos' => "0"));
        }

        $query="SELECT idestado, nombre FROM EstadoRequerimiento WHERE eliminado=0;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaEstados = array();

        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaEstados['Estados'][] = $recorre;
        }

        if(count($ListaEstados) == 0){
            $ListaEstados = $this->llenar_lista_vacia('Estados', $ListaEstados, array('idestado'=> "0", 'nombre' => 'SELECCIONAR'));
        }

        $query="SELECT idobra, nomenclatura, UPPER(codigoObra) AS codigoObra, gastos FROM Obra WHERE idresidente = ".$idtrabajador." AND eliminado=0;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaObras = array();

        while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaObras['Obras'][] = $recorre;
        }

        if(count($ListaObras) == 0){
            $ListaObras = $this->llenar_lista_vacia('Obras', $ListaObras, array('idobra'=> "0", 'nomenclatura' => 'SELECCIONAR'));
        }

        $query = "SELECT idunidad, Descripcion FROM UnidadMedida;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaUnidad = array();

        $ListaUnidad = $this->llenar_lista_vacia('Unidades', $ListaUnidad, array('idunidad'=> "0", 'Descripcion' => 'SELECCIONE'));

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaUnidad['Unidades'][] = $recorre;
		}		



        $ListaRetorno = [$ListaCentroCostos,$ListaConceptos,$ListaCanales, $ListaItems, $ListaEstados, $ListaObras, $ListaUnidad];       
        $list ['ResponseService'] = $ListaRetorno;      
		header('Content-Type: application/json');
		echo json_encode($list);
        }

        public function ObtenerDetalleRequerimiento($idrequerimiento){
            $query = "SELECT i.iditem, UPPER(i.nombreitem) as nombreitem, UPPER(i.unidaditem) as unidaditem, i.cantidaditem,i.cantidadpendiente, i.observacionitem, i.iditemcentrocostos, 0 as precio, i.estadoitem
			from ItemRequerimiento i 
			where i.idrequerimiento = ".$idrequerimiento." and i.estadoitem!=2 and i.estadoitem!=4;";
            $respuestaQuery = mysqli_query($this->dbConnect, $query);

            $ListaDetalle = array();
            
            while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
                $ListaDetalle[] = $recorre;
            }		
            header('Content-Type: application/json');
            echo json_encode($ListaDetalle);
        }
        
        private function llenar_lista_vacia($nombrelista, $lista, $valoreslista){
            $lista[$nombrelista][] = $valoreslista;
            return $lista;
        }
    }
    

    
?>