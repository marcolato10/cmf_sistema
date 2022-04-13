<?php

	class Modulo extends ClaseSistema{
		
		public $MENSAJE_GRAL = array();
		
		public function isModulo($cla_id){
			$data = $this->getModulos($cla_id);
			if(count($data) > 0)			
				return true;
			return false;
		}
		
		
		
		public function getModulos($cla_id){
			//if($this->_SESION->getVariable('_CACHE_MODULOS_CLA'.$cla_id) === false ){
				$bind = array(':cla_id' => $cla_id);
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_MODULO_PKG.fun_modulosClasificacion','function',$bind);			
				$data = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('_CACHE_MODULOS_CLA'.$cla_id,$data);
		//	}
			return $this->_SESION->getVariable('_CACHE_MODULOS_CLA'.$cla_id);
		}
		
		public function fun_tiposAtributos($tip = NULL){
			if($this->_SESION->getVariable('_CACHE_TIPOS_ATRIBUTOS') === false ){
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_MODULO_PKG.fun_tiposAtributos','function');			
				$data = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('_CACHE_TIPOS_ATRIBUTOS',$data);
			}
			if($tip === NULL){
				return $this->_SESION->getVariable('_CACHE_TIPOS_ATRIBUTOS');
			}else{
				foreach($this->_SESION->getVariable('_CACHE_TIPOS_ATRIBUTOS') as $data){
					if($data['ATR_ID'] == $id){
						return $data;
					}
				}
			}
		}
		
		public function fun_atributos($mod_id){
			$variable_cache = '_CACHE_MODULOS_CLA'.$mod_id;
//			if($this->_SESION->getVariable($variable_cache) === false ){
				$bind = array(':mod_id' => $mod_id);
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_MODULO_PKG.fun_atributos','function',$bind);			
				$data = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable($variable_cache,$data);
	//		}
			return $this->_SESION->getVariable($variable_cache);
		}
		
		public function generarHTML($cla_id){
			
			$MOD_ID_AUX = $this->_SESION->getVariable('MOD_ID_AUX');
			
			$this->MENSAJE_GRAL[] = 'Se inicia generarHTML con cla_id = '.$cla_id;
			if($this->isModulo($cla_id)){
				$modulos = $this->getModulos($cla_id);
				//$this->MENSAJE_GRAL[] = 'Modulos asociados a = '.$cla_id.' son: '.print_r($modulos,true); //se da bien
				
				foreach($modulos as $reg){
					if(!isset($MOD_ID_AUX[$reg['MOD_ID']])){
						$MOD_ID_AUX[$reg['MOD_ID']] = $reg['MOD_ID'];
						$this->MENSAJE_GRAL[] = 'Registro de modulo = '.$reg['MOD_ID'].' es: '.print_r($reg,true);
						$this->generaModuloHTML($reg);
						//Ver si se repite
						if($reg['MOD_REPETIR'] == 'S'){
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.repetir');
						}
						$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion');
					}
				}
				$this->_SESION->setVariable('RES_MOD_IDs',$this->_SESION->getVariable('MOD_ID_AUX'));
				$this->_SESION->setVariable('MOD_ID_AUX',$MOD_ID_AUX);
				return $this->_TEMPLATE->text('main.paso2.modulo_clasificacion');
			}
		}
		
		
		public function cambioAtributoModuloInput(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			//$clasificacion = $this->_SESION->getVariable('CLA_ID');	
			$clasificacion = '_';
			$id_sesion_modulo = 'MODULO_CLA_ID_'.$clasificacion.'_MOD_ID_'.$_POST['MOD_ID'];
			$valores = $this->_SESION->getVariable($id_sesion_modulo);
			if($valores === false){
				$valores = array();
			}
			if(!isset($valores[$_POST['REG_ID']])){
				$valores[$_POST['REG_ID']] = array();
			}			
			$valores[$_POST['REG_ID']][$_POST['ATR_ID']] = $_POST['ATR_VALUE'];
			$MENSAJES[] = $id_sesion_modulo;
			$MENSAJES[] = print_r($valores,true);
			$this->_SESION->setVariable($id_sesion_modulo,$valores);
			//$CAMBIA['#arbol_propiedades'] = $this->CLASIFICACION_OBJ->dibujaPropiedades(); 
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		
		
		
	
		public function cambioAtributoModuloSelect(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			//$clasificacion = $this->_SESION->getVariable('CLA_ID');	
			$clasificacion = '_';
			$id_sesion_modulo = 'MODULO_CLA_ID_'.$clasificacion.'_MOD_ID_'.$_POST['MOD_ID'];
			$valores = $this->_SESION->getVariable($id_sesion_modulo);
			if($valores === false){
				$valores = array();
			}
			if(!isset($valores[$_POST['REG_ID']])){
				$valores[$_POST['REG_ID']] = array();
			}			
			$valores[$_POST['REG_ID']][$_POST['ATR_ID']] = $_POST['ATR_VALUE'];
			$this->_SESION->setVariable($id_sesion_modulo,$valores);
			//$CAMBIA['#arbol_propiedades'] = $this->CLASIFICACION_OBJ->dibujaPropiedades(); 
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		private function generaModuloHTML($reg){
			
			$this->MENSAJE_GRAL[] = 'Ingresa a generaModuloHTML con modulo '.$reg['MOD_ID'];
			$reg['MOD_TITULO_ID'] = strtolower($reg['MOD_TITULO']);
			$reg['MOD_TITULO_ID'] = str_replace(array(' ','á','é','í','ó','ú'),array('','a','e','i','o','u'),$reg['MOD_TITULO_ID']);
			$id = array(
				'TABLA' => 'RSO_MODULO',
				'CAMPO' => 'MOD_TITULO',
				'ID' => $reg['MOD_ID']
			);
					
			$reg['TABLA'] = 'MODULO';
			$id = json_encode($id);
			$reg['ID'] = $this->encriptar($id);									

			$this->_TEMPLATE->assign('MODULO',$reg);
			$this->MENSAJE_GRAL[] = 'Atributos Modulo '.$reg['MOD_ID'].' '.print_r($atrs,true);
			$atrs = $this->fun_atributos($reg['MOD_ID']);
			
			
			//print_r($atrs);
			//$this->_SESION->getVariable('CLA_ID')
			$clasificacion = '_';
			$id_sesion_modulo = 'MODULO_CLA_ID_'.$clasificacion.'_MOD_ID_'.$reg['MOD_ID'];
			//$this->_SESION->setVariable($id_sesion_modulo,array());
			$valores_gral = $this->_SESION->getVariable($id_sesion_modulo);
			if($valores_gral == false){
				$valores_gral = array();
				$valores_gral[0] = array();
			}
			$this->MENSAJE_GRAL[] = 'Los valores a cargar son: '.print_r($valores_gral,true);
			
			
			$primero = true;
			foreach($valores_gral as $key_valores => $valores){
				$this->MENSAJE_GRAL[] = 'Comienza seteo de variables';
				
				
				foreach($atrs as $atr){						
					if(isset($atr['MODATR_TAMANO']) && is_numeric($atr['MODATR_TAMANO'])){
							$atr['TAMANO'] = 'maxlength="'.$atr['MODATR_TAMANO'].'"';
					}												
					
					if(is_array($valores) && isset($valores[$atr['MODATR_NAME']])){
						$this->MENSAJE_GRAL[] = 'Valores:'.print_r($valores,true);
						$atr['VALUE'] = $valores[$atr['MODATR_NAME']];
					}
					
					$id = array(
						'TABLA' => 'RSO_MOD_ATR',
						'CAMPO' => 'MODATR_LABEL',
						'ID' => $atr['MOD_ID'].'_'.$atr['ATR_ID'].'_'.$atr['MODATR_ORDEN'].'_'.$atr['MODATR_NAME']
					);
					$atr['TABLA'] = 'ATRIBUTO';
					$id = json_encode($id);
					$atr['ID'] = $this->encriptar($id);

					$orden = array(
						'TABLA' => 'RSO_MOD_ATR',
						'CAMPO' => 'MODATR_ORDEN',
						'ID' => $atr['MOD_ID'].'_'.$atr['ATR_ID'].'_'.$atr['MODATR_ORDEN'].'_'.$atr['MODATR_NAME']
					);
					$atr['TABLA'] = 'ATRIBUTO';
					$orden = json_encode($orden);
					$atr['ORDEN'] = $this->encriptar($orden);
					
					if($reg['MOD_REPETIR'] == 'S'){
						$atr['REPETIR_ID'] = $reg['MOD_ID'].'_'.$repetir_cantidad;
						$atr['REPETIR_NAME'] = '['.$reg['MOD_ID'].']['.$repetir_cantidad.']';
						
					}
					$atr['NUM_REG'] = $key_valores;
					$this->_TEMPLATE->assign('ATR',$atr);
					
					if($atr['ATR_TIPO'] == 'input'){
						$this->MENSAJE_GRAL[] = 'Es Input con:'.print_r($atr,true);
						if($atr['MODATR_REQUIRED'] == 'S'){
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_input.required');
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_input.required_mensaje');
						}						
						$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_input');
					}
					
					if($atr['ATR_TIPO'] == 'fecha'){
						$this->MENSAJE_GRAL[] = 'Es Fecha';
						if($atr['MODATR_REQUIRED'] == 'S'){
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_fecha.required');
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_fecha.required_mensaje');
						}						
						$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_fecha');
					}
					
					if($atr['ATR_TIPO'] == 'numerico'){
						$this->MENSAJE_GRAL[] = 'Es numerico';
						if($atr['MODATR_REQUIRED'] == 'S'){
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_input_numero.required');
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_input_numero.required_mensaje');
						}									
						$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_input_numero');
					}
					
					if($atr['ATR_TIPO'] == 'select'){
						$this->MENSAJE_GRAL[] = 'Es Select';
						$datos = array();														
						if($atr['MODATR_TIPO_VALOR'] == 'estatico'){
							$this->MENSAJE_GRAL[] = 'Es estatico';
							$json_valores = $atr['MODATR_VALORES'];
							$json_valores = json_decode ($json_valores);
							foreach($json_valores as $key => $val){
								//$ATR_TPL = array('KEY' => $key,'VAL' => $val);
								$datos[] = array('ID' => $key, 'VALOR' => $val);
							}
						}
						
						if($atr['MODATR_TIPO_VALOR'] == 'funcion'){
							$this->MENSAJE_GRAL[] = 'Es funcion';
							$funcion = $atr['MODATR_VALORES'];
							$cursor = $this->_ORA->retornaCursor($funcion,'function');
							while($data = $this->_ORA->FetchArray($cursor)){
								$datos[] = $data;
							}
						}
						
						
						foreach($datos as $key => $val){
							$ATR_TPL = $val;//array('ID' => $val[''],'VALOR' => $val);	
							$clasificacion = '_';
							//$this->_SESION->getVariable('CLA_ID');
							$id_sesion_modulo = 'MODULO_CLA_ID_'.$clasificacion.'_MOD_ID_'.$reg['MOD_ID'];
							$valores_select = $this->_SESION->getVariable($id_sesion_modulo);
							if(is_array($valores_select) && isset($valores_select[$key_valores][$atr['MODATR_NAME']]) && $valores_select[$key_valores][$atr['MODATR_NAME']] == $val['ID']){									
								$ATR_TPL['SELECTED'] = 'selected';
							}	
							$this->_TEMPLATE->assign('ATR_OP',$ATR_TPL);
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_select.option_atr');
						}
						$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.atr_select');
					}					
					$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo');
				}
				
				
				
				
				if($primero){
					$primero = false;
				}else{
					$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.eliminar_fila');
					$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo');
				}
				
				$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo.hr');
				$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atributo');
				
			}
			$this->MENSAJE_GRAL[] = 'Fin funcion';
			return $this->_TEMPLATE->text('main.paso2.modulo_clasificacion.atributo');
		}
		
		public function eliminarOtroRegistro(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();						
			
			//$modulos = $this->getModulos($this->_SESION->getVariable('CLA_ID'));
			$modulos = array();
			
			if(is_array($this->_SESION->getVariable('CLA_ID'))){
				foreach($this->_SESION->getVariable('CLA_ID') as $cla_id_aux){		
					$modulos_aux = $this->getModulos($cla_id_aux);
					$modulos = array_merge($modulos, $modulos_aux);
				}
			}
			
			
			
			foreach($modulos as $reg){
				if($reg['MOD_ID'] == $_POST['MOD_ID'])
					break;
			}
			//$this->_SESION->getVariable('CLA_ID')
			$clasificacion = '_';
			$id_sesion_modulo = 'MODULO_CLA_ID_'.$clasificacion.'_MOD_ID_'.$reg['MOD_ID'];
			$valores_gral = $this->_SESION->getVariable($id_sesion_modulo);
			$MENSAJES[] = print_r($valores_gral,true);
			$MENSAJES[] = "Eliminando :".$_POST['REG_ID'];
			unset($valores_gral[$_POST['REG_ID']]);															
			
			$this->_SESION->setVariable($id_sesion_modulo,$valores_gral);
			
			//Hasta acá se pudieron grabar las variables globales grales.
			
			
			$MOD_ID_AUX = array();
			foreach($modulos as $reg){
				if(!isset($MOD_ID_AUX[$reg['MOD_ID']])){
					//print_r($reg);
					$MOD_ID_AUX[$reg['MOD_ID']] = $reg['MOD_ID'];
					$this->MENSAJE_GRAL[] = 'Registro de modulo = '.$reg['MOD_ID'].' es: '.print_r($reg,true);
					$HTML = $this->generaModuloHTML($reg);
						//Ver si se repite
					if($reg['MOD_REPETIR'] == 'S'){
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.repetir');
					}
					$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion');
					
				}
			}
			
			$HTML = $this->_TEMPLATE->text('main.paso2.modulo_clasificacion');
			
			//$HTML = $this->generaModuloHTML($reg);
			//echo $HTML;
			//exit();
			$CAMBIA['#div_modulosClasificacion'] = $HTML;
			
			
			
			//$HTML = $this->generaModuloHTML($reg);
			//$CAMBIA['#div_atributosModulo'] = $HTML;
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		
		public function agregarOtroRegistro(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			
			$modulos = array();
			if(is_array($this->_SESION->getVariable('CLA_ID'))){
				foreach($this->_SESION->getVariable('CLA_ID') as $cla_id_aux){		
					$modulos_aux = $this->getModulos($cla_id_aux);
					$modulos = array_merge($modulos, $modulos_aux);
				}
			}
			
			
			$MENSAJES[] = print_r($modulos,true);
			
			
			
			
			//print_r($modulos);
			//exit();
			//$modulos = $this->getModulos($this->_SESION->getVariable('CLA_ID'));
			foreach($modulos as $reg){
				if($reg['MOD_ID'] == $_POST['MOD_ID']){
					//echo $_POST['MOD_ID']." ".print_r($reg,true);
					break;
				}
			}
			$MENSAJES[] = print_r($reg,true);
			
			
			//$this->_SESION->getVariable('CLA_ID');
			$clasificacion = '_';
			$id_sesion_modulo = 'MODULO_CLA_ID_'.$clasificacion.'_MOD_ID_'.$reg['MOD_ID'];
			$valores_gral = $this->_SESION->getVariable($id_sesion_modulo);
			
			if($valores_gral == false){
				$valores_gral = array();
				$valores_gral[0] = array();
				$valores_gral[] = array();
				$this->_SESION->setVariable($id_sesion_modulo,$valores_gral);
			}else{
				$MENSAJES[] = print_r($valores_gral,true);
				$valores_gral[] = array();
				$this->_SESION->setVariable($id_sesion_modulo,$valores_gral);
			}
			//Hasta acá se pudieron grabar las variables globales grales.
			
			
			
			$MOD_ID_AUX = array();
			foreach($modulos as $reg){
				if(!isset($MOD_ID_AUX[$reg['MOD_ID']])){
					//print_r($reg);
					$MOD_ID_AUX[$reg['MOD_ID']] = $reg['MOD_ID'];
					$this->MENSAJE_GRAL[] = 'Registro de modulo = '.$reg['MOD_ID'].' es: '.print_r($reg,true);
					$HTML = $this->generaModuloHTML($reg);
						//Ver si se repite
					if($reg['MOD_REPETIR'] == 'S'){
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.repetir');
					}
					$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion');
					
				}
			}
			
			$HTML = $this->_TEMPLATE->text('main.paso2.modulo_clasificacion');
			
			//$HTML = $this->generaModuloHTML($reg);
			//echo $HTML;
			//exit();
			$CAMBIA['#div_modulosClasificacion'] = $HTML;
			
			$json['CAMBIA'] = $CAMBIA;
			$json['MENSAJES'] =  $MENSAJES;
			return json_encode($json);
		}
		
		
		
		/*
		$xml = new SimpleXMLElement('<root/>');
array_walk_recursive($test_array, array ($xml, 'addChild'));
print $xml->asXML();*/
		
		

		
	}

?>