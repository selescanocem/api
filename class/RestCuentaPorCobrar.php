<?php
class RestCuentaPorCobrar{
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

    public function cargardatosregistrocuentaporcobrar(){
        $ListaRetorno = array();	
		$list = array();
		$query="SELECT idmoneda, UPPER(nombremoneda) AS nombremoneda FROM Moneda;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaMonedas = array();

		$ListaMonedas = $this->llenar_lista_vacia("Monedas",$ListaMonedas, array("idmoneda" => "0", "nombremoneda" => "SELECCIONE"));
		while($recorre = mysqli_fetch_assoc($respuestaQuery)){
			$ListaMonedas['Monedas'][] = $recorre;
		}

		
		$query="SELECT idtipo,UPPER(nombretipo) AS nombretipo FROM TipoCuentaCobrar;";
		$respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaTipoOrdenes = array();

		$ListaTipoOrdenes = $this->llenar_lista_vacia("Tipos",$ListaTipoOrdenes, array("idtipo" => "0", "nombretipo" => "SELECCIONE"));
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

		$ListaRetorno = [$ListaMonedas, $ListaTipoOrdenes, $ListaEmpresas];

		$list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);
    }


    public function registrarCuentaPorCobrar($data){
		$this->dbConnect->autocommit(false);
		$this->dbConnect->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

        $tipocuentaporcobrar = $data->tipocuentaporcobrar;
        $documento = $data->documento;
        $razonsocial= $data->razonsocial;
        $conceptopago=$data->conceptopago; 
        $importecobro=$data->importecobro; 
        $idmoneda=$data->idmoneda; 
        $fechalimitecobro=$data->fechalimitecobro; 
        $codigoObra=$data->codigoObra; 
        $idobra=$data->idobra; 
        $idempresa=$data->idempresa;

		$query = "INSERT INTO CuentaporCobrar VALUES(DEFAULT,?,?,?,?,?,?,0,'-',CURDATE(), STR_TO_DATE(? , '%Y-%m-%d'),NULL, NULL,1, '','', NULL, ?, ?,?,0,'');";
		$statement=$this->dbConnect->prepare($query);
		if($statement){
			if($statement->bind_param("isssdissii",$tipocuentaporcobrar, $documento, $razonsocial,$conceptopago,$importecobro,$idmoneda,$fechalimitecobro,$codigoObra,$idobra,$idempresa)){
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


    public function listarcuentasporcobrar(){
        $ListaRetorno = array();	
		$list = array();
        $query="SELECT CC.idcuentaporcobrar, CC.tipocuentaporcobrar ,TPC.nombretipo, CC.razonsocial, CC.conceptopago, CC.importecobro,DATEDIFF(CC.fechalimitecobro,now()) AS diasplazo ,ECC.descripcion AS estado,
        CC.codigoObra, CC.idempresa , E.nombreempresa FROM CuentaporCobrar CC INNER JOIN TipoCuentaCobrar TPC ON TPC.idtipo = CC.tipocuentaporcobrar 
        INNER JOIN EstadoCuentaCobrar ECC ON CC.idestado=ECC.idestado INNER JOIN Empresa E ON CC.idempresa=E.idempresa;";
        $respuestaQuery = mysqli_query($this->dbConnect, $query);
		$ListaCuentas = array();

		while( $recorre = mysqli_fetch_assoc($respuestaQuery) ) {
				$ListaCuentas['Cuentas'][]  = $recorre;
		}

        $ListaRetorno = [$ListaCuentas];

		$list ['ResponseService'] = $ListaRetorno;
		header('Content-Type: application/json');
		echo json_encode($list);
    }

	public function confirmarcuentaporcobrar($data){
		/*
		{"idcuentaporcobrar":1,"tipocuentaporcobrar":0,"documento":null,"razonsocial":null,"conceptopago":null,"importecobro":0.0,"idmoneda":0,"identidadbancaria":0,"numerocuenta":"165165165165",
		"fechacuentaporcobrar":null,"fechalimitecobro":null,"fechapago":"2021-3-16","fechavalor":"2021-3-16","idestado":0,"numoperacion":"2124","refbanco":"REF51561691659","ITF":7.50,"codigoObra":null,
		"idobra":0,"idempresa":0,"tipocomprobante":0,"numerocomprobante":null}
		*/
		$idcuentaporcobrar=$data->idcuentaporcobrar;
		$importecobro=$data->importecobro;
		$identidadbancaria=$data->identidadbancaria;
		$numerocuenta=$data->numerocuenta;
		$fechapago=$data->fechapago;
		$fechavalor=$data->fechavalor;
		$numoperacion=$data->numoperacion;
		$refbanco=$data->refbanco;
		$ITF=$data->ITF;
		$idempresa=$data->idempresa;

		$query="UPDATE CuentaporCobrar SET identidadbancaria=?, numerocuenta=?, fechapago=STR_TO_DATE(? , '%Y-%m-%d'), fechavalor=STR_TO_DATE(? , '%Y-%m-%d'), idestado=2, numoperacion=?, refbanco=?, ITF=? WHERE idcuentaporcobrar=?;";
		$statement=$this->dbConnect->prepare($query);
		if($statement){
			if($statement->bind_param("isssssdi",$identidadbancaria,$numerocuenta,$fechapago,$fechavalor,$numoperacion,$refbanco,$ITF,$idcuentaporcobrar)){
				if($statement->execute()){
					$errcode=0;
					if($this->registrarIngresoEmpresa($errcode, $idempresa, $importecobro)){
						$status=1;
						$message="Cuenta por cobrar confirmada correctamente.";
					}
					else{
						$status=0;
						$message="Error al confirmar cuenta por cobrar. Código de error {$this->dbConnect->errno}";
					}
				}
				else{
					$status=0;
					$message="Error al confirmar cuenta por cobrar. Código de error {$this->dbConnect->errno}";
				}
			}
			else{
				$status=0;
				$message="Error al confirmar cuenta por cobrar. Código de error {$this->dbConnect->errno}";
			}
		}
		else{
			$status=0;
			$message="Error al confirmar cuenta por cobrar. Código de error {$this->dbConnect->errno}";
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

	private function registrarIngresoEmpresa(&$codigoError,$idempresa, $importe){
		$query="UPDATE Empresa SET presupuesto=presupuesto+? WHERE idempresa=?";
		$statement=$this->dbConnect->prepare($query);
		if($statement){
			if($statement->bind_param("di",$importe,$idempresa)){
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
}

?>