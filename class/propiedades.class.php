<?php

	class Propiedades extends ClaseSistema{
		
		
		public function guardar(){
			try{
				$json = array();
				$json['RESULTADO'] = 'OK';
				$MENSAJES = array();
				$CAMBIA = array();											
				$json_particular = $this->desencriptar($_POST['hidden_idObjeto']);
				$json_particular = json_decode($json_particular);
				$bind = array(
					':p_tabla' => (string)$json_particular->TABLA,
					':p_columna' =>  (string)$json_particular->CAMPO,
					':p_id' => (string)$json_particular->ID,
					':p_valor' => $_POST['input_valorPropiedadObjeto']
				);
				$resultado = $this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA.'RSO_PROPIEDADES_PKG.fun_modificaPropiedadTabla',$bind);
				$this->_ORA->Commit();
				//$MENSAJES[] = print_r($bind,true);
				
				$json['MENSAJES'] =  $MENSAJES;
				$modulo = new Modulo();
				$modulo->setControl($this);			
				$CAMBIA['#div_modulosClasificacion'] = $modulo->generarHTML($this->_SESION->getVariable('CLA_ID'));
				$json['CAMBIA'] = $CAMBIA;
				return json_encode($json);
			}catch(Exception $e){
				print_r($e);
			}
		}
		
		public function agregarAtributo(){
			try{
				$json = array();
				$json['RESULTADO'] = 'OK';
				$MENSAJES = array();
				$CAMBIA = array();														
				$bind = array(
					':p_mod_id' => $_POST['hidden_idModulo'],
					':p_atr_id' => $_POST['select_tipoAtributo'],
					':p_modatr_orden' => $_POST['input_atrOrden'],
					':p_modatr_label' => $_POST['input_atrLabel'],
					':p_modatr_tamano' => $_POST['input_atrTamano'],
					':p_modatr_name' => $_POST['input_atrColumna'],
					':p_modatr_valores' => $_POST['input_atrDatoTipoDato'],
					':p_modatr_tipo_valor' => $_POST['select_tipoDatosAtributo']
				);
				$resultado = $this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA.'RSO_PROPIEDADES_PKG.fun_agregarAtributo',$bind);
				$this->_ORA->Commit();
				$json['MENSAJES'] =  $MENSAJES;
				$modulo = new Modulo();
				$modulo->setControl($this);			
				$CAMBIA['#div_modulosClasificacion'] = $modulo->generarHTML($this->_SESION->getVariable('CLA_ID'));
				$json['CAMBIA'] = $CAMBIA;
				return json_encode($json);
			}catch(Exception $e){
				print_r($e);
			}
		}
		
		public function crearModulo(){
			try{
				$json = array();
				$json['RESULTADO'] = 'OK';
				$MENSAJES = array();
				$CAMBIA = array();														
				$bind = array(					
					':p_clasificacion' => $this->_SESION->getVariable('CLA_ID')
				);
				$resultado = $this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA.'RSO_MODULO_PKG.fun_agregarModulo',$bind);
				$this->_ORA->Commit();
				$json['MENSAJES'] =  $MENSAJES;
				$modulo = new Modulo();
				$modulo->setControl($this);			
				$CAMBIA['#div_modulosClasificacion'] = $modulo->generarHTML($this->_SESION->getVariable('CLA_ID'));
				$json['CAMBIA'] = $CAMBIA;
				return json_encode($json);
			}catch(Exception $e){
				print_r($e);
			}
		}
		
	}

?>