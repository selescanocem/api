<?php

    class RestReportes{
        

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
        
        
        public function CargarDatosReporteGastosCostos($idobra){

            $ListaRetorno = array();	
            $list = array();

            $query = "SELECT Empresa.nombreempresa, Empresa.RUC AS rucempresa FROM Obra INNER JOIN Empresa ON Empresa.idempresa = Obra.operariotributario WHERE idobra=".$idobra.";";
            $respuestaQuery = mysqli_query($this->dbConnect, $query);
            $dataObra = mysqli_fetch_assoc($respuestaQuery);
            #$nombreObra = $dataObra[0];
            $listaData= array();
            $listaData['Data'][]= $dataObra;

            $query= "SELECT * FROM(SELECT A.nombrearticulo,'GASTO' AS Tipo, G.fechagasto AS fecha, O.preciounitario, O.cantidadoperacion, G.cantidadgasto AS subtotal FROM Gasto G 
            INNER JOIN OperacionAlmacen O ON G.idoperacion = O.idoperacion INNER JOIN DetalleProductoAlmacen D ON O.iddetalle = D.iddetalle INNER JOIN Articulo A ON D.idarticulo = A.idarticulo WHERE G.idobra={$idobra}
            UNION ALL 
            SELECT A.nombrearticulo,'COSTO' AS Tipo, C.fechacosto AS fecha, O.preciounitario, ABS(O.cantidadoperacion), C.cantidadcosto AS subtotal FROM Costo C 
            INNER JOIN OperacionAlmacen O ON C.idoperacion = O.idoperacion INNER JOIN DetalleProductoAlmacen D ON O.iddetalle = D.iddetalle INNER JOIN Articulo A ON D.idarticulo = A.idarticulo WHERE C.idobra={$idobra} 
            UNION ALL
            SELECT I.nombreitem AS nombrearticulo, 'COSTO' As Tipo, CO.fechacosto AS fecha, I.precio AS preciounitario, I.cantidad AS cantidadoperacion, CO.cantidadcosto AS subtotal FROM ItemContratacion I 
            INNER JOIN CostoServicio CO ON CO.idcontratacion = I.idcontratacion) Result ORDER BY Result.fecha;";
        
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

        public function CargarDatosControlBanco($idempresa){
            $query ="SELECT UPPER(E.fechavalor) AS 'FECHA DE VALOR', UPPER(E.fechapago) AS 'FECHA DE OPERACION', UPPER(P.rucproveedor) AS 'RUC/DNI', UPPER(P.razonsocial) AS 'NOMBRE O RAZÓN SOCIAL',
            UPPER(OP.conceptopago) AS 'DESCRIPCIÓN', (CASE WHEN CB.nrocuentabancaria IS NULL THEN '-' ELSE UPPER(CB.nrocuentabancaria) END) AS 'NUMERO DE CUENTA', 
            (CASE WHEN EB.nombreentidad IS NULL THEN '-' ELSE UPPER(EB.nombreentidad) END) AS 'BANCO', UPPER(M.nombremoneda) AS 'MONEDA', 
            UPPER(E.refbanco) AS 'REFERENCIA BANCO' , UPPER(E.numoperacion) AS 'N° DE OPERACIÓN',UPPER(E.monto*-1) AS 'INGRESOS/EGRESOS', E.ITF, UPPER(O.codigoobra) AS 'OBRA',
            UPPER(TC.nombretipo) AS 'TIPO CP', UPPER(TCON.nombretipocontratacion) AS 'ORDEN LOGÍSTICA' FROM Egreso E INNER JOIN OrdenPago OP ON E.idordenpago=OP.idordenpago INNER JOIN Contratacion C ON OP.idcontratacion = C.idcontratacion INNER JOIN 
            Proveedor P ON C.idproveedor = P.idproveedor LEFT JOIN CuentaBancaria CB ON C.idcuenta=CB.idcuenta LEFT JOIN EntidadBancaria EB ON CB.identidad = EB.identidad LEFT JOIN 
            Moneda M ON C.idmoneda = M.idmoneda LEFT JOIN Obra O ON C.idobra=O.idobra LEFT JOIN TipoComprobante TC ON C.idtipocomprobante=TC.idtipocomprobante LEFT JOIN 
            TipoContratacion TCON ON C.tipocontratacion = TCON.idtipocontratacion WHERE OP.idempresa={$idempresa}
            UNION ALL
            SELECT E.fechavalor,E.fechapago,CPP.documento,CPP.razonsocial,CPP.conceptopago,CPP.numerocuenta,EB.nombreentidad,UPPER(M.nombremoneda),E.refbanco,E.numoperacion,E.monto*-1,E.ITF,Obra.codigoObra,TipoComprobante.nombretipo,
            '-' AS 'ORDEN LOGISTICA' FROM Egreso E INNER JOIN CuentaPorPagar CPP ON E.idcuentaporpagar=CPP.idcuentaporpagar INNER JOIN EntidadBancaria EB ON CPP.identidad = EB.identidad
            INNER JOIN Moneda M ON CPP.idmoneda = M.idmoneda LEFT JOIN Obra ON Obra.idobra=CPP.idobra INNER JOIN TipoComprobante ON TipoComprobante.idtipocomprobante=CPP.tipoComprobante 
            WHERE CPP.idempresa={$idempresa}
            UNION ALL
            SELECT fechavalor, fechapago, documento, razonsocial, conceptopago, numerocuenta, EB.nombreentidad, UPPER(M.nombremoneda), refbanco, numoperacion, importecobro, ITF, 
            Obra.codigoobra, ' ' AS nombretipo, '-' AS 'ORDEN LOGISTICA' FROM CuentaporCobrar INNER JOIN EntidadBancaria EB ON CuentaporCobrar.identidadbancaria = EB.identidad 
            INNER JOIN Moneda M ON CuentaporCobrar.idmoneda=M.idmoneda LEFT JOIN Obra ON Obra.idobra=CuentaporCobrar.idobra WHERE idestado=2 AND CuentaporCobrar.idempresa={$idempresa};";
            $respuestaQuery = mysqli_query($this->dbConnect, $query);
            $DatosControl = array();
            while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
                $DatosControl [] = $recorre;
            }		
            header('Content-Type: application/json');
            echo json_encode($DatosControl);
        }

        public function CargarDatosReporteTrazabilidad($idobra){

        $ListaRetorno = array();	
        $list = array();

        $query = "SELECT nomenclatura,  Trabajador.nombretrabajador AS residente, Empresa.nombreempresa AS operariotributario, Empresa.RUC FROM Obra INNER JOIN Trabajador ON Trabajador.idtrabajador = Obra.idresidente INNER JOIN Empresa ON Empresa.idempresa = Obra.operariotributario WHERE idobra=".$idobra.";";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
        $dataObra = mysqli_fetch_assoc($respuestaQuery);
        #$nombreObra = $dataObra[0];
        $listaData= array();
        $listaData['Data'][]= $dataObra;

        //CAMBIAR NOMBRES DE LOS CAMPOS 
        $query = "SELECT ItemRequerimiento.idrequerimiento, RequerimientoObra.fecharequerimiento, ItemRequerimiento.nombreitem, 
        ItemRequerimiento.cantidaditem, ItemRequerimiento.unidaditem, ItemRequerimiento.iditemcentrocostos,
        (CASE 
        WHEN ItemContratacion.idcontratacion is null THEN '-'
        ELSE ItemContratacion.idcontratacion END) AS idcontratacion, ItemRequerimiento.idrequerimiento, 
        (CASE 
        WHEN ItemContratacion.cantidad IS NULL THEN '-'
        ELSE ItemContratacion.cantidad END) AS cantidad, 
        (CASE 
        WHEN ItemContratacion.precio IS NULL THEN '-'
        ELSE ItemContratacion.precio END) AS precio, 
        (CASE WHEN Contratacion.fechacontratacion IS NULL THEN '-'
        ELSE Contratacion.fechacontratacion END) AS fechacontratacion, ItemRequerimiento.cantidadpendiente FROM ItemRequerimiento 
        INNER JOIN RequerimientoObra ON ItemRequerimiento.idrequerimiento = RequerimientoObra.idrequerimiento 
        LEFT JOIN ItemContratacion ON ItemContratacion.iditemrequerimiento = ItemRequerimiento.iditem 
        LEFT JOIN Contratacion ON Contratacion.idcontratacion = ItemContratacion.idcontratacion
        WHERE RequerimientoObra.idobra=".$idobra.";";
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

        public function CargarDatosReporteTrazabilidad2($idobra){

            $ListaRetorno = array();	
            $list = array();
    
            $query = "SELECT Obra.codigoObra AS nomenclatura,  Trabajador.nombretrabajador AS residente, Empresa.nombreempresa AS operariotributario, Empresa.RUC FROM Obra INNER JOIN Trabajador ON Trabajador.idtrabajador = Obra.idresidente INNER JOIN Empresa ON Empresa.idempresa = Obra.operariotributario WHERE idobra=".$idobra.";";
            $respuestaQuery = mysqli_query($this->dbConnect, $query);
            $dataObra = mysqli_fetch_assoc($respuestaQuery);
            #$nombreObra = $dataObra[0];
            $listaData= array();
            $listaData['Data'][]= $dataObra;
    
            //CAMBIAR NOMBRES DE LOS CAMPOS 
            $query = "SELECT RequerimientoObra.codigoRequerimiento AS idrequerimiento, RequerimientoObra.fecharequerimiento, ItemRequerimiento.nombreitem, 
            ItemRequerimiento.cantidaditem, ItemRequerimiento.unidaditem, CONCAT(CanalCentroCostos.idconcepto, '.' , ItemRequerimiento.iditemcentrocostos) AS iditemcentrocostos,
            (CASE 
            WHEN Contratacion.codigoContratacion is null THEN '-'
            ELSE Contratacion.codigoContratacion END) AS idcontratacion,UPPER(Moneda.nombremoneda) AS simbolomoneda,Contratacion.totalcontrato, ItemRequerimiento.idrequerimiento AS reqid, 
            (CASE 
            WHEN ItemContratacion.cantidad IS NULL THEN '-'
            ELSE ItemContratacion.cantidad END) AS cantidad, 
            (CASE 
            WHEN ItemContratacion.precio IS NULL THEN '-'
            ELSE ItemContratacion.precio END) AS precio, 
            (CASE WHEN Contratacion.fechacontratacion IS NULL THEN '-'
            ELSE Contratacion.fechacontratacion END) AS fechacontratacion, ItemRequerimiento.cantidadpendiente,EstadoContratacion.nombre as estadocontratacion,OrdenPago.idordenpago,
            OrdenPago.conceptopago, OrdenPago.importepago, OrdenPago.fechapago, OrdenPago.numerotransaccion, (CASE WHEN tipoordenpago.nombre LIKE '%Inmediato' THEN 'EFECTIVO' ELSE 'CREDITO' END)
            as tipoorden, Proveedor.razonsocial, EstadoRequerimiento.nombre As estadorequerimiento
            FROM ItemRequerimiento 
            INNER JOIN RequerimientoObra ON ItemRequerimiento.idrequerimiento = RequerimientoObra.idrequerimiento 
            LEFT JOIN ItemContratacion ON ItemContratacion.iditemrequerimiento = ItemRequerimiento.iditem 
            LEFT JOIN Contratacion ON Contratacion.idcontratacion = ItemContratacion.idcontratacion
            LEFT JOIN OrdenPago ON OrdenPago.idcontratacion = Contratacion.idcontratacion
            LEFT JOIN tipoordenpago ON OrdenPago.idtipoorden = tipoordenpago.idtipoorden
            LEFT JOIN EstadoContratacion ON Contratacion.idestadocontratacion = EstadoContratacion.idestado
            LEFT JOIN Moneda ON Contratacion.idmoneda = Moneda.idmoneda
            INNER JOIN CanalCentroCostos ON CanalCentroCostos.idcanal = ItemRequerimiento.iditemcentrocostos
            LEFT JOIN Proveedor ON Contratacion.idproveedor = Proveedor.idproveedor
            INNER JOIN EstadoRequerimiento ON RequerimientoObra.estado=EstadoRequerimiento.idestado
            WHERE RequerimientoObra.idobra={$idobra} ORDER BY idrequerimiento;";
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