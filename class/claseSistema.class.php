<?php

class ClaseSistema{
	public $_ORA;
	public $_SESION;
	public $_TEMPLATE;
	public $_VAR_GLOBAL;
	public $_LOG;
	public $_AMBIENTE;
	public $_RESOLUCION;
	
	public $_MENSAJES_GRAL = array();
	public $_CAMBIA_GRAL = array();
	
	public function setControl($obj){
		$this->_ORA = (is_object($obj->_ORA)) ? $obj->_ORA : NULL;
		$this->_SESION = (is_object($obj->_SESION)) ? $obj->_SESION : NULL;
		$this->_TEMPLATE = (is_object($obj->_TEMPLATE)) ? $obj->_TEMPLATE : NULL;
		$this->_VAR_GLOBAL = (is_object($obj->_VAR_GLOBAL)) ? $obj->_VAR_GLOBAL : NULL;
		$this->DATA_CASO = $obj->DATA_CASO;
		$this->_AMBIENTE = $obj->_AMBIENTE;
		$this->_LOG = $obj->_LOG;
		$this->_RESOLUCION = $obj->_RESOLUCION;
		$this->PREFIJO_SCHEMA = $obj->PREFIJO_SCHEMA;
		
		
	}
	
	public function autoControl($directorio = ''){
		$conn = conectar();
        $this->_ORA = new Conexion_ora();
        $this->_ORA->connect( $conn );
        $this->_ORA->SetFetchMode( OCI_ASSOC );
		
		$this->_SESION = new Sesion();		
		$this->_VAR_GLOBAL = simplexml_load_file( $directorio."Sistema/config/config.xml" );
		
		 $this->_ORA->SetClientIdentifier($this->_SESION->USUARIO);
		
		$codigo = (string)$this->_VAR_GLOBAL->codigo;
		$correos = array();
		if(isset($this->_VAR_GLOBAL->variables->correo_error)){
			foreach ( $this->_VAR_GLOBAL->variables->correo_error as $var ) {
				$correos[] = ( string )$var;
			}
			$correos = implode( ",", $correos );
		}
		$this->_LOG = new Log();
		$this->_LOG->opcionesLog(LOG_ARCHIVO);
		$this->_LOG->opcionesError(LOG_ARCHIVO  | LOG_CORREO );
		$this->_LOG->AMBIENTE = $this->_AMBIENTE;
		$this->_LOG->CODIGO_APLICACION = $codigo;
		$this->_LOG->USUARIO = $this->_SESION->USUARIO;
		$this->_LOG->CORREOS = $correos;
		$this->_LOG->ORA = $this->_ORA;
		
		$this->_AMBIENTE = $this->_ORA->ejecutaFunc('ambiente');


		
	}
	
	public function setTemplate($template){
		$this->_TEMPLATE = $template;
	}
	
	protected function desencriptar($secret){
			$secret = base64_decode($secret);
			$cipher = "rijndael-128"; 
			$mode = "cbc"; 
			$secret_key = "D4:6E:AC:3F:F0:BE"; 
			//iv length should be 16 bytes 
			$iv = "fedcba9876543210"; 
			// Make sure the key length should be 16 bytes 
			$key_len = strlen($secret_key); 
			if($key_len < 16 ){ 
				$addS = 16 - $key_len; 
				for($i =0 ;$i < $addS; $i++){ 
					$secret_key.=" "; 
				} 
			}else{ 
				$secret_key = substr($secret_key, 0, 16); 
			} 

			$td = mcrypt_module_open($cipher, "", $mode, $iv); 
			mcrypt_generic_init($td, $secret_key, $iv); 
			//$decrypted_text = mdecrypt_generic($td, $this->hex2bin($secret)); 
			$decrypted_text = mdecrypt_generic($td, $this->hex2bin($secret)); 
			mcrypt_generic_deinit($td); 
			mcrypt_module_close($td); 
			$decrypted_text = base64_decode($decrypted_text);
			$pos_b = strpos($decrypted_text,'[_BCF_]');
			$pos_b = $pos_b+7;
			$decrypted_text = substr($decrypted_text,$pos_b);
			$pos_e = strpos($decrypted_text,'[_ECF_]');
			$decrypted_text = substr($decrypted_text,0,$pos_e);
			return $decrypted_text;
		}
		
		
		protected  function encriptar($secret){
			$secret = '[_BCF_]'.$secret.'[_ECF_]';
			$secret = base64_encode($secret);
			$cipher = "rijndael-128"; 
			$mode = "cbc"; 
			//iv length should be 16 bytes 
			$iv = "fedcba9876543210"; 
			$secret_key = "D4:6E:AC:3F:F0:BE"; 
			// Make sure the key length should be 16 bytes 
			$key_len = strlen($secret_key); 
			if($key_len < 16 ){ 
				$addS = 16 - $key_len; 
				for($i =0 ;$i < $addS; $i++){ 
					$secret_key.=" "; 
				} 
			}else{ 
				$secret_key = substr($secret_key, 0, 16); 
			} 
			$td = mcrypt_module_open($cipher, "", $mode, $iv); 
			mcrypt_generic_init($td, $secret_key, $iv); 
			$cyper_text = mcrypt_generic($td, $secret); 
			mcrypt_generic_deinit($td); 
			mcrypt_module_close($td); 
			return base64_encode(bin2hex($cyper_text)). "
"; 
		}
		
		private function hex2bin($data){
			$bin = "";
			$i = 0;
			do {
				$bin .= chr(hexdec($data{$i}.$data{($i + 1)}));
				$i += 2;
			} while ($i < strlen($data));		
			return $bin;
		}
}

?>