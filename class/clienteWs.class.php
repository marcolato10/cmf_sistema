<?php

class clienteWs extends ClaseSistema{
	
	public $SERVICIO;
	public $VARIABLE;
	public $AMBIENTE;
	public $WSDL;
	
	public function __construct(){
		require_once("nusoap/nusoap.php");
	}
	
	
	
	public function consumir(){
		if($this->WSDL == NULL){
			$PATH_APLIC = dirname(str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
			$this->WSDL = 'https://'.$_SERVER['SERVER_NAME'].$PATH_APLIC.'/ws.php?wsdl';									
		}

		$cliente = new soapclient($this->WSDL, 'wsdl');
		if ($error = $cliente->getError()){
			return "Funcion WS: No se pudo realizar la operacion.<br />Error:".$error;
		}
		$resultado = $cliente->call($this->SERVICIO,$this->VARIABLE);
		$error = $cliente->getError();
		$this->ERROR = $error;
		if($error) {
			echo $this->SERVICIO."<br>\n";
			echo "ERROR POR SOCKET----".htmlspecialchars($cliente->response, ENT_QUOTES)."-------\n";
			echo "ERROR POR SOCKET----".htmlspecialchars($cliente->request, ENT_QUOTES)."-------\n";
			echo "ERROR POR SOCKET----".htmlspecialchars($cliente->debug_str, ENT_QUOTES)."-------\n";		
			return $error;
		}else{
			return $resultado;
		}
	}
	
	
}

?>