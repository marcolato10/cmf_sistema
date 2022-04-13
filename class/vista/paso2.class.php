<?php

	class Paso2 extends ClaseSistema{
		
		//Esto es como si reemplazara el main
		static $DEFAULT_PASO1_TIPO_RESOLUCION = 'exenta';
		public $MENSAJE_GRAL = array();
		
		
		
		public function generarHTML(){
			
			$data = $this->getTipoEnvio();
			foreach($data as $registro){
				$registro['CHECKED'] = '';
				if(is_array($this->_RESOLUCION->ENV_ID)){
					foreach($this->_RESOLUCION->ENV_ID as $checkeado){
						if($checkeado['ENV_ID'] == $registro['ENV_ID']){
							$registro['CHECKED'] = 'checked';
						}
					}
				}
			
				$this->_TEMPLATE->assign('SELECTED_JUR_'.$this->_RESOLUCION->RES_JURISPRUDENCIA,'selected');
				$this->_TEMPLATE->assign('TIPENV',$registro);
				$this->_TEMPLATE->parse('main.paso2.div_tipoEnvio.select_tipoEnvio.option_tipoEnvio');
			}
			
			$this->_TEMPLATE->parse('main.paso2.div_tipoEnvio.select_tipoEnvio');
			$this->_TEMPLATE->parse('main.paso2.div_tipoEnvio');
			
			/*$this->_RESOLUCION->CLASIFICACION_OBJ->getHTMLPropiedades();
			if($this->_RESOLUCION->CLASIFICACION_OBJ->PRI_ID == 'reser'){
				$this->_RESOLUCION->CLASIFICACION_OBJ->dibujaUsrPrivacidad();
				$this->_RESOLUCION->CLASIFICACION_OBJ->PRI_NOMBRE = 'Reservado';
			}

			
			
			*/
			
			$this->_SESION->setVariable('MOD_ID_AUX',array());
			if(is_array($this->_RESOLUCION->CLA_ID)){
				foreach($this->_RESOLUCION->CLA_ID as $cla_id_aux){		
					$this->_RESOLUCION->MODULO_OBJ->generarHTML($cla_id_aux);
				}
			}
			$this->_SESION->setVariable('RES_MOD_IDs',$this->_SESION->getVariable('MOD_ID_AUX'));
			$this->_SESION->setVariable('MOD_ID_AUX',array());
			
			//
			//echo 'ARBPRO_ID: '.print_r($this->_SESION->getVariable('FIRMANTES'),true);
			//exit();
			
			//ARBPRO_FIRMA_MULT
			if(is_numeric($this->_SESION->getVariable('ARBPRO_ID'))){
				$DATOS_ARBOL = $this->_RESOLUCION->CLASIFICACION_OBJ->fun_getArbolPropiedades($this->_SESION->getVariable('ARBPRO_ID'));
				if($DATOS_ARBOL['ARBPRO_FIRMA_MULT'] == 'SI'){
					$USUARIOS = $this->_ORA->Select("SELECT ep_usuario, TRIM(EP_ALIAS) || ' ' || TRIM(EP_APE_PAT) || ' ' || TRIM(EP_APE_MAT) NOMBRE FROM EP_FUN_TODOS WHERE EP_VIGENTE = 'S' ORDER BY EP_ALIAS");
					

					
					while($D_USUARIOS = $this->_ORA->fetchArray($USUARIOS)){
						
						$bind_u = array(':usuario' => $D_USUARIOS['EP_USUARIO'] , ':dis'=> 2);
						$ROLES = $this->_ORA->retornaCursor('FED.FEL_ROL_FIRMA_PKG.get_rol_para_firmar','procedure',$bind_u);
						//$cantidad = 0;
						while($data_roles = $this->_ORA->FetchArray($ROLES) ){
							//print_r($data_roles);
							//$cantidad++;
							//$this->_TEMPLATE->assign('EP_USUARIO', $D_USUARIOS['EP_USUARIO']);
							$this->_TEMPLATE->assign('EP_USUARIO', $D_USUARIOS['EP_USUARIO'].'|-|'.$data_roles['FEL_ROL_COD_DESCRIP']);
							$this->_TEMPLATE->assign('NOMBRES', $D_USUARIOS['NOMBRE'].' ('.$data_roles['FEL_DESCRIPCION'].')');
							$this->_TEMPLATE->parse('main.paso2.firma_multiple.option_firmante_seleccionar');
							
						}
						
						
						//if($cantidad > 0){
							
						//}
					}
					//$this->_TEMPLATE->parse('main.paso2.firma_multiple');
					//<!-- BEGIN: option_firmante_seleccionar --><option value="{EP_USUARIO}">{NOMBRES}</option><!-- END: option_firmante_seleccionar -->
					
					
					
					$FIRMANTES_AUX = $this->_SESION->getVariable('FIRMANTES');
					$FIRMANTES_AUX = (is_array($FIRMANTES_AUX)) ? $FIRMANTES_AUX : array();
					
					$ORDEN = 1;
					
					foreach($FIRMANTES_AUX as $fir_aux){
						$fir_aux = explode('|-|',$fir_aux);
						//print_r($fir_aux);
						//exit();
						$USUARIOS = $this->_ORA->Select("SELECT ep_usuario, TRIM(EP_ALIAS) || ' ' || TRIM(EP_APE_PAT) || ' ' || TRIM(EP_APE_MAT) NOMBRE FROM EP_FUN_TODOS WHERE EP_VIGENTE = 'S' AND EP_USUARIO = '".$fir_aux[0]."'"); //cuidado inj
						$NOMBRES = '';
	
						
						while($D_USUARIOS = $this->_ORA->fetchArray($USUARIOS)){
							
							//--
							$bind_u = array(':usuario' => $D_USUARIOS['EP_USUARIO'] , ':dis'=> 2);
							$ROLES = $this->_ORA->retornaCursor('FED.FEL_ROL_FIRMA_PKG.get_rol_para_firmar','procedure',$bind_u);
							//$cantidad = 0;
							while($data_roles = $this->_ORA->FetchArray($ROLES) ){
								if($data_roles['FEL_ROL_COD_DESCRIP'] == $fir_aux[1]){
									$NOMBRES = $D_USUARIOS['NOMBRE'].' ('.$data_roles['FEL_DESCRIPCION'].')';
								}
								
							}
							
							//--
							
							
							
							
							//$NOMBRES =  $D_USUARIOS['NOMBRE'];
						}
						
						
						
						
						
						
						
				
						$this->_TEMPLATE->assign('FIRMANTE',$NOMBRES);
						$this->_TEMPLATE->assign('ORDEN_FIRMANTE',$fir_aux[0]);
						
						$this->_TEMPLATE->assign('EP_USUARIO',$fir_aux[0].'|-|'.$fir_aux[1]);
						$this->_TEMPLATE->parse('main.paso2.firma_multiple.div_listadoFirmantes.firmante');
						$ORDEN++;
					}
					
					$this->_TEMPLATE->parse('main.paso2.firma_multiple.div_listadoFirmantes');
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					$this->_TEMPLATE->parse('main.paso2.firma_multiple');
					
					
				}
			}
		
			//print_r($_SESSION);
			
			
			
			
			//print_r($this->_RESOLUCION->MODULO_OBJ->MENSAJE_GRAL);
			
			/*$MODULOS_IND = array();
			foreach($this->_RESOLUCION->CLA_ID as $cla_id_aux){
				$modulo = new Modulo();
				$modulo->setControl($this);
				if($modulo->isModulo($cla_id_aux)){
					$modulos = $modulo->getModulos($cla_id_aux);		
					foreach($modulos as $reg){
						if(!isset($MODULOS_IND[$reg['MOD_ID']])){
							$MODULOS_IND[$reg['MOD_ID']] = $reg['MOD_ID'];
							$reg['MOD_TITULO_ID'] = strtolower($reg['MOD_TITULO']);
							$reg['MOD_TITULO_ID'] = str_replace(array(' ','á','é','í','ó','ú'),array('','a','e','i','o','u'),$reg['MOD_TITULO_ID']);
							$this->_TEMPLATE->assign('MODULO',$reg);
							$atrs = $modulo->fun_atributos($reg['MOD_ID']);
							//print_r($atrs);
							foreach($atrs as $atr){
									if(isset($atr['MODATR_TAMANO']) && is_numeric($atr['MODATR_TAMANO'])){
											$atr['TAMANO'] = 'maxlength="'.$atr['MODATR_TAMANO'].'"';
									}
									$this->_TEMPLATE->assign('ATR',$atr);	
									if($atr['ATR_TIPO'] == 'input'){
										
										$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion.atr_input');
									}
									
									
							}
							$this->_TEMPLATE->parse('main.paso2.modulo_clasificacion');
						}
					}
				}
			}
			*/	
			
//			 <!-- BEGIN: option_tipoAtributo --><option value="{ATR.ATR_ID}" >{ATR.ATR_DESCRIPCION}</option><!-- END: option_tipoAtributo -->

			/*
			$modulo = new Modulo();
			$modulo->setControl($this);
			$atrs = $modulo->fun_atributos();
			print_r($atrs);

			*/


			$data = $this->_RESOLUCION->MODULO_OBJ->fun_tiposAtributos();
			foreach($data as $reg){
				$this->_TEMPLATE->assign('ATR',$reg);
				$this->_TEMPLATE->parse('main.paso2.option_tipoAtributo');	
			}
			
			
			/*
			
			if($this->PRI_ID == 'reser'){
				$this->_MENSAJES_GRAL[] = 'Es reservado se muestra las personas en privacidad ';
				$this->_CAMBIA_GRAL["#div_AutorizadosRevisar"] = $this->dibujaUsrPrivacidad();	
			}else{
				$this->_MENSAJES_GRAL[] = 'No es reservado, no se hace nada';
			}
			*/
			
			if($this->_SESION->getVariable('PRI_ID') == 'reser'){
				$this->_RESOLUCION->CLASIFICACION_OBJ->dibujaUsrPrivacidad();
			}
			
			
			$this->_TEMPLATE->parse('main.paso2');
			
			//print_r($this->_SESION->getVariable('ENV_ID'));
		}
		
		
		
		public function getTipoEnvio($tipo = NULL){
			if($this->_SESION->getVariable('_CACHE_TIPOS_ENVIO') === false ){
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_PROPIEDADES_PKG.fun_getTipoEnvio','function');			
				$data = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('_CACHE_TIPOS_ENVIO',$data);
			}
			if($id === NULL){
				return $this->_SESION->getVariable('_CACHE_TIPOS_ENVIO');
			}else{
				foreach($this->_SESION->getVariable('_CACHE_TIPOS_ENVIO') as $data){
					if($data['ENV_ID'] == $id){
						return $data;
					}
				}
			}
		}
	}

?>