<?php
require_once(dirname(__FILE__).'/clasificacion.class.php');
require_once(dirname(__FILE__).'/destinatario.class.php');
require_once(dirname(__FILE__).'/adjunto.class.php');
require_once(dirname(__FILE__).'/notificacion.class.php');
require_once(dirname(__FILE__).'/funcionario.class.php');





	class Resolucion extends ClaseSistema{	

		
	
		/**********************************************************  ATRIBUTOS DE LA CLASE DE RESOLUCION *******************************************/
		// VALORES POR DEFECTO DE LAS RESOLUCIONES
		static $DEF_TIPRES_ID = 'exenta';
		static $DEF_CLA_ID = 1;
		static $DEF_PRI_ID = 'publi';
		static $DEF_PRI_NOMBRE = 'Pública';
		static $DEF_EST_ID = 'redac';
		static $DEF_PASO_SELECCIONADO = 1;
		static $DEF_ENV_ID = 'elect';
		static $DEF_RES_JURISPRUDENCIA = 'N';
		static $DEF_DIS_ID = 'inmed';
		static $DEF_DIS_NOMBRE = 'Inmediata';
		static $DEF_RES_PAGO = 'N';
		static $DEF_RES_MULTA = 'N';
		static $DEF_PUB_ID = 'inmed';
		static $DEF_PUB_NOMBRE = 'Inmediata';
		static $DEF_RES_PAGO_NOMBRE = 'No';
		static $DEF_RES_MULTA_NOMBRE = 'No';
		static $DEF_RES_COMUNIQUESE = 'An&oacute;tese, Comun&iacute;quese y Arch&iacute;vese.';
						
		public $_VERSION_RSO = 'v2';
		//ATRIBUTOS DE LA RESOLUCION EN PARTICULAR
		//Paso1
		public 	$TIPRES_ID;
		public 	$CLA_ID;
		public 	$DESTINATARIO;
		public 	$DESTINATARIO_COPIA;
		public  $PASO_SELECCIONADO;
		public  $ENV_ID;
		public  $RES_JURISPRUDENCIA;
		public  $RES_COMUNIQUESE;
		public 	$RES_REFERENCIA;
		
		//OBJETOS DE LA CLASE RESOLUCION
		public $CLASIFICACION_OBJ;
		public $DESTINATARIO_OBJ;	
		public $MODULO_OBJ;	
		public $ADJUNTO_OBJ;
		public $NOTIFICACION_OBJ;
		
		//public $CERTIFICADO_OBJ;//ml
		/********************************************************************* FIN ATRIBUTOS *******************************************************/
		
		
		
		
		// Método constructor de la resolucion
		public function __construct($obj){
			
			$this->setControl($obj);
				
			// Se deben cargar los objetos de la clase resolucion
			$this->CLASIFICACION_OBJ = new Clasificacion();
			$this->CLASIFICACION_OBJ->setControl($obj);
			$this->DESTINATARIO_OBJ = new Destinatario();
			$this->DESTINATARIO_OBJ->setControl($obj);
			$this->MODULO_OBJ = new Modulo();
			$this->MODULO_OBJ->setControl($obj);
			
			$this->ADJUNTO_OBJ =  new Adjunto();
			$this->ADJUNTO_OBJ->setControl($obj);
			
			$this->NOTIFICACION_OBJ = new Notificacion();
			$this->NOTIFICACION_OBJ->setControl($obj);
			

		

			$this->cargarSesion();
			

			//$this->DESTINATARIO_COPIA = new Destinatario();
			//$this->DESTINATARIO->setControl($obj);
		}
		
		
		
		    //ml: chequeamos los datos sensibles si existen para habilitar opciones DATOS SENSIBLES
			/*public function fun_chequea_datos_sensibles($sensible,$tipo_certificado){

				try{  
					
					//$tipo_certificado = $this->_SESION->getVariable('TIPO_CERTIFICADO');
	
					if($sensible == 'NO'){
						$identifica = 'NO';    
						$bind = array(":p_tipodoc_id"=>$tipo_certificado, ":p_identifica" =>$identifica);
						$resultado = $this->_ORA->ejecutaFunc("GDE.GDE_PRIVACIDAD_PKG.FUN_EXISTE_DATOS_SENSIBLES", $bind);
					}else{
						$identifica = 'SI';    
						$bind = array(":p_tipodoc_id"=>$tipo_certificado, ":p_identifica" =>$identifica);
						$resultado = $this->_ORA->ejecutaFunc("GDE.GDE_PRIVACIDAD_PKG.FUN_EXISTE_DATOS_SENSIBLES", $bind);
					}
					
					if($resultado > 0){
						$resultado = '';
					}else{
						$resultado = 'disabled';
					}
					
					return $resultado; 
		
		
				}catch (Exception $e){
					$this->util->mailError($e);
				}
				
			}*/
	



		/******************************** PASO 1 *************************************************/
		public function setTipoResolucion(){
			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();			
			$TIPO_OLD = $this->_SESION->getVariable('TIPRES_ID');
			$MENSAJES[] = 'Tipo Old: '.$TIPO_OLD;
			$this->TIPRES_ID = $_POST['TIPRES_ID'];
			$MENSAJES[] = 'Tipo Nuevo: '.$this->TIPRES_ID;
			$this->_SESION->setVariable('TIPRES_ID',$this->TIPRES_ID);
			//SE SE CAMBIA EL TIPO DE RESOLUCION SE DEBE CAMBIAR LAS CLASIFICACIONES ASOCIADAS.			
			if($TIPO_OLD !== $this->TIPRES_ID){
				$CLASIFICACION = $this->CLASIFICACION_OBJ->fun_getIdClasificacionTipo($this->TIPRES_ID);
				$MENSAJES[] = 'La clasificacion seleccionada es: '.$CLASIFICACION;
				//Una vez que se tiene la clasificacion se deben tener los hijos para proponer
				$data = $this->CLASIFICACION_OBJ->fun_getHijosClasificacion($CLASIFICACION);
				$MENSAJES[] = 'Se obtienen '.count($data).' Clasificaciones para mostrar';																
				$CAMBIA['#div_clasificacion'] = $this->CLASIFICACION_OBJ->fun_dibujaClasificacion($CLASIFICACION); 
			}									
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		
		
		
		public function fun_getTipoResolucion(){
			$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_TIPO_RESOLUCION_PKG.fun_getAllTipoResolucion','function');
			return $this->_ORA->FetchAll($cursor);
		}
		
		
		public function isValidoModificar(){
			//echo $this->_SESION->getVariable('EST_ID') . ' '. $this->_SESION->getVariable('RES_DUENO');
			if(		((	$this->_SESION->getVariable('EST_ID') == 'redac' || 
						$this->_SESION->getVariable('EST_ID') == 'visac' || $this->_SESION->getVariable('EST_ID') == 'pagad') && 
						
						$this->_SESION->USUARIO == $this->_SESION->getVariable('RES_DUENO')) || 
						$this->_SESION->getVariable('RES_DUENO') == false){
				return true;
			}else{
				return false;
			}
		}
		
		public function setJurisprudencia(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$this->RES_JURISPRUDENCIA = $_POST['RES_JURISPRUDENCIA'];			
			$this->_SESION->setVariable('RES_JURISPRUDENCIA',$this->RES_JURISPRUDENCIA);
			$MENSAJES = array();
			$MENSAJES[] = 'Valor de Jurisprudencia: '.$this->RES_JURISPRUDENCIA;
			$CAMBIA = array();						
			$CAMBIA['#arbol_propiedades'] = $this->CLASIFICACION_OBJ->dibujaPropiedades(); 
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		public function setDistribucion(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			$this->CLASIFICACION_OBJ->setDistribucion();
			$MENSAJES = array_merge($MENSAJES,$this->CLASIFICACION_OBJ->_MENSAJES_GRAL);			
			$CAMBIA = array_merge($CAMBIA,$this->CLASIFICACION_OBJ->_CAMBIA_GRAL);			
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		public function setPublicacion(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			$this->CLASIFICACION_OBJ->setPublicacion();
			$MENSAJES = array_merge($MENSAJES,$this->CLASIFICACION_OBJ->_MENSAJES_GRAL);			
			$CAMBIA = array_merge($CAMBIA,$this->CLASIFICACION_OBJ->_CAMBIA_GRAL);			
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		public function setPrivacidad(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			$this->CLASIFICACION_OBJ->setPrivacidad();
			$MENSAJES = array_merge($MENSAJES,$this->CLASIFICACION_OBJ->_MENSAJES_GRAL);			
			$CAMBIA = array_merge($CAMBIA,$this->CLASIFICACION_OBJ->_CAMBIA_GRAL);					
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		
		
		
		public function setMulta(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			$this->CLASIFICACION_OBJ->setMulta();
			$MENSAJES = array_merge($MENSAJES,$this->CLASIFICACION_OBJ->_MENSAJES_GRAL);			
			$CAMBIA = array_merge($CAMBIA,$this->CLASIFICACION_OBJ->_CAMBIA_GRAL);			
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}	
		
		public function setPago(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			$this->CLASIFICACION_OBJ->setPago();
			$MENSAJES = array_merge($MENSAJES,$this->CLASIFICACION_OBJ->_MENSAJES_GRAL);			
			$CAMBIA = array_merge($CAMBIA,$this->CLASIFICACION_OBJ->_CAMBIA_GRAL);			
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		public function fun_usuariosVigentesPrivacidad(){
			$json = array();
			$bind = array(':p_res_id' =>  $this->_SESION->getVariable('RES_ID'), ':p_res_version' => $this->_SESION->getVariable('RES_VERSION'),':p_patron' => $_GET['tag']);
			$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_usuariosVigentesBusq','function',$bind);
			$todos = array();
			while($data = $this->_ORA->FetchArray($cursor)){
				$json[] = array("caption" => $data['NOMBRE'] ,"value" => $data['EP_USUARIO']);
				$todos[$data['EP_USUARIO']] = $data;
			}				
			$this->setUsuariosTodos($todos);			
			echo json_encode($json);
		}
		
		
		public function setUsuariosTodos($array){
			$usuarios_actuales = $this->_SESION->getVariable('USUARIOS_TODOS');
			if(!is_array($usuarios_actuales)){
				$usuarios_actuales = array();
			}
			$nuevos_usr = array_merge($usuarios_actuales,$array);
			$this->_SESION->setVariable('USUARIOS_TODOS',$nuevos_usr);
			
		}
		
		
		
		public function setPrivacidadUsr(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$this->PRI_ID_USR = $_POST['PRI_ID_USR'];			
			$this->_SESION->setVariable('PRI_ID_USR',$this->PRI_ID_USR);
			$MENSAJES = array();
			$CAMBIA = array();						
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		
		
		//Obsoleta
		public function setClasificacion(){
			return $this->CLASIFICACION_OBJ->setClasificacion();
		}
		
		public function seleccionarClasificacion(){
			return $this->CLASIFICACION_OBJ->seleccionarClasificacion();
		}

		public function setClasificacion2(){
			return $this->CLASIFICACION_OBJ->setClasificacion2();
		}
	
	
		public function agregarClasificacion(){			
			return $this->CLASIFICACION_OBJ->agregarClasificacion();
		}
		
		public function eliminarClasificacion(){		
			return $this->CLASIFICACION_OBJ->eliminarClasificacion();
		}

		
		
		public function setEnvio(){
			$json = array();
			$MENSAJES = array();
			$json['RESULTADO'] = 'OK';
			$this->ENV_ID = $this->_SESION->getVariable('ENV_ID');
			$this->ENV_ID = (is_array($this->ENV_ID)) ? $this->ENV_ID : array();
			$ARRAY_AUX = array();
			$MENSAJES[] = 'Existente '.print_r($this->ENV_ID,true);
			foreach($this->ENV_ID as $existentes){
				if($existentes['CLAENV_MOD'] == 'N'){
					$ARRAY_AUX[$existentes['ENV_ID']] = $existentes;
				}
			}

			$this->ENV_ID = $ARRAY_AUX;			
			if(is_array($_POST['check_tipoEnvio'])){
				foreach($_POST['check_tipoEnvio'] as $env){
					//Se debe revisar que no se borren los que ya existen
					if(!isset($this->ENV_ID[$env])){
						$this->ENV_ID[$env] = array('ENV_ID' => $env, 'CLAENV_MOD' => 'S');
					}
				}
				$this->_SESION->setVariable('ENV_ID',$this->ENV_ID);
			}
				
			$this->_LOG->log(print_r($this->ENV_ID,true));
			
			$MENSAJES[] = 'Envios '.print_r($this->ENV_ID,true);
			$MENSAJES[] = 'Post '.print_r($_POST,true);
			$CAMBIA = array();
			#$CAMBIA['#arbol_propiedades'] = $this->CLASIFICACION_OBJ->dibujaPropiedades();
			$this->_SESION->setVariable('ENV_ID',$this->ENV_ID);
			$CAMBIA['#div_tipoEnvio'] =  $this->CLASIFICACION_OBJ->getHTMLtipoEnvio($this->ENV_ID);	
			
				
			/*
			$bind = array(':res_id' => $this->_SESION->getVariable('RES_ID'),':version' => NULL);
			$r = $this->_ORA->ejecutaFunc('RSO_DESTINATARIO_PKG.fun_compruebaDestinatariosSVS',$bind);
			$MENSAJES[] = "RESULTADO $r";
			$this->_ORA->Commit();
			*/
			
		
			
			
			
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		
		
		
		public function buscarClasificacion(){
			return $this->CLASIFICACION_OBJ->buscarClasificacion();
		}
	
		
		
		
		private function cargarSesion(){
			
			
			
			
			$this->TIPRES_ID = ($this->_SESION->getVariable('TIPRES_ID')) ? $this->_SESION->getVariable('TIPRES_ID') : Resolucion::$DEF_TIPRES_ID;
			
			$this->CLA_ID = ($this->_SESION->getVariable('CLA_ID')) ? $this->_SESION->getVariable('CLA_ID') : Resolucion::$DEF_CLA_ID;
			if($this->_SESION->getVariable('CLA_ID') === false){
				$this->_SESION->setVariable('CLA_ID',$this->CLA_ID);
			}
			
			
			$this->EST_ID = ($this->_SESION->getVariable('EST_ID')) ? $this->_SESION->getVariable('EST_ID') : Resolucion::$DEF_EST_ID;
			$this->_SESION->setVariable('EST_ID',$this->EST_ID);
			
			$this->DESTINATARIO = ($this->_SESION->getVariable('DESTINATARIO')) ? $this->_SESION->getVariable('DESTINATARIO') : array();
			$this->PASO_SELECCIONADO = ($this->_SESION->getVariable('PASO_SELECCIONADO')) ? $this->_SESION->getVariable('PASO_SELECCIONADO') : Resolucion::$DEF_PASO_SELECCIONADO;
			$this->ENV_ID = ($this->_SESION->getVariable('ENV_ID')) ? $this->_SESION->getVariable('ENV_ID') : Resolucion::$DEF_ENV_ID;
			$this->RES_JURISPRUDENCIA = ($this->_SESION->getVariable('RES_JURISPRUDENCIA')) ? $this->_SESION->getVariable('RES_JURISPRUDENCIA') : Resolucion::$DEF_RES_JURISPRUDENCIA;			
			$this->CLASIFICACION_OBJ->DIS_ID = ($this->_SESION->getVariable('DIS_ID')) ? $this->_SESION->getVariable('DIS_ID') : Resolucion::$DEF_DIS_ID;
			if($this->_SESION->getVariable('DIS_ID') === false){
				$this->_SESION->setVariable('DIS_ID',$this->CLASIFICACION_OBJ->DIS_ID);
			}
			
			$this->CLASIFICACION_OBJ->DIS_NOMBRE = ($this->_SESION->getVariable('DIS_NOMBRE')) ? $this->_SESION->getVariable('DIS_NOMBRE') : Resolucion::$DEF_DIS_NOMBRE;
			$this->CLASIFICACION_OBJ->RES_PAGO = ($this->_SESION->getVariable('RES_PAGO')) ? $this->_SESION->getVariable('RES_PAGO') : Resolucion::$DEF_RES_PAGO;
			$this->CLASIFICACION_OBJ->RES_MULTA = ($this->_SESION->getVariable('RES_MULTA')) ? $this->_SESION->getVariable('RES_MULTA') : Resolucion::$DEF_RES_MULTA;
			
			
			$this->CLASIFICACION_OBJ->PUB_ID = ($this->_SESION->getVariable('PUB_ID')) ? $this->_SESION->getVariable('PUB_ID') : Resolucion::$DEF_PUB_ID;
			if($this->_SESION->getVariable('PUB_ID') === false){
				$this->_SESION->setVariable('PUB_ID',$this->CLASIFICACION_OBJ->PUB_ID);
			}
			
			$this->CLASIFICACION_OBJ->PUB_NOMBRE = ($this->_SESION->getVariable('PUB_NOMBRE')) ? $this->_SESION->getVariable('PUB_NOMBRE') : Resolucion::$DEF_PUB_NOMBRE;
			if($this->_SESION->getVariable('PUB_NOMBRE') === false){
				$this->_SESION->setVariable('PUB_NOMBRE',$this->CLASIFICACION_OBJ->PUB_NOMBRE);
			}
			
			$this->CLASIFICACION_OBJ->RES_PAGO_NOMBRE = ($this->_SESION->getVariable('RES_PAGO_NOMBRE')) ? $this->_SESION->getVariable('RES_PAGO_NOMBRE') : Resolucion::$DEF_RES_PAGO_NOMBRE;
			$this->CLASIFICACION_OBJ->RES_MULTA_NOMBRE = ($this->_SESION->getVariable('RES_MULTA_NOMBRE')) ? $this->_SESION->getVariable('RES_MULTA_NOMBRE') : Resolucion::$DEF_RES_MULTA_NOMBRE;
			
			$this->CLASIFICACION_OBJ->PRI_ID = ($this->_SESION->getVariable('PRI_ID')) ? $this->_SESION->getVariable('PRI_ID') : Resolucion::$DEF_PRI_ID;
			if($this->_SESION->getVariable('PRI_ID') === false){
				$this->_SESION->setVariable('PRI_ID',$this->CLASIFICACION_OBJ->PRI_ID);
			}
			
			$this->CLASIFICACION_OBJ->PRI_NOMBRE = ($this->_SESION->getVariable('PRI_NOMBRE')) ? $this->_SESION->getVariable('PRI_NOMBRE') : Resolucion::$DEF_PRI_NOMBRE;
			if($this->_SESION->getVariable('PRI_NOMBRE') === false){
				$this->_SESION->setVariable('PRI_NOMBRE',$this->CLASIFICACION_OBJ->PRI_NOMBRE);
			}
			
			
			
			$this->RES_REFERENCIA = ($this->_SESION->getVariable('RES_REFERENCIA')) ? $this->_SESION->getVariable('RES_REFERENCIA') : '';
			$this->RES_VISTOS = ($this->_SESION->getVariable('RES_VISTOS')) ? $this->_SESION->getVariable('RES_VISTOS') : '';
			$this->RES_CONSIDERANDO = ($this->_SESION->getVariable('RES_CONSIDERANDO')) ? $this->_SESION->getVariable('RES_CONSIDERANDO') : '';
			
			$this->RES_RESUELVO = ($this->_SESION->getVariable('RES_RESUELVO')) ? $this->_SESION->getVariable('RES_RESUELVO') : '';
			
			$this->RES_COMUNIQUESE = ($this->_SESION->getVariable('RES_COMUNIQUESE') !== false) ? $this->_SESION->getVariable('RES_COMUNIQUESE') : Resolucion::$DEF_RES_COMUNIQUESE;
			$this->_SESION->setVariable('RES_COMUNIQUESE',$this->RES_COMUNIQUESE);
			$this->RES_ID = ($this->_SESION->getVariable('RES_ID') !== false) ? $this->_SESION->getVariable('RES_ID') : NULL;
			
			
			
			
			
		}
		
		
		
		
		public function getDestinatariosTipo(){
			return $this->DESTINATARIO_OBJ->getDestinatariosTipo();
		}
		
		
		
		
		public function agregarSeleccionadoDestinatario(){
			$json = $this->DESTINATARIO_OBJ->agregarSeleccionadoDestinatario();	
			$this->DESTINATARIO = $this->_SESION->getVariable('DESTINATARIO');					
			return $json;
		}
		
		
		public function getHtmlEnvio(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$this->ENV_ID = $_POST['ENV_ID'];			
			$this->_SESION->setVariable('ENV_ID',$this->ENV_ID);
			$MENSAJES = array();
			$CAMBIA = array();
			$MENSAJES[] = 'El nuevo tipo de envio es '.$this->ENV_ID;
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		
		public function setReferencia(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$this->RES_REFERENCIA = $_POST['RES_REFERENCIA'];			
			$this->_SESION->setVariable('RES_REFERENCIA',$this->RES_REFERENCIA);
			$MENSAJES = array();
			$CAMBIA = array();
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		public function setVistos(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$this->RES_VISTOS = $_POST['RES_VISTOS'];			
			$this->_SESION->setVariable('RES_VISTOS',$this->RES_VISTOS);
			$MENSAJES = array();
			$CAMBIA = array();
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		public function setConsiderando(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$this->RES_CONSIDERANDO = $_POST['RES_CONSIDERANDO'];
			$this->_SESION->setVariable('RES_CONSIDERANDO',$this->RES_CONSIDERANDO);
			$MENSAJES = array();
			$CAMBIA = array();
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		public function setResuelvo(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$this->RES_RESUELVO = $_POST['RES_RESUELVO'];			
			$this->_SESION->setVariable('RES_RESUELVO',$this->RES_RESUELVO);
			$MENSAJES = array();
			$CAMBIA = array();
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		public function setComuniquese(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$this->RES_COMUNIQUESE = $_POST['RES_COMUNIQUESE'];			
			$this->_SESION->setVariable('RES_COMUNIQUESE',$this->RES_COMUNIQUESE);
			$MENSAJES = array();
			$CAMBIA = array();
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		public function anular(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$guardar = new Guardar();
			$guardar->setControl($this);
			$this->_SESION->setVariable('mensaje_anular',$_POST['msg']);
			$rsp = $guardar->anular();
			if($rsp != 'OK'){
				$json['RESULTADO'] = 'ERROR';
				$json['ERROR'] = $rsp;
			}
			$MENSAJES = array();
			$CAMBIA = array();
			$CALLBACK = array();
			$CALLBACK[] = "window.location.href = 'index.php?pagina=paginas.salirRedaccion'";
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$json['CALLBACK'] = $CALLBACK;
			
			return json_encode($json);
		}
		
		public function enviarResolucion($n){
			$json = array();
			$json['RESULTADO'] = 'OK';
		
			
		
		
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}




		
		public function fun_cargarBD($caso,$version = -1){
			try{
				
				$bind = array(':p_res_id' => $caso, ':p_res_version' => $version);
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_getResolucion','function',$bind);
				$data = $this->_ORA->FetchArray($cursor);
				//print_r($data);
				
				foreach($data as $key => $campo){
					if(is_object($campo)){
						$this->_SESION->setVariable($key,$campo->load());
					}else{
						$this->_SESION->setVariable($key,$campo);
					}
				}
				
				//Desde acá se deberia rescatar los valores de las clasificaciones y revisar los envios
				
				
				$bind = array(
					':res_id' => $this->_SESION->getVariable('RES_ID'),
					':res_version' => $this->_SESION->getVariable('RES_VERSION'));
				$isPDF = $this->_ORA->ejecutaFunc('rso.RSO_OPCION_PDF_PKG.fun_getIsOpcionPdf',$bind);
				
				if($isPDF == 'S'){
					$bind = array(
						':res_id' => $this->_SESION->getVariable('RES_ID'),
						':res_version' => $this->_SESION->getVariable('RES_VERSION'));
					$cursor = $this->_ORA->retornaCursor('rso.RSO_OPCION_PDF_PKG.fun_getDatosPDF','function',$bind);
					$dato_pdf = $this->_ORA->FetchArray($cursor);
					
					if(isset($dato_pdf['OPCPDF_ARCHIVO']) && is_object($dato_pdf['OPCPDF_ARCHIVO'])){
					
						$this->_SESION->setVariable('archivo_pdf_subido',$dato_pdf['OPCPDF_ARCHIVO']->load());
						$this->_SESION->setVariable('_OPCION_SUBIR_PDF',true);
						$this->_SESION->setVariable('archivo_pdf_subido_md5',$dato_pdf['OPCPDF_MD5']);
					}
					
				}
				
				
				$RES_DATOS_VARIABLES = $this->_SESION->getVariable('RES_DATOS_VARIABLES');
				try{
					$XML_DV = new SimpleXMLElement($RES_DATOS_VARIABLES);
					foreach($XML_DV->MODULO as $modulo){
						$clasificacion = '_';
						$ID_SESION_XML = 'MODULO_CLA_ID_'.$clasificacion.'_MOD_ID_'.$modulo['MOD_ID'];
						$variables_sesion = $this->_SESION->getVariable($ID_SESION_XML);
						if($variables_sesion === false){
							$variables_sesion = array();
						}
						foreach($modulo->REGISTRO as $registro){
							$array_variables = array();
							foreach($registro as $key => $variable){
								$array_variables[$key] = (string)$variable;
							}
							$variables_sesion[] = $array_variables;
						}
						$variables_sesion = $this->_SESION->setVariable($ID_SESION_XML,$variables_sesion);
					}
				}catch(Exception $e){
				}
				
				
				//echo $RES_DATOS_VARIABLES;
				
				
				
				
				
				$bind = array(':p_res_id' => $caso, ':p_res_version' => $this->_SESION->getVariable('RES_VERSION'));
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_ADJUNTO_PKG.fun_getAdjunto','function',$bind);
				$ADJUNTOS = array();
				while($data = $this->_ORA->FetchArray($cursor)){
					$ADJUNTOS[$data['ADJ_HASH']] = array('ID' => $data['ADJ_ID'],'ADJ_NOMBRE' => $data['ADJ_NOMBRE'],'ADJ_SEQ' => $data['ADJ_SEQ'],'ADJ_HASH' => $data['ADJ_HASH']);
				}								
				$this->_SESION->setVariable("RSO_ADJUNTO",$ADJUNTOS);
				 
				 
				 
				
				$bind = array(':p_res_id' => $caso, ':p_res_version' => $this->_SESION->getVariable('RES_VERSION'));
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_DESTINATARIO_PKG.fun_getDestinatarios','function',$bind);
				$DESTINATARIOS = array();
				$usuarios_seleccionados = $this->DESTINATARIO_OBJ->getUsuariosSeilEntidadSelect($caso,$this->_SESION->getVariable('RES_VERSION'));
				
				
				while($data = $this->_ORA->FetchArray($cursor)){
					if(isset($data['DES_TIPO_ENT']) && $data['DES_TIPO_ENT'] != 'OTRCU' ){
						
					
						$usuarios = $this->DESTINATARIO_OBJ->getUsuariosSeilEntidad($data['DES_RUT']);
						/** acá tengo que ver si para este rut existe algun otros **/
						$USUARIOS_RUT = array();
						foreach($usuarios_seleccionados as $sel){
							//print_r($data);
							//print_r($sel);
							//echo "\n\n\n\n";
							if($data['DES_ID'] == $sel['DES_ID'] && isset($sel['USUENV_TIPO']) && $sel['USUENV_TIPO'] == 'otro'){
								$sel['CHECKED'] = 'checked';
								$USUARIOS_RUT[] = $sel;
							}
						}
						
						
						
						
						
						//$usuarios_paso = $usuarios;
						/*
						print_r($usuarios);
						exit();
						$tipos_entidad = array();
						foreach($usuarios as $key => $usr){
							foreach($usuarios_seleccionados as $sel_usr){
							}
							//USUENV_USUARIO
						}
						*/
						foreach($usuarios as $key => $usr){
							$usuarios[$key]['CHECKED'] = '';
							foreach($usuarios_seleccionados as $sel_usr){
								
								if($usuarios[$key]['COD_USUARIO'] == $sel_usr['USUENV_USUARIO']){
									$usuarios[$key]['CHECKED'] = 'checked';
								}
							}
							//USUENV_USUARIO
						}
						
						
						$data['USUARIOS_SEIL'] = array_merge($usuarios,$USUARIOS_RUT);
						$DESTINATARIOS[$data['DES_TIPO_ENT']][$data['DES_RUT']] = $data;
					
					}else{
						$DESTINATARIOS[$data['DES_TIPO_ENT']][$data['DES_RUT']] = $data;
					}
					
					
				}
				$this->_SESION->setVariable('DESTINATARIO',$DESTINATARIOS);
				
				
				//print_r($DESTINATARIOS);
				
				
				//FIRMANTES
				$bind = array(':p_res_id' => $caso);
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_FIRMANTES_PKG.fun_getFirmantes','function',$bind);
				$FIRMANTES = array();
				while ($data = $this->_ORA->FetchArray($cursor)){
					$FIRMANTES[] = $data['RES_FIRMA'].'|-|'.$data['RES_CARGO_FIRMA'];
				
				}
				
				$this->_SESION->setVariable('FIRMANTES',$FIRMANTES);
				
				
				
				
				
				$bind = array(':p_res_id' => $caso, ':p_res_version' => $this->_SESION->getVariable('RES_VERSION'));
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getClasificacionRes','function',$bind);
				$CLA_ID = array();
				
				$ARBOL_ID = NULL;
				
				while($data = $this->_ORA->FetchArray($cursor)){
					$CLA_ID[$data['CLA_ID']] = $data['CLA_ID'];
					
					$bind_cla = array(':p_cla_id' => $data['CLA_ID']);
					$cursor_cla = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getClasificacion','function',$bind_cla);
					while($data_cla = $this->_ORA->FetchArray($cursor_cla)){
						if($ARBOL_ID == NULL){
							$ARBOL_ID = $data_cla['ARBPRO_ID'];
						}else{
							if($ARBOL_ID != $data_cla['ARBPRO_ID']){
								$this->_LOG->error("Error asociado a las clasificaciones, poseen distinto arbol, comuniquese con Informática.");
								echo "Error asociado a las clasificaciones, poseen distinto arbol, comuniquese con Informática.";
								exit();
							}
						}
						
					}
					
				}

				$this->_SESION->setVariable('ARBPRO_ID',$ARBOL_ID);
				
				$bind = array(':p_arb_id' => $ARBOL_ID);
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getArbolPropiedades','function',$bind);
				//while(
				$data = $this->_ORA->FetchArray($cursor);//){
					//print_r($data);
				//}
				//exit();				
				//$ARBOL_ID				
				$this->_SESION->setVariable('TIPRES_ID',$data['TIPRES_ID']);

				$this->_SESION->setVariable('CLA_ID',$CLA_ID);
				
				
				
				
				$SEL_ENVIO_ARRAY = array();

				foreach($CLA_ID as $cla_id_aux){
					$sel_envio = $this->CLASIFICACION_OBJ->getEnviosClasificacion($cla_id_aux);
					if(is_array($sel_envio)){							
						foreach($sel_envio as $env_id_aux){
							$selector = (isset($SEL_ENVIO_ARRAY[$env_id_aux['ENV_ID']])) ? $SEL_ENVIO_ARRAY[$env_id_aux['ENV_ID']]['CLAENV_MOD'] : 'N';
							$env_id_aux['CLAENV_MOD'] = ($selector == 'S') ? 'S' : $env_id_aux['CLAENV_MOD'];
							unset($env_id_aux['CLA_ID']);
							$SEL_ENVIO_ARRAY[$env_id_aux['ENV_ID']] = $env_id_aux;
						}
					}
				}
					
					
				
				
				//Array ( [corre] => Array ( [ENV_ID] => corre [CLAENV_MOD] => S ) [mano] => Array ( [ENV_ID] => mano [CLAENV_MOD] => N ) ) 
				
				
				$bind = array(':p_res_id' => $caso, ':p_res_version' => $this->_SESION->getVariable('RES_VERSION'));
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_getTipoEnvio','function',$bind);
				$ENV_ID = array();
								
				while($data = $this->_ORA->FetchArray($cursor)){
					$modificacion = 'S';
					if(isset($SEL_ENVIO_ARRAY[$data['ENV_ID']])){
						$modificacion = $SEL_ENVIO_ARRAY[$data['ENV_ID']]['CLAENV_MOD'];
					}
					$ENV_ID[$data['ENV_ID']] = array('ENV_ID' => $data['ENV_ID'],'CLAENV_MOD' => $modificacion);
				}
				
				$this->_SESION->setVariable('ENV_ID',$ENV_ID);
				//print_r($ENV_ID);
				
				
				
				
				$bind = array(':p_res_id' => $caso, ':p_res_version' => $this->_SESION->getVariable('RES_VERSION'));
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_getUsuariosAutorizados','function',$bind);
				$PRI_ID_USR = array();
				
				
				while($data = $this->_ORA->FetchArray($cursor)){
					//$bind = array(':usr' => $data['USUPRI_USUARIO']);
					//$nombre = $this->_ORA->ejecutaFunc('wfa_usr.getNombreUsuario',$bind);
					$PRI_ID_USR[] = $data['USUPRI_USUARIO'];//array('EP_USUARIO' => $data['USUPRI_USUARIO'], 'NOMBRE' =>$nombre);
				}
				
				$this->_SESION->setVariable('PRI_ID_USR',$PRI_ID_USR);
				//print_r($PRI_ID_USR);
				
				$bind = array(':p_res_id' => $caso);
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_getNotificacionInterna','function',$bind);
				$notificaciones = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('NOTIFICACIONES_INTERNAS',$notificaciones);
				
				
				$bind = array(':p_res_id' => $caso, ':p_version' => $this->_SESION->getVariable('RES_VERSION'));				
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_getEncargadosUnidad','function',$bind);
				$notificaciones = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('ENCARGADOS_UNIDAD',$notificaciones);
				
				
				$bind = array(':p_res_id' => $caso, ':p_version' => $this->_SESION->getVariable('RES_VERSION'));				
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_POSICION_RUBRICA_PKG.fun_getPosicionFirma','function',$bind);
				$posicion = $this->_ORA->FetchAll($cursor);
				if(count($posicion) > 0){
					$this->_SESION->setVariable('_POSICION_FIRMA',$posicion[0]['POSRUB_POSICION']);
				}
				
				
				
				
				
				
			
			}catch(Exception $e){
				print_r($e);
				$this->_LOG->error(print_r($e,true));
			}
			
			//print_r($data);
			/*foreach($data as $key => $campo){
				if(is_object($campo)){
					$this->_SESION->setVariable($key,$campo->load());
				}else{
					$this->_SESION->setVariable($key,$campo);
				}
			}*/
			
			
		}


	
		public function verExpediente($padre){
		
			//var_dump($padre);
			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();	
			$OPEN = array();			
			$MENSAJES[] = 'MOSTRAMOS MODAL CON LOS EXPEDIENTES DISPONIBLES';
			
			
			//$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_DESTINATARIO_PKG.fun_getTratamiento','function');	
			//$data_all = $this->getTratamiento();
			//foreach($data_all as $data){
			//	$this->_TEMPLATE->assign('TRA',$data);
			//	$this->_TEMPLATE->parse('main.div_dialogOtroo.option_tratamiento');
			//}
			//$this->_TEMPLATE->assign('DISTRIBUCION',$_POST['distribucion']);
			
			$this->cargarExpedientes($padre);
			
			$this->_TEMPLATE->parse('main.div_verExpediente');
			$CAMBIA['#div_verExpediente'] = $this->_TEMPLATE->text('main.div_verExpediente');
			$OPEN['#div_verExpediente'] = 'open';
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$json['OPEN'] = $OPEN;
			return json_encode($json);			
		}
	




		public function cargarExpedientes($padre){
			
			
			
			//Acá me falta estebecer seguridad para saber que los archivos del expediente padre tengo acceso
			//$bind = array(':padre' => $this->CASO_PADRE);
			$bind = array(':padre' => $padre);
			$cursor = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getExpediente','function',$bind);
			
			$arr_exp = array();
			$num = 0;
			/*if($this->SOLO_VER === FALSE){
				$this->_TEMPLATE->parse($this->PARSER_ANTERIOR.'.subir1');
			}*/
			while($row = $this->_ORA->FetchArray($cursor)){
				//print_r($row);
				$array = array();
				$bind_v = array(':id' => $row['ID_SISTEMA']);
				
				$cursor_variable = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getVariablesFuncion','function',$bind_v);
				while($row_var = $this->_ORA->FetchArray($cursor_variable)){
					$array[] = $row[$row_var['WFA_VARIABLE']];		
				}				
	
	
			
	
			  
				$obj = $this->ejecutarFuncionXml($row['WFA_PACKAGE'],$row['WFA_FUNCION_XML'],$array );	
				
				//var_dump($obj);exit();   
	
			 
				//print_r($obj);
				//Esta retrocediendo tantas veces sea el ultimo nivel	
				$nombre = ($row['WFA_SUBTIPO']=='oficio') ? 'Oficio' : $obj->Nombre;
				$tipo = ($row['WFA_SUBTIPO']=='adjunto_oficio') ? 'Adj. ' : "";
				$ver = ($row['WFA_TIPO']=='sgd') ? "javascript:popupVerArchivo(".$row['WFA_ID_REFERENCIAL'].",'sgd')" : "javascript:popupVerArchivo(".$row['WFA_ID_REFERENCIAL'].",'fel')";
				$tipo_archivo = ($row['WFA_TIPO'] =='sgd') ? 'sgd' : 'exp';
				$sgd =  ($row['WFA_TIPO'] =='sgd') ? $row['WFA_ID_REFERENCIAL'] : '&nbsp;';
				$ver = "javascript:popupVerArchivo('".$row['WFA_ID_DOCUMENTO']."','exp')";
				
				if($num%2==0){
					$color = '#F4F4F4';
				}else{
					$color = '';
				}
				$exp = array('BGCOLOR'=>$color,
					'WFA_ID_REFERENCIAL'=> $row['WFA_ID_REFERENCIAL'],
					'WFA_TIPO'=>$row['WFA_TIPO'],
					'ID_SISTEMA'=>$row['ID_SISTEMA'],
					'ID_DOC'=>$row['WFA_ID_DOCUMENTO'],
					'FECHA'=>$row['WFA_FECHA'],
					'DESCRIPCION'=>$nombre,
					'SGD'=>$sgd,
					'VER'=>$ver);
				
				$exp['VAL'] = substr(md5(md5($exp['ID_DOC'])),3,5);
				
				$sgd = $this->limpiar_sgd($sgd);

				if($sgd){
					//var_dump("EXISTE :: ".$sgd);
					$exp['EXISTE_SGD'] = 'SI';	
				}else{
					//var_dump("NO EXISTE :: ".$sgd);
					$exp['EXISTE_SGD'] = 'NO';
				}
				



				$this->_TEMPLATE->assign('EXP',$exp);
			    //if($this->SOLO_VER === FALSE){
					if($this->esPdf($row)){
						
						$this->_TEMPLATE->parse('main.div_verExpediente.registro_expediente_fecha.subir2');
					}
				//}
				$this->_TEMPLATE->parse('main.div_verExpediente.registro_expediente_fecha');
				$num++;
			
			}
			
			
		}
		
		private function limpiar_sgd($sgd){

			$sgd = trim($sgd);
			if($sgd == '&nbsp;' || $sgd == 'undefined'){
				$sgd = null;
			}
			return $sgd;

		}
	
		private function esPdf($row){
			$bind_v = array(':id' => $row['ID_SISTEMA']);
			$cursor_variable = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getVariablesFuncion','function',$bind_v);
			$array = array();
			while($row_var = $this->_ORA->FetchArray($cursor_variable)){
				$array[] = $row[$row_var['WFA_VARIABLE']];		
			}
			//print_r($array);			
			$obj = $this->ejecutarFuncionXml($row['WFA_PACKAGE'],$row['WFA_FUNCION_XML'],$array );	
			//print_r($obj);
			$NOMBRE = (isset($obj->Nombre)) ? (string)$obj->Nombre : '';
			$NOMBRE_ARCHIVO = (isset($obj->Nombre_Archivo)) ?  (string)$obj->Nombre_Archivo : '';
			$MIME = (isset($obj->Nombre_Archivo)) ?  (string)$obj->Mime : '';
			
			return (strpos(strtolower($NOMBRE), '.pdf') !== FALSE || strpos(strtolower($NOMBRE_ARCHIVO), '.pdf') !== FALSE || strpos(strtolower($MIME), '/pdf') !== FALSE );
		}


		protected function ejecutarFuncionXml($package,$funcion, $variable){
			$cant = count($variable);
			
			$bindPkg = array();
			foreach($variable as $key => $var){				
				$bindPkg[":var$key"] = $var;
			}
			
			if(strtolower($package) == 'wfa_doctos_pkg'){
				$package = 'wfa_doctos_doc2_pkg';
			}
			$xml = $this->_ORA->ejecutaFunc($package.".".$funcion,$bindPkg);
	
			
			try{
				$xml2=$this->htmlentities_entities($xml);
				//$obj = new SimpleXMLElement(utf8_encode($xml2));
				//echo  "<!--".$xml2."-->";
				$xml2 = str_replace('==&sec','==&amp;sec',$xml2);
				//echo  "<!--".$xml2."-->";
				$obj = new SimpleXMLElement($xml2);
			}catch(Exception $e){	
				$obj = NULL;
			}
			return $obj;
			
		}
		
		public function htmlentities_entities($xml) {
			foreach ($this->get_html_translation_table_CP1252(HTML_ENTITIES) as $key => $value) {
					$name = substr($value, 1, strlen($value) - 2);
					switch ($name) {
							// These ones we can skip because they're built into XML
							case 'gt':
							case 'lt':
							case 'quot':
							case 'apos':
							case 'amp': break;
							default: 
							$xml = str_replace($value, $key, $xml);
					}
			}
			return($xml);
		} 
	
	
		public function get_html_translation_table_CP1252($type) {
			$trans = get_html_translation_table($type);
			$trans[chr(130)] = '&sbquo;';    // Single Low-9 Quotation Mark
			$trans[chr(131)] = '&fnof;';    // Latin Small Letter F With Hook
			$trans[chr(132)] = '&bdquo;';    // Double Low-9 Quotation Mark
			$trans[chr(133)] = '&hellip;';    // Horizontal Ellipsis
			$trans[chr(134)] = '&dagger;';    // Dagger
			$trans[chr(135)] = '&Dagger;';    // Double Dagger
			$trans[chr(136)] = '&circ;';    // Modifier Letter Circumflex Accent
			$trans[chr(137)] = '&permil;';    // Per Mille Sign
			$trans[chr(138)] = '&Scaron;';    // Latin Capital Letter S With Caron
			$trans[chr(139)] = '&lsaquo;';    // Single Left-Pointing Angle Quotation Mark
			$trans[chr(140)] = '&OElig;';    // Latin Capital Ligature OE
			$trans[chr(145)] = '&lsquo;';    // Left Single Quotation Mark
			$trans[chr(146)] = '&rsquo;';    // Right Single Quotation Mark
			$trans[chr(147)] = '&ldquo;';    // Left Double Quotation Mark
			$trans[chr(148)] = '&rdquo;';    // Right Double Quotation Mark
			$trans[chr(149)] = '&bull;';    // Bullet
			$trans[chr(150)] = '&ndash;';    // En Dash
			$trans[chr(151)] = '&mdash;';    // Em Dash
			$trans[chr(152)] = '&tilde;';    // Small Tilde
			$trans[chr(153)] = '&trade;';    // Trade Mark Sign
			$trans[chr(154)] = '&scaron;';    // Latin Small Letter S With Caron
			$trans[chr(155)] = '&rsaquo;';    // Single Right-Pointing Angle Quotation Mark
			$trans[chr(156)] = '&oelig;';    // Latin Small Ligature OE
			$trans[chr(159)] = '&Yuml;';    // Latin Capital Letter Y With Diaeresis
			$trans['euro'] = '&euro;';    // euro currency symbol
			ksort($trans);
			return $trans;
		}

		
	}
	

?>