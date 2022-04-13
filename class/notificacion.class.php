<?php

	require_once("class/correo.class.php");
	
	class Notificacion extends ClaseSistema{
		
		public $_ARCHIVO_FIRMADO;

		public function setPdf($FIRMADO){
			$this->_ARCHIVO_FIRMADO = $FIRMADO;
		}
		
		public function setBus($bus){
			$this->_BUS_FIRMA = $bus;
		}


		public function notificacionResolucion(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			$OPEN = array();
			$MENSAJES[] = 'Inicio de las notificaciones de Rersoluciones Internas';
			$CAMBIA['#div_notificacion'] = $this->getNotificaciones();

			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;

			$OPEN['#div_notificacion'] = 'open';
			$json['OPEN'] = $OPEN;
			return json_encode($json);
		}
		
		public function setNotificaNoPago($redactor, $usr_notifica){
			$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_no_pago.html');
			$correo = new Correo();
			$correo->ORA = $this->_ORA;
			$correo->APLIC = 'PURSO';
			$correo->FIRMADO = false;
			$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';


			//$bind = array(':redactor' => $autorizador);
			//$NOMBRE_USUARIO_AUTORIZADOR = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
			//$plantilla_interna->assign('NOMBRE_AUTORIZADOR',$NOMBRE_USUARIO_AUTORIZADOR);
			$plantilla_interna->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));
			$plantilla_interna->parse('asunto');
			$correo->ASUNTO = $plantilla_interna->text('asunto');
			$bind = array(':usuario' => $redactor);		
			$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
			$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
			
			if($this->_AMBIENTE == 'DESA' /*|| $this->_AMBIENTE == 'TEST'*/){
					
				$plantilla_interna->parse('texto.debug');
				$correo->setPara('culloa@svs.cl');
			}else{
				$correo->setPara($CORREO_DESTINO);
			}
			
			$plantilla_interna->parse('texto');
			$correo->TEXTO =  $plantilla_interna->text('texto');
			$correo->enviar();	
				
		}
		
		
		public function setNotificacionFirmaDocumentos($usuario, $res_id, $numero_resolucion){
			$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_firma_certificado.html');
			$correo = new Correo();
			$correo->ORA = $this->_ORA;
			$correo->APLIC = 'PURSO';
			$correo->FIRMADO = false;
			$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';


			//$bind = array(':redactor' => $autorizador);
			//$NOMBRE_USUARIO_AUTORIZADOR = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
			//$plantilla_interna->assign('NOMBRE_AUTORIZADOR',$NOMBRE_USUARIO_AUTORIZADOR);
			$plantilla_interna->assign('RES_ID',$res_id);
			$plantilla_interna->assign('RES_NUMERO_RESOLUCION',$numero_resolucion);
			$plantilla_interna->parse('asunto');
			$correo->ASUNTO = $plantilla_interna->text('asunto');
			$bind = array(':usuario' => $usuario);		
			$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
			$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
		
			if($this->_AMBIENTE == 'DESA' /*|| $this->_AMBIENTE == 'TEST'*/){
					
				$plantilla_interna->parse('texto.debug');
				$correo->setPara('culloa@svs.cl');
			}else{
				$correo->setPara($CORREO_DESTINO);
			}
			
			$plantilla_interna->parse('texto');
			$correo->TEXTO =  $plantilla_interna->text('texto');
			$correo->enviar();
		}
		
		public function setNotificaSiPago($firmante, $usr_notifica){
			$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_si_pago.html');
			$correo = new Correo();
			$correo->ORA = $this->_ORA;
			$correo->APLIC = 'PURSO';
			$correo->FIRMADO = false;
			$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';


			//$bind = array(':redactor' => $autorizador);
			//$NOMBRE_USUARIO_AUTORIZADOR = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
			//$plantilla_interna->assign('NOMBRE_AUTORIZADOR',$NOMBRE_USUARIO_AUTORIZADOR);
			$plantilla_interna->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));
			$plantilla_interna->parse('asunto');
			$correo->ASUNTO = $plantilla_interna->text('asunto');
			$bind = array(':usuario' => $firmante);		
			$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
			$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
			
			if($this->_AMBIENTE == 'DESA'  /*|| $this->_AMBIENTE == 'TEST'*/){
					
				$plantilla_interna->parse('texto.debug');
				$correo->setPara('culloa@svs.cl');
			}else{
				$correo->setPara($CORREO_DESTINO);
			}
			
			$plantilla_interna->parse('texto');
			$correo->TEXTO =  $plantilla_interna->text('texto');
			$correo->enviar();	
			
		}
		
		
		public function setNotificaSiPagoCaso($firmante, $usr_notifica, $caso, $ora, $ambiente){
			$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_si_pago.html');
			$correo = new Correo();
			$correo->ORA = $ora;
			$correo->APLIC = 'PURSO';
			$correo->FIRMADO = false;
			$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';


			//$bind = array(':redactor' => $autorizador);
			//$NOMBRE_USUARIO_AUTORIZADOR = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
			//$plantilla_interna->assign('NOMBRE_AUTORIZADOR',$NOMBRE_USUARIO_AUTORIZADOR);
			$plantilla_interna->assign('RES_ID',$caso);
			$plantilla_interna->parse('asunto');
			$correo->ASUNTO = $plantilla_interna->text('asunto');
			$bind = array(':usuario' => $firmante);		
			$CORREO_DESTINO = $ora->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
			$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
			
			if($ambiente == 'DESA' /*|| $ambiente == 'TEST'*/){
					
				$plantilla_interna->parse('texto.debug');
				$correo->setPara('culloa@svs.cl');
			}else{
				$correo->setPara($CORREO_DESTINO);
			}
			$plantilla_interna->parse('texto.firmante');
			$plantilla_interna->parse('texto');
			$correo->TEXTO =  $plantilla_interna->text('texto');
			$correo->enviar();	
				
		}
		
		
		public function enviarNotificacionInternaPago($caso, $ora, $ambiente, $firmante){															
			$bind = array(':p_res_id' => $caso);
			$ultima_version = $ora->ejecutaFunc('rso.RSO_OBTENER_PKG.fun_getUltimaVersion',$bind);
			
			
			$bind = array(':p_rol' => 'NOTIF_SG');
			$cursor = $ora->retornaCursor('wfa.wf_rso_pkg.getRol','function',$bind);
			$data = $ora->FetchAll($cursor);
			$UNIDAD = $data[0]['WFA_UNIDAD'];
			
			
			
			$bind = array(':p_unidad' => $UNIDAD);
			$cursor = $ora->retornaCursor('wfa.wfa_usr.getNombresUsrsUnidad','function',$bind);
			$USUARIOS_SG = array();
			while($data = $ora->FetchArray($cursor)){
				$USUARIOS_SG[] = $data['EP_USUARIO'];
			}
			
			
			
			
			
																				
			$bind = array(':p_res_id' => $caso);
			$cursor = $ora->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_getNotificacionInterna','function',$bind);
			$participantes = $ora->FetchAll($cursor);

			$bind = array(':p_res_id' => $caso, ':p_version' => $ultima_version);				
			$cursor = $ora->retornaCursor('RSO.RSO_OBTENER_PKG.fun_getEncargadosUnidad','function',$bind);
			$encargados_unidad = $ora->FetchAll($cursor);			
			//print_r($participantes);			
			//print_r($encargados_unidad);
			$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_si_pago.html');
			
			
			/*$bind = array(':p_res_id' => $caso, ':p_res_version' => $ultima_version);
			$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_getResolucion','function',$bind);
			$data_res = $this->_ORA->FetchArray($cursor);
			
			$TIPO = ($data_res['TIPRES_ID'] == 'exenta') ? 'Exenta' : 'Afecta';*/
			$plantilla_interna->assign('RES_ID',$caso);
			$plantilla_interna->parse('asunto');
			
			
			$enviados = 0;
			if(is_array($participantes) || is_array($encargados_unidad) || is_array($USUARIOS_SG)){
				$cant_participantes =  (is_array($participantes)) ? count($participantes) : 0;
				$cant_encargados_unidad =  (is_array($encargados_unidad)) ? count($encargados_unidad) : 0;
				$cant_sg =  (is_array($USUARIOS_SG)) ? count($USUARIOS_SG) : 0;
			

				if(($cant_participantes + $cant_encargados_unidad + $cant_sg) > 0){
					$correo = new Correo();
					$correo->ORA = $ora;
					$correo->APLIC = 'PURSO';
					$correo->FIRMADO = false;
					$correo->ASUNTO = $plantilla_interna->text('asunto');
				}

				if($cant_participantes > 0){
					foreach($participantes as $participante){
						$cantidad_participantes++;

						if($participante['NOTINT_VIGENTE'] == 'S' && $firmante != $participante['NOTINT_USUARIO']){

							$reservados[$participante['NOTINT_USUARIO']] = 1;
				


							$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
							$bind = array(":usuario" => $participante['NOTINT_USUARIO']);
							$CORREO_DESTINO = $ora->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);

							if($ambiente == 'DESA' /*|| $ambiente == 'TEST'*/){
								$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
								$plantilla_interna->parse('texto.debug');
								$correo->setPara('culloa@svs.cl');
							}else{
								$correo->setPara($CORREO_DESTINO);
							}
							$enviados++;
						}
					}
				}
								
				if($cant_encargados_unidad > 0){
					foreach($encargados_unidad as $encargado_unidad){
						$cantidad_participantes++;

						if($encargado_unidad['CHECKED'] == 'S' && $encargado_unidad['NOTINT_USUARIO'] != $firmante){

							$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
							$CORREO_DESTINO = $encargado_unidad['PARUNI_VALOR'];

							if($ambiente == 'DESA' /*|| $ambiente == 'TEST'*/){
								$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
								$plantilla_interna->parse('texto.debug');
								$correo->setPara('culloa@svs.cl');
							}else{
								$correo->setPara($CORREO_DESTINO);
							}
							$enviados++;

						}
					}
				}
				
				
				if($cant_sg > 0){
					foreach($USUARIOS_SG as $usuario_sg){
						$cantidad_participantes++;
						$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
						$bind = array(':usuario' => $usuario_sg);
						$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind); 
						if($ambiente == 'DESA' || $ambiente == 'TEST'){
							$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
							$plantilla_interna->parse('texto.debug');
							$correo->setPara('culloa@svs.cl');
						}else{
							$correo->setPara($CORREO_DESTINO);
						}
						$enviados++;
					}
				}
				
				
				
				if(($cant_participantes + $cant_encargados_unidad + $cant_sg) > 0 && $enviados > 0){
					$plantilla_interna->parse('texto');
					$correo->TEXTO = $plantilla_interna->text('texto');
					$ID_CORREO = $correo->enviar();
					$bind = array(":res_id" => $caso, ":p_id_correo" => $correo->ID_CORREO);
					$CORREO_DESTINO = $ora->ejecutaFunc("rso.RSO_GUARDAR_PKG.fun_guardaIdCorreoNotificacion",$bind);
				}
			}
				
			
			
			//preguntar si la resolucion es privada y por mano para enviar correo de js
			/*
			if($this->_SESION->getVariable('PRI_ID') == 'reser' && $this->fun_esNoElectronico()){

				//se debe obtener el redactor.



				$REDACTOR = $this->_SESION->getVariable('RES_REDACTOR');

				$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_reserv_mano.html');
				$not_reser = new Correo();
				$not_reser->ORA = $this->_ORA;
				$not_reser->APLIC = 'PURSO';
				$not_reser->FIRMADO = false;
				$not_reser->DESDE_NOMBRE = 'Resoluciones Electronicas';
				$bind = array(':redactor' => $REDACTOR);
				$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
				$NOMBRE_USUARIO = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);

				$plantilla_interna->assign('NOMBRE_DESTINO',$NOMBRE_USUARIO);
				$plantilla_interna->assign('NUMERO_RESOLUCION',$this->_SESION->getVariable('NUMERO_RESOLUCION'));
				$plantilla_interna->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));

				$plantilla_interna->parse('asunto');
				$not_reser->ASUNTO = $plantilla_interna->text('asunto');
				$plantilla_interna->parse('texto');
				$not_reser->TEXTO =  $plantilla_interna->text('texto');

				if($this->_AMBIENTE == 'DESA'){
					$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
					$plantilla_interna->parse('texto.debug');
					$not_reser->setPara('culloa@svs.cl');
				}else{
					$not_reser->setPara($CORREO_DESTINO);
				}
				$not_reser->enviar();
			}*/
		
			
		
		}
		
		
		
		
		public function enviarComprobantePago($CASO, $ORA, $RUT_PAGA, $PRODUCTO, $ID_TRANSACCION, $MONTO,$CORREOS,$LOG){
			
			//Para enviar algun comprobante en el caso de que se requiera
			 $LOG->log(__METHOD__ . "(".__LINE__.") :Inicio de la funcion ");
			$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_destinatario.html');
			$correo = new Correo();
			$correo->ORA = $ORA;
			$correo->APLIC = 'PURSO';
			$correo->FIRMADO = false;
			$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
			$plantilla_interna->assign('NUMERO_CASO',$CASO);
			$plantilla_interna->parse('comprobante_pago.asunto');
			$correo->ASUNTO = $plantilla_interna->text('comprobante_pago.asunto');
			$LOG->log(__METHOD__ . "(".__LINE__.") :Asunto ".$correo->ASUNTO);
			
			$plantilla_interna->assign('RUT_PAGA',$RUT_PAGA);
			$plantilla_interna->assign('PRODUCTO',$PRODUCTO);
			$plantilla_interna->assign('ID_TRANSACCION',$ID_TRANSACCION);
			$plantilla_interna->assign('MONTO',$MONTO);
			
			
			$plantilla_interna->parse('comprobante_pago.texto');
			$correo->TEXTO =  $plantilla_interna->text('comprobante_pago.texto');
			
			$LOG->log(__METHOD__ . "(".__LINE__.") :Texto ".$correo->TEXTO);
			
			//revisar los para
			
			$LOG->log(__METHOD__ . "(".__LINE__.") :Correos ".$CORREOS);
			$CORREOS = explode(',',$CORREOS);
			$LOG->log(__METHOD__ . "(".__LINE__.") :Correos explode".print_r($CORREOS,true));
			foreach($CORREOS as $CORREO_DESTINO){
				$correo->setPara(trim($CORREO_DESTINO));
			}
			
			
			$correo->enviar();
			




			/*$bind = array(':redactor' => $autorizador);
			$NOMBRE_USUARIO_AUTORIZADOR = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
			$plantilla_interna->assign('NOMBRE_AUTORIZADOR',$NOMBRE_USUARIO_AUTORIZADOR);
			$plantilla_interna->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));*/
			
			
			
		}
		
		
		public function setNotificarEsperaPago($usuarios, $autorizador){

			$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_res_pago.html');
			$correo = new Correo();
			$correo->ORA = $this->_ORA;
			$correo->APLIC = 'PURSO';
			$correo->FIRMADO = false;
			$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
	

			$bind = array(':redactor' => $autorizador);
			$NOMBRE_USUARIO_AUTORIZADOR = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
			$plantilla_interna->assign('NOMBRE_AUTORIZADOR',$NOMBRE_USUARIO_AUTORIZADOR);
			$plantilla_interna->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));
			$plantilla_interna->parse('asunto');
			$correo->ASUNTO = $plantilla_interna->text('asunto');
			
			
			
			foreach($usuarios as $usr){
				$bind = array(':usuario' => $usr);
				$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
				if($this->_AMBIENTE == 'DESA' || $this->_AMBIENTE == 'TEST'){ //como es para sec general comento el correo				
					$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
					$plantilla_interna->parse('texto.debug');
					$correo->setPara('culloa@svs.cl');
				}else{
					$correo->setPara($CORREO_DESTINO);
				}
			}				
			$plantilla_interna->parse('texto');
			$correo->TEXTO =  $plantilla_interna->text('texto');
			$correo->enviar();
		}


		private function getNotificaciones(){
			$participantes = $this->_SESION->getVariable('NOTIFICACIONES_INTERNAS');
			$cantidad_participantes = 0;
			if(is_array($participantes)){
				foreach($participantes as $participante){
					$cantidad_participantes++;
					$CHECKED = ($participante['NOTINT_VIGENTE'] == 'S') ? 'checked' : '';
					$this->_TEMPLATE->assign('CHECKED',$CHECKED);
					$this->_TEMPLATE->assign('CODIGO_USUARIO',$participante['NOTINT_USUARIO']);
					$this->_TEMPLATE->assign('NOMBRE_FUNC',Funcionario::getNombre($participante['NOTINT_USUARIO'], $this->_ORA));
					$this->_TEMPLATE->assign('NOMBRE_UNIDAD',Funcionario::getUnidadUsuario($participante['NOTINT_USUARIO'], $this->_ORA));
					$this->_TEMPLATE->parse('main.div_notificacion.usuario_visar');
				}
			}

			if($cantidad_participantes <= 0){
				$this->_TEMPLATE->parse('main.div_notificacion.no_existe_usuarios');
			}


			$participantes_unidad = $this->_SESION->getVariable('ENCARGADOS_UNIDAD');

			$cantidad_encargados = 0;
			if(is_array($participantes_unidad)){
				foreach($participantes_unidad as $participante){
					//print_r($participante);
					$cantidad_encargados++;
					$CHECKED = ($participante['CHECKED'] == 'S') ? 'checked' : '';
					$this->_TEMPLATE->assign('CHECKED',$CHECKED);
					$this->_TEMPLATE->assign('PARUNI_UNIDAD',$participante['PARUNI_UNIDAD']);
					$this->_TEMPLATE->assign('PARUNI_VALOR',$participante['PARUNI_VALOR']);
					$this->_TEMPLATE->assign('PARUNI_ID',$participante['PARUNI_ID']);
					$this->_TEMPLATE->assign('NOMBRE_UNIDAD', $participante['NOMBRE_UNIDAD']);
					$this->_TEMPLATE->parse('main.div_notificacion.encargado_unidad');
				}
			}
			if($cantidad_encargados <= 0){
				$this->_TEMPLATE->parse('main.div_notificacion.no_existe_encargado_unidad');
			}


			$this->_TEMPLATE->parse('main.div_notificacion');
			return $this->_TEMPLATE->text('main.div_notificacion');
		}

		public function guardarNotificacionesInternas(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			$OPEN = array();
			$MENSAJES[] = 'Inicio de GUARDAR Rersoluciones Internas';
			//Verificar notificaciones internas, estas se puden seleccionar hacer check, o quitar check
			//pero no puede aparecer otra
			$EXISTENTES = $this->_SESION->getVariable('NOTIFICACIONES_INTERNAS');
			$SELECCIONADO_POST  = $_POST['NOTINT_USUARIO'];
			$USUARIO_BD = array();
			if(is_array($EXISTENTES)){
				foreach($EXISTENTES as $ex){
					if($ex['NOTINT_VIGENTE'] == 'S'){
						$USUARIO_BD	[$ex['NOTINT_USUARIO']] = 'S';
					}
				}
			}

			$MANTIENEN = array();
			$AGREGA = array();
			$ELIMINA = array();

			if(is_array($SELECCIONADO_POST)){
				foreach($SELECCIONADO_POST as $sel){
					if(isset($USUARIO_BD[$sel])){
						$MANTIENEN[$sel] = $sel;
						unset($USUARIO_BD[$sel]);
					}else{
						$AGREGA[$sel] = $sel;
					}
				}
			}

			if(is_array($USUARIO_BD)){
				foreach($USUARIO_BD as $sel => $s){
					$ELIMINA[$sel] = $sel;
				}
			}

			$MENSAJES[] = 'MANTIENEN:'.print_r($MANTIENEN,true);
			$MENSAJES[] = 'AGREGA:'.print_r($AGREGA,true);
			$MENSAJES[] = 'ELIMINA:'.print_r($ELIMINA,true);


			if(is_array($ELIMINA)){
				foreach($ELIMINA as $eli){
					$bind = array(
						':p_res_id' => $this->_SESION->getVariable('RES_ID'),
						':p_usuario' => $eli,
						':p_usuario_cambia' => $this->_SESION->USUARIO,
						':p_vigente_ant' => 'S',
						':p_vigente_act' => 'N'
					);
					$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA.'RSO_GUARDAR_PKG.fun_guardaNotificionInterna',$bind);
				}
			}


			if(is_array($AGREGA)){
				foreach($AGREGA as $agr){
					$bind = array(
						':p_res_id' => $this->_SESION->getVariable('RES_ID'),
						':p_usuario' => $agr,
						':p_usuario_cambia' => $this->_SESION->USUARIO,
						':p_vigente_ant' => 'N',
						':p_vigente_act' => 'S'
					);
					$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA.'RSO_GUARDAR_PKG.fun_guardaNotificionInterna',$bind);
				}
			}





			$EXISTENTES_UNI = $this->_SESION->getVariable('ENCARGADOS_UNIDAD');

			$MANTIENEN = array();
			$AGREGA = array();
			$ELIMINA = array();

			$SELECCIONADO_POST = array();

			if(is_array($_POST['ENCUNI_UNIDAD'])){
				foreach($_POST['ENCUNI_UNIDAD'] as $un){
					$valores = explode("_",$un);
					$SELECCIONADO_POST[$un] = array('unidad' => $valores[0], 'id' => $valores[1]);
				}
			}





			if(is_array($EXISTENTES_UNI)){
				foreach($EXISTENTES_UNI as $ex){

					if($ex['CHECKED'] == 'S'){
						if(isset($SELECCIONADO_POST[$ex['PARUNI_UNIDAD'].'_'.$ex['PARUNI_ID']])){
							$MANTIENEN[$ex['PARUNI_UNIDAD'].'_'.$ex['PARUNI_ID']] = array('unidad' => $ex['PARUNI_UNIDAD'], 'id' => $ex['PARUNI_ID']);
						}else{
							$ELIMINA[$ex['PARUNI_UNIDAD'].'_'.$ex['PARUNI_ID']] = array('unidad' => $ex['PARUNI_UNIDAD'], 'id' => $ex['PARUNI_ID']);
						}
					}

					if($ex['CHECKED'] == 'N'){
						if(isset($SELECCIONADO_POST[$ex['PARUNI_UNIDAD'].'_'.$ex['PARUNI_ID']])){
							$AGREGA[$ex['PARUNI_UNIDAD'].'_'.$ex['PARUNI_ID']] = array('unidad' => $ex['PARUNI_UNIDAD'], 'id' => $ex['PARUNI_ID']);
						}else{
							$MANTIENEN[$ex['PARUNI_UNIDAD'].'_'.$ex['PARUNI_ID']] = array('unidad' => $ex['PARUNI_UNIDAD'], 'id' => $ex['PARUNI_ID']);
						}
					}

				}
			}

			$MENSAJES[] = 'MANTIENEN UNIDAD:'.print_r($MANTIENEN,true);
			$MENSAJES[] = 'AGREGA UNIDAD:'.print_r($AGREGA,true);
			$MENSAJES[] = 'ELIMINA UNIDAD:'.print_r($ELIMINA,true);
			$MENSAJES[] = 'BD UNIDAD:'.print_r($EXISTENTES_UNI,true);
			$MENSAJES[] = 'POST UNIDAD:'.print_r($SELECCIONADO_POST,true);


			if(is_array($AGREGA)){
				foreach($AGREGA as $ag){
					//$valores = explode("_",$ag);
					//print_r($ag);
					$bind = array(
						':p_res_id' => $this->_SESION->getVariable('RES_ID'),
						':p_res_version' => $this->_SESION->getVariable('RES_VERSION'),
						':p_paruni_id' => $ag['id'],
						':p_res_checked' => 'S'
					);
					$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA.'RSO_GUARDAR_PKG.fun_guardaNotificionUnidad',$bind);
				}
			}

			if(is_array($ELIMINA)){
				foreach($ELIMINA as $el){
					$bind = array(
						':p_res_id' => $this->_SESION->getVariable('RES_ID'),
						':p_res_version' => $this->_SESION->getVariable('RES_VERSION'),
						':p_paruni_id' => $el['id'],
						':p_res_checked' => 'N'
					);
					//print_r($bind);
					$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA.'RSO_GUARDAR_PKG.fun_guardaNotificionUnidad',$bind);
				}
			}






			$this->_ORA->Commit();
			$bind = array(':p_res_id' => $this->_SESION->getVariable('RES_ID'));
			$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_getNotificacionInterna','function',$bind);
			$notificaciones = $this->_ORA->FetchAll($cursor);
			$this->_SESION->setVariable('NOTIFICACIONES_INTERNAS',$notificaciones);


			$bind = array(':p_res_id' => $this->_SESION->getVariable('RES_ID'), ':p_version' => $this->_SESION->getVariable('RES_VERSION'));
			$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_getEncargadosUnidad','function',$bind);
			$notificaciones = $this->_ORA->FetchAll($cursor);
			$this->_SESION->setVariable('ENCARGADOS_UNIDAD',$notificaciones);


			$json['MENSAJES'] =  $MENSAJES;
			//$json['CAMBIA'] = $CAMBIA;

			//$OPEN['#div_notificacion'] = 'open';
			//$json['OPEN'] = $OPEN;
			return json_encode($json);
		}

		public function fun_esElectronico(){
			$tipos_de_envio = $this->_SESION->getVariable('ENV_ID');
			$es_electronico = false;
			foreach($tipos_de_envio as $key => $tipo){
				if($key == 'elect'){
					$es_electronico = true;
				}
			}
			return $es_electronico;
		}

		public function fun_esNoElectronico(){
			$tipos_de_envio = $this->_SESION->getVariable('ENV_ID');
			if(isset($tipos_de_envio['elect'])){
				unset($tipos_de_envio['elect']);
			}
			return (count($tipos_de_envio) > 0) ? true : false;
		}


		public function fun_cantidadSeil($destinatario){
			$cantidad = 0;
			if(isset($destinatario['USUARIOS_SEIL']) && is_array($destinatario['USUARIOS_SEIL'])){
				foreach ($destinatario['USUARIOS_SEIL'] as $des){
					if($des['CHECKED'] == 'checked'){
						$cantidad++;
					}
				}
			}
			return $cantidad;
		}

		public function fun_noEsSVS($mail){
			$mail = strtolower($mail);
			$posicion = strpos($mail, "@svs.cl");
			if($posicion === false){
				return true;
			}else{
				return false;
			}
		}
		
		
		public function getDestinarios($caso,$version){
			
			$bind = array(':p_res_id' => $caso, ':p_res_version' => $version);
			//$this->_LOG->log(__METHOD__." (".__LINE__.") bind".print_r($bind,true));
			$cursor_d = $this->_ORA->retornaCursor('rso.RSO_DESTINATARIO_PKG.fun_getDestinatarios','function',$bind);
			$DESTINATARIOS = array();
			
			$bind = array(':caso' => $caso, ':version' => $version);
			$cursor_u = $this->_ORA->retornaCursor('rso.RSO_DESTINATARIO_PKG.fun_getUsuariosRes','function',$bind);
			$usuarios_seleccionados = $this->_ORA->FetchAll($cursor_u);
			$this->_LOG->log(__METHOD__." (".__LINE__.") bind".print_r($bind,true));
			while($data = $this->_ORA->FetchArray($cursor_d)){
				$this->_LOG->log(__METHOD__." (".__LINE__.") ".print_r($data,true));
				if(isset($data['DES_TIPO_ENT']) && $data['DES_TIPO_ENT'] != 'OTRCU' ){

					$rut = explode('-',$data['DES_RUT']);
					$rut = $rut[0];
					$bind = array(':rut' => $rut,':aplic' => 'PUFED'); //PUFED SEIL				
					$cursor_bus = $this->_ORA->retornaCursor('web_usuarios_seil.get_usuarios_busqueda_aplic','function',$bind);			
					$usuarios = $this->_ORA->FetchAll($cursor_bus);
					// ac� tengo que ver si para este rut existe algun otros
					$USUARIOS_RUT = array();
					foreach($usuarios_seleccionados as $sel){
						if($data['DES_ID'] == $sel['DES_ID'] && isset($sel['USUENV_TIPO']) && $sel['USUENV_TIPO'] == 'otro'){
							$sel['CHECKED'] = 'checked';
							$USUARIOS_RUT[] = $sel;
						}
					}
					
					foreach($usuarios as $key => $usr){
						$usuarios[$key]['CHECKED'] = '';
						foreach($usuarios_seleccionados as $sel_usr){
							
							if($usuarios[$key]['COD_USUARIO'] == $sel_usr['USUENV_USUARIO']){
								$usuarios[$key]['CHECKED'] = 'checked';
							}
						}
					}

					$data['USUARIOS_SEIL'] = array_merge($usuarios,$USUARIOS_RUT);
					$DESTINATARIOS[$data['DES_TIPO_ENT']][$data['DES_RUT']] = $data;
					
				}else{
					$DESTINATARIOS[$data['DES_TIPO_ENT']][$data['DES_RUT']] = $data;
				}
					
				
			}
			return $DESTINATARIOS;
		}
		
		
		
		
		public function enviarNotificacionDestinatariosSinSes($data_res){
			
			$TIENE_DESTINATARIO_DOCDIG = FALSE;
			
			$destinatarios = $this->getDestinarios($data_res['RES_ID'],$data_res['RES_VERSION']);
			$this->_LOG->log(__METHOD__." (".__LINE__.") DATOS ".print_r($data_res,true));
	
			$es_electronico = false;
			foreach($data_res['ENV_ID'] as $key => $tipo){
				if($key == 'elect'){
					$es_electronico = true;
				}
			}

			if($es_electronico){
				if(is_array($destinatarios) && count($destinatarios) > 0){ //tiene saneado el else
					$this->_LOG->log(__METHOD__." (".__LINE__.") "."Existe mas de un destinatario");

					foreach ($destinatarios as $dest_ent){
						foreach($dest_ent as $des){
							$CONT_CONTROL = 0; //Variable de control para las lecturas, se incorpora solo en los usuarios asociados a una entidad
							//$this->_LOG->log(__METHOD__." (".__LINE__.") "."Comenzando el destinatario : ".print_r($des,true));

							$plantilla_destinatario = new XTemplate(dirname (__FILE__).'/../correos/notificacion_destinatario.html');
							$plantilla_destinatario->assign('RES_ID',$data_res['RES_ID']);

							$correo_seil = new Correo(); //Correo por destinatario a los usuarios SEIL
							$correo_seil->ORA = $this->_ORA;
							$correo_seil->APLIC = 'PURSO';
							$correo_seil->FIRMADO = false;
							$correo_seil->DESDE_NOMBRE = 'Resoluciones Electronicas';

							$existe_correo_seil = false;

							if($this->fun_cantidadSeil($des) > 0){ //si no tiene usuario SEIL deberia ser de tipo otro o por mano
								$this->_LOG->log(__METHOD__." (".__LINE__.") "."Tiene Usuarios SEIL");
								//lo mas normal y el caso que mas se dar�

								foreach($des['USUARIOS_SEIL'] as $usr_seil){
									if($usr_seil['CHECKED'] == 'checked'){
										$CONT_CONTROL++;
										$LIC_USUARIO = (isset($usr_seil['USUENV_TIPO']) &&  $usr_seil['USUENV_TIPO'] == 'otro') ? NULL :$usr_seil['COD_USUARIO'];
										$MAIL = (isset($usr_seil['USUENV_TIPO']) &&  $usr_seil['USUENV_TIPO'] == 'otro') ? $usr_seil['USUENV_EMAIL'] :$usr_seil['MAIL'];
										$bind = array(
											":res_id" => $data_res['RES_ID'],
											":res_version" => $data_res['RES_VERSION'],
											":lic_usuario" => $LIC_USUARIO,
											":des_id" => $des['DES_ID'],
											":mail" => $MAIL,
											":usuenv_control" => $CONT_CONTROL
										);
										$this->_LOG->log('Antes de setear control en usuarios: '.print_r($bind,true));
										$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdControlUsr",$bind);

										$this->_LOG->log(__METHOD__.' ('.__LINE__.') Antes de verificar correo');
										//verificar si tiene correo, en caso de que no error
										if(isset($usr_seil['MAIL']) && strlen($usr_seil['MAIL'])> 5){
											if($this->fun_noEsSVS($usr_seil['MAIL'])){
												$existe_correo_seil = true;
												$correo_seil->setPara($usr_seil['MAIL']);
												$this->_LOG->log(__METHOD__." (".__LINE__.") "."Se envia correo a: ".$usr_seil['MAIL']);
											}else{
												if(($this->_AMBIENTE == 'DESA' || $this->_AMBIENTE == 'TEST') && $usr_seil['MAIL'] == 'culloa@svs.cl' || $usr_seil['MAIL'] == 'jsoto@svs.cl' ){
													$existe_correo_seil = true;
													$correo_seil->setPara($usr_seil['MAIL']);
													$this->_LOG->log(__METHOD__." (".__LINE__.") "."Se envia correo a: ".$usr_seil['MAIL']." por ser TEST / DESA ");
												}else{
													$this->_LOG->log(__METHOD__." (".__LINE__.") "."Para ".$data_res['RES_ID']."No se envia notificacion por que es usuario SVS ".$usr_seil['MAIL']);
												}
											}
										}else{
											if(isset($usr_seil['USUENV_TIPO']) &&  $usr_seil['USUENV_TIPO'] == 'otro'){
												if(isset($usr_seil['USUENV_EMAIL']) && strlen($usr_seil['USUENV_EMAIL'])> 5){

													if($this->fun_noEsSVS($usr_seil['USUENV_EMAIL'])){

														$correo_no_seil = new Correo();
														$correo_no_seil->ORA = $this->_ORA;
														$correo_no_seil->APLIC = 'PURSO';
														$correo_no_seil->FIRMADO = false;
														$correo_no_seil->DESDE_NOMBRE = 'Resoluciones Electronicas';

														$plantilla_destinatario->assign('NUMERO_RESOLUCION', $data_res['RES_NUMERO_RESOLUCION']);
														$plantilla_destinatario->parse('not_destinatario_no_seil.asunto');
														$correo_no_seil->ASUNTO = $plantilla_destinatario->text('not_destinatario_no_seil.asunto');

														$bind = array(':p_res_id' => $data_res['RES_ID']);
														$link = $this->_ORA->ejecutaFunc('rso.rso_obtener_pkg.fun_getLink',$bind);
														$plantilla_destinatario->assign('TRATAMIENTO', "Estimado (a)");
														$plantilla_destinatario->assign('LINK_RESOLUCION', $link."&cont=".$CONT_CONTROL."&com=".substr(md5($CONT_CONTROL),7,3));
														$plantilla_destinatario->assign('NOMBRE_USUARIO', $usr_seil['USUENV_NOMBRE']);

														$plantilla_destinatario->parse('not_destinatario_no_seil.texto');
														$correo_no_seil->TEXTO =  $plantilla_destinatario->text('not_destinatario_no_seil.texto');
														$correo_no_seil->setPara($usr_seil['USUENV_EMAIL']);
														$correo_no_seil->enviar();

														$bind = array(':p_des' => $des['DES_ID'], ':p_usuenv_control' => $CONT_CONTROL, ':p_correo' => $correo_no_seil->ID_CORREO);
														$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreoControl",$bind);



														$this->_LOG->log(__METHOD__." (".__LINE__.") "."Se envia correo a: >> OTRO << ".$usr_seil['USUENV_EMAIL']);
													}else{
													//	if(($this->_AMBIENTE == 'DESA' || $this->_AMBIENTE == 'TEST') && $usr_seil['USUENV_EMAIL'] == 'culloa@svs.cl' || $usr_seil['USUENV_EMAIL'] == 'jsoto@svs.cl' ){
															$correo_no_seil = new Correo();
															$correo_no_seil->ORA = $this->_ORA;
															$correo_no_seil->APLIC = 'PURSO';
															$correo_no_seil->FIRMADO = false;
															$correo_no_seil->DESDE_NOMBRE = 'Resoluciones Electronicas';

															$plantilla_destinatario->assign('NUMERO_RESOLUCION', $data_res['RES_NUMERO_RESOLUCION']);
															$plantilla_destinatario->parse('not_destinatario_no_seil.asunto');
															$correo_no_seil->ASUNTO = $plantilla_destinatario->text('not_destinatario_no_seil.asunto');


															$plantilla_destinatario->assign('TRATAMIENTO', "Estimado (a)");
															$plantilla_destinatario->assign('LINK_RESOLUCION', $this->_SESION->getVariable('LINK_RESOLUCION')."&cont=".$CONT_CONTROL."&com=".substr(md5($CONT_CONTROL),7,3));
															$plantilla_destinatario->assign('NOMBRE_USUARIO', $usr_seil['USUENV_NOMBRE']);

															$plantilla_destinatario->parse('not_destinatario_no_seil.texto');
															$correo_no_seil->TEXTO =  $plantilla_destinatario->text('not_destinatario_no_seil.texto');
															$correo_no_seil->setPara($usr_seil['USUENV_EMAIL']);
															$correo_no_seil->enviar();


															$bind = array(':p_des' => $des['DES_ID'], ':p_usuenv_control' => $usr_seil['USUENV_CONTROL'], ':p_correo' => $correo_no_seil->ID_CORREO);
															$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreoControl",$bind);


															$this->_LOG->log(__METHOD__." (".__LINE__.") "."Se envia correo >> OTRO << a: ".$usr_seil['USUENV_EMAIL']." por ser TEST / DESA ");
														/*}else{
															$this->_LOG->error(__METHOD__." (".__LINE__.") "."Para ".$this->_SESION->getVariable('RES_ID')." No se envia notificacion por que es usuario SVS ".$usr_seil['USUENV_EMAIL']);
														}*/
													}
												}else{
													$this->_LOG->error(__METHOD__." (".__LINE__.") "."Un destinatario con SEIL tipo >>>> OTRO <<<<  No posee correo electronico, RES_ID:".$data_res['RES_ID']." - ".print_r($usr_seil,true));
												}
											}else{
												$this->_LOG->error(__METHOD__." (".__LINE__.") "."Un destinatario con SEIL No posee correo electronico, RES_ID:".$data_res['RES_ID']." - ".print_r($usr_seil,true));
											}
										}
									}
								}

							}else{


								$this->_LOG->log(__METHOD__." (".__LINE__.") "."NOOO Tiene Usuarios SEIL");
								//Si no tiene usuarios seil y es electr�nico es porque tiene correo electr�nico
								if(isset($des['DES_CORREO'])){
									//tiene correo electr�nico no hay problema

									$correo_no_seil = new Correo();
									$correo_no_seil->ORA = $this->_ORA;
									$correo_no_seil->APLIC = 'PURSO';
									$correo_no_seil->FIRMADO = false;
									$correo_no_seil->DESDE_NOMBRE = 'Resoluciones Electronicas';

									$plantilla_destinatario->assign('NUMERO_RESOLUCION', $data_res['RES_NUMERO_RESOLUCION']);
									$plantilla_destinatario->parse('not_destinatario_no_seil.asunto');
									$correo_no_seil->ASUNTO = $plantilla_destinatario->text('not_destinatario_no_seil.asunto');
									
									
									$bind = array(':p_res_id' => $data_res['RES_ID']);
									$link = $this->_ORA->ejecutaFunc('rso.rso_obtener_pkg.fun_getLink',$bind);
									$this->_LOG->log(__METHOD__.' ('.__LINE__.') '.'LINK RESOLUCION: '. $link);

									$plantilla_destinatario->assign('TRATAMIENTO', $des['TRA_NOMBRE']);
									$plantilla_destinatario->assign('NOMBRE_USUARIO', $des['DES_NOMBRE']);
									$plantilla_destinatario->assign('LINK_RESOLUCION', $link."&des=".$des['DES_ID']."&com=".substr(md5($des['DES_ID']),7,3));
									$plantilla_destinatario->parse('not_destinatario_no_seil.texto');
									$correo_no_seil->TEXTO =  $plantilla_destinatario->text('not_destinatario_no_seil.texto');
									$correo_no_seil->setPara($des['DES_CORREO']);
									$correo_no_seil->enviar();


									$bind = array(':p_des' => $des['DES_ID'], ':p_tipo' => 'otro', ':p_correo' => $correo_no_seil->ID_CORREO);
									$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreo",$bind);

									$this->_LOG->log(__METHOD__." (".__LINE__.") "."Se enviara al correo ingresado ya que es de tipo Otro, y posee");
								}else{
									//Ac� podemos preguntar si es por doc_digital
									//pregunta finalmente por ROR.
									if($des['DES_MEDIO_ENVIO'] == 'DOCDIGITAL'){
									//if($des['DES_DIRECCION'] == 'DocDigital'){
										
										if(!$TIENE_DESTINATARIO_DOCDIG){
											$TIENE_DESTINATARIO_DOCDIG = true;
											
											
											$bind = array(':doc_usr' => $data_res['RES_FIRMA']);
											$rut_firmante = $this->_ORA->ejecutaFunc('wfa.wfa_usr.getRutUsuario',$bind);
											$archivo_base64 = base64_encode($this->_ARCHIVO_FIRMADO);
											$archivo = $this->_ORA->NewDescriptor();
											$archivo->WriteTemporary($archivo_base64,OCI_TEMP_CLOB);
			
			
											$bind = array(
												':p_ENVDOC_MATERIA' => $data_res['RES_REFERENCIA'],
												':p_ENVDOC_DESCRIPCION' => 'Resoluci�n Nro:'.$data_res['RES_NUMERO_RESOLUCION'],
												':p_ENVDOC_FOLIO' => $data_res['RES_NUMERO_RESOLUCION'],
												':p_ENVDOC_NOMBRE' => 'resolucion_'.$data_res['RES_NUMERO_RESOLUCION'].'_'.$data_res['RES_ANO'].'.pdf',
												':p_ENVDOC_RUT_USR_CREADOR' => $rut_firmante,
												':p_ENVDOC_ES_RESERVADO' => 'NO',//($privacidad_documento === 0) ? 'NO' : 'SI',
												':p_ENVDOC_DOCTO' => $archivo
											);
											
											$this->_LOG->log(print_r($bind,true));
											$result = $this->_ORA->ejecutaFunc('ISP.ISP_DDIG_ENVIO_DOCTOS_PKG.fun_insertDocto',$bind);
											$this->_LOG->log(__LINE__." Resultado es: ".$result);
											
										}
										
										
										$bind = array(
											':p_ENVDOC_ID' => $result,
											':p_ENVDOC_RUT' => $des['DES_RUT'],
											':p_ENVDOC_NOMBRE' => $des['DES_NOMBRE']);
										$this->_LOG->log(print_r($bind,true));
										$result_2 = $this->_ORA->ejecutaFunc('ISP.ISP_DDIG_ENVIO_DOCTOS_PKG.fun_insertDoctoRemitente',$bind);
										$this->_LOG->log(__LINE__." Resultado 2 es: ".$result_2);
									}else{
										
										
										
										
										//Si no tiene correo es porque tiene por mano y direccion
										$tipos_de_envio = $data_res['ENV_ID'];
										if(isset($tipos_de_envio['elect'])){
											unset($tipos_de_envio['elect']);
										}
										$NoEsElectronico = (count($tipos_de_envio) > 0) ? true : false;
									
										if($NoEsElectronico){
											if(isset($des['DES_DIRECCION']) && strlen(trim($des['DES_DIRECCION'])) > 5){
												$this->_LOG->error(__METHOD__." (".__LINE__.") "."El documento ".$data_res['RES_ID']." es electronico y por mano, tiene direccion fisica y se enviar� solo por ese  medio ".print_r($des,true));
											}else{
												//es electronico y mano pero no tiene ningun metodo de envio
												$this->_LOG->error(__METHOD__." (".__LINE__.") "."El documento ".$data_res['RES_ID']." es electronico y por mano pero no tiene forma de envio, se realiza exit: ".print_r($des,true));
												exit();
											}
										}else{
											//significa que es solo electronico y no hay como enviar
										}
									}
								}

							}


							if($existe_correo_seil){

								$plantilla_destinatario->assign('NUMERO_RESOLUCION', $data_res['RES_NUMERO_RESOLUCION']);
								$plantilla_destinatario->parse('not_destinatario_seil.asunto');
								$correo_seil->ASUNTO = $plantilla_destinatario->text('not_destinatario_seil.asunto');
								$plantilla_destinatario->assign('DES_NOMBRE',$des['DES_NOMBRE']);
								$plantilla_destinatario->parse('not_destinatario_seil.texto');
								$correo_seil->TEXTO =  $plantilla_destinatario->text('not_destinatario_seil.texto');
								$correo_seil->enviar();
								$bind = array(':p_des' => $des['DES_ID'], ':p_tipo' => 'seil', ':p_correo' => $correo_seil->ID_CORREO);
								$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreo",$bind);

							}




						}
					}

				}else{
					$this->_LOG->error(__METHOD__." (".__LINE__.") "."El documento ".$data_res['RES_ID']." es electronico pero no tiene destinatarios, se realiza exit");
					exit();
				}


			}else{
				$this->_LOG->log(__METHOD__." (".__LINE__.") "."No es Electronico por lo tanto no hay notificacion a los destinatarios");
			}



			//print_r($destinatarios);
			//exit();

			/*Array
(
    [CSGEN] => Array
        (
            [99225000] => Array
                (
                    [RES_ID] => 97253
                    [RES_VERSION] => 1
                    [TIPDES_ID] => seil
                    [DES_ID] => 174
                    [DES_RUT] => 99225000
                    [DES_TIPO_ENT] => CSGEN
                    [DES_NOMBRE] => ACE SEGUROS S.A.
                    [DES_CARGO] => Gerente General
                    [DES_DIRECCION] => MIRAFLORES 222 PISO 16 -17 - Comuna: SANTIAGO -  Ciudad: SANTIAGO
 - Reg. Metropolitana
                    [DES_CON_COPIA] => N
                    [USUARIOS_SEIL] => Array
                        (
                            [0] => Array
                                (
                                    [RUT_ENTIDAD] => 99225000
                                    [RUT_PERSONA] => 15911752
                                    [DG_ENTIDAD] => 3
                                    [COD_TIPO_INFO] => -
                                    [MAIL] => sbelmar@svs.cl
                                    [COD_USUARIO] => 976PZG376
                                    [PASS_USUARIO] => -
                                    [PREGUNTA1] => -
                                    [RESPUESTA1] => -
                                    [PREGUNTA2] =>
                                    [RESPUESTA2] =>
                                    [ACTIVADO] => S
                                    [FECHA_MODIFICACION] =>
                                    [NOMBRE_USUARIO] => CLAUDIO
                                    [FECHA_SOLICITUD] => 28/07/2011
                                    [FECHA_ACTIVACION] => 28/07/2011
                                    [TIPO_USUARIO] => FISC
                                    [COD_USUARIO_ADMIN] => ADM2011244
                                    [CODIGO_AUTORIZACION] => # ??
                                    [PASS_USUARIO_ENC] => ? B?QG??? ??g???
                                    [NOMBRE_USUARIO_C] => CLAUDIO
                                    [COD_USU_ADMIN] => ADM2011244
                                    [FECHA_ULTIMO_USO] =>
                                    [CHECKED] => checked
                                )

                            [1] => Array
                                (
                                    [RUT_ENTIDAD] => 99225000
                                    [RUT_PERSONA] => 15911752
                                    [DG_ENTIDAD] => 3
                                    [COD_TIPO_INFO] => -
                                    [MAIL] => culloa@svs.cl
                                    [COD_USUARIO] => CULLOA
                                    [PASS_USUARIO] => cu123
                                    [PREGUNTA1] => 06
                                    [RESPUESTA1] => claudio
                                    [PREGUNTA2] => 04
                                    [RESPUESTA2] => claudio
                                    [ACTIVADO] => S
                                    [FECHA_MODIFICACION] => 03/09/2015
                                    [NOMBRE_USUARIO] => CLAUDIO ULLOA MERINO
                                    [FECHA_SOLICITUD] => 17/04/2009
                                    [FECHA_ACTIVACION] =>
                                    [TIPO_USUARIO] => FISC
                                    [COD_USUARIO_ADMIN] =>
                                    [CODIGO_AUTORIZACION] =>
                                    [PASS_USUARIO_ENC] => ?w? : R?e? ?? ??
                                    [NOMBRE_USUARIO_C] => CLAUDIO ULLOA MERINO
                                    [COD_USU_ADMIN] =>
                                    [FECHA_ULTIMO_USO] =>
                                    [CHECKED] => checked
                                )

                            [2] => Array
                                (
                                    [RUT_ENTIDAD] => 99225000
                                    [RUT_PERSONA] => 13256082
                                    [DG_ENTIDAD] => 5
                                    [COD_TIPO_INFO] => -
                                    [MAIL] => jsoto@svs.cl
                                    [COD_USUARIO] => JLSOTO
                                    [PASS_USUARIO] => 123
                                    [PREGUNTA1] => 06
                                    [RESPUESTA1] => tonijua
                                    [PREGUNTA2] => 06
                                    [RESPUESTA2] => tonijua
                                    [ACTIVADO] => S
                                    [FECHA_MODIFICACION] => 08/05/2006
                                    [NOMBRE_USUARIO] => Juan Luis Soto V.
                                    [FECHA_SOLICITUD] =>
                                    [FECHA_ACTIVACION] =>
                                    [TIPO_USUARIO] => FISC
                                    [COD_USUARIO_ADMIN] =>
                                    [CODIGO_AUTORIZACION] =>
                                    [PASS_USUARIO_ENC] => $?]X8,R??&/??w?4
                                    [NOMBRE_USUARIO_C] => Juan Luis Soto V.
                                    [COD_USU_ADMIN] =>
                                    [FECHA_ULTIMO_USO] =>
                                    [CHECKED] => checked
                                )

                            [3] => Array
                                (
                                    [RUT_ENTIDAD] => 99225000
                                    [RUT_PERSONA] => 1
                                    [DG_ENTIDAD] => 9
                                    [COD_TIPO_INFO] => -
                                    [MAIL] => culloa@svs.cl
                                    [COD_USUARIO] => P99225000
                                    [PASS_USUARIO] => 123
                                    [PREGUNTA1] => x
                                    [RESPUESTA1] => x
                                    [PREGUNTA2] =>
                                    [RESPUESTA2] =>
                                    [ACTIVADO] => S
                                    [FECHA_MODIFICACION] =>
                                    [NOMBRE_USUARIO] => Usuario prueba99225000
                                    [FECHA_SOLICITUD] =>
                                    [FECHA_ACTIVACION] =>
                                    [TIPO_USUARIO] => FISC
                                    [COD_USUARIO_ADMIN] =>
                                    [CODIGO_AUTORIZACION] =>
                                    [PASS_USUARIO_ENC] => ?L?C!p?a??s
                                    [NOMBRE_USUARIO_C] => Usuario prueba99225000
                                    [COD_USU_ADMIN] =>
                                    [FECHA_ULTIMO_USO] =>
                                    [CHECKED] => checked
                                )

                        )

                )

            [99147000] => Array
                (
                    [RES_ID] => 97253
                    [RES_VERSION] => 1
                    [TIPDES_ID] => seil
                    [DES_ID] => 175
                    [DES_RUT] => 99147000
                    [DES_TIPO_ENT] => CSGEN
                    [DES_NOMBRE] => BCI SEGUROS GENERALES S.A.
                    [DES_CARGO] => Gerente General
                    [DES_DIRECCION] => HUERFANOS 1189 PISOS 2-3 Y 4 - Comuna: SANTIAGO -  Ciudad: SANTIAGO
 - Reg. Metropolitana
                    [DES_CON_COPIA] => N
                    [USUARIOS_SEIL] => Array
                        (
                            [0] => Array
                                (
                                    [RUT_ENTIDAD] => 99147000
                                    [RUT_PERSONA] => 1
                                    [DG_ENTIDAD] => 9
                                    [COD_TIPO_INFO] => -
                                    [MAIL] => jsoto@svs.cl
                                    [COD_USUARIO] => P99147000
                                    [PASS_USUARIO] => 123
                                    [PREGUNTA1] => x
                                    [RESPUESTA1] => x
                                    [PREGUNTA2] =>
                                    [RESPUESTA2] =>
                                    [ACTIVADO] => S
                                    [FECHA_MODIFICACION] =>
                                    [NOMBRE_USUARIO] => Usuario prueba99147000
                                    [FECHA_SOLICITUD] =>
                                    [FECHA_ACTIVACION] =>
                                    [TIPO_USUARIO] => FISC
                                    [COD_USUARIO_ADMIN] =>
                                    [CODIGO_AUTORIZACION] =>
                                    [PASS_USUARIO_ENC] => ????72?]??g
                                    [NOMBRE_USUARIO_C] => Usuario prueba99147000
                                    [COD_USU_ADMIN] =>
                                    [FECHA_ULTIMO_USO] =>
                                    [CHECKED] => checked
                                )

                            [1] => Array
                                (
                                    [DES_ID] => 175
                                    [RES_ID] => 97253
                                    [RES_VERSION] => 1
                                    [USUENV_USUARIO] =>
                                    [USUENV_TIPO] => otro
                                    [USUENV_NOMBRE] => claudio ulloa
                                    [USUENV_DIRECCION] => HUERFANOS 1189 PISOS 2-3 Y 4 - Comuna: SANTIAGO
 -  Ciudad: SANTIAGO - Reg. Metropolitana
                                    [USUENV_EMAIL] => culloa@svs.cl
                                    [CHECKED] => checked
                                )

                        )

                )

        )

    [OTRCU] => Array
        (
            [15911752-9] => Array
                (
                    [DES_RUT] => 15911752-9
                    [DES_NOMBRE] => CLAUDIO AGREGAR OTROssss
                    [DES_DIRECCION] => LAGO DEL PETROHUE
                    [DES_CORREO] => culloa@svs.cl
                    [TRA_ID] => ustre
                    [TRA_NOMBRE] => ILUSTRE
                    [DES_TIPO_ENT] => OTRCU
                    [DES_CON_COPIA] => N
                    [RAND] => N
					[DES_ID] = 23
                )

        )

)
		*/


		}


		public function enviarNotificacionDestinatarios(){
			$destinatarios = $this->_SESION->getVariable('DESTINATARIO');


			//DES_ID
			//print_r($destinatarios);
			//exit();
			$this->_LOG->log(__METHOD__." (".__LINE__.") "."enviarNotificacionDestinatarios");

			if($this->fun_esElectronico()){
				if(is_array($destinatarios) && count($destinatarios) > 0){ //tiene saneado el else

					$this->_LOG->log(__METHOD__." (".__LINE__.") "."Existe mas de un destinatario");


					foreach ($destinatarios as $dest_ent){
						foreach($dest_ent as $des){
							$CONT_CONTROL = 0; //Variable de control para las lecturas, se incorpora solo en los usuarios asociados a una entidad
							//$this->_LOG->log(__METHOD__." (".__LINE__.") "."Comenzando el destinatario : ".print_r($des,true));




							$plantilla_destinatario = new XTemplate(dirname (__FILE__).'/../correos/notificacion_destinatario.html');
							$plantilla_destinatario->assign('RES_ID',$this->_SESION->RES_ID);

							$correo_seil = new Correo(); //Correo por destinatario a los usuarios SEIL
							$correo_seil->ORA = $this->_ORA;
							$correo_seil->APLIC = 'PURSO';
							$correo_seil->FIRMADO = false;
							$correo_seil->DESDE_NOMBRE = 'Resoluciones Electronicas';

							$existe_correo_seil = false;

							if($this->fun_cantidadSeil($des) > 0){ //si no tiene usuario SEIL deberia ser de tipo otro o por mano
								$this->_LOG->log(__METHOD__." (".__LINE__.") "."Tiene Usuarios SEIL");
								//lo mas normal y el caso que mas se dar�

								foreach($des['USUARIOS_SEIL'] as $usr_seil){
									if($usr_seil['CHECKED'] == 'checked'){
										$CONT_CONTROL++;
										//--------- AC� SE GUARDAR� EL REGISTRO EN BD
										/*$bind = array(
											":res_id" => $this->_SESION->getVariable('RES_ID'),
											":res_version" => $this->_SESION->getVariable('RES_VERSION'),
											":lic_usuario" => $usr_seil['COD_USUARIO'],
											":des_rut" => $des[''],
											":des_id" => '',
											":usuenv_control" => ''
										);
										*/

										$LIC_USUARIO = (isset($usr_seil['USUENV_TIPO']) &&  $usr_seil['USUENV_TIPO'] == 'otro') ? NULL :$usr_seil['COD_USUARIO'];
										$MAIL = (isset($usr_seil['USUENV_TIPO']) &&  $usr_seil['USUENV_TIPO'] == 'otro') ? $usr_seil['USUENV_EMAIL'] :$usr_seil['MAIL'];
										$bind = array(
											":res_id" => $this->_SESION->getVariable('RES_ID'),
											":res_version" => $this->_SESION->getVariable('RES_VERSION'),
											":lic_usuario" => $LIC_USUARIO,
											":des_id" => $des['DES_ID'],
											":mail" => $MAIL,
											":usuenv_control" => $CONT_CONTROL
										);
										$this->_LOG->log('Antes de setear control en usuarios: '.print_r($bind,true));
										$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdControlUsr",$bind);




										$this->_LOG->log('Antes de verificar correo');
										//verificar si tiene correo, en caso de que no error
										if(isset($usr_seil['MAIL']) && strlen($usr_seil['MAIL'])> 5){
											if($this->fun_noEsSVS($usr_seil['MAIL'])){
												$existe_correo_seil = true;
												$correo_seil->setPara($usr_seil['MAIL']);
												$this->_LOG->log(__METHOD__." (".__LINE__.") "."Se envia correo a: ".$usr_seil['MAIL']);
											}else{
												if(($this->_AMBIENTE == 'DESA' || $this->_AMBIENTE == 'TEST') && $usr_seil['MAIL'] == 'culloa@svs.cl' || $usr_seil['MAIL'] == 'jsoto@svs.cl' ){
													$existe_correo_seil = true;
													$correo_seil->setPara($usr_seil['MAIL']);
													$this->_LOG->log(__METHOD__." (".__LINE__.") "."Se envia correo a: ".$usr_seil['MAIL']." por ser TEST / DESA ");
												}else{
													$this->_LOG->log(__METHOD__." (".__LINE__.") "."Para ".$this->_SESION->getVariable('RES_ID')."No se envia notificacion por que es usuario SVS ".$usr_seil['MAIL']);
												}
											}
										}else{
											if(isset($usr_seil['USUENV_TIPO']) &&  $usr_seil['USUENV_TIPO'] == 'otro'){
												if(isset($usr_seil['USUENV_EMAIL']) && strlen($usr_seil['USUENV_EMAIL'])> 5){

													if($this->fun_noEsSVS($usr_seil['USUENV_EMAIL'])){

														$correo_no_seil = new Correo();
														$correo_no_seil->ORA = $this->_ORA;
														$correo_no_seil->APLIC = 'PURSO';
														$correo_no_seil->FIRMADO = false;
														$correo_no_seil->DESDE_NOMBRE = 'Resoluciones Electronicas';

														$plantilla_destinatario->assign('NUMERO_RESOLUCION', $this->_SESION->getVariable('RES_NUMERO_RESOLUCION'));
														$plantilla_destinatario->parse('not_destinatario_no_seil.asunto');
														$correo_no_seil->ASUNTO = $plantilla_destinatario->text('not_destinatario_no_seil.asunto');

														$bind = array(':p_res_id' => $this->_SESION->getVariable('RES_ID'));
														$link = $this->_ORA->ejecutaFunc('rso.rso_obtener_pkg.fun_getLink',$bind);
														$plantilla_destinatario->assign('TRATAMIENTO', "Estimado (a)");
														$plantilla_destinatario->assign('LINK_RESOLUCION', $link."&cont=".$CONT_CONTROL."&com=".substr(md5($CONT_CONTROL),7,3));
														$plantilla_destinatario->assign('NOMBRE_USUARIO', $usr_seil['USUENV_NOMBRE']);

														$plantilla_destinatario->parse('not_destinatario_no_seil.texto');
														$correo_no_seil->TEXTO =  $plantilla_destinatario->text('not_destinatario_no_seil.texto');
														$correo_no_seil->setPara($usr_seil['USUENV_EMAIL']);
														$correo_no_seil->enviar();

														$bind = array(':p_des' => $des['DES_ID'], ':p_usuenv_control' => $CONT_CONTROL, ':p_correo' => $correo_no_seil->ID_CORREO);
														$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreoControl",$bind);



														$this->_LOG->log(__METHOD__." (".__LINE__.") "."Se envia correo a: >> OTRO << ".$usr_seil['USUENV_EMAIL']);
													}else{
													//	if(($this->_AMBIENTE == 'DESA' || $this->_AMBIENTE == 'TEST') && $usr_seil['USUENV_EMAIL'] == 'culloa@svs.cl' || $usr_seil['USUENV_EMAIL'] == 'jsoto@svs.cl' ){
															$correo_no_seil = new Correo();
															$correo_no_seil->ORA = $this->_ORA;
															$correo_no_seil->APLIC = 'PURSO';
															$correo_no_seil->FIRMADO = false;
															$correo_no_seil->DESDE_NOMBRE = 'Resoluciones Electronicas';

															$plantilla_destinatario->assign('NUMERO_RESOLUCION', $this->_SESION->getVariable('RES_NUMERO_RESOLUCION'));
															$plantilla_destinatario->parse('not_destinatario_no_seil.asunto');
															$correo_no_seil->ASUNTO = $plantilla_destinatario->text('not_destinatario_no_seil.asunto');


															$plantilla_destinatario->assign('TRATAMIENTO', "Estimado (a)");
															$plantilla_destinatario->assign('LINK_RESOLUCION', $this->_SESION->getVariable('LINK_RESOLUCION')."&cont=".$CONT_CONTROL."&com=".substr(md5($CONT_CONTROL),7,3));
															$plantilla_destinatario->assign('NOMBRE_USUARIO', $usr_seil['USUENV_NOMBRE']);

															$plantilla_destinatario->parse('not_destinatario_no_seil.texto');
															$correo_no_seil->TEXTO =  $plantilla_destinatario->text('not_destinatario_no_seil.texto');
															$correo_no_seil->setPara($usr_seil['USUENV_EMAIL']);
															$correo_no_seil->enviar();


															$bind = array(':p_des' => $des['DES_ID'], ':p_usuenv_control' => $usr_seil['USUENV_CONTROL'], ':p_correo' => $correo_no_seil->ID_CORREO);
															$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreoControl",$bind);


															$this->_LOG->log(__METHOD__." (".__LINE__.") "."Se envia correo >> OTRO << a: ".$usr_seil['USUENV_EMAIL']." por ser TEST / DESA ");
														/*}else{
															$this->_LOG->error(__METHOD__." (".__LINE__.") "."Para ".$this->_SESION->getVariable('RES_ID')." No se envia notificacion por que es usuario SVS ".$usr_seil['USUENV_EMAIL']);
														}*/
													}
												}else{
													$this->_LOG->error(__METHOD__." (".__LINE__.") "."Un destinatario con SEIL tipo >>>> OTRO <<<<  No posee correo electronico, RES_ID:".$this->_SESION->getVariable('RES_ID')." - ".print_r($usr_seil,true));
												}
											}else{
												$this->_LOG->error(__METHOD__." (".__LINE__.") "."Un destinatario con SEIL No posee correo electronico, RES_ID:".$this->_SESION->getVariable('RES_ID')." - ".print_r($usr_seil,true));
											}
										}
									}
								}

							}else{


								$this->_LOG->log(__METHOD__." (".__LINE__.") "."NOOO Tiene Usuarios SEIL");
								//Si no tiene usuarios seil y es electr�nico es porque tiene correo electr�nico
								if(isset($des['DES_CORREO'])){
									//tiene correo electr�nico no hay problema

									$correo_no_seil = new Correo();
									$correo_no_seil->ORA = $this->_ORA;
									$correo_no_seil->APLIC = 'PURSO';
									$correo_no_seil->FIRMADO = false;
									$correo_no_seil->DESDE_NOMBRE = 'Resoluciones Electronicas';

									$plantilla_destinatario->assign('NUMERO_RESOLUCION', $this->_SESION->getVariable('RES_NUMERO_RESOLUCION'));
									$plantilla_destinatario->parse('not_destinatario_no_seil.asunto');
									$correo_no_seil->ASUNTO = $plantilla_destinatario->text('not_destinatario_no_seil.asunto');
									
									
									$bind = array(':p_res_id' => $this->_SESION->getVariable('RES_ID'));
									$link = $this->_ORA->ejecutaFunc('rso.rso_obtener_pkg.fun_getLink',$bind);
									$this->_LOG->log('LINK RESOLUCION: '. $link);

									$plantilla_destinatario->assign('TRATAMIENTO', $des['TRA_NOMBRE']);
									$plantilla_destinatario->assign('NOMBRE_USUARIO', $des['DES_NOMBRE']);
									$plantilla_destinatario->assign('LINK_RESOLUCION', $link."&des=".$des['DES_ID']."&com=".substr(md5($des['DES_ID']),7,3));
									$plantilla_destinatario->parse('not_destinatario_no_seil.texto');
									$correo_no_seil->TEXTO =  $plantilla_destinatario->text('not_destinatario_no_seil.texto');
									$correo_no_seil->setPara($des['DES_CORREO']);
									$correo_no_seil->enviar();


									$bind = array(':p_des' => $des['DES_ID'], ':p_tipo' => 'otro', ':p_correo' => $correo_no_seil->ID_CORREO);
									$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreo",$bind);





									//$correo_no_seil->setPara($des['DES_CORREO']);
									$this->_LOG->log(__METHOD__." (".__LINE__.") "."Se enviara al correo ingresado ya que es de tipo Otro, y posee");
								}else{
									//Si no tiene correo es porque tiene por mano y direccion
									if($this->fun_esNoElectronico()){
										if(isset($des['DES_DIRECCION']) && strlen(trim($des['DES_DIRECCION'])) > 5){
											//tiene direccion por lo tanto se enviar� por mano, es una situacion extra�a pero factible
											$this->_LOG->error(__METHOD__." (".__LINE__.") "."El documento ".$this->_SESION->getVariable('RES_ID')." es electronico y por mano, tiene direccion fisica y se enviar� solo por ese  medio ".print_r($des,true));
										}else{
											//es electronico y mano pero no tiene ningun metodo de envio
											$this->_LOG->error(__METHOD__." (".__LINE__.") "."El documento ".$this->_SESION->getVariable('RES_ID')." es electronico y por mano pero no tiene forma de envio, se realiza exit: ".print_r($des,true));
											exit();
										}
									}else{
										//significa que es solo electronico y no hay como enviar
									}
								}

							}


							if($existe_correo_seil){

								$plantilla_destinatario->assign('NUMERO_RESOLUCION', $this->_SESION->getVariable('RES_NUMERO_RESOLUCION'));
								$plantilla_destinatario->parse('not_destinatario_seil.asunto');
								$correo_seil->ASUNTO = $plantilla_destinatario->text('not_destinatario_seil.asunto');
								$plantilla_destinatario->assign('DES_NOMBRE',$des['DES_NOMBRE']);
								$plantilla_destinatario->parse('not_destinatario_seil.texto');
								$correo_seil->TEXTO =  $plantilla_destinatario->text('not_destinatario_seil.texto');
								$correo_seil->enviar();
								$bind = array(':p_des' => $des['DES_ID'], ':p_tipo' => 'seil', ':p_correo' => $correo_seil->ID_CORREO);
								$this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreo",$bind);

							}




						}
					}

				}else{
					$this->_LOG->error(__METHOD__." (".__LINE__.") "."El documento ".$this->_SESION->getVariable('RES_ID')." es electronico pero no tiene destinatarios, se realiza exit");
					exit();
				}


			}else{
				$this->_LOG->log(__METHOD__." (".__LINE__.") "."No es Electronico por lo tanto no hay notificacion a los destinatarios");
			}



			//print_r($destinatarios);
			//exit();

			/*Array
(
    [CSGEN] => Array
        (
            [99225000] => Array
                (
                    [RES_ID] => 97253
                    [RES_VERSION] => 1
                    [TIPDES_ID] => seil
                    [DES_ID] => 174
                    [DES_RUT] => 99225000
                    [DES_TIPO_ENT] => CSGEN
                    [DES_NOMBRE] => ACE SEGUROS S.A.
                    [DES_CARGO] => Gerente General
                    [DES_DIRECCION] => MIRAFLORES 222 PISO 16 -17 - Comuna: SANTIAGO -  Ciudad: SANTIAGO
 - Reg. Metropolitana
                    [DES_CON_COPIA] => N
                    [USUARIOS_SEIL] => Array
                        (
                            [0] => Array
                                (
                                    [RUT_ENTIDAD] => 99225000
                                    [RUT_PERSONA] => 15911752
                                    [DG_ENTIDAD] => 3
                                    [COD_TIPO_INFO] => -
                                    [MAIL] => sbelmar@svs.cl
                                    [COD_USUARIO] => 976PZG376
                                    [PASS_USUARIO] => -
                                    [PREGUNTA1] => -
                                    [RESPUESTA1] => -
                                    [PREGUNTA2] =>
                                    [RESPUESTA2] =>
                                    [ACTIVADO] => S
                                    [FECHA_MODIFICACION] =>
                                    [NOMBRE_USUARIO] => CLAUDIO
                                    [FECHA_SOLICITUD] => 28/07/2011
                                    [FECHA_ACTIVACION] => 28/07/2011
                                    [TIPO_USUARIO] => FISC
                                    [COD_USUARIO_ADMIN] => ADM2011244
                                    [CODIGO_AUTORIZACION] => # ??
                                    [PASS_USUARIO_ENC] => ? B?QG??? ??g???
                                    [NOMBRE_USUARIO_C] => CLAUDIO
                                    [COD_USU_ADMIN] => ADM2011244
                                    [FECHA_ULTIMO_USO] =>
                                    [CHECKED] => checked
                                )

                            [1] => Array
                                (
                                    [RUT_ENTIDAD] => 99225000
                                    [RUT_PERSONA] => 15911752
                                    [DG_ENTIDAD] => 3
                                    [COD_TIPO_INFO] => -
                                    [MAIL] => culloa@svs.cl
                                    [COD_USUARIO] => CULLOA
                                    [PASS_USUARIO] => cu123
                                    [PREGUNTA1] => 06
                                    [RESPUESTA1] => claudio
                                    [PREGUNTA2] => 04
                                    [RESPUESTA2] => claudio
                                    [ACTIVADO] => S
                                    [FECHA_MODIFICACION] => 03/09/2015
                                    [NOMBRE_USUARIO] => CLAUDIO ULLOA MERINO
                                    [FECHA_SOLICITUD] => 17/04/2009
                                    [FECHA_ACTIVACION] =>
                                    [TIPO_USUARIO] => FISC
                                    [COD_USUARIO_ADMIN] =>
                                    [CODIGO_AUTORIZACION] =>
                                    [PASS_USUARIO_ENC] => ?w? : R?e? ?? ??
                                    [NOMBRE_USUARIO_C] => CLAUDIO ULLOA MERINO
                                    [COD_USU_ADMIN] =>
                                    [FECHA_ULTIMO_USO] =>
                                    [CHECKED] => checked
                                )

                            [2] => Array
                                (
                                    [RUT_ENTIDAD] => 99225000
                                    [RUT_PERSONA] => 13256082
                                    [DG_ENTIDAD] => 5
                                    [COD_TIPO_INFO] => -
                                    [MAIL] => jsoto@svs.cl
                                    [COD_USUARIO] => JLSOTO
                                    [PASS_USUARIO] => 123
                                    [PREGUNTA1] => 06
                                    [RESPUESTA1] => tonijua
                                    [PREGUNTA2] => 06
                                    [RESPUESTA2] => tonijua
                                    [ACTIVADO] => S
                                    [FECHA_MODIFICACION] => 08/05/2006
                                    [NOMBRE_USUARIO] => Juan Luis Soto V.
                                    [FECHA_SOLICITUD] =>
                                    [FECHA_ACTIVACION] =>
                                    [TIPO_USUARIO] => FISC
                                    [COD_USUARIO_ADMIN] =>
                                    [CODIGO_AUTORIZACION] =>
                                    [PASS_USUARIO_ENC] => $?]X8,R??&/??w?4
                                    [NOMBRE_USUARIO_C] => Juan Luis Soto V.
                                    [COD_USU_ADMIN] =>
                                    [FECHA_ULTIMO_USO] =>
                                    [CHECKED] => checked
                                )

                            [3] => Array
                                (
                                    [RUT_ENTIDAD] => 99225000
                                    [RUT_PERSONA] => 1
                                    [DG_ENTIDAD] => 9
                                    [COD_TIPO_INFO] => -
                                    [MAIL] => culloa@svs.cl
                                    [COD_USUARIO] => P99225000
                                    [PASS_USUARIO] => 123
                                    [PREGUNTA1] => x
                                    [RESPUESTA1] => x
                                    [PREGUNTA2] =>
                                    [RESPUESTA2] =>
                                    [ACTIVADO] => S
                                    [FECHA_MODIFICACION] =>
                                    [NOMBRE_USUARIO] => Usuario prueba99225000
                                    [FECHA_SOLICITUD] =>
                                    [FECHA_ACTIVACION] =>
                                    [TIPO_USUARIO] => FISC
                                    [COD_USUARIO_ADMIN] =>
                                    [CODIGO_AUTORIZACION] =>
                                    [PASS_USUARIO_ENC] => ?L?C!p?a??s
                                    [NOMBRE_USUARIO_C] => Usuario prueba99225000
                                    [COD_USU_ADMIN] =>
                                    [FECHA_ULTIMO_USO] =>
                                    [CHECKED] => checked
                                )

                        )

                )

            [99147000] => Array
                (
                    [RES_ID] => 97253
                    [RES_VERSION] => 1
                    [TIPDES_ID] => seil
                    [DES_ID] => 175
                    [DES_RUT] => 99147000
                    [DES_TIPO_ENT] => CSGEN
                    [DES_NOMBRE] => BCI SEGUROS GENERALES S.A.
                    [DES_CARGO] => Gerente General
                    [DES_DIRECCION] => HUERFANOS 1189 PISOS 2-3 Y 4 - Comuna: SANTIAGO -  Ciudad: SANTIAGO
 - Reg. Metropolitana
                    [DES_CON_COPIA] => N
                    [USUARIOS_SEIL] => Array
                        (
                            [0] => Array
                                (
                                    [RUT_ENTIDAD] => 99147000
                                    [RUT_PERSONA] => 1
                                    [DG_ENTIDAD] => 9
                                    [COD_TIPO_INFO] => -
                                    [MAIL] => jsoto@svs.cl
                                    [COD_USUARIO] => P99147000
                                    [PASS_USUARIO] => 123
                                    [PREGUNTA1] => x
                                    [RESPUESTA1] => x
                                    [PREGUNTA2] =>
                                    [RESPUESTA2] =>
                                    [ACTIVADO] => S
                                    [FECHA_MODIFICACION] =>
                                    [NOMBRE_USUARIO] => Usuario prueba99147000
                                    [FECHA_SOLICITUD] =>
                                    [FECHA_ACTIVACION] =>
                                    [TIPO_USUARIO] => FISC
                                    [COD_USUARIO_ADMIN] =>
                                    [CODIGO_AUTORIZACION] =>
                                    [PASS_USUARIO_ENC] => ????72?]??g
                                    [NOMBRE_USUARIO_C] => Usuario prueba99147000
                                    [COD_USU_ADMIN] =>
                                    [FECHA_ULTIMO_USO] =>
                                    [CHECKED] => checked
                                )

                            [1] => Array
                                (
                                    [DES_ID] => 175
                                    [RES_ID] => 97253
                                    [RES_VERSION] => 1
                                    [USUENV_USUARIO] =>
                                    [USUENV_TIPO] => otro
                                    [USUENV_NOMBRE] => claudio ulloa
                                    [USUENV_DIRECCION] => HUERFANOS 1189 PISOS 2-3 Y 4 - Comuna: SANTIAGO
 -  Ciudad: SANTIAGO - Reg. Metropolitana
                                    [USUENV_EMAIL] => culloa@svs.cl
                                    [CHECKED] => checked
                                )

                        )

                )

        )

    [OTRCU] => Array
        (
            [15911752-9] => Array
                (
                    [DES_RUT] => 15911752-9
                    [DES_NOMBRE] => CLAUDIO AGREGAR OTROssss
                    [DES_DIRECCION] => LAGO DEL PETROHUE
                    [DES_CORREO] => culloa@svs.cl
                    [TRA_ID] => ustre
                    [TRA_NOMBRE] => ILUSTRE
                    [DES_TIPO_ENT] => OTRCU
                    [DES_CON_COPIA] => N
                    [RAND] => N
					[DES_ID] = 23
                )

        )

)
		*/


		}





		public function getUrl($sgd)
		{
			//2015060069823  --> documento base, con partes 
			//2015030157220  --> documento sistema old
			
			
			
			$codigo =   md5(md5($sgd)).base64_encode(base64_encode(base64_encode($sgd)));
			$url = '/intranet/aplic/serdoc/ver_sgd.php?s567=';
			$PRE = '';
			if($this->_AMBIENTE == 'DESA'){
				$PRE ='http://palto.svs.local';
			}
			if($this->_AMBIENTE == 'TEST'){
				$PRE ='http://pimiento.svs.local';
			}
			if($this->_AMBIENTE == 'PROD'){
				$PRE ='http://intranet.svs.local';
			}
			
			
			return $PRE.$url.$codigo."&secuencia=-1";
		}



		
		
		
		public function enviarNotificacionInternaSinSes($data_res){		
		
			$tipos_de_envio = $data_res['ENV_ID'];
			if(isset($tipos_de_envio['elect'])){
				unset($tipos_de_envio['elect']);
			}
			$NoEsElectronico = (count($tipos_de_envio) > 0) ? true : false;
			
			$es_electronico = false;
			foreach($data_res['ENV_ID'] as $key => $tipo){
				if($key == 'elect'){
					$es_electronico = true;
				}
			}
			$AMBIENTE = $this->_ORA->ejecutaFunc('ambiente');
		
		
			$this->_LOG->log(__METHOD__." (".__LINE__.") DATOS ".print_r($data_res,true));
			$reservados  = ''; //$this->_SESION->getVariable('USR_RESERVADOS'); //AUN CUANDO NO SEA RESERVADO IGUAL QUEDA ESTA VARIABLE EN SESION
			$reservados  = (is_array($reservados)) ? $reservados : array();
			
			
			$bind = array(':p_res_id' => $data_res['RES_ID']);
			$cursor = $this->_ORA->retornaCursor('RSO.RSO_OBTENER_PKG.fun_getNotificacionInterna','function',$bind);
			$participantes = $this->_ORA->FetchAll($cursor);
				
			
			
			$bind = array(':p_res_id' => $data_res['RES_ID'], ':p_version' => $data_res['RES_VERSION']);
			$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_OBTENER_PKG.fun_getEncargadosUnidad','function',$bind);
			$encargados_unidad = $this->_ORA->FetchAll($cursor);
			$this->_LOG->log(__METHOD__." (".__LINE__.") LISTO LOS ENCARGADOS");
			$this->_LOG->log(__METHOD__." (".__LINE__.") PARTICIPANTES:".print_r($participantes,true));
			$this->_LOG->log(__METHOD__." (".__LINE__.") ENCARGADOS DE UNIDAD:".print_r($encargado_unidad,true));
			
			$cantidad_participantes = 0;
			$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_interna.html');
			$TIPO = ($data_res['TIPRES_ID'] == 'exenta') ? 'Exenta' : 'Afecta';
			$plantilla_interna->assign('RES_ID',$data_res['RES_ID']);
			$plantilla_interna->assign('TIPO',$TIPO);
			$plantilla_interna->assign('NUMERO_RESOLUCION',$data_res['NUMERO_RESOLUCION']);
			$plantilla_interna->assign('LINK_RESOLUCION',$this->getUrl($data_res['NUMERO_SGD']));
			$plantilla_interna->parse('asunto');
			$RES_REFERENCIA = (is_object($data_res['RES_REFERENCIA'])) ? $data_res['RES_REFERENCIA']->load() : $data_res['RES_REFERENCIA'] ;
			$plantilla_interna->assign('RES_MATERIA',html_entity_decode(strip_tags($RES_REFERENCIA)));
			$cla_ids = $data_res['CLA_ID'];
			$this->_LOG->log(__METHOD__." (".__LINE__.") clasificaciones ".print_r($cla_ids,true));
			
			
			foreach($cla_ids as $cla_id){		
				$bind = array(':p_cla_id' => $cla_id);
				$cursor_cla = $this->_ORA->retornaCursor('RSO_CLASIFICACION_PKG.fun_getClasificacion','function',$bind);
				$data_cla = $this->_ORA->FetchArray($cursor_cla);
				$plantilla_interna->assign('RES_CLASIFICACION', $data_cla['CLA_NOMBRE']);
				$plantilla_interna->parse('texto.clasificacion');
			}

			if(is_array($participantes) || is_array($encargados_unidad)){
				$cant_participantes =  (is_array($participantes)) ? count($participantes) : 0;
				$cant_encargados_unidad =  (is_array($encargados_unidad)) ? count($encargados_unidad) : 0;

				if(($cant_participantes + $cant_encargados_unidad) > 0){
					$correo = new Correo();
					$correo->ORA = $this->_ORA;
					$correo->APLIC = 'PURSO';
					$correo->FIRMADO = false;
					$correo->ASUNTO = $plantilla_interna->text('asunto');
				}

				if($cant_participantes > 0){
					foreach($participantes as $participante){
						$cantidad_participantes++;

						if($participante['NOTINT_VIGENTE'] == 'S'){

							$reservados[$participante['NOTINT_USUARIO']] = 1;
							/*$bind = array(":usuario" => $participante['NOTINT_USUARIO']);
							$notificacion_nombre = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
							$plantilla_interna->assign('NOMBRE_USUARIO',$notificacion_nombre);
							*/


							$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
							$bind = array(":usuario" => $participante['NOTINT_USUARIO']);
							$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);

							if($AMBIENTE == 'DESA' /*|| $AMBIENTE == 'TEST'*/){
								$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
								$plantilla_interna->parse('texto.debug');
								$correo->setPara('culloa@svs.cl');
							}else{
								$correo->setPara($CORREO_DESTINO);
							}


						}
					}
				}
				//$this->_SESION->setVariable('USR_RESERVADOS',$reservados);
				if($cant_encargados_unidad > 0){
					foreach($encargados_unidad as $encargado_unidad){
						$cantidad_participantes++;

						if($encargado_unidad['CHECKED'] == 'S'){


							/*$bind = array(":usuario" => $participante['NOTINT_USUARIO']);
							$notificacion_nombre = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
							$plantilla_interna->assign('NOMBRE_USUARIO',$notificacion_nombre);
							*/


							$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
							$CORREO_DESTINO = $encargado_unidad['PARUNI_VALOR'];

							if($AMBIENTE == 'DESA' || $AMBIENTE == 'TEST'){
								$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
								$plantilla_interna->parse('texto.debug');
								$correo->setPara('culloa@svs.cl');
							}else{
								$correo->setPara($CORREO_DESTINO);
							}


						}
					}
				}



				if(($cant_participantes + $cant_encargados_unidad) > 0){
					$plantilla_interna->parse('texto');
					$correo->TEXTO = $plantilla_interna->text('texto');
					$ID_CORREO = $correo->enviar();
					$bind = array(":res_id" => $data_res['RES_ID'], ":p_id_correo" => $correo->ID_CORREO);
					$CORREO_DESTINO = $this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreoNotificacion",$bind);
				}
			}


			//preguntar si la resolucion es privada y por mano para enviar correo de js
			
			
			if($data_res['PRI_ID'] == 'reser' && $NoEsElectronico){

				//se debe obtener el redactor.



				$REDACTOR = $data_res['RES_REDACTOR'];

				$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_reserv_mano.html');
				$not_reser = new Correo();
				$not_reser->ORA = $this->_ORA;
				$not_reser->APLIC = 'PURSO';
				$not_reser->FIRMADO = false;
				$not_reser->DESDE_NOMBRE = 'Resoluciones Electronicas';
				$bind = array(':redactor' => $REDACTOR);
				$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
				$NOMBRE_USUARIO = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);

				$plantilla_interna->assign('NOMBRE_DESTINO',$NOMBRE_USUARIO);
				$plantilla_interna->assign('NUMERO_RESOLUCION',$data_res['NUMERO_RESOLUCION']);
				$plantilla_interna->assign('RES_ID',$data_res['RES_ID']);

				$plantilla_interna->parse('asunto');
				$not_reser->ASUNTO = $plantilla_interna->text('asunto');
				$plantilla_interna->parse('texto');
				$not_reser->TEXTO =  $plantilla_interna->text('texto');

				if($AMBIENTE == 'DESA' /*|| $AMBIENTE == 'TEST'*/){
					$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
					$plantilla_interna->parse('texto.debug');
					$not_reser->setPara('culloa@svs.cl');
				}else{
					$not_reser->setPara($CORREO_DESTINO);
				}
				$not_reser->enviar();
			}
		}
		
		

		public function enviarNotificacionInterna(){		
			//require_once('class/mostrarDocumento.class.php');
			$reservados  = $this->_SESION->getVariable('USR_RESERVADOS'); //AUN CUANDO NO SEA RESERVADO IGUAL QUEDA ESTA VARIABLE EN SESION
			$reservados  = (is_array($reservados)) ? $reservados : array();
			$participantes = $this->_SESION->getVariable('NOTIFICACIONES_INTERNAS');
			$encargados_unidad = $this->_SESION->getVariable('ENCARGADOS_UNIDAD');
			$cantidad_participantes = 0;
			$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_interna.html');
			$TIPO = ($this->_SESION->getVariable('TIPRES_ID') == 'exenta') ? 'Exenta' : 'Afecta';
			$plantilla_interna->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));
			$plantilla_interna->assign('TIPO',$TIPO);
			$plantilla_interna->assign('NUMERO_RESOLUCION',$this->_SESION->getVariable('NUMERO_RESOLUCION'));
			$plantilla_interna->assign('LINK_RESOLUCION',$this->getUrl($this->_SESION->getVariable('NUMERO_SGD')));
			$plantilla_interna->parse('asunto');
			$plantilla_interna->assign('RES_MATERIA',html_entity_decode(strip_tags($this->_SESION->getVariable('RES_REFERENCIA'))));
			$cla_ids = $this->_SESION->getVariable('CLA_ID');
			foreach($cla_ids as $cla_id){		
				$bind = array(':p_cla_id' => $cla_id);
				$cursor_cla = $this->_ORA->retornaCursor('RSO_CLASIFICACION_PKG.fun_getClasificacion','function',$bind);
				$data_cla = $this->_ORA->FetchArray($cursor_cla);
				$plantilla_interna->assign('RES_CLASIFICACION', $data_cla['CLA_NOMBRE']);
				$plantilla_interna->parse('texto.clasificacion');
			}




			if(is_array($participantes) || is_array($encargados_unidad)){
				$cant_participantes =  (is_array($participantes)) ? count($participantes) : 0;
				$cant_encargados_unidad =  (is_array($encargados_unidad)) ? count($encargados_unidad) : 0;

				if(($cant_participantes + $cant_encargados_unidad) > 0){
					$correo = new Correo();
					$correo->ORA = $this->_ORA;
					$correo->APLIC = 'PURSO';
					$correo->FIRMADO = false;
					$correo->ASUNTO = $plantilla_interna->text('asunto');
				}

				if($cant_participantes > 0){
					foreach($participantes as $participante){
						$cantidad_participantes++;

						if($participante['NOTINT_VIGENTE'] == 'S'){

							$reservados[$participante['NOTINT_USUARIO']] = 1;
							/*$bind = array(":usuario" => $participante['NOTINT_USUARIO']);
							$notificacion_nombre = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
							$plantilla_interna->assign('NOMBRE_USUARIO',$notificacion_nombre);
							*/


							$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
							$bind = array(":usuario" => $participante['NOTINT_USUARIO']);
							$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);

							if($this->_AMBIENTE == 'DESA' /*|| $this->_AMBIENTE == 'TEST'*/){
								$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
								$plantilla_interna->parse('texto.debug');
								$correo->setPara('culloa@svs.cl');
							}else{
								$correo->setPara($CORREO_DESTINO);
							}


						}
					}
				}
				$this->_SESION->setVariable('USR_RESERVADOS',$reservados);
				if($cant_encargados_unidad > 0){
					foreach($encargados_unidad as $encargado_unidad){
						$cantidad_participantes++;

						if($encargado_unidad['CHECKED'] == 'S'){


							/*$bind = array(":usuario" => $participante['NOTINT_USUARIO']);
							$notificacion_nombre = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
							$plantilla_interna->assign('NOMBRE_USUARIO',$notificacion_nombre);
							*/


							$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
							$CORREO_DESTINO = $encargado_unidad['PARUNI_VALOR'];

							if($this->_AMBIENTE == 'DESA' || $this->_AMBIENTE == 'TEST'){
								$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
								$plantilla_interna->parse('texto.debug');
								$correo->setPara('culloa@svs.cl');
							}else{
								$correo->setPara($CORREO_DESTINO);
							}


						}
					}
				}



				if(($cant_participantes + $cant_encargados_unidad) > 0){
					$plantilla_interna->parse('texto');
					$correo->TEXTO = $plantilla_interna->text('texto');
					$ID_CORREO = $correo->enviar();
					$bind = array(":res_id" => $this->_SESION->getVariable('RES_ID'), ":p_id_correo" => $correo->ID_CORREO);
					$CORREO_DESTINO = $this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreoNotificacion",$bind);
				}
			}


			//preguntar si la resolucion es privada y por mano para enviar correo de js
			if($this->_SESION->getVariable('PRI_ID') == 'reser' && $this->fun_esNoElectronico()){

				//se debe obtener el redactor.



				$REDACTOR = $this->_SESION->getVariable('RES_REDACTOR');

				$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_reserv_mano.html');
				$not_reser = new Correo();
				$not_reser->ORA = $this->_ORA;
				$not_reser->APLIC = 'PURSO';
				$not_reser->FIRMADO = false;
				$not_reser->DESDE_NOMBRE = 'Resoluciones Electronicas';
				$bind = array(':redactor' => $REDACTOR);
				$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
				$NOMBRE_USUARIO = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);

				$plantilla_interna->assign('NOMBRE_DESTINO',$NOMBRE_USUARIO);
				$plantilla_interna->assign('NUMERO_RESOLUCION',$this->_SESION->getVariable('NUMERO_RESOLUCION'));
				$plantilla_interna->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));

				$plantilla_interna->parse('asunto');
				$not_reser->ASUNTO = $plantilla_interna->text('asunto');
				$plantilla_interna->parse('texto');
				$not_reser->TEXTO =  $plantilla_interna->text('texto');

				if($this->_AMBIENTE == 'DESA' /*|| $this->_AMBIENTE == 'TEST'*/){
					$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
					$plantilla_interna->parse('texto.debug');
					$not_reser->setPara('culloa@svs.cl');
				}else{
					$not_reser->setPara($CORREO_DESTINO);
				}
				$not_reser->enviar();
			}
		}
		
		
		public function enviarNotificacionInternaPostDocumento(){			
			$reservados  = $this->_SESION->getVariable('USR_RESERVADOS'); //AUN CUANDO NO SEA RESERVADO IGUAL QUEDA ESTA VARIABLE EN SESION
			$reservados  = (is_array($reservados)) ? $reservados : array();
			$participantes = $this->_SESION->getVariable('NOTIFICACIONES_INTERNAS');
			$encargados_unidad = $this->_SESION->getVariable('ENCARGADOS_UNIDAD');
			$cantidad_participantes = 0;
			$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_interna_post_documento.html');
			$TIPO = ($this->_SESION->getVariable('TIPRES_ID') == 'exenta') ? 'Exenta' : 'Afecta';
			$plantilla_interna->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));
			//$plantilla_interna->assign('TIPO',$TIPO);
			$plantilla_interna->assign('NUMERO_RESOLUCION',$this->_SESION->getVariable('RES_NUMERO_RESOLUCION'));
			$plantilla_interna->parse('asunto');			
			$plantilla_interna->assign('RES_MATERIA',html_entity_decode(strip_tags($this->_SESION->getVariable('RES_REFERENCIA'))));
			$cla_ids = $this->_SESION->getVariable('CLA_ID');
			foreach($cla_ids as $cla_id){		
				$bind = array(':p_cla_id' => $cla_id);
				$cursor_cla = $this->_ORA->retornaCursor('RSO_CLASIFICACION_PKG.fun_getClasificacion','function',$bind);
				$data_cla = $this->_ORA->FetchArray($cursor_cla);
				$plantilla_interna->assign('RES_CLASIFICACION', $data_cla['CLA_NOMBRE']);
				$plantilla_interna->parse('texto.clasificacion');
			}




			if(is_array($participantes) || is_array($encargados_unidad)){
				$cant_participantes =  (is_array($participantes)) ? count($participantes) : 0;
				$cant_encargados_unidad =  (is_array($encargados_unidad)) ? count($encargados_unidad) : 0;

				if(($cant_participantes + $cant_encargados_unidad) > 0){
					$correo = new Correo();
					$correo->ORA = $this->_ORA;
					$correo->APLIC = 'PURSO';
					$correo->FIRMADO = false;
					$correo->ASUNTO = $plantilla_interna->text('asunto');
				}

				if($cant_participantes > 0){
					foreach($participantes as $participante){
						$cantidad_participantes++;

						if($participante['NOTINT_VIGENTE'] == 'S'){

							$reservados[$participante['NOTINT_USUARIO']] = 1;
							/*$bind = array(":usuario" => $participante['NOTINT_USUARIO']);
							$notificacion_nombre = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
							$plantilla_interna->assign('NOMBRE_USUARIO',$notificacion_nombre);
							*/


							$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
							$bind = array(":usuario" => $participante['NOTINT_USUARIO']);
							$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);

							if($this->_AMBIENTE == 'DESA' /*|| $this->_AMBIENTE == 'TEST'*/){
								$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
								$plantilla_interna->parse('texto.debug');
								$correo->setPara('culloa@svs.cl');
							}else{
								$correo->setPara($CORREO_DESTINO);
							}


						}
					}
				}
				$this->_SESION->setVariable('USR_RESERVADOS',$reservados);
				if($cant_encargados_unidad > 0){
					foreach($encargados_unidad as $encargado_unidad){
						$cantidad_participantes++;

						if($encargado_unidad['CHECKED'] == 'S'){


							/*$bind = array(":usuario" => $participante['NOTINT_USUARIO']);
							$notificacion_nombre = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);
							$plantilla_interna->assign('NOMBRE_USUARIO',$notificacion_nombre);
							*/


							$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
							$CORREO_DESTINO = $encargado_unidad['PARUNI_VALOR'];

							if($this->_AMBIENTE == 'DESA' /*|| $this->_AMBIENTE == 'TEST'*/){
								$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
								$plantilla_interna->parse('texto.debug');
								$correo->setPara('culloa@svs.cl');
							}else{
								$correo->setPara($CORREO_DESTINO);
							}


						}
					}
				}



				if(($cant_participantes + $cant_encargados_unidad) > 0){
					$plantilla_interna->parse('texto');
					$correo->TEXTO = $plantilla_interna->text('texto');
					$ID_CORREO = $correo->enviar();
					$bind = array(":res_id" => $this->_SESION->getVariable('RES_ID'), ":p_id_correo" => $correo->ID_CORREO);
					$CORREO_DESTINO = $this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA."RSO_GUARDAR_PKG.fun_guardaIdCorreoNotificacion",$bind);
				}
			}


			//preguntar si la resolucion es privada y por mano para enviar correo de js
			if($this->_SESION->getVariable('PRI_ID') == 'reser' && $this->fun_esNoElectronico()){

				//se debe obtener el redactor.



				$REDACTOR = $this->_SESION->getVariable('RES_REDACTOR');

				$plantilla_interna = new XTemplate(dirname (__FILE__).'/../correos/notificacion_reserv_mano.html');
				$not_reser = new Correo();
				$not_reser->ORA = $this->_ORA;
				$not_reser->APLIC = 'PURSO';
				$not_reser->FIRMADO = false;
				$not_reser->DESDE_NOMBRE = 'Resoluciones Electronicas';
				$bind = array(':redactor' => $REDACTOR);
				$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
				$NOMBRE_USUARIO = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);

				$plantilla_interna->assign('NOMBRE_DESTINO',$NOMBRE_USUARIO);
				$plantilla_interna->assign('NUMERO_RESOLUCION',$this->_SESION->getVariable('NUMERO_RESOLUCION'));
				$plantilla_interna->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));

				$plantilla_interna->parse('asunto');
				$not_reser->ASUNTO = $plantilla_interna->text('asunto');
				

				if($this->_AMBIENTE == 'DESA' /*|| $this->_AMBIENTE == 'TEST*/){
					$plantilla_interna->assign('CORREO_DESTINO',$CORREO_DESTINO);
					$plantilla_interna->parse('texto.debug');
					$not_reser->setPara('culloa@svs.cl');
				}else{
					$not_reser->setPara($CORREO_DESTINO);
				}
				
				$plantilla_interna->parse('texto');
				$not_reser->TEXTO =  $plantilla_interna->text('texto');
				$not_reser->enviar();
			}
		}
		
		
		function enviarNotificacionOFPartesSinSes($data_res){
			$tipos_de_envio = $data_res['ENV_ID'];
			if(isset($tipos_de_envio['elect'])){
				unset($tipos_de_envio['elect']);
			}
			$NoEsElectronico = (count($tipos_de_envio) > 0) ? true : false;
			$AMBIENTE = $this->_ORA->ejecutaFunc('ambiente');
			$this->_LOG->log(__METHOD__.' ('.__LINE__.') '.$AMBIENTE);
			if($NoEsElectronico){
				$bind = array(':p_rol' => 'OFPARTES');
				$cursor = $this->_ORA->retornaCursor('DOC2.doc_roles_documento_pkg.fun_usuarios_roles','function',$bind);		
				$USUARIOS = array();
				$correo = new Correo();
				$correo->ORA = $this->_ORA;
				$correo->APLIC = 'PURSO';
				$correo->FIRMADO = false;
				$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
				$plantilla_correo = new XTemplate(dirname (__FILE__).'/../correos/envio_resolucion_ofpartes.html');		
				while($data = $this->_ORA->FetchArray($cursor)){				
				
					$bind = array(':redactor' => $data['EP_USUARIO']);
					$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
					$NOMBRE_USUARIO = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);

					if($AMBIENTE == 'DESA' || $AMBIENTE == 'TEST'){
						$plantilla_correo->assign('CORREO_DESTINO',$CORREO_DESTINO);
						$plantilla_correo->parse('texto.debug');
						$correo->setPara('culloa@svs.cl');
					}else{
						$correo->setPara($CORREO_DESTINO);
					}
				}
				$plantilla_correo->assign('RES_NUMERO_RESOLUCION',$data_res['RES_NUMERO_RESOLUCION']);
				$plantilla_correo->assign('RES_ID',$data_res['RES_ID']);
				$plantilla_correo->parse('asunto');
				$correo->ASUNTO = $plantilla_correo->text('asunto');
				$plantilla_correo->parse('texto');
				$correo->TEXTO =  $plantilla_correo->text('texto');
				$correo->enviar();
			}
			$this->_LOG->log(__METHOD__.' ('.__LINE__.') Despues de la consulta');
		}
		
		
		
		function enviarNotificacionOFPartes(){
			if($this->fun_esNoElectronico()){
				$bind = array(':p_rol' => 'OFPARTES');
				$cursor = $this->_ORA->retornaCursor('DOC2.doc_roles_documento_pkg.fun_usuarios_roles','function',$bind);		
				$USUARIOS = array();
				$correo = new Correo();
				$correo->ORA = $this->_ORA;
				$correo->APLIC = 'PURSO';
				$correo->FIRMADO = false;
				$correo->DESDE_NOMBRE = 'Resoluciones Electronicas';
				$plantilla_correo = new XTemplate(dirname (__FILE__).'/../correos/envio_resolucion_ofpartes.html');		
				while($data = $this->_ORA->FetchArray($cursor)){				
				
					$bind = array(':redactor' => $data['EP_USUARIO']);
					$CORREO_DESTINO = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind);
					$NOMBRE_USUARIO = $this->_ORA->ejecutaFunc("wfa_usr.getNombreUsuario",$bind);

					//$plantilla_correo->assign('NOMBRE_DESTINO',$NOMBRE_USUARIO);
					

					
					

					if($this->_AMBIENTE == 'DESA' || $this->_AMBIENTE == 'TEST'){
						$plantilla_correo->assign('CORREO_DESTINO',$CORREO_DESTINO);
						$plantilla_correo->parse('texto.debug');
						$correo->setPara('culloa@svs.cl');
					}else{
						$correo->setPara($CORREO_DESTINO);
					}
				}
				$plantilla_correo->assign('RES_NUMERO_RESOLUCION',$this->_SESION->getVariable('RES_NUMERO_RESOLUCION'));
				$plantilla_correo->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));
				$plantilla_correo->parse('asunto');
				$correo->ASUNTO = $plantilla_correo->text('asunto');
				$plantilla_correo->parse('texto');
				$correo->TEXTO =  $plantilla_correo->text('texto');
				$correo->enviar();
			}
		}
	}
?>