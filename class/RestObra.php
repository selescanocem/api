<?php

class RestObra{

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

    public function ListarObras(){			

		$query = "SELECT o.idobra, o.nomenclatura, o.enconsorcio, e.descripcion as estadoobra, o.cliente, o.idresidente, 
        t.nombretrabajador as residentenombre, o.operariotributario ,
        em.nombreempresa as nombreoperario, o.gestor, ee.nombreempresa as nombregestor, UPPER(o.codigoObra) AS codigoObra
        from Obra o
        inner join EstadoObra e on o.estadoobra = e.idestadoobra
        inner join Trabajador t on o.idresidente = t.idtrabajador
        INNER JOIN Empresa em on o.operariotributario = em.idempresa
        inner JOIN Empresa ee on o.gestor = ee.idempresa
        where o.eliminado=0";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaObras = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaObras[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaObras);
    }

    public function CargarDatosRegistrarObra(){		
        $ListaRetorno = array();	
        $list = array();

		$query="select idestadoobra,descripcion from EstadoObra;";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaEstados = array();
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaEstados['EstadosObra'][] = $recorre;
        }

        if(count($ListaEstados) == 0 ){
            $ListaEstados = $this->llenar_lista_vacia('EstadosObra', $ListaEstados, array("idestadoobra" => "0", "descripcion" => "SELECCIONAR ESTADO"));
        }

        $query = "SELECT idconsorcio, nombreconsorcio FROM Consorcio WHERE Consorcio.eliminado=0";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
        $ListaConsorcios = array();
        while($recorre = mysqli_fetch_assoc($respuestaQuery)){
            $ListaConsorcios['Consorcios'][] = $recorre;
        }

        if(count($ListaConsorcios) == 0 ){
            $ListaConsorcios = $this->llenar_lista_vacia('Consorcios', $ListaConsorcios, array("idconsorcio" => "0", "nombreconsorcio" => "SELECCIONAR CONSORCIO"));
        }

        $query = "SELECT idempresa, nombreempresa FROM Empresa WHERE Empresa.tipoempresa = 1";

        //$query = "SELECT idconsorcio, nombreconsorcio FROM Consorcio WHERE Consorcio.eliminado=0";
        $respuestaQuery= mysqli_query($this->dbConnect, $query);
        $Recuperar = array();
        while($recorre = mysqli_fetch_assoc($respuestaQuery)){
            $Recuperar['Empresas'][] = $recorre;
        }

        $query = "SELECT idcentrocosto,nombrecentrocostos, total FROM CentroCostos";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);

		$ListaCentroCostos = array();

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaCentroCostos['Centros'][] = $recorre;
        }

        if(count($ListaCentroCostos) == 0){
            $ListaCentroCostos = $this->llenar_lista_vacia('Centros', $ListaCentroCostos, array('idcentrocosto'=> "0", 'nombrecentrocostos' => 'SELECCIONAR', 'total' => "0"));
        }

        //TRAER CONSORCIOS ACTIVOS
        //empresa
        $ListaRetorno = [$ListaEstados,$ListaConsorcios, $Recuperar, $ListaCentroCostos];       
        
        $list ['ResponseService'] = $ListaRetorno;      
         

		header('Content-Type: application/json');
		echo json_encode($list);
    }



    public function ListarObrasDeTrabajador($idtrabajador){			

		$query = "SELECT o.idobra, o.nomenclatura, o.enconsorcio, e.descripcion as estadoobra, o.cliente, o.idresidente, 
        t.nombretrabajador as residentenombre, o.operariotributario ,
        em.nombreempresa as nombreoperario, o.gestor, ee.nombreempresa as nombregestor , UPPER(o.codigoObra) AS codigoObra
        from Obra o
        inner join EstadoObra e on o.estadoobra = e.idestadoobra
        inner join Trabajador t on o.idresidente = t.idtrabajador
        INNER JOIN Empresa em on o.operariotributario = em.idempresa
        inner JOIN Empresa ee on o.gestor = ee.idempresa
        where o.eliminado=0 AND o.idresidente=".$idtrabajador.";";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaObras = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaObras[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaObras);
    }

    public function obtenerEmpresasConsorcio($idconsorcio){
        $query = "SELECT Empresa.idempresa, Empresa.nombreempresa FROM DetalleConsorcio 
        INNER JOIN Empresa ON DetalleConsorcio.idempresa=Empresa.idempresa WHERE DetalleConsorcio.idconsorcio = ".$idconsorcio.";";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $ListaEmpresas = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$ListaEmpresas[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($ListaEmpresas);
    }

    public function CargarDatosObra($idobra){
        $query = "SELECT objetocontrato, nomenclatura, encontrato, numerocontrato, Obra.idcentrocostos, CC.nombrecentrocostos , codigoObra, cliente,Trabajador.nombretrabajador, 
        estadoobra,EO.descripcion,enconsorcio, operariotributario, E1.nombreempresa AS nombreop, gestor, E2.nombreempresa AS nombregestor, idcontratista, C.nombreconsorcio FROM Obra  
        INNER JOIN Trabajador ON Obra.idresidente = Trabajador.idtrabajador
        INNER JOIN CentroCostos CC ON Obra.idcentrocostos=CC.idcentrocosto
        INNER JOIN EstadoObra EO ON Obra.estadoobra=EO.idestadoobra
        INNER JOIN Empresa E1 ON Obra.operariotributario = E1.idempresa
        INNER JOIN Empresa E2 ON Obra.gestor = E2.idempresa
        LEFT JOIN Consorcio C ON Obra.idcontratista = C.idconsorcio WHERE Obra.idobra={$idobra};";

		$respuestaQuery = mysqli_query($this->dbConnect, $query);

        $DetalleObra = array();
        
		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
			$DetalleObra[] = $recorre;
		}		
		header('Content-Type: application/json');
		echo json_encode($DetalleObra);
    }

    public function modificarobra($dataobra){
		$this->dbConnect->autocommit(false);
		#Inicio de la transaccion
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); 
		#obteniendo datos de nuevo articulo

		$idobra = $dataobra->idobra;
        $objetocontrato = $dataobra->objetocontrato;
		$nomenclatura = $dataobra->nomenclatura;
		$encontrato = $dataobra->encontrato;
		$numerocontrato =$dataobra->numerocontrato;
		$cliente =$dataobra->cliente;
        $idresidente = $dataobra->idresidente;
		$nombreresidente = $dataobra->nombreresidente;
		$estadoobra =$dataobra->estadoobra;
		$enconsorcio= $dataobra->enconsorcio;
		$idcontratista =$dataobra->idcontratista;
		$codigoObra= $dataobra->codigoObra;
		$gestor= $dataobra->gestor;
		$operariotributario= $dataobra->operariotributario;

        $query = "UPDATE Obra SET objetocontrato=? , nomenclatura=? , encontrato=? , numerocontrato=? , cliente=? , estadoobra=? , enconsorcio=? , idcontratista=? , codigoObra=? , gestor=? , operariotributario=? WHERE idobra=?";
        $statement = $this->dbConnect->prepare($query);
        if($statement){
            if($statement->bind_param("sssssisisiii", $objetocontrato, $nomenclatura, $encontrato, $numerocontrato, $cliente, $estadoobra, $enconsorcio, $idcontratista, $codigoObra, $gestor,$operariotributario, $idobra)){
                if($statement->execute()){
                    $query = "UPDATE Trabajador SET nombretrabajador = ? WHERE idtrabajador = ?";
                    $statement = $this->dbConnect->prepare($query);
                    if($statement){
                        if($statement->bind_param("si",$nombreresidente, $idresidente)){
                            if($statement->execute()){
                                $status=1;
                                $message="Obra modificada correctamente";
                                $this->dbConnect->commit();
                            }
                            else{
                                $status=0;
                                $message="Obra no modificada. Código de error: {$this->dbConnect->errno}";
                                $this->dbConnect->rollback();
                            }
                        }
                        else{
                            $status=0;
                            $message="Obra no modificada. Código de error: {$this->dbConnect->errno}";
                            $this->dbConnect->rollback();
                        }
                    }
                    else{
                        $status=0;
                        $message="Obra no modificada. Código de error: {$this->dbConnect->errno}";
                        $this->dbConnect->rollback();
                    }
                }
                else{
                    $status=0;
                    $message="Obra no modificada. Código de error: {$this->dbConnect->errno}";
                    $this->dbConnect->rollback();
                }
            }
            else{
                $status=0;
                $message="Obra no modificada. Código de error: {$this->dbConnect->errno}";
                $this->dbConnect->rollback();
            }
        }
        else{
            $status=0;
            $message="Obra no modificada. Código de error: {$this->dbConnect->errno}";
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
    
    public function RegistrarObras($ObraData){

        //$this->dbConnect->autocommit(false);
        $this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
        $objetocontrato=$ObraData["objetocontrato"];
        $nomenclaturacontrato=$ObraData["nomenclatura"];
        $encontrato=$ObraData["encontrato"];
        $numerocontrato=$ObraData["numerocontrato"];
        $enconsorcio=$ObraData["enconsorcio"];
        $estadoobra=$ObraData["estadoobra"];
        $cliente=$ObraData["cliente"];
        $nombreresidente=$ObraData["nombreresidente"];
        $operariotributario=$ObraData["operariotributario"];
        $gestor = $ObraData["gestor"];
        $idcontratista = $ObraData["idcontratista"];
        $codigo = $ObraData["codigoObra"];
        $idcentrocostos = $ObraData["idcentrocostos"];
        $presupuestorestante = $ObraData["presupuestorestante"];

        
        $arr = explode(' ',trim($nombreresidente));

        $usuario = $arr[0];
        $pass = $arr[0];
        

        $query = "INSERT INTO Trabajador VALUES(DEFAULT, ?, '', NULL, NULL, NULL, ?, ?, 2, NULL, 0,0)";
        $statement = $this->dbConnect->prepare($query);
        $statement->bind_param("sss",$nombreresidente,$usuario, $pass);
        if($statement->execute()){
            $query =  "SELECT LAST_INSERT_ID()";
            $respuesta_query = mysqli_query($this->dbConnect, $query);
            $lastId=$respuesta_query->fetch_row();
            $idresidente=$lastId[0];
            $query = "INSERT INTO Obra VALUES(DEFAULT, ?,?,?,?,?,?,?,0,?,?,?,?,?,?,?,0);";
            $statement = $this->dbConnect->prepare($query);
            $statement->bind_param("sssssisiiiisid", $objetocontrato,$nomenclaturacontrato,$encontrato,$numerocontrato,$enconsorcio,$estadoobra,$cliente,$idresidente,$operariotributario,$gestor,$idcontratista,$codigo,$idcentrocostos,$presupuestorestante);
            

            //CORREO

            /*
            $para      = 'selescanoce@gmail.com';
            $titulo    = 'Nueva Obra Registrada';
            $mensaje   = 'Nueva obra registrada satisfactoriamente.'."\n".'DATOS DE INICIO DE SESION:'."\n\t".'USUARIO: '.$nombreresidente."\n\t".'CLAVE: '.$nombreresidente;
            $cabeceras = 'From: brianreyesg@gmail.com' . "\r\n" .
                'Reply-To: brianreyesg@gmail.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            mail($para, $titulo, $mensaje, $cabeceras);*/

            if($statement->execute()){
                $query =  "SELECT LAST_INSERT_ID()";
                $respuesta_query = mysqli_query($this->dbConnect, $query);
                $lastId=$respuesta_query->fetch_row();
                $idanex=$lastId[0];
                $messgae = "Obra Registrada con exito.";
                $status = $idanex;	
    
    
                $this->dbConnect->commit();		
            } else {
                $messgae = "Obra No Registrada. Código de error {$this->dbConnect->errno}";
                $status = 0;
                if($this->dbConnect->errno == 1062){
                    $messgae= $messgae."Codigo repetido";
                }
            }
        
        
        }
        else{
            $messgae = "Error al registrar obra. Código de error {$this->dbConnect->errno}. ";
            $status=0;

            if($this->dbConnect->errno == 1062){
                $messgae= $messgae."El residente ya ha sido asignado a otra obra";
            }

            
        }   
        $statement->close();
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