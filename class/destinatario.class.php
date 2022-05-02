<?php

	class Destinatario extends ClaseSistema{
		

		/*
		 * Funcion: getDestinatariosTipo
		 * Busca los fiscalizados una vez que se selecciona el tipo de entidad en el paso1 de resoluciones
		 */
		public function getDestinatariosTipo(){
			
			

			try{
				$json = array();
				$json['RESULTADO'] = 'OK';			
				$MENSAJES = array();
				$CAMBIA = array();			
				$MENSAJES[] = 'Tipo de Entidad: '.$_POST['tipo_entidad'];
				
				
				$bind_te = array(':tipo_entidad' => $_POST['tipo_entidad']);
				$cursor_entidad = $this->_ORA->retornaCursor('FEL_OBTENER_DATOS.getTipoEntidad','function',$bind_te);
				$data_ent = $this->_ORA->FetchArray($cursor_entidad);
		
		
	
	
				$bind = array(':tipo_entidad' => $_POST['tipo_entidad'],':filtro' => $_POST['texto']);
				$cursor = $this->_ORA->retornaCursor('PUB_OBTENER_DATOS.fun_getFiscalizados','function',$bind);			
				$i=0;		
				$this->_TEMPLATE->assign('DISTRIBUCION',$_POST['distribucion']);
				$this->_TEMPLATE->assign('DES_TIPO_ENT',$_POST['tipo_entidad']);
					
				$lista_distribucion = $this->_SESION->getVariable('DESTINATARIO');
				$seleccionados = (isset($lista_distribucion[$_POST['tipo_entidad']])) ? $lista_distribucion[$_POST['tipo_entidad']] : array();
				$TOTAL = 0;
				
				$data = array();
				while($data_aux = $this->_ORA->FetchArray($cursor)){
						$data[] = $data_aux;
				}
				

				//------------------------------->

				if($_POST['tipo_entidad'] == 'OPUBL'){
					$cursor = $this->_ORA->retornaCursor('FED.FEL_DOC_DIGITAL_PKG.fun_getEntidades','function');	
					$data_aux = array();
					while($registro = $this->_ORA->FetchArray($cursor)){
						if($registro['VIGENTE'] == 'S'){
							$data1_aux = array();
							//$data1_aux[0] = (strlen($registro['RUT_ENTIDAD']) > 1) ? $registro['RUT_ENTIDAD'] : $registro['ENTIDAD_ID'];
							$data1_aux['RUT'] = (strlen($registro['RUT_ENTIDAD']) > 1) ? $registro['RUT_ENTIDAD'] : $registro['ENTIDAD_ID'];
							//$data1_aux[1] =$registro['ENTIDAD_NOMBRE'];
							$data1_aux['FISCALIZADO'] =$registro['ENTIDAD_NOMBRE'];
							//$data1_aux[2] = 'Doc Digital';
							$data1_aux['DIRECCION'] = 'DocDigital';
							//$data1_aux[3] = '';
							$data1_aux['CARGO'] = '';
							//$data1_aux[4] = '';
							$data1_aux['CANTIDAD'] = 'DocDig';
							//$data1_aux[5] = '';
							$data1_aux['USUARIO'] = '';
							//$data1_aux[6] = '';
							$data1_aux['CORREO'] = '';
							//$data1_aux[7] = 'DOCDIGITAL';
							$data1_aux['MEDIO_ENVIO'] = 'DOCDIGITAL';
							$data_aux[] = $data1_aux;
						}
					}
					
					$RUT_EXISTENTES = array();
					
					$data_final = array();
					foreach($data as $registro){
						$existe = false;
						foreach($data_aux as $registro_aux){
							if($registro['RUT'] == $registro_aux['RUT']){
								$existe = TRUE;
								$data_final[] = $registro_aux;
								$RUT_EXISTENTES[$registro_aux['RUT']] = 1;
							}
						}
						if(!$existe){
							//$registro[] = $data_ent[0]['TIPENT_ENVIO_ELECTRONICO'];
							$registro['MEDIO_ENVIO'] = $data_ent['TIPENT_ENVIO_ELECTRONICO'];
							$data_final[] = $registro;
							$RUT_EXISTENTES[$registro['RUT']] = 1;
						}
					}
					foreach($data_aux as $reg){
						if(isset($RUT_EXISTENTES[$reg['RUT']])){
						}else{
							$data_final[] = $reg;
						}
					}
					
					$data_final_1 = array();
					if(strlen($filtroEntidad)>0){
						foreach($data_final as $registro){
							if(strripos($this->eliminar_acentos($registro['FISCALIZADO']),$this->eliminar_acentos($f)) !== false){
								$data_final_1[] = $registro;
							}
						}
						$data_final = $data_final_1;
					
					}
					
					
				}else{
					foreach($data as $key => $registro){
						$data[$key][7] = $data_ent[0]['TIPENT_ENVIO_ELECTRONICO'];
						$data[$key]['MEDIO_ENVIO'] = $data_ent[0]['TIPENT_ENVIO_ELECTRONICO'];
					
					}
					$data_final= $data;
					//$data_final[] = $data_ent[0]['TIPENT_ENVIO_ELECTRONICO'];
					//$data_final['MEDIO_ENVIO'] = $data_ent[0]['TIPENT_ENVIO_ELECTRONICO'];
				}

				//print_r($data_final);
				//exit();







			//------------------------------->


				//ya no es data pero despues lo veo
				foreach($data_final as $data){
					$TOTAL++;	
					
					//ml: me permite checkear los usario que estan en mi bbdd (tabla: GDE_DISTRIBUCION)
					if($this->_SESION->getVariable("ACCION_CERTIFICADO") == 'M'){

						$wf = $this->_SESION->getVariable("WF");	
						$version = $this->_SESION->getVariable("VERSION_CERTIFICADO");
						$tipo_entidad = $_POST['tipo_entidad'];
						$rut = $data['RUT'];	

						$bind = array(":p_doc_id"=> $wf,
                        ":p_version" => $version,
                        ":p_tipo_entidad" => $tipo_entidad,
                        ":p_rut" => $rut
                   		 );
                
                		$existe = $this->_ORA->ejecutaFunc("GDE.gde_distribucion_pkg.fun_existe_distribucion", $bind);

						if($existe > 0){
							$data['CHECKED'] = 'checked';
							$data['DISABLED'] = 'disabled';
						}else{
							$data['CHECKED'] = '';
							$data['DISABLED'] = '';
						}		
					
					}else if($this->_SESION->getVariable("ACCION_CERTIFICADO") == 'N'){
						$data['CHECKED'] = '';
						$data['DISABLED'] = '';
					}else{
						$data['CHECKED'] = (isset($seleccionados[$data['RUT']])) ? 'checked' : '';
						$data['DISABLED'] = (isset($seleccionados[$data['RUT']])) ? 'disabled' : '';	
						
					}	
					
					
					$i++;
					if($i%2 == 0){
						$data['COLOR'] = '#FFFFFF';
					}else{
						$data['COLOR'] = '#F2F2F2';
					}
				
					if($data['CANTIDAD'] == 0){
						$data['COLOR_USUARIO'] = '#FF0000';
					}
					if($data['CANTIDAD'] == 'DocDig'){
						$data['COLOR_USUARIO'] = '#252EC8';
					}
				
					$data['LETRA_INICIAL'] = strtoupper(substr(str_replace(array('Á','É','Í','Ó','Ú'),array('A','E','I','O','U'),$data['FISCALIZADO']),0,1));
					
					$data['DES_NOMBRE'] = $data['FISCALIZADO'];
					$data['DES_DIRECCION'] = $data['DIRECCION'];
					
					$data['DES_RUT'] = $data['RUT'];
					$data['DES_CARGO'] = $data['CARGO'];
					$data['MEDIO_ENVIO'] = $data['MEDIO_ENVIO'];

				
	
					//echo "<pre>";var_dump($data);echo "</pre>";exit();
					
					$this->_TEMPLATE->assign('FISCALIZADO',$data);
					$this->_TEMPLATE->parse('main.paso1.div_dialogPara.fiscalizado');
					//print_r($data);
					
				}

				//echo "<pre>";var_dump("EL TIPO DE ENTIDAD ES :".$_POST['tipo_entidad']);echo "</pre>"; 
				//echo "<pre>";var_dump("LOS DESTINATARIOS SON :");echo "</pre>"; 
				//echo "<pre>";var_dump($data_final);echo "</pre>";
				//exit();


				
				
				$this->_TEMPLATE->parse('main.paso1.div_dialogPara');
				
				//echo $this->_TEMPLATE->text('main.paso1.div_dialogPara');
				$CAMBIA['#div_dialogPara'] = $this->_TEMPLATE->text('main.paso1.div_dialogPara');
				$MENSAJES[] = 'Total de Entidades son: '.$TOTAL;
				$json['MENSAJES'] =  $MENSAJES;
				$json['CAMBIA'] = $CAMBIA;
				$OPEN = array();
				$OPEN['#div_dialogPara'] = 'open';
				//$json['CAMBIA'] = $CAMBIA;
				$json['OPEN'] = $OPEN;
				return json_encode($json);
			}catch(Exception $e){
				print_r($e);
			}
		}
		
			
			
		//usado en el autocompletado	
		public function agregarSeleccionadoDestinatario(){
			
			//echo "<pre>";var_dump("INICIO");echo "</pre>";
			//echo "<pre>";var_dump($_POST['medio_envio']);echo "</pre>";
			//echo "<pre>";var_dump("FIN");echo "</pre>";
			
			

			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();			
			$MENSAJES[] = 'Ingresando para agregar Destinatario: '.print_r($_POST['checkbox_fiscalizado'],true);			
			$lista_distribucion = $this->_SESION->getVariable('DESTINATARIO');
			
			
			
			
			$MENSAJES[] = 'Existen actualmente '.count($lista_distribucion ).' entidades';			
			$arreglo_tipo_entidad = (isset($lista_distribucion[$_POST['hidden_tipoEntidad']])) ? $lista_distribucion[$_POST['hidden_tipoEntidad']] :array();
			$checkbox_fiscalizado = (is_array($_POST['checkbox_fiscalizado'])) ? $_POST['checkbox_fiscalizado'] : array();
			$MENSAJES[] = 'El arreglo de tipo de entidad es: '.print_r($arreglo_tipo_entidad,true);
			$MENSAJES[] = 'El tipo de distribucion enviado es: '.$_POST['hidden_tipoDistribucion'];			
			
			foreach($checkbox_fiscalizado as $rut){
				$MENSAJES[] = 'Rut es'.$rut;
				$arreglo_piezas = explode('&|C',$_POST['hidden_'.$rut]);
				$arreglo_rut = array();
				$arreglo_rut['DES_RUT'] = $rut;
				$arreglo_rut['DES_NOMBRE'] = $arreglo_piezas[1];
				$arreglo_rut['DES_DIRECCION'] = $arreglo_piezas[0];
				$arreglo_rut['DES_CARGO'] = $arreglo_piezas[2];
				$arreglo_rut['DES_TIPO_ENT'] = $_POST['hidden_tipoEntidad'];
				$arreglo_rut['DES_CON_COPIA'] = ((int)$_POST['hidden_tipoDistribucion'] === 1) ? 'N' : 'S';

				$arreglo_rut['MEDIO_ENVIO'] = $_POST['medio_envio'];

				if(isset($arreglo_piezas[3]) && strlen($arreglo_piezas[3]) > 5){
					$arreglo_rut['CORREO'] = $arreglo_piezas[3];
				}
				
				$usuarios = $this->getUsuariosSeilEntidad($rut);
				foreach($usuarios as $key => $usuario){
					$usuarios[$key]['CHECKED'] = 'checked';
				}
				$arreglo_rut['USUARIOS_SEIL'] = $usuarios;
				$arreglo_rut['DES_MEDIO_ENVIO'] = $arreglo_piezas[4];
				
				$MENSAJES[] = 'Arreglo a incorporar es: '.print_r($arreglo_rut,true);
				$arreglo_tipo_entidad[$rut] = $arreglo_rut;
			}									
			$lista_distribucion[$_POST['hidden_tipoEntidad']] = $arreglo_tipo_entidad;
			//print_r($lista_distribucion);
			$this->_SESION->setVariable('DESTINATARIO',$lista_distribucion);
			
			$CAMBIA['#div_listaDistribucion'] = $this->seteaHtml(1);
			$CAMBIA['#div_listaDistribucionCopia'] = $this->seteaHtml(2);
			$MENSAJES[] = 'Total de Entidades son: '.$TOTAL;
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$CLOSE = array();
			$CLOSE['#div_dialogPara'] = 'open';
			$json['CAMBIA'] = $CAMBIA;
			$json['CLOSE'] = $CLOSE;
			return json_encode($json);
							
		}
		
		public function seteaHtml($distribucion){
			
			$lista_distribucion = ($this->_SESION->getVariable('DESTINATARIO')) ? $this->_SESION->getVariable('DESTINATARIO') : array();
			
			
			//echo "<pre>";print_r($lista_distribucion);echo "</pre>";exit();

			
			$lista_completa = array();
			foreach($lista_distribucion as $tipos_entidades){
				foreach($tipos_entidades as $fiscalizado){
					$lista_completa[] = $fiscalizado;
				}					
			}
			uasort($lista_completa, 'cmp');		
			$this->_TEMPLATE->assign('DES_TIPO_ENT',$_POST['hidden_tipoEntidad']);
			$con_copia = ($distribucion === 1) ? 'N' : 'S';
			
			
			
			foreach($lista_completa as $fiscalizado){
				//print_r($fiscalizado);
				$CANTIDAD_USUARIOS = count($fiscalizado['USUARIOS_SEIL']);
				$CANTIDAD_USUARIOS_SEL = 0;
				if(is_array($fiscalizado['USUARIOS_SEIL'])){
					foreach($fiscalizado['USUARIOS_SEIL'] as $user){
						  if($user['CHECKED'] == 'checked'){
							  $CANTIDAD_USUARIOS_SEL++;
						  }
					}
				}
				
				$this->_TEMPLATE->assign('NUMERO_USUARIOS',$CANTIDAD_USUARIOS_SEL.' de '.$CANTIDAD_USUARIOS);
			
				if($fiscalizado['DES_CON_COPIA'] == 'S' && $con_copia == 'S'){
					
					//print_r("PASO 1");	
					
					if(isset($fiscalizado['TRA_ID']) && strlen(trim($fiscalizado['TRA_ID'])) > 0){
						$tratamiento = $this->getTratamiento($fiscalizado['TRA_ID']);
						$fiscalizado['TRA_NOMBRE'] = $tratamiento['TRA_NOMBRE'];
					}
										
					$this->_TEMPLATE->assign('FISCALIZADO',$fiscalizado);
					
					if(isset($fiscalizado['DES_DIRECCION']) && strlen(trim($fiscalizado['DES_DIRECCION']))){
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion_copia.fiscalizado_lista.direccion');
					}
					

					if(isset($fiscalizado['MEDIO_ENVIO']) && strlen(trim($fiscalizado['MEDIO_ENVIO']))){
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion_copia.fiscalizado_lista.medio_envio');
					}

					//ML: cambie CORREO POR DES_CORREO
					if(isset($fiscalizado['DES_CORREO']) && strlen(trim($fiscalizado['DES_CORREO']))){
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion_copia.fiscalizado_lista.email');
					}
					
					
					
					if($fiscalizado['DES_TIPO_ENT'] !== 'PUPUB'){
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion_copia.fiscalizado_lista.seleccionar_usuarios');
					}else{
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion_copia.fiscalizado_lista.editar_destinatario');						
					}

					$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion_copia.fiscalizado_lista.es_copia');
					//print_r($this->_SESION->getVariable('DESTINATARIO'));
					$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion_copia.fiscalizado_lista');

					

				}
				
				
				if($fiscalizado['DES_CON_COPIA'] == 'N' && $con_copia == 'N'){
					
					//print_r("PASO 2");	
					if(isset($fiscalizado['TRA_ID']) && strlen(trim($fiscalizado['TRA_ID'])) > 0){
						$tratamiento = $this->getTratamiento($fiscalizado['TRA_ID']);				
						$fiscalizado['TRA_NOMBRE'] = $tratamiento['TRA_NOMBRE'];
					}
					$this->_TEMPLATE->assign('FISCALIZADO',$fiscalizado);
					
					if(isset($fiscalizado['DES_DIRECCION']) && strlen(trim($fiscalizado['DES_DIRECCION']))){
						
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion.fiscalizado_lista.direccion');
					}
					/*
					mlatorre: comente para probar con DES_CORREO
					if(isset($fiscalizado['CORREO']) && strlen(trim($fiscalizado['CORREO']))){
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion.fiscalizado_lista.email');
					}
					*/
					
					if(isset($fiscalizado['MEDIO_ENVIO']) && strlen(trim($fiscalizado['MEDIO_ENVIO']))){
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion.fiscalizado_lista.medio_envio');
					}

					if(isset($fiscalizado['DES_CORREO']) && strlen(trim($fiscalizado['DES_CORREO']))){
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion.fiscalizado_lista.email');
					}
					
					if($fiscalizado['DES_TIPO_ENT'] !== 'PUPUB'){
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion.fiscalizado_lista.seleccionar_usuarios');
					}else{
						$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion.fiscalizado_lista.editar_destinatario');
					}
					
					$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion.fiscalizado_lista');

				}										
			}
			
			
			
			
			if( $con_copia == 'N'){
				$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion');
				return $this->_TEMPLATE->text('main.paso1.div_listaDistribucion');	
			}else{
				$this->_TEMPLATE->parse('main.paso1.div_listaDistribucion_copia');
				return $this->_TEMPLATE->text('main.paso1.div_listaDistribucion_copia');	
			}
		}
		
		
		
		
		
		public function eliminarSeleccionadoDestinatario(){
			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();			
			$MENSAJES[] = 'Eliminando : '.$_POST['rut'];
			
			$lista_distribucion = $this->_SESION->getVariable('DESTINATARIO');
			$lista_completa = array();
			
			foreach($lista_distribucion as $key_entidad => $tipos_entidades){
				foreach($tipos_entidades as $fiscalizado){
					if( $fiscalizado['DES_RUT'] == $_POST['rut'] ){
						unset($lista_distribucion[$key_entidad][$fiscalizado['DES_RUT']]);
					}
					
					/*foreach($_POST['rut'] as $rut_eliminar){
						if($rut_eliminar == $fiscalizado['RUT']){
							unset($lista_distribucion[$key_entidad][$fiscalizado['RUT']]);
						}
					}*/
	
				}
				
			}
			$this->_SESION->setVariable('DESTINATARIO',$lista_distribucion);
						
			$CAMBIA['#div_listaDistribucion'] = $this->seteaHtml(1);
			$CAMBIA['#div_listaDistribucionCopia'] = $this->seteaHtml(2);
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);						

		}
		
	
		
		/* ml:borrer si es necesario
		public function verExpediente(){
			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();	
			$OPEN = array();			
			$MENSAJES[] = 'Creando dialogo para otro';
			
			
			//$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_DESTINATARIO_PKG.fun_getTratamiento','function');	
			$data_all = $this->getTratamiento();
			//foreach($data_all as $data){
			//	$this->_TEMPLATE->assign('TRA',$data);
			//	$this->_TEMPLATE->parse('main.div_dialogOtroo.option_tratamiento');
			//}
			//$this->_TEMPLATE->assign('DISTRIBUCION',$_POST['distribucion']);
			
			
			
			$this->_TEMPLATE->parse('main.div_verExpediente');
			$CAMBIA['#div_verExpediente'] = $this->_TEMPLATE->text('main.div_verExpediente');
			$OPEN['#div_verExpediente'] = 'open';
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$json['OPEN'] = $OPEN;
			return json_encode($json);			
		}
		*/


		public function agregarOtro(){
			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();	
			$OPEN = array();			
			$MENSAJES[] = 'Creando dialogo para otro';
			
			
			//$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_DESTINATARIO_PKG.fun_getTratamiento','function');	
			$data_all = $this->getTratamiento();
			foreach($data_all as $data){
				$this->_TEMPLATE->assign('TRA',$data);
				$this->_TEMPLATE->parse('main.paso1.div_dialogOtro.option_tratamiento');
			}
			$this->_TEMPLATE->assign('DISTRIBUCION',$_POST['distribucion']);
			
			
			
			$this->_TEMPLATE->parse('main.paso1.div_dialogOtro');
			$CAMBIA['#div_dialogOtro'] = $this->_TEMPLATE->text('main.paso1.div_dialogOtro');
			$OPEN['#div_dialogOtro'] = 'open';
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$json['OPEN'] = $OPEN;
			return json_encode($json);			
		}
		
		public function agregarFiscalizadoParaOtro(){
			
			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();	
			$OPEN = array();			
			$MENSAJES[] = 'Se comienza la creacion de OTRO con:';
			$MENSAJES[] = print_r($_POST,true);
			
			
			/************************************************/		
			$lista_distribucion = $this->_SESION->getVariable('DESTINATARIO');
			$MENSAJES[] = 'Existen actualmente '.count($lista_distribucion ).' entidades';			
			$arreglo_tipo_entidad = (isset($lista_distribucion['PUPUB'])) ? $lista_distribucion['PUPUB'] :array();
			//$checkbox_fiscalizado = (is_array($_POST['checkbox_fiscalizado'])) ? $_POST['checkbox_fiscalizado'] : array();
			
			$MENSAJES[] = 'El arreglo de tipo de entidad es: '.print_r($arreglo_tipo_entidad,true);
			$MENSAJES[] = 'El tipo de distribucion enviado es: '.$_POST['hidden_distribucion'];			
			
			$MENSAJES[] = 'Rut es :'.substr($_POST['input_otroRut'], 0, -2);		
			//$MENSAJES[] = 'Rut es :'.$_POST['input_otroRut'];	
			$MENSAJES[] = 'hidden_rutEditar :'.$_POST['hidden_rutEditar'];
			
			$editar = (isset($_POST['hidden_rutEditar']) && strlen(trim( $_POST['hidden_rutEditar'])) > 0) ? 'S' : 'N';
			
			
			
			$rand = (trim($_POST['input_otroRut']) == "") ? 'S': 'N'; 
			
			if($editar == 'S'){
				unset($arreglo_tipo_entidad[$_POST['hidden_rutEditar']]);
			}
			
			
			$rut = (trim($_POST['input_otroRut']) == "") ? rand ( 1111111 , 99999999 ): substr($_POST['input_otroRut'], 0, -2);
			//$rut = (trim($_POST['input_otroRut']) == "") ? rand ( 1111111 , 99999999 ): $_POST['input_otroRut'];
			
			
			$arreglo_rut = array();
			$arreglo_rut['DES_RUT'] = $rut;
			$arreglo_rut['DES_NOMBRE'] = $_POST['input_otroNombre'];
			$arreglo_rut['DES_DIRECCION'] = $_POST['input_otroDireccion'];
			$arreglo_rut['DES_CORREO'] = $_POST['input_otroCorreoElectronico'];
			
			$arreglo_rut['TRA_ID'] = $_POST['select_listaTratamiento'];
			$TRA = $this->getTratamiento($_POST['select_listaTratamiento']);
			$arreglo_rut['TRA_NOMBRE'] = $TRA['TRA_NOMBRE'];
			$arreglo_rut['DES_TIPO_ENT'] = 'PUPUB';			
			$arreglo_rut['DES_CON_COPIA'] = ((int)$_POST['hidden_distribucion'] === 1) ? 'N' : 'S';
			$arreglo_rut['RAND'] = $rand;

			$arreglo_rut['MEDIO_ENVIO'] = $_POST['medio_envio'];

			$MENSAJES[] = 'Arreglo a incorporar es: '.print_r($arreglo_rut,true);
			$arreglo_tipo_entidad[$rut] = $arreglo_rut;
												
			$lista_distribucion['PUPUB'] = $arreglo_tipo_entidad;
			$this->_SESION->setVariable('DESTINATARIO',$lista_distribucion);
			
			$CAMBIA['#div_listaDistribucion'] = $this->seteaHtml(1);
			$CAMBIA['#div_listaDistribucionCopia'] = $this->seteaHtml(2);
			$MENSAJES[] = 'Total de Entidades son: '.$TOTAL;
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$CLOSE = array();
			$CLOSE['#div_dialogOtro'] = 'close';
			$json['CAMBIA'] = $CAMBIA;
			$json['CLOSE'] = $CLOSE;
			return json_encode($json);									
			
		}
		
		//permite mostrar el dialogo con la informacion prellenada
		public function editarDestinatario(){
			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();	
			$OPEN = array();			
			$MENSAJES[] = 'Creando dialogo para otro';
			
			
			
			
			$lista_distribucion = $this->_SESION->getVariable('DESTINATARIO');
			$MENSAJES[] = 'Existen actualmente '.count($lista_distribucion ).' entidades';			

			$arreglo_tipo_entidad = (isset($lista_distribucion['PUPUB'])) ? $lista_distribucion['PUPUB'] :array();
			
			$MENSAJES[] = 'Atributos '.print_r($arreglo_tipo_entidad,true );			
			$ciudadano = $arreglo_tipo_entidad[$_POST['rut']];
			
			if(isset($ciudadano['RAND']) && $ciudadano['RAND'] == 'S'){
				$ciudadano['DES_RUT'] = '';								
			}
			
			
			
			
			
			$ciudadano['RUT_EDITAR'] = $_POST['rut'];
			
			$this->_TEMPLATE->assign('CIU',$ciudadano);
			
			
			
			$distribucion = ($ciudadano['DES_CON_COPIA'] == 'S') ? 2 : 1;

			
			$data_all = $this->getTratamiento();
			foreach($data_all as $data){
				if($data['TRA_ID'] == $ciudadano['TRA_ID'] ){
					$data['SELECTED'] = 'selected';
				}else{
					$data['SELECTED'] = '';
				}
				$this->_TEMPLATE->assign('TRA',$data);
				$this->_TEMPLATE->parse('main.paso1.div_dialogOtro.option_tratamiento');
			}
			$this->_TEMPLATE->assign('DISTRIBUCION',$distribucion);
			
			
			
			$this->_TEMPLATE->parse('main.paso1.div_dialogOtro');
			$CAMBIA['#div_dialogOtro'] = $this->_TEMPLATE->text('main.paso1.div_dialogOtro');
			$OPEN['#div_dialogOtro'] = 'open';
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$json['OPEN'] = $OPEN;
			return json_encode($json);
		}
		

		
		
		public function mostrarSeilUsuario(){
			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();	
			$OPEN = array();			
			$MENSAJES[] = 'Inicia mostrar Usuarios SEIL';

			$lista_distribucion = $this->_SESION->getVariable('DESTINATARIO');
			//print_r($lista_distribucion);
			foreach($lista_distribucion as $fiscalizados){
				foreach($fiscalizados as $registro){
					//print_r($_POST);
					//print_r($registro);
					
					if($registro['DES_RUT'] == $_POST['rut']){
						$DESTINATARIO = $registro;
						$this->_TEMPLATE->assign('DES_NOMBRE',$registro['DES_NOMBRE']);
						$this->_TEMPLATE->assign('DES_DIRECCION',$registro['DES_DIRECCION']);
						$this->_TEMPLATE->assign('DES_RUT',$_POST['rut']);
						break;
					}
					
				}
			}
			$lista = $DESTINATARIO['USUARIOS_SEIL'];
			//print_r($lista);
			foreach($lista as $registro){
				if(isset($registro['USUENV_TIPO']) && isset($registro['USUENV_TIPO']) == 'otro'){
					$registro['NOMBRE_USUARIO'] = $registro['USUENV_NOMBRE'].' - '.$registro['USUENV_DIRECCION'];
					$registro['MAIL'] = $registro['USUENV_EMAIL'];
					
				}
				$this->_TEMPLATE->assign('USUARIO',$registro);
				$this->_TEMPLATE->parse('main.paso1.div_eligeUsuarios.usuario');
			}			
						
			$this->_TEMPLATE->parse('main.paso1.div_eligeUsuarios');

			$CAMBIA['#div_eligeUsuarios'] = $this->_TEMPLATE->text('main.paso1.div_eligeUsuarios');
			$OPEN['#div_eligeUsuarios'] = 'open';
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$json['OPEN'] = $OPEN;
			return json_encode($json);
			
			
		}
		

		
		
		public function getTratamiento($id =NULL){
			if($this->_SESION->getVariable('_CACHE_TRATAMIENTO') === false ){
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_DESTINATARIO_PKG.fun_getTratamiento','function');
				$data = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('_CACHE_TRATAMIENTO',$data);
			}
			if($id === NULL){
				return $this->_SESION->getVariable('_CACHE_TRATAMIENTO');
			}else{
				foreach($this->_SESION->getVariable('_CACHE_TRATAMIENTO') as $data){
					if($data['TRA_ID'] == $id){
						return $data;
					}
				}
			}
		}
		
		
		public function getUsuariosSeilEntidad($rut){
			if($this->_SESION->getVariable('_CACHE_SEIL_'.$rut) === false ){
				try{
					
					$rut = explode('-',$rut);
					$rut = $rut[0];
					$bind = array(':rut' => $rut,':aplic' => 'PUFED');				
					$cursor = $this->_ORA->retornaCursor('web_usuarios_seil.get_usuarios_busqueda_aplic','function',$bind);			
					$data = $this->_ORA->FetchAll($cursor);
					$this->_SESION->setVariable('_CACHE_SEIL_'.$rut,$data);
				}catch(Exception $e){
					print_r($e);
					//exit();
				}
									
			}

			return $this->_SESION->getVariable('_CACHE_SEIL_'.$rut);

			
		}
		
		public function guardarUsuariosSeleccionados(){
			
			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();	
			$OPEN = array();			
			$MENSAJES[] = 'Inicia guardarUsuariosSeleccionados';


			$SELECCIONADOS = array();
			$check_usuariosSeleccionados = (is_array($_POST['check_usuariosSeleccionados'])) ? $_POST['check_usuariosSeleccionados'] : array();
			foreach($check_usuariosSeleccionados as $seleccionado){
				$SELECCIONADOS[$seleccionado] = 1;
			}
			$lista_distribucion = $this->_SESION->getVariable('DESTINATARIO');
			
			foreach($lista_distribucion as $key_tipoEntidad => $registro){
				foreach($lista_distribucion[$key_tipoEntidad] as $key_rutEntidad => $registro2){
					if($_POST['hidden_rutFiscalizado'] == $key_rutEntidad){
						$USUARIOS_SEIL = $lista_distribucion[$key_tipoEntidad][$key_rutEntidad]['USUARIOS_SEIL'];
						foreach($USUARIOS_SEIL as $key_usuario => $usuario ){
							if(isset($SELECCIONADOS[$usuario['COD_USUARIO']]) && $SELECCIONADOS[$usuario['COD_USUARIO']] == 1){
								$USUARIOS_SEIL[$key_usuario]['CHECKED'] = 'checked';
							}else{
								$USUARIOS_SEIL[$key_usuario]['CHECKED'] = '';
							}
						}
						$lista_distribucion[$key_tipoEntidad][$key_rutEntidad]['USUARIOS_SEIL'] = $USUARIOS_SEIL;
					}															
				}
			}
			$this->_SESION->setVariable('DESTINATARIO',$lista_distribucion);
			
			
			
			$MENSAJES[] = 'OK';
			
			
			
			$CAMBIA['#div_eligeUsuarios'] = $this->_TEMPLATE->text('main.paso1.div_eligeUsuarios');
			$CAMBIA['#div_listaDistribucion'] = $this->seteaHtml(1);
			$CAMBIA['#div_listaDistribucionCopia'] = $this->seteaHtml(2);
			$CLOSE['#div_eligeUsuarios'] = 'close';
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			//$json['OPEN'] = $OPEN;
			$json['CLOSE'] = $CLOSE;
			return json_encode($json);
		}
		
		public function getUsuariosSeilEntidadSelect($caso, $version){
			$bind = array(':caso' => $caso, ':version' => $version);
			$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_DESTINATARIO_PKG.fun_getUsuariosRes','function',$bind);
			$data = $this->_ORA->FetchAll($cursor);
			return $data;
		}
		
		public function guardaOtrosUsuarioEntidad(){
			$lista_distribucion = $this->_SESION->getVariable('DESTINATARIO');
			foreach($lista_distribucion as $key_tipo => $dest){
				foreach($dest as $key_rut => $registro){
					if($key_rut == $_POST['rut']){
						
						$nuevo_usuario = array(
							'USUENV_TIPO' => 'otro',
							'USUENV_NOMBRE' => $_POST['nombre'],
							'USUENV_DIRECCION' => $_POST['direccion'],
							'USUENV_EMAIL' => $_POST['email'],
							'USUENV_USUARIO' => substr(strtolower(str_replace(' ','',$_POST['nombre'])),0,25),
							'COD_USUARIO' => substr(strtolower(str_replace(' ','',$_POST['nombre'])),0,25),
							'CHECKED' => 'checked'							
							);
						$lista_distribucion[$key_tipo][$key_rut]['USUARIOS_SEIL'][] = $nuevo_usuario;
						$this->_SESION->setVariable('DESTINATARIO',$lista_distribucion);
					}				
				}
			}
			return $this->mostrarSeilUsuario();
			
		}
		
		public function cargoDestinatario(){
			$json = array();
			$json['RESULTADO'] = 'OK';			
			$MENSAJES = array();
			$CAMBIA = array();	
			$OPEN = array();			
			$MENSAJES[] = 'Inicia cargoDestinatario';

			
			$lista_distribucion = $this->_SESION->getVariable('DESTINATARIO');
			
			foreach($lista_distribucion as $key_tipoEntidad => $registro){
				foreach($lista_distribucion[$key_tipoEntidad] as $key_rutEntidad => $registro2){
					if($key_rutEntidad == $_POST['rut']){
						$lista_distribucion[$key_tipoEntidad][$key_rutEntidad]['DES_CARGO'] = $_POST['cargo'];
					}
				}
			}
			$this->_SESION->setVariable('DESTINATARIO',$lista_distribucion);
			
			
			
			$MENSAJES[] = 'OK';
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			//$json['OPEN'] = $OPEN;
			$json['CLOSE'] = $CLOSE;
			return json_encode($json);
		}
		
		
		
		/***
		/*
		/* Busqueda en BD de FISCALIZADOS, se deben retornar todos los tipos de entidad
		/**/
		
		public function buscarFiscalizado(){
			$json = array();
			$bind = array(':busqueda' => mb_strtoupper($_GET['term'], 'UTF-8'));
			//print_r($bind);
			$cursor = $this->_ORA->retornaCursor('PUB_OBTENER_DATOS.fun_getBusquedaDestinatario','function',$bind);			
			while($data = $this->_ORA->FetchArray($cursor)){
				//print_r($data);
				$IDENTIFICACION = array();
				$IDENTIFICACION[] = $data['RUT'];
				$IDENTIFICACION[] = $data['DIRECCION'].'&|C'.$data['FISCALIZADO'].'&|CGerente General&|C';
				$IDENTIFICACION[] = $data['TIPO_ENTIDAD'];																	
				$json[] = array('id' => implode(']SEPARA[',$IDENTIFICACION),'label' => trim($data['FISCALIZADO'])." [".trim($data['TIPO_ENTIDAD']."]"));
			}			
			return json_encode($json);
		}
		
		/*
		(
    [RUT] => 900027994
    [FISCALIZADO] => COMERCIAL DEL ACERO S.A.
    [DIRECCION] => --- -  Ciudad: SANTIAGO - Reg. Metropolitana
    [CARGO] => Gerente General
    [CANTIDAD] => 0
    [USUARIO] => 0
    [TIPO_ENTIDAD] => RVEXT
)
*/
		
	
	}
	
	
	
	
	
	
	function cmp($a, $b) {
		if($a['DES_NOMBRE'] == $b['DES_NOMBRE']) {
			return 0;
		}
    	return strcasecmp($a['DES_NOMBRE'],$b['DES_NOMBRE']);
	}

?>