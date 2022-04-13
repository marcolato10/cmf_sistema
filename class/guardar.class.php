<?php

	class Guardar extends ClaseSistema{
		
		public $JSON_WS = array();
		public $ES_VISACION = false;
		
		
		public function anular(){
			$cliente = new clienteWs();
			$cliente->setControl($this);			
			$cliente->SERVICIO = 'anular';
			$this->JSON_WS['RES_ID'] = $this->_SESION->getVariable('RES_ID');
			$this->JSON_WS['USUARIO'] = $this->_SESION->USUARIO;
			$this->JSON_WS['MSG'] = $this->_SESION->getVariable('mensaje_anular');
			$cliente->VARIABLE = array(
					'svsap_resolucion' => json_encode($this->JSON_WS),
					'svsap_usuario' => '',
					'svsap_pass' => '',
					'svsap_aplicacion' => '');
			$resultado =  $cliente->consumir();
			$resultado = json_decode($resultado);
			if((string)$resultado->RESULTADO == 'OK'){
				return 'OK';
			}else{
				return (string)$resultado->MSG;
			}
				
		//	print_r($resultado);
		}
		
		
		public function GuardarResolucion($es_visa = 'N'){			
			$this->ES_VISACION = $es_visa;
			$this->consolidaDatos();
			$resultado = $this->enviarWS();			
			return $resultado;
		}
		
		private function consolidaDatos(){

			
			if($this->ES_VISACION){
				$this->JSON_WS['RES_ENVIANDO'] = $this->_SESION->getVariable('usuario_enviar');	
				$copias = $this->_SESION->getVariable('notificacion_copia');
				if(is_array($copias)){					
					//foreach($copias as $copia_usuario){
					$this->JSON_WS['RES_USUARIOS_COPIA'] =$copias;
					//}					
				}
				$this->JSON_WS['VIS_MSG'] = $this->_SESION->getVariable('comentario_enviar');	
				$this->JSON_WS['RES_GENERA_VERSION'] = 'S';					
				$this->JSON_WS['PRICOM_ID'] = ($this->_SESION->getVariable('privado') == 'S') ? 'unida' : 'publi';	//Privacidad del comentario
				$this->JSON_WS['NOTIFICACION_MAIL'] = $this->_SESION->getVariable('notificacion');
				$this->JSON_WS['NOTIFICACION_INT'] = $this->_SESION->getVariable('notificacion_int');
				//Revisar si se guarda el id del correo enviado.
				
			}else{
				$this->JSON_WS['RES_GENERA_VERSION'] = 'N';	
			}
			
			
			$this->JSON_WS['RES_ID'] = $this->_SESION->getVariable('RES_ID');
			$this->JSON_WS['RES_VERSION'] = $this->_SESION->getVariable('RES_VERSION');
			$this->JSON_WS['PRI_ID'] = $this->_SESION->getVariable('PRI_ID');
			$this->JSON_WS['EST_ID'] = $this->_SESION->getVariable('EST_ID');
			$this->JSON_WS['PUB_ID'] = $this->_SESION->getVariable('PUB_ID');
			$this->JSON_WS['DIS_ID'] = $this->_SESION->getVariable('DIS_ID');
			$this->JSON_WS['CLA_ID'] = $this->_SESION->getVariable('CLA_ID');
			$this->JSON_WS['PLA_ID'] = $this->_SESION->getVariable('PLA_ID');
			$this->JSON_WS['RES_JURISPRUDENCIA'] = $this->_SESION->getVariable('RES_JURISPRUDENCIA');
			$this->JSON_WS['RES_PAGO'] = $this->_SESION->getVariable('RES_PAGO');
			$this->JSON_WS['RES_MULTA'] = $this->_SESION->getVariable('RES_MULTA');
			$this->JSON_WS['RES_REFERENCIA'] = $this->_SESION->getVariable('RES_REFERENCIA');
			$this->JSON_WS['RES_VISTOS'] = $this->_SESION->getVariable('RES_VISTOS');
			$this->JSON_WS['RES_CONSIDERANDO'] = $this->_SESION->getVariable('RES_CONSIDERANDO');
			$this->JSON_WS['RES_RESUELVO'] = $this->_SESION->getVariable('RES_RESUELVO');
			$this->JSON_WS['RES_COMUNIQUESE'] = $this->_SESION->getVariable('RES_COMUNIQUESE');
			$this->JSON_WS['USUARIO'] = $this->_SESION->USUARIO;
			$this->JSON_WS['RES_CASO_PADRE'] = $this->_SESION->getVariable('RES_CASO_PADRE');
			
			
			$FIRMANTES = $this->_SESION->getVariable('FIRMANTES');
			$FIRMANTES_JSON = array();
			if(is_array($FIRMANTES)){
				foreach($FIRMANTES as $fir){
					$fir = explode('|-|',$fir);
					$FIRMANTES_JSON[] = array('USUARIO' => $fir[0],'ROL' => $fir[1]);
				}					
				
			}
			$this->JSON_WS['RSO_FIRMANTES'] = $FIRMANTES_JSON;
			
			
			$destinatarios = $this->_SESION->getVariable('DESTINATARIO');
			//print_r($destinatarios);
			//exit();
			$FISCALIZADO_JSON = array();
			if(is_array($destinatarios)){
				foreach($destinatarios as $grupo){
					foreach($grupo as $fiscalizado){
						$FISCALIZADO_JSON[] = array(
							'DES_ID' => $fiscalizado['DES_ID'],
							'DES_RUT' => $fiscalizado['DES_RUT'],
							'DES_NOMBRE' => $fiscalizado['DES_NOMBRE'],
							'DES_DIRECCION' => $fiscalizado['DES_DIRECCION'],
							'DES_CARGO' => $fiscalizado['DES_CARGO'],
							'DES_TIPO_ENT' => $fiscalizado['DES_TIPO_ENT'],
							'DES_CON_COPIA' => $fiscalizado['DES_CON_COPIA'],
							'TRA_ID' => $fiscalizado['TRA_ID'],
							'DES_CORREO' => $fiscalizado['DES_CORREO'],
							'USUARIOS_SEIL' => $fiscalizado['USUARIOS_SEIL'],
							'DES_MEDIO_ENVIO' => $fiscalizado['DES_MEDIO_ENVIO']
						);					
					}
				}
			}
			//print_r($FISCALIZADO_JSON);
			$this->JSON_WS['DESTINATARIOS'] = $FISCALIZADO_JSON; 
			
			
			
			
			//AcÃ¡ se debe guardar el tipo de envio que no se estÃ¡ guardando
			//AsÃ­ se llama el campo de sesion
			//ENV_ID
			$this->JSON_WS['ENV_ID'] = $this->_SESION->getVariable('ENV_ID');
			//
			
			
			
			
			
			
			//PRIMERO SE DEBEN SACAR LOS MODULOS...
			$clasificacion = '_';
			//$this->_SESION->getVariable('CLA_ID')
			//$modulos = $this->_SESION->getVariable('_CACHE_MODULOS_CLA'.$clasificacion);
			$modulos = $this->_SESION->getVariable('RES_MOD_IDs');
			$JSON_MODULO = array();
			if($modulos !== false){
				foreach($modulos as $mod){
					$id_sesion_modulo = 'MODULO_CLA_ID_'.$clasificacion.'_MOD_ID_'.$mod['MOD_ID'];
					$valores = $this->_SESION->getVariable($id_sesion_modulo);
					$JSON_MODULO[$mod['MOD_ID']] = array('MOD_ID' => $mod['MOD_ID'],'VALORES' => $valores);										
				}
			}
			
			$this->JSON_WS['MODULOS'] = $JSON_MODULO;
			
			
			
			$adjuntos = $this->_SESION->getVariable('RSO_ADJUNTO');
			//print_r($adjuntos);
			$JSON_ADJUNTOS = array();
			if($adjuntos !== false){
				foreach($adjuntos as $adj){	
					$JSON_ADJUNTOS[] = array('ADJ_ID' => $adj['ID'], 'ADJ_HASH' => $adj['ADJ_HASH'],'ADJ_NOMBRE' => $adj['ADJ_NOMBRE'], 'ADJ_SEQ' => $adj['ADJ_SEQ']);
				}
			}
			
			$this->JSON_WS['ADJUNTOS'] = $JSON_ADJUNTOS;
			
			
			
			$usuarios = $this->_SESION->getVariable('PRI_ID_USR');

			$JSON_USUARIOS_PRIV = array();
			if($usuarios !== false){
				if(is_array($usuarios)){
					foreach($usuarios as $usr){	
						$JSON_USUARIOS_PRIV[] = $usr;
					}
				}
			}
			
			$this->JSON_WS['USUARIOS_PRIV'] = $JSON_USUARIOS_PRIV;

			
			//CON LÃ‘A CLASIFICACION YA ESTAN
			/*
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			$clasificacion = $this->_SESION->getVariable('CLA_ID');			
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
			return json_encode($json);*/			


			
			
			
		}
		
		private function enviarWS(){
			$cliente = new clienteWs();
			$cliente->setControl($this);			
			$cliente->SERVICIO = 'guardar';
			//echo json_encode($this->JSON_WS);
			//exit();			
			$cliente->VARIABLE = array(
					'svsap_resolucion' => json_encode($this->JSON_WS),
					'svsap_usuario' => '',
					'svsap_pass' => '',
					'svsap_aplicacion' => '');
			$resultado =  $cliente->consumir();
			$resultado = json_decode($resultado);
			if($this->_SESION->getVariable('RES_ID') === false){
				$this->_SESION->setVariable('RES_ID',$resultado->RES_ID);
				$this->_SESION->setVariable('RES_VERSION','0');
				
			}
			
			//$this->_LOG->log(print_r($resultado->PAR_ADJUNTOS,true));
			if(is_array($resultado->PAR_ADJUNTOS) || is_object($resultado->PAR_ADJUNTOS) ){
				$adjuntos_sesion = $this->_SESION->getVariable('RSO_ADJUNTO');
				foreach($resultado->PAR_ADJUNTOS as $adjunto){
					
					$hash = (string)$adjunto->HASH;
					$seq = (string)$adjunto->SEQ_ID;
					$adjuntos_sesion[$hash]['ADJ_SEQ'] = $seq;
				}
				$this->_SESION->setVariable('RSO_ADJUNTO',$adjuntos_sesion);
			}
			
			if(is_array($resultado->DESTINATARIOS_RETORNO) || is_object($resultado->DESTINATARIOS_RETORNO) ){
				$destinatarios = $this->_SESION->getVariable('DESTINATARIO');
				foreach($resultado->DESTINATARIOS_RETORNO as $des){
					if((string)$des->DES_ANTIGUO != (string)$des->DES_NUEVO ){
						$DES_ID = $destinatarios[(string)$des->DES_TIPO_ENT][(string)$des->DES_RUT]['DES_ID'];
						if($DES_ID != (string)$des->DES_ANTIGUO){
							$this->_LOG->error("Al reestablecer destinatarios despues de guardar no se encuentra la igualdad de los destinatarios");
						}
						$destinatarios[(string)$des->DES_TIPO_ENT][(string)$des->DES_RUT]['DES_ID'] = (string)$des->DES_NUEVO;
					}
				}
				$this->_SESION->setVariable('DESTINATARIO',$destinatarios);
				$this->_LOG->log(print_r($destinatarios,true));
			}else{
				$this->_LOG->log('No hay retorno');
				$this->_LOG->log(print_r($resultado,true));
			}

			
			
			
			
			
			/*if($resultado->ESTADO == 'OK'){
				//Se actualizan los datos que sean necesarios
				$this->_DESTINATARIOS_RETORNOION->fun_cargarBD($resultado->RES_ID);								
				return $resultado->RES_ID;
			}*/
			return $resultado;
		}
	}


?>