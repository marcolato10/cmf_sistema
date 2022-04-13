<?php
	/** Clase de Clasificación que se encarga de hacer las consultas a la BD y además de retornar los HTML pertinentes de algunos "div" **/
	/** Además se encarga de las propiedades de las clasificaciones 																	**/
	class Clasificacion extends ClaseSistema{

		
		/******************************************** setClasificacion ***********************************************************************/
		/** 	Setea una clasificacion y retorna el HTML respectivo del arbol 																**/
		/** 	Además deja las propiedades setedas																							**/
		/*public function setClasificacion(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$this->CLA_ID = $_POST['CLA_ID'];						
			$MENSAJES = array();
			$CAMBIA = array();
			$MENSAJES[] = 'La nueva Clasificacion es '.$this->CLA_ID;			
			$CAMBIA['#div_clasificacion'] = $this->fun_dibujaClasificacion($this->CLA_ID);
			$this->_SESION->setVariable('CLA_ID',$this->CLA_ID);
			
			
			//print_r($this->_RESOLUCION);
			$MENSAJES = array_merge($MENSAJES,$this->_MENSAJES_GRAL);
			$CAMBIA['#arbol_propiedades'] = $this->dibujaPropiedades(); 
			$CAMBIA['#div_propiedadesResolucion'] = $this->getHTMLPropiedades();
			$modulo = new Modulo();
			$modulo->setControl($this);
			
			$CAMBIA['#div_modulosClasificacion'] = $modulo->generarHTML($this->CLA_ID);
			$MENSAJES[] = 'MULTA: '.$this->RES_MULTA;
			$MENSAJES[] = 'PUBLICACION: '.$this->PUB_ID;
			$MENSAJES[] = 'PUBLICACION: '.$this->PUB_NOMBRE;
			
				
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			
			return json_encode($json);
		}*/
		/*************************************************************************************************************************************/
		
		
		public function setClasificacion(){
			$CAMBIA = array();
			$MENSAJES = array();
			$VAL = array();
			$json = array();
			$json['RESULTADO'] = 'OK';
			$this->CLA_ID = $this->_SESION->getVariable('CLA_ID');
			if(!is_array($this->CLA_ID)){
				$this->CLA_ID = array();
			}
			//$MENSAJES[] = 'Las clasificaciones ya existentes son: '.print_r($this->CLA_ID,true);													
			$CAMBIA['#div_agregarClasificacion'] = $this->fun_dibujaClasificacionSeleccion($_POST['CLA_ID']);
			
			$esHoja = $this->_SESION->getVariable('CLASIFICACION_HOJA');
			if($esHoja == 'S'){
				$MENSAJES[] = 'La Clasificacion es HOJA: '.$_POST['CLA_ID'];
				$VAL['#input_clasificacion'] = $_POST['CLA_ID'];
				$data_cla = $this->fun_getClasificacion($_POST['CLA_ID']);
				$arbol = $data_cla['ARBPRO_ID'];
				
		
				$prop = $this->fun_getArbolPropiedades($arbol);
				if($prop['RES_PAGO'] == 'S'){
					$this->_TEMPLATE->parse('main.paso1.div_clasificacion.tabla_propiedades_clasificaciones.pago_derechos');
				}
				if($prop['RES_MULTA'] == 'S'){
					$this->_TEMPLATE->parse('main.paso1.div_clasificacion.tabla_propiedades_clasificaciones.pago_derechos');
				}
				$this->_TEMPLATE->assign('PRIVACIDAD',$prop['PRI_NOMBRE']);
				$this->_TEMPLATE->assign('PUBLICACION',$prop['PUB_NOMBRE']);
				$this->_TEMPLATE->assign('DISTRIBUCION',$prop['DIS_NOMBRE']);
				$this->_TEMPLATE->parse('main.paso1.div_clasificacion.tabla_propiedades_clasificaciones');
			
			//print_r($prop);
			
			/*
			[RES_PAGO_NOMBRE] => Sí
    [RES_MULTA_NOMBRE] => No
    [ARBPRO_ID] => 3
    [TIPRES_ID] => exenta
    [RES_PAGO] => S
    [RES_MULTA] => N
    [PUB_ID] => inmed
    [PRI_ID] => publi
    [ARBPRO_TIENE_DOCUMENTOS] => N
    [ARBPRO_TIPO_FIRMA] => TOKEN
    [ARBPRO_SUBROGANCIA] => NO
    [TIPRES_NOMBRE] => Exenta
    [PUB_NOMBRE] => Inmediata
    [PUB_DESCRIPCION] => Se debe publicar a penas se firma la resolucion
    [PRI_NOMBRE] => Público
    [PRI_DESCRIPCION] => Para todo público
    [PRI_GESDOC] => 0*/
	
	
	
				$CAMBIA['#div_tabla_propiedades_clasificaciones'] = $this->_TEMPLATE->text('main.paso1.div_clasificacion.tabla_propiedades_clasificaciones');
			}else{
				$MENSAJES[] = 'No es Hoja';
				$VAL['#input_clasificacion'] = '';
			}
																																								
			$MENSAJES = array_merge($MENSAJES,$this->_MENSAJES_GRAL);
			//$CAMBIA['#arbol_propiedades'] = $this->dibujaPropiedades(); 
			//$CAMBIA['#div_propiedadesResolucion'] = $this->getHTMLPropiedades();
			//$modulo = new Modulo();
			//$modulo->setControl($this);
		
			//$CAMBIA['#div_modulosClasificacion'] = $modulo->generarHTML($this->CLA_ID);
			//$MENSAJES[] = 'MULTA: '.$this->RES_MULTA;
			//$MENSAJES[] = 'PUBLICACION: '.$this->PUB_ID;
			//$MENSAJES[] = 'PUBLICACION: '.$this->PUB_NOMBRE;						
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$json['VAL'] = $VAL;			
			return json_encode($json);
		}
		
		public function agregarClasificacion(){
			
			$HTML =  $this->fun_dibujaClasificacionSeleccion(1);
			
			$json = array();
			$json['RESULTADO'] = 'OK';	
			
			$CAMBIA['#div_agregarClasificacion'] = $HTML;			
			$OPEN = array();
			$OPEN['#div_agregarClasificacion'] = 'open';
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$json['OPEN'] = $OPEN;
			
			return json_encode($json);
		}
				
		
		/*
		 * Funcion: seleccionarClasificacion
		 *          Funcion que agrega una clasificacion a la lista, esta debe validar que pertenezca al mismo flujo (arbol), que las anteriores
		 *          Para ello se considerará una variable de sesion ARBOL_CLA, para indicar el arbol o flujo que posee las que ya estan setreadas
		 *
		 */
		
		
		public function seleccionarClasificacion(){
			$json = array();
			$MENSAJES = array();
			$ALERT = array();
			$SHOW = array();
			$CLOSE = array();
			$CAMBIA = array();
			$json['RESULTADO'] = 'OK';
			if($this->fun_claIsHoja($_POST['CLA_ID'])){
				$flujoAnterior = $this->_SESION->getVariable('ARBOL_CLA');
				$data = $this->fun_getClasificacion($_POST['CLA_ID']);
				if($flujoAnterior == $data['ARBPRO_ID'] || $flujoAnterior === false){
					$MENSAJES[] = "El Flujo/Arbol es: ".$data['ARBPRO_ID'];
					$CLA_ID_SESION = $this->_SESION->getVariable('CLA_ID');
					$CLA_ID_SESION = (is_array($CLA_ID_SESION)) ? $CLA_ID_SESION : array();				
					$CLA_ID_SESION[$_POST['CLA_ID']] = $_POST['CLA_ID'];
					$this->_SESION->setVariable('CLA_ID',$CLA_ID_SESION);
					$this->_SESION->setVariable('ARBOL_CLA',$data['ARBPRO_ID']);
					
					$CAMBIA['#div_clasificacion'] = $this->fun_dibujaClasificacion($CLA_ID_SESION);
					$CLOSE['#div_agregarClasificacion'] = 'close';
					$this->seteaVariablesPropiedades($data['ARBPRO_ID']);
					
					$SEL_ENVIO_ARRAY = array();
					// De acuerdo a la cantidad de clasificaciones seleccionadas se procederá a seleccionar el tipo de envio
					foreach($CLA_ID_SESION as $cla_id){
						$sel_envio = $this->getEnviosClasificacion($cla_id);
						if(is_array($sel_envio)){
							$this->_LOG->log(print_r($sel_envio,true));						
							foreach($sel_envio as $env_id){
								$selector = (isset($SEL_ENVIO_ARRAY[$env_id['ENV_ID']])) ? $SEL_ENVIO_ARRAY[$env_id['ENV_ID']]['CLAENV_MOD'] : 'N';
								$this->_LOG->log("Selector Inicial para ".$env_id['ENV_ID']." es: $selector");
								$env_id['CLAENV_MOD'] = ($selector == 'S') ? 'S' : $env_id['CLAENV_MOD'];
								unset($env_id['CLA_ID']);
								$SEL_ENVIO_ARRAY[$env_id['ENV_ID']] = $env_id;
							}
						}
					}
					$this->_LOG->log(print_r($SEL_ENVIO_ARRAY,true));
								
					$this->_SESION->setVariable('ENV_ID',$SEL_ENVIO_ARRAY);
					$CAMBIA['#div_tipoEnvio'] =  $this->getHTMLtipoEnvio($SEL_ENVIO_ARRAY);													
				
				}else{
					
					$MENSAJES[] = 'La clasificacion seleccionada no pertenece al mismo flijograma de propiedades que la anterior';
					$ALERT[] = 'No se puede agregar la clasificacion, posee propiedades distintas a las clasificaciones seleccionadas anteriormente';
				}														

				
				
				$MENSAJES = array_merge($MENSAJES,$this->_MENSAJES_GRAL);
				if(isset($this->_CAMBIA_GRAL) && is_array($this->_CAMBIA_GRAL)){
					$CAMBIA = array_merge($CAMBIA,$this->_CAMBIA_GRAL);
				}
				
				
				
				
				
				
				
				//$CAMBIA['#arbol_propiedades'] = $this->dibujaPropiedades(); 
				//$CAMBIA['#div_propiedadesResolucion'] = $this->getHTMLPropiedades();
				$modulo = new Modulo();
				$modulo->setControl($this);
				$CAMBIA['#div_modulosClasificacion'] = '';
				$this->_SESION->setVariable('MOD_ID_AUX',array());
				if(is_array($CLA_ID_SESION)){
					foreach($CLA_ID_SESION as $cla_id_aux){
						$MENSAJES[] = "Se generará eL HTML para los modulos de la clasificacion $cla_id_aux";
						$CAMBIA['#div_modulosClasificacion'] = $modulo->generarHTML($cla_id_aux);
					}
				}
				
				
				
				if($CAMBIA['#div_modulosClasificacion'] !== ''){
						$SHOW['#div_modulosClasificacion'] = '#div_modulosClasificacion';
				}
				$this->_SESION->setVariable('RES_MOD_IDs',$this->_SESION->getVariable('MOD_ID_AUX'));
				$this->_SESION->setVariable('MOD_ID_AUX',array());
				//$MENSAJES[] = 'MULTA: '.$this->RES_MULTA;
				//$MENSAJES[] = 'PUBLICACION: '.$this->PUB_ID;
				//$MENSAJES[] = 'PUBLICACION: '.$this->PUB_NOMBRE;				
				
				
				
				
				
				
				
				if(isset($modulo) && isset($modulo->MENSAJE_GRAL)){
					$MENSAJES = array_merge($MENSAJES,$modulo->MENSAJE_GRAL);
				}
				$json['MENSAJES'] =  $MENSAJES;
				$json['ALERT'] =  $ALERT;
				$json['CAMBIA'] = $CAMBIA;
				$json['SHOW'] = $SHOW;
				$json['OPEN'] = $OPEN;
				$json['CLOSE'] = $CLOSE;
			}else{
				$json['RESULTADO'] = 'NOK';
				$MENSAJES[] = 'La clasificacion enviada no es una HOJA';
			}
			
			
			
			
					
			return json_encode($json);
		}
		
		
		
		public function seteaVariablesPropiedades($arbol){
			$prop = $this->fun_getArbolPropiedades($arbol);		
			//print_r($prop);
			$this->_SESION->setVariable('TIPRES_ID',$prop['TIPRES_ID']);
			
			$this->TIPRES_ID = $prop['TIPRES_ID'];			
			
			if(isset($prop['RES_PAGO']) && strlen($prop['RES_PAGO']) > 0){
				$this->RES_PAGO_NOMBRE = $prop['RES_PAGO_NOMBRE'];
				$this->_SESION->setVariable('RES_PAGO_NOMBRE',$this->RES_PAGO_NOMBRE);
				$this->RES_PAGO = $prop['RES_PAGO'];
				$this->_SESION->setVariable('RES_PAGO',$this->RES_PAGO);
				if(strlen($this->RES_PAGO) > 0){
					$this->_SESION->setVariable('RES_PAGO_BD','S');
				}else{
					$this->_SESION->setVariable('RES_PAGO_BD','N');
				}
			}
			
						
			if(isset($prop['RES_MULTA']) && strlen($prop['RES_MULTA']) > 0){									
				$this->RES_MULTA_NOMBRE = $prop['RES_MULTA_NOMBRE'];
				$this->_SESION->setVariable('RES_MULTA_NOMBRE',$this->RES_MULTA_NOMBRE);
				$this->RES_MULTA = $prop['RES_MULTA'];
				$this->_SESION->setVariable('RES_MULTA',$this->RES_MULTA);
				if(strlen($this->RES_MULTA) > 0){
					$this->_SESION->setVariable('RES_MULTA_BD','S');
				}else{
					$this->_SESION->setVariable('RES_MULTA_BD','N');
				}
			}
			
					
			if(isset($prop['PUB_ID']) && strlen($prop['PUB_ID']) > 0){
				$this->PUB_NOMBRE = $prop['PUB_NOMBRE'];
				$this->_SESION->setVariable('PUB_NOMBRE',$this->PUB_NOMBRE);
				$this->PUB_ID = $prop['PUB_ID'];
				$this->_SESION->setVariable('PUB_ID',$this->PUB_ID);
				if(strlen($this->PUB_ID) > 0){
					$this->_SESION->setVariable('PUB_ID_BD','S');
				}else{
					$this->_SESION->setVariable('PUB_ID_BD','N');
				}						
			}
			
						
			if(isset($prop['DIS_NOMBRE']) && strlen($prop['DIS_NOMBRE']) > 0){
				$this->DIS_NOMBRE = $prop['DIS_NOMBRE'];
				$this->_SESION->setVariable('DIS_NOMBRE',$this->DIS_NOMBRE);
				$this->DIS_ID = $prop['DIS_ID'];
				$this->_SESION->setVariable('DIS_ID',$this->DIS_ID);
				if(strlen($this->DIS_ID) > 0){
					$this->_SESION->setVariable('DIS_ID_BD','S');
				}else{
					$this->_SESION->setVariable('DIS_ID_BD','N');
				}
			}
			
			
			if(isset($prop['PRI_NOMBRE']) && strlen($prop['PRI_NOMBRE']) > 0){
				$this->PRI_NOMBRE = $prop['PRI_NOMBRE'];
				$this->_SESION->setVariable('PRI_NOMBRE',$this->PRI_NOMBRE);
				$this->PRI_ID = $prop['PRI_ID'];
				$this->_SESION->setVariable('PRI_ID',$this->PRI_ID);
				if(strlen($this->PRI_ID) > 0){
					$this->_SESION->setVariable('PRI_ID_BD','S');
				}else{
					$this->_SESION->setVariable('PRI_ID_BD','N');
				}
			}
			
			
			$this->_MENSAJES_GRAL[] = '$this->RES_PAGO: '.$this->RES_PAGO; 
			$this->_MENSAJES_GRAL[] = '$this->RES_MULTA: '.$this->RES_MULTA;
			$this->_MENSAJES_GRAL[] = '$this->PUB_ID: '.$this->PUB_ID;
			$this->_MENSAJES_GRAL[] = '$this->DIS_ID: '.$this->DIS_ID;						
			$this->_MENSAJES_GRAL[] = '$this->PRI_ID: '.$this->PRI_ID;
			$this->_MENSAJES_GRAL[] = '$this->TIPRES_ID: '.$this->TIPRES_ID;
			
			if($this->PRI_ID == 'reser'){
				$this->_MENSAJES_GRAL[] = 'Es reservado se muestra las personas en privacidad ';
				$this->_CAMBIA_GRAL["#div_AutorizadosRevisar"] = $this->dibujaUsrPrivacidad();	
			}else{
				$this->_MENSAJES_GRAL[] = 'No es reservado, no se hace nada';
			}
		}
		
		
		/*
		 *
		 * Funcion: fun_dibujaClasificacionSeleccion
		 * Retorna el HTML del arbol de clasificacion
		 * Además setea en sesion cada una de las propiedades asociadas al arbol que pertenece a la clasificacion
		 *
		 */
		 
		 
		 
		public function fun_dibujaClasificacionSeleccion($cla_id){
			//$this->_TEMPLATE->assign('CLASIFICACION_SELECCIONADA',$cla_id);
			//Para dibujar la clasificacion se necesita tener todos los padres y luego dibujar los hijos
			$CLASIFICACION = array();			
			$data = $this->fun_getClasificacion($cla_id);
			
			/*if(isset($data['ARBPRO_ID']) && $data['ARBPRO_ID'] != 0){			
				$arbol = $data['ARBPRO_ID'];								
				if($this->_SESION->getVariable('CLA_ID') != $cla_id){
					$this->_MENSAJES_GRAL[] = 'La clasificacion es distinta..';
					$this->_SESION->setVariable('ARBPRO_ID',$arbol);				
					$this->seteaVariablesPropiedades($arbol);					
				}else{
					$this->_MENSAJES_GRAL[] = 'La clasificacion es la misma';
				}
			}*/

		

			
			$CLASIFICACION[count($CLASIFICACION)] = $data;
			$cla_padre = (int)$data['CLA_PADRE'];
			$SELECTED = $cla_id;
			$i=0;
			while ($cla_padre !== -1){
				$data = $this->fun_getClasificacion($cla_padre);
				$CLASIFICACION[count($CLASIFICACION)] = $data;
				$cla_padre = (int)$data['CLA_PADRE'];
				$i++;
			}			
			
			krsort($CLASIFICACION);
			
			foreach($CLASIFICACION as $key => $registro){
				$SELECTED = $registro['CLA_ID'];
				$data = $this->fun_getHijosClasificacion($registro['CLA_PADRE']);
				foreach($data as $registro_cla){
					if((int)$registro_cla['CLA_ID'] == (int)$SELECTED){
						$registro_cla['SELECTED'] = 'selected';
					}else{
						$registro_cla['SELECTED'] = '';
					}
					$this->_TEMPLATE->assign('CLASIFICACION',$registro_cla);
					$this->_TEMPLATE->assign('PADRE',$key); //Se deja key para dejar algo distinto, pero solo identifica un select
					$this->_TEMPLATE->parse('main.paso1.div_clasificacion.select_clasificacion.option_clasificacion');
				}	
				$this->_TEMPLATE->parse('main.paso1.div_clasificacion.select_clasificacion');												
			}
			//echo $SELECTED;
			//exit();
			$data = $this->fun_getHijosClasificacion($SELECTED);
			$this->_SESION->setVariable('CLASIFICACION_HOJA','N');
			
			
			
			if(count($data) > 0){
				$this->_TEMPLATE->assign('CLASIFICACION',array('CLA_ID' => $SELECTED,'CLA_NOMBRE' => ''));
				$this->_TEMPLATE->parse('main.paso1.div_clasificacion.select_clasificacion.option_clasificacion');
				$cant = 0;
				foreach($data as $registro_cla){
					
					//print_r($registro_cla);
					if(isset($registro_cla['CLA_TRADUCCION_GDOC'])){
						$cant++;
						$this->_TEMPLATE->assign('CLASIFICACION',$registro_cla);
						$this->_TEMPLATE->parse('main.paso1.div_clasificacion.select_clasificacion.option_clasificacion');
					}
				}
				if($cant > 0)
				$this->_TEMPLATE->parse('main.paso1.div_clasificacion.select_clasificacion');
			}else{
				$this->_SESION->setVariable('CLASIFICACION_HOJA','S');
			}
			//Acá se deben dejar todos los hijos
			
			//print_r($this->_TEMPLATE);
			
			$this->_TEMPLATE->parse('main.paso1.div_clasificacion');
			return $this->_TEMPLATE->text('main.paso1.div_clasificacion');
		}
		/*************************************************************************************************************************************/
		
		
		
		
		
		
		
		/*
		 * FUNCION: eliminarClasificacion
		 * Elimina desde el array CLA_ID en sesion la clasificacion que recibe por POST[CLA_ID]
		 */
		
		public function eliminarClasificacion(){								
			$json = array();
			$VAL = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();
			$MENSAJES[] = 'La Clasificacion que se eliminará es '.$_POST['CLA_ID'];
			
			$CLA_ID = $this->_SESION->getVariable('CLA_ID');
			$CLA_ID_AUX = array();
			foreach($CLA_ID as $id){
				if($id != $_POST['CLA_ID']){
					$CLA_ID_AUX[] = $id;
				}
			}
			$this->_SESION->setVariable('CLA_ID',$CLA_ID_AUX);
			$CAMBIA['#div_clasificacion'] = $this->fun_dibujaClasificacion($CLA_ID_AUX);
			$MENSAJES = array_merge($MENSAJES,$this->_MENSAJES_GRAL);
			//$CAMBIA['#arbol_propiedades'] = $this->dibujaPropiedades(); 
			//$CAMBIA['#div_propiedadesResolucion'] = $this->getHTMLPropiedades();
			//$modulo = new Modulo();
			//$modulo->setControl($this);			
			//$CAMBIA['#div_modulosClasificacion'] = $modulo->generarHTML($this->CLA_ID);
			
			
			
			if($this->_SESION->getVariable('PRI_ID')){
				$CAMBIA["#div_AutorizadosRevisar"] = $this->dibujaUsrPrivacidad();	
			}else{
				$CAMBIA["#div_AutorizadosRevisar"] = '';	
			}
			
			
			$modulo = new Modulo();
			$modulo->setControl($this);
			$CAMBIA['#div_modulosClasificacion'] = '';
			$this->_SESION->setVariable('MOD_ID_AUX',array());
			
			
			if(is_array($CLA_ID_SESION)){
				foreach($CLA_ID_SESION as $cla_id_aux){
					$MENSAJES[] = "Se generará eL HTML para los modulos de la clasificacion $cla_id_aux";
					$CAMBIA['#div_modulosClasificacion'].= $modulo->generarHTML($cla_id_aux);
				}
			}
			if($CAMBIA['#div_modulosClasificacion'] !== ''){
					$SHOW['#div_modulosClasificacion'] = '#div_modulosClasificacion';
			}
			$this->_SESION->setVariable('RES_MOD_IDs',$this->_SESION->getVariable('MOD_ID_AUX'));
			$this->_SESION->setVariable('MOD_ID_AUX',array());
				
				
			
			if(count($CLA_ID_AUX) <= 0){
				$this->_SESION->setVariable('ARBOL_CLA',false);
			}
			
			$VAL['#input_cantidadClasificacion'] = count($CLA_ID_AUX); 
			//$MENSAJES[] = print_r($CLA_ID_AUX,true);
			
			$this->seleccionarTipoEnvioClasificacion($CLA_ID_AUX);
			$CAMBIA['#div_tipoEnvio'] =  $this->getHTMLtipoEnvio($this->_SESION->getVariable('ENV_ID'));
			//<input id="" type="hidden" value="{}" />
			
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$json['VAL'] = $VAL;
			return json_encode($json);
		}
		

		/*
		 * FUNCION: fun_dibujaClasificacion
		 * Se utiliza para dibujar las clasificaciones seleccionadas y el botón para agregar mas clasificaciones
		 */ 
		
		
		
		
		public function fun_dibujaClasificacion($cla_id){
			#$this->_TEMPLATE->assign('CLASIFICACION_SELECCIONADA',$cla_id);
			//print_r($cla_id);exit();
			$caso_padre = $this->_SESION->getVariable('RES_CASO_PADRE');
			$cambia_clasificacion = 'NO';
			if(isset($caso_padre) && is_numeric($caso_padre)){
				
				$bind = array(':caso_padre' => $caso_padre);
				$prcs_id = $this->_ORA->ejecutaFunc('casos_tab.obtiene_id_proceso',$bind);
				$bind = array(':prcs_id' => $prcs_id);			
				$cambia_clasificacion = $this->_ORA->ejecutaFunc($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getPermiteCambio',$bind);

			}
			
			if(is_array($cla_id)){
				foreach($cla_id as $id){
					$txt_clasificacion = '';
					$CLASIFICACION = array();
					$data = $this->fun_getClasificacion($id);
					//print_r($data);
					$CLASIFICACION[count($CLASIFICACION)] = $data['CLA_NOMBRE'];
					$cla_padre = (int)$data['CLA_PADRE'];
					
					while ($cla_padre !== -1){
						$data = $this->fun_getClasificacion($cla_padre);
						//print_r($data);
						$CLASIFICACION[count($CLASIFICACION)] = $data['CLA_NOMBRE'];;
						$cla_padre = (int)$data['CLA_PADRE'];
						//$i++;
					}
					krsort($CLASIFICACION);			

					$txt_clasificacion.= implode("<img src='Sistema/img/siguiente-clasificacion-16.png'/>",$CLASIFICACION);
					$this->_TEMPLATE->assign('CLA_SELECCIONADA',$txt_clasificacion);
					$this->_TEMPLATE->assign('CLA_ID',$id);
					
					
					
					if($cambia_clasificacion == 'SI'){
						$this->_TEMPLATE->parse('main.paso1.clasificacion.seleccionada.eliminar_clasificacion');					
					}
					$this->_TEMPLATE->parse('main.paso1.clasificacion.seleccionada');
				}
			}
			
			 //<!-- BEGIN: seleccionada --><li>{CLA_SELECCIONADA}</li><!-- END: seleccionada -->
			 $this->_TEMPLATE->assign('CANTIDAD_CLASIFICACION',count($cla_id));
			
			if($cambia_clasificacion == 'SI'){
				$this->_TEMPLATE->parse('main.paso1.clasificacion.boton_clasificacion');
				
			}
			
			$this->_TEMPLATE->parse('main.paso1.clasificacion');
			return $this->_TEMPLATE->text('main.paso1.clasificacion');

			
			
			
			
			
			
			//Para dibujar la clasificacion se necesita tener todos los padres y luego dibujar los hijos
			$CLASIFICACION = array();
			$data = $this->fun_getClasificacion($cla_id);
			if(isset($data['ARBPRO_ID']) && $data['ARBPRO_ID'] != 0){			
				$arbol = $data['ARBPRO_ID'];
				
				
				if($this->_SESION->getVariable('CLA_ID') != $cla_id){
					$this->_MENSAJES_GRAL[] = 'La clasificacion es distinta';
					$this->_SESION->setVariable('ARBPRO_ID',$arbol);
					
					/*$prop = $this->fun_getArbolPropiedades($arbol);
					
				//	print_r($prop);
					if(isset($prop['RES_PAGO']) && strlen($prop['RES_PAGO']) > 0){
						$this->RES_PAGO_NOMBRE = $prop['RES_PAGO_NOMBRE'];
						$this->_SESION->setVariable('RES_PAGO_NOMBRE',$this->RES_PAGO_NOMBRE);
						$this->RES_PAGO = $prop['RES_PAGO'];
						$this->_SESION->setVariable('RES_PAGO',$this->RES_PAGO);
						if(strlen($this->RES_PAGO) > 0){
							$this->_SESION->setVariable('RES_PAGO_BD','S');
						}else{
							$this->_SESION->setVariable('RES_PAGO_BD','N');
						}
					}
						
					if(isset($prop['RES_MULTA']) && strlen($prop['RES_MULTA']) > 0){									
						$this->RES_MULTA_NOMBRE = $prop['RES_MULTA_NOMBRE'];
						$this->_SESION->setVariable('RES_MULTA_NOMBRE',$this->RES_MULTA_NOMBRE);
						$this->RES_MULTA = $prop['RES_MULTA'];
						$this->_SESION->setVariable('RES_MULTA',$this->RES_MULTA);
						if(strlen($this->RES_MULTA) > 0){
							$this->_SESION->setVariable('RES_MULTA_BD','S');
						}else{
							$this->_SESION->setVariable('RES_MULTA_BD','N');
						}
					}
					
					
					if(isset($prop['PUB_ID']) && strlen($prop['PUB_ID']) > 0){
						$this->PUB_NOMBRE = $prop['PUB_NOMBRE'];
						$this->_SESION->setVariable('PUB_NOMBRE',$this->PUB_NOMBRE);
						$this->PUB_ID = $prop['PUB_ID'];
						$this->_SESION->setVariable('PUB_ID',$this->PUB_ID);
						if(strlen($this->PUB_ID) > 0){
							$this->_SESION->setVariable('PUB_ID_BD','S');
						}else{
							$this->_SESION->setVariable('PUB_ID_BD','N');
						}						
					}
					if(isset($prop['DIS_NOMBRE']) && strlen($prop['DIS_NOMBRE']) > 0){
						$this->DIS_NOMBRE = $prop['DIS_NOMBRE'];
						$this->_SESION->setVariable('DIS_NOMBRE',$this->DIS_NOMBRE);
						$this->DIS_ID = $prop['DIS_ID'];
						$this->_SESION->setVariable('DIS_ID',$this->DIS_ID);
						if(strlen($this->DIS_ID) > 0){
							$this->_SESION->setVariable('DIS_ID_BD','S');
						}else{
							$this->_SESION->setVariable('DIS_ID_BD','N');
						}
					}
					
					if(isset($prop['PRI_NOMBRE']) && strlen($prop['PRI_NOMBRE']) > 0){
						$this->PRI_NOMBRE = $prop['PRI_NOMBRE'];
						$this->_SESION->setVariable('PRI_NOMBRE',$this->PRI_NOMBRE);
						$this->PRI_ID = $prop['PRI_ID'];
						$this->_SESION->setVariable('PRI_ID',$this->PRI_ID);
						if(strlen($this->PRI_ID) > 0){
							$this->_SESION->setVariable('PRI_ID_BD','S');
						}else{
							$this->_SESION->setVariable('PRI_ID_BD','N');
						}
					}*/
					$this->seteaVariablesPropiedades($arbol);
					
				}else{
					$this->_MENSAJES_GRAL[] = 'La clasificacion es la misma';
				}
			}						
			$CLASIFICACION[count($CLASIFICACION)] = $data;
			$cla_padre = (int)$data['CLA_PADRE'];
			$SELECTED = $cla_id;
			$i=0;
			while ($cla_padre !== -1){
				$data = $this->fun_getClasificacion($cla_padre);
				$CLASIFICACION[count($CLASIFICACION)] = $data;
				$cla_padre = (int)$data['CLA_PADRE'];
				$i++;
			}			
			
			krsort($CLASIFICACION);			
			foreach($CLASIFICACION as $registro){
				if((int)$registro['CLA_PADRE'] != -1){
					$SELECTED = $registro['CLA_ID'];
					$data = $this->fun_getHijosClasificacion($registro['CLA_PADRE']);
					foreach($data as $registro_cla){
						if((int)$registro_cla['CLA_ID'] == (int)$SELECTED){
							$registro_cla['SELECTED'] = 'selected';
						}else{
							$registro_cla['SELECTED'] = '';
						}
						if(isset($registro_cla['CLA_TRADUCCION_GDOC'])){
							$this->_TEMPLATE->assign('CLASIFICACION',$registro_cla);
							$this->_TEMPLATE->parse('main.paso1.clasificacion.select_clasificacion.option_clasificacion');					
						}
					}	
					$this->_TEMPLATE->parse('main.paso1.clasificacion.select_clasificacion');					
				}												
			}
			
			$data = $this->fun_getHijosClasificacion($SELECTED);
			$this->_SESION->setVariable('CLASIFICACION_HOJA','N');
			if(count($data) > 0){							
				$this->_TEMPLATE->assign('CLASIFICACION',array('CLA_ID' => $SELECTED,'CLA_NOMBRE' => ''));
				$this->_TEMPLATE->parse('main.paso1.clasificacion.select_clasificacion.option_clasificacion');				
				
				foreach($data as $registro_cla){
					if(isset($registro_cla['CLA_TRADUCCION_GDOC'])){
						$this->_TEMPLATE->assign('CLASIFICACION',$registro_cla);
						$this->_TEMPLATE->parse('main.paso1.clasificacion.select_clasificacion.option_clasificacion');				
					}
				}	
				$this->_TEMPLATE->parse('main.paso1.clasificacion.select_clasificacion');
			}else{
				$this->_SESION->setVariable('CLASIFICACION_HOJA','S');
			}
			//Acá se deben dejar todos los hijos
			
			//print_r($this->_TEMPLATE);
			$this->_TEMPLATE->parse('main.paso1.clasificacion');
			return $this->_TEMPLATE->text('main.paso1.clasificacion');						
		}
		/*************************************************************************************************************************************/
		
		
		public function setDistribucion(){
			$this->_MENSAJES_GRAL = array();
			$this->_CAMBIA_GRAL = array();
			$this->DIS_ID = $_POST['DIS_ID'];
			$this->_SESION->setVariable('DIS_ID',$this->DIS_ID);
			$data = $this->fun_getDistribucion($this->DIS_ID);
			$this->DIS_NOMBRE = $data['DIS_NOMBRE'];
			$this->_SESION->setVariable('DIS_NOMBRE',$this->DIS_NOMBRE);						
			$this->_CAMBIA_GRAL['#div_propiedadesResolucion'] = $this->getHTMLPropiedades();
			$this->_CAMBIA_GRAL['#arbol_propiedades'] = $this->dibujaPropiedades();			
		}
		
		public function setPublicacion(){
			$this->_MENSAJES_GRAL = array();
			$this->_CAMBIA_GRAL = array();
			$this->PUB_ID = $_POST['PUB_ID'];
			$this->_SESION->setVariable('PUB_ID',$this->PUB_ID);
			$data = $this->fun_getPublicacion($this->PUB_ID);
			$this->PUB_NOMBRE = $data['PUB_NOMBRE'];
			$this->_SESION->setVariable('PUB_NOMBRE',$this->PUB_NOMBRE);						
			$this->_CAMBIA_GRAL['#div_propiedadesResolucion'] = $this->getHTMLPropiedades();
			$this->_CAMBIA_GRAL['#arbol_propiedades'] = $this->dibujaPropiedades();			
		}
		
		public function setPrivacidad(){
			$this->_MENSAJES_GRAL = array();
			$this->_CAMBIA_GRAL = array();
			$this->PRI_ID = $_POST['PRI_ID'];
			$this->_SESION->setVariable('PRI_ID',$this->PRI_ID);
			$data = $this->fun_getPrivacidad($this->PRI_ID);
			$this->PRI_NOMBRE = $data['PRI_NOMBRE'];
			$this->_SESION->setVariable('PRI_NOMBRE',$this->PRI_NOMBRE);
			$this->_MENSAJES_GRAL[] = 'Se setea: '.$this->PRI_NOMBRE;
			$this->_CAMBIA_GRAL['#div_propiedadesResolucion'] = $this->getHTMLPropiedades();
			$this->_CAMBIA_GRAL['#arbol_propiedades'] = $this->dibujaPropiedades();		
			
			if($this->PRI_ID == 'reser'){
				$this->_CAMBIA_GRAL["#div_AutorizadosRevisar"] = $this->dibujaUsrPrivacidad();	
			}else{
				$this->_CAMBIA_GRAL["#div_AutorizadosRevisar"] = '';	
			}
			
		}		
		
		
		public function dibujaUsrPrivacidad(){
			//$this->_TEMPLATE->reset();
			//<!-- BEGIN: option --><option value="{OPT.VALUE}">{OPT.TEXT}</option><!-- END: option --> 
			$usuarios = $this->_SESION->getVariable('USUARIOS_TODOS');
			$usuarios_seleccionados = $this->_SESION->getVariable('PRI_ID_USR');
			//print_r($usuarios_seleccionados);
			$data = $this->_SESION->getVariable('USUARIOS_TODOS');
			//print_r($data );exit();
			if(is_array($usuarios_seleccionados)){
				foreach($usuarios_seleccionados as $usr){
			
					if(isset($data[$usr])){
						$usuario = $data[$usr];
					}else{
						$bind = array(':usr' => $usr);
						$nombre = $this->_ORA->ejecutaFunc('wfa_usr.getNombreUsuario',$bind);
						$usuario = array('EP_USUARIO' => $usr,'NOMBRE' =>$nombre );						
					}																																				
										
					$this->_TEMPLATE->assign('OPT',$usuario);
					$this->_TEMPLATE->parse('main.paso2.div_AutorizadosRevisar.option');
				}
			}
			$this->_TEMPLATE->parse('main.paso2.div_AutorizadosRevisar');
			return $this->_TEMPLATE->text('main.paso2.div_AutorizadosRevisar');
		}
		
		public function setMulta(){
			$this->_MENSAJES_GRAL = array();
			$this->_CAMBIA_GRAL = array();
			$this->RES_MULTA = $_POST['RES_MULTA'];
			$this->_SESION->setVariable('RES_MULTA',$this->RES_MULTA);
			$this->RES_MULTA_NOMBRE = ($this->RES_MULTA == 'S') ? 'Sí' : 'No';
			$this->_SESION->setVariable('RES_MULTA_NOMBRE',$this->RES_MULTA_NOMBRE);						
			$this->_CAMBIA_GRAL['#div_propiedadesResolucion'] = $this->getHTMLPropiedades();
			$this->_CAMBIA_GRAL['#arbol_propiedades'] = $this->dibujaPropiedades();			
		}		
		
		public function setPago(){
			$this->_MENSAJES_GRAL = array();
			$this->_CAMBIA_GRAL = array();
			$this->RES_PAGO = $_POST['RES_PAGO'];
			$this->_SESION->setVariable('RES_PAGO',$this->RES_PAGO);
			$this->RES_PAGO_NOMBRE = ($this->RES_PAGO == 'S') ? 'Sí' : 'No';
			$this->_SESION->setVariable('RES_PAGO_NOMBRE',$this->RES_PAGO_NOMBRE);						
			$this->_CAMBIA_GRAL['#div_propiedadesResolucion'] = $this->getHTMLPropiedades();
			$this->_CAMBIA_GRAL['#arbol_propiedades'] = $this->dibujaPropiedades();			
		}			
		
		
		
		
		
		
		
		
		/***************************************************buscarClasificacion***************************************************************/
		/** Busqueda en BD de clasificaciones																								**/
		public function buscarClasificacion(){
			$json = array();
			$bind = array(':busqueda' => $_GET['term']);
			//print_r();
			$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getBuscaClasificacion','function',$bind);			
			while($data_cla = $this->_ORA->FetchArray($cursor)){
				$txt_clasificacion = '';
				$CLASIFICACION = array();
				$data = $this->fun_getClasificacion( $data_cla['CLA_ID']);
				$CLASIFICACION[count($CLASIFICACION)] = $data['CLA_NOMBRE'];
				$cla_padre = (int)$data['CLA_PADRE'];				
				while ($cla_padre !== -1){
					$data = $this->fun_getClasificacion($cla_padre);
					$CLASIFICACION[count($CLASIFICACION)] = trim($data['CLA_NOMBRE']);
					$cla_padre = (int)$data['CLA_PADRE'];
					//$i++;
				}
				krsort($CLASIFICACION);	
				$txt_clasificacion.= implode(" > ",$CLASIFICACION);
					
				
				
				$json[] = array('id' => $data_cla['CLA_ID'],'label' => trim($txt_clasificacion));
			}			
			
			
			
			
			
			
					
			
			
			
			
			
			
			
			
			
			
			
			return json_encode($json);
		}
		/*************************************************************************************************************************************/		
		
		
		
		public function getHTMLPropiedades(){
			$data = $this->fun_getClasificacion($this->_SESION->getVariable('CLA_ID'));
			$editar = ($data['CLA_EDITAR_PROP'] == 'S') ? true : false;
			$this->_MENSAJES_GRAL [] = 'Iniciando getHTMLPropiedades';

			$this->_MENSAJES_GRAL [] = 'Editar ='.$data['CLA_EDITAR_PROP'];
			
			//Pago
			if(isset($this->RES_PAGO) && strlen($this->RES_PAGO) > 0){
				//esto quiere decir que existe dato
				if(!$editar && $this->_SESION->getVariable('RES_PAGO_BD') == 'S'){
					$this->_TEMPLATE->assign('DISABLED_PAGO','disabled');
					$this->_MENSAJES_GRAL [] = 'Pago Disabled RES_PAGO_BD='.$this->_SESION->getVariable('RES_PAGO_BD');
				}				
			}
			$this->_TEMPLATE->assign('SELECTED_PAGO_'.$this->RES_PAGO,'selected');
			$this->_TEMPLATE->parse('main.paso2.div_propiedadesResolucion.div_propiedadPago');
			//Multa
			if(isset($this->RES_MULTA) && strlen($this->RES_MULTA) > 0){
				//esto quiere decir que existe dato
				if(!$editar && $this->_SESION->getVariable('RES_MULTA_BD') == 'S'){
					$this->_TEMPLATE->assign('DISABLED_MULTA','disabled');
					$this->_MENSAJES_GRAL [] = 'Multa Disabled RES_MULTA_BD='.$this->_SESION->getVariable('RES_MULTA_BD');	
				}				
			}
			$this->_TEMPLATE->assign('SELECTED_MULTA_'.$this->RES_MULTA,'selected');				
			$this->_TEMPLATE->parse('main.paso2.div_propiedadesResolucion.div_propiedadMulta');
			//Publicación
			if(isset($this->PUB_ID) && strlen($this->PUB_ID) > 0){
				//esto quiere decir que existe dato
				if(!$editar && $this->_SESION->getVariable('PUB_ID_BD') == 'S'){
					$this->_TEMPLATE->assign('DISABLED_PUBLICACION','disabled');
					$this->_MENSAJES_GRAL [] = 'Publicacion Disabled PUB_ID_BD='.$this->_SESION->getVariable('PUB_ID_BD');	
				}				
			}
			$data = $this->fun_getPublicacion();
			foreach($data as $registro){
				if($this->PUB_ID == $registro['PUB_ID']){
					$registro['SELECTED'] = 'selected';
				}else{
					$registro['SELECTED'] = '';
				}
				
				$this->_TEMPLATE->assign('PUB',$registro);	
				$this->_TEMPLATE->parse('main.paso2.div_propiedadesResolucion.div_propiedadPublicacion.option_publicacion');	
			}
			$this->_TEMPLATE->parse('main.paso2.div_propiedadesResolucion.div_propiedadPublicacion');
			//Distribucion
			
			if(isset($this->DIS_ID) && strlen($this->DIS_ID) > 0){
				//esto quiere decir que existe dato
				if(!$editar && $this->_SESION->getVariable('DIS_ID_BD') == 'S'){
					$this->_TEMPLATE->assign('DISABLED_DISTRIBUCION','disabled');	
					$this->_MENSAJES_GRAL [] = 'Distribucion Disabled DIS_ID_BD='.$this->_SESION->getVariable('DIS_ID_BD');
				}
				
			}
			
			$data = $this->fun_getDistribucion();
			foreach($data as $registro){
				if($this->DIS_ID == $registro['DIS_ID']){
					$registro['SELECTED'] = 'selected';
				}else{
					$registro['SELECTED'] = '';
				}
				
				$this->_TEMPLATE->assign('DIS',$registro);	
				$this->_TEMPLATE->parse('main.paso2.div_propiedadesResolucion.div_propiedadDistribucion.option_distribucion');	
			}
			$this->_TEMPLATE->parse('main.paso2.div_propiedadesResolucion.div_propiedadDistribucion');
			
			
			
			
			if(isset($this->PRI_ID) && strlen($this->PRI_ID) > 0){
				//esto quiere decir que existe dato
				if(!$editar && $this->_SESION->getVariable('PRI_ID_BD') == 'S'){
					$this->_TEMPLATE->assign('DISABLED_PRIVACIDAD','disabled');	
					$this->_MENSAJES_GRAL [] = 'Privacidad Disabled PRI_ID_BD='.$this->_SESION->getVariable('PRI_ID_BD');
				}
				
			}
			
			$data = $this->fun_getPrivacidad();
			foreach($data as $registro){
				if($this->PRI_ID == $registro['PRI_ID']){
					$registro['SELECTED'] = 'selected';
				}else{
					$registro['SELECTED'] = '';
				}
				
				$this->_TEMPLATE->assign('PRI',$registro);	
				$this->_TEMPLATE->parse('main.paso2.div_propiedadesResolucion.div_propiedadPrivacidad.option_privacidad');	
			}
			
			
			
			$this->_TEMPLATE->parse('main.paso2.div_propiedadesResolucion.div_propiedadPrivacidad');
	
			

			$this->_TEMPLATE->parse('main.paso2.div_propiedadesResolucion');
			return $this->_TEMPLATE->text('main.paso2.div_propiedadesResolucion');
		}
		
		
		
		public function fun_getPublicacion($id =NULL){
			if($this->_SESION->getVariable('_CACHE_PUBLICACION') === false ){
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getPublicacion','function');			
				$data = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('_CACHE_PUBLICACION',$data);
			}
			if($id === NULL){
				return $this->_SESION->getVariable('_CACHE_PUBLICACION');
			}else{
				foreach($this->_SESION->getVariable('_CACHE_PUBLICACION') as $data){
					if($data['PUB_ID'] == $id){
						return $data;
					}
				}
			}
		}
		
		public function fun_getPrivacidad($id =NULL){
			if($this->_SESION->getVariable('_CACHE_PRIVACIDAD') === false ){
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getPrivacidad','function');			
				$data = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('_CACHE_PRIVACIDAD',$data);
			}
			if($id === NULL){
				return $this->_SESION->getVariable('_CACHE_PRIVACIDAD');
			}else{
				foreach($this->_SESION->getVariable('_CACHE_PRIVACIDAD') as $data){
					if($data['PRI_ID'] == $id){
						return $data;
					}
				}
			}
		}		
		
		
		
		
		
		public function fun_getDistribucion($id =NULL){
			if($this->_SESION->getVariable('_CACHE_DISTRIBUCION') === false ){
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getDistribucion','function');			
				$data = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('_CACHE_DISTRIBUCION',$data);
			}
			if($id === NULL){
				return $this->_SESION->getVariable('_CACHE_DISTRIBUCION');
			}else{
				foreach($this->_SESION->getVariable('_CACHE_DISTRIBUCION') as $data){
					if($data['DIS_ID'] == $id){
						return $data;
					}
				}
			}
		}
		
		
		
		
		
		
		
		/***************************************************buscarClasificacion***************************************************************/
		/** Retorna la data sde un arbol de clasificacion																					**/		
		/*public function getArbolClasificacion($TIPRES_ID){
			$id_padre = $this->fun_getIdClasificacionTipo($TIPRES_ID);
			$data = $this->fun_getHijosClasificacion($id_padre);
			return $data;			
		}*/
		/*************************************************************************************************************************************/		
		
		
		
		
		
				
		/***************************************************dibujaPropiedades*****************************************************************/
		/** Obtiene el HTML de las propiedades 																								**/
		public function dibujaPropiedades(){
			
			
			if($this->RES_PAGO_NOMBRE !== 'No'){
				$this->_TEMPLATE->assign('RES_PAGO_NOMBRE',$this->RES_PAGO_NOMBRE);
				$this->_TEMPLATE->parse('main.propiedades.cobro_derechos');
			}
			if($this->RES_MULTA_NOMBRE !== 'No'){
				$this->_TEMPLATE->assign('RES_MULTA_NOMBRE',$this->RES_MULTA_NOMBRE);
				$this->_TEMPLATE->parse('main.propiedades.multa');
			}
			$this->_TEMPLATE->assign('PUB_NOMBRE',$this->PUB_NOMBRE);
			$this->_TEMPLATE->assign('DIS_NOMBRE',$this->DIS_NOMBRE);
			$this->_TEMPLATE->assign('PRI_NOMBRE',$this->PRI_NOMBRE);
			$this->_TEMPLATE->assign('JURISPRIDENCIA',($this->_SESION->getVariable('RES_JURISPRUDENCIA')== 'S') ? 'Sí' : 'No');
			$TIPOS = array();
			$tipos_env = $this->_SESION->getVariable('ENV_ID');
			//print_r($tipos_env);
			//exit();
			if(is_array($tipos_env)){
				foreach($tipos_env as $tipo){
					//print_r($tipo);
					$data = $this->getTipoEnvio($tipo['ENV_ID']);
					//print_r($data);
					$TIPOS[] = $data['ENV_NOMBRE'];
				}
			}
		//	print_r($TIPOS);
			$TIPOS_ENVIO = implode(',',$TIPOS);
			$this->_TEMPLATE->assign('TIPO_ENVIO',$TIPOS_ENVIO);
			
			
			$this->_TEMPLATE->parse('main.propiedades');
			return $this->_TEMPLATE->text('main.propiedades');
		}
		/*************************************************************************************************************************************/		
		
		
		
		
		
		

		/***************************************************fun_getIdClasificacionTipo********************************************************/
		/** Obtiene el id de la clasificacion (TIPO)    																					**/
		public function fun_getIdClasificacionTipo($TIPRES_ID){
			$bind = array(':tipres_id' => $TIPRES_ID);
			$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getIdClasificacionTipo','function',$bind);
			$data = $this->_ORA->FetchArray($cursor);
			return $data['CLA_ID'];
		}
		/*********************************************************fun_getClasificacion********************************************************/
		
		
		
		
		
		
		/***************************************************fun_getClasificacion********************** ***************************************/
		/** Obtiene el registro perteneciente a una clasificacion desde la BD 																**/
		public function fun_getClasificacion($cla_id){
			if($this->_SESION->getVariable('_CACHE_CLA_ID_'.$cla_id) === false){				
				$bind = array(':cla_id' => $cla_id);
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getClasificacion','function',$bind);
				$data =  $this->_ORA->FetchArray($cursor);
				$this->_SESION->setVariable('_CACHE_CLA_ID_'.$cla_id,$data);
			}
			
			return $this->_SESION->getVariable('_CACHE_CLA_ID_'.$cla_id);
		}
		
		
		/*************************************************************************************************************************************/
		
		
		public function fun_getDistribucionID($dis_id){
			if($this->_SESION->getVariable('_CACHE_DIS_ID_'.$dis_id) === false){	
				$bind = array();
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getDistribucion','function',$bind);
				$data =  $this->_ORA->FetchAll($cursor);
				//print_r($data);
				$data_reg = array();
				foreach($data as $reg){
					if($reg['DIS_ID'] == $dis_id){
						$data_reg  = $reg;
					}
				}
				$this->_SESION->setVariable('_CACHE_DIS_ID_'.$dis_id,$data_reg);
			}
			$return = $this->_SESION->getVariable('_CACHE_DIS_ID_'.$dis_id);
			//print_r($return);
			return $return;
		}
		
		
		
	
		/*****************************************************fun_getHijosClasificacion*******************************************************/	
		/** Obtiene los Hijos de una clasificacion determinada 																				**/
		public function fun_getHijosClasificacion($cla_id){
			if($this->_SESION->getVariable('_CACHE_HIJO_CLA_ID_'.$cla_id) === false){
				$bind = array(':cla_id' => $cla_id);
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getClasificacionHijos','function',$bind);
				$data = $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('_CACHE_HIJO_CLA_ID_'.$cla_id,$data);
			}
			return $this->_SESION->getVariable('_CACHE_HIJO_CLA_ID_'.$cla_id);
		}
		/*************************************************************************************************************************************/

		
		public function getTipoEnvio($id = NULL){
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
		
		
		
		
		/***************************************************fun_getArbolPropiedades***********************************************************/
		/** Obtiene las propiedades asociadas a un arbol de propiedad 																		**/
		public function fun_getArbolPropiedades($arbpro_id){
			//if($this->_SESION->getVariable('_CACHE_ARBOL_PROP_CLA_ID_'.$arbpro_id) === false){
				$bind = array(':cla_id' => $arbpro_id);
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getArbolPropiedades','function',$bind);
				$data =  $this->_ORA->FetchArray($cursor);
				$this->_SESION->setVariable('_CACHE_ARBOL_PROP_CLA_ID_'.$arbpro_id,$data);
			//}
			return $this->_SESION->getVariable('_CACHE_ARBOL_PROP_CLA_ID_'.$arbpro_id);
		}
		/*************************************************************************************************************************************/
		
		public function fun_claIsHoja($cla_id){
			$data = $this->fun_getHijosClasificacion($cla_id);
			
			if(count($data) > 0){				
				return false;
			}else{
				return true;
			}			
		}
		
		
		
		public function getEnviosClasificacion($cla_id){
			if($this->_SESION->getVariable('_CACHE_ENVIOS_CLA_ID_'.$cla_id) === false){
				$bind = array(':cla_id' => $cla_id);
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getEnviosClasificacion','function',$bind);
				$data =  $this->_ORA->FetchAll($cursor);
				$this->_SESION->setVariable('_CACHE_ENVIOS_CLA_ID_'.$cla_id,$data);
			}
			return $this->_SESION->getVariable('_CACHE_ENVIOS_CLA_ID_'.$cla_id);
			
		}
		
		
		
		public function getHTMLtipoEnvio($ARRAY_ENVIO){
			
			$data = $this->getTipoEnvio();
			foreach($data as $registro){
				$registro['CHECKED'] = '';
				if(is_array($ARRAY_ENVIO)){
					foreach($ARRAY_ENVIO as $checkeado){
						if($checkeado['ENV_ID'] == $registro['ENV_ID']){
							$registro['CHECKED'] = 'checked';
						}
					}
				}			
				$this->_TEMPLATE->assign('TIPENV',$registro);
				$this->_TEMPLATE->parse('main.paso2.div_tipoEnvio.select_tipoEnvio.option_tipoEnvio');
			}

			

			$this->_TEMPLATE->parse('main.paso2.div_tipoEnvio.select_tipoEnvio');
			$this->_TEMPLATE->parse('main.paso2.div_tipoEnvio');
			
			return $this->_TEMPLATE->text('main.paso2.div_tipoEnvio');
	
		}
		
		public function seleccionarTipoEnvioClasificacion($CLA_ID_SESION){
			$SEL_ENVIO_ARRAY = array();
			// De acuerdo a la cantidad de clasificaciones seleccionadas se procederá a seleccionar el tipo de envio
			foreach($CLA_ID_SESION as $cla_id){
				$sel_envio = $this->getEnviosClasificacion($cla_id);
				if(is_array($sel_envio)){
					$this->_LOG->log(print_r($sel_envio,true));						
					foreach($sel_envio as $env_id){
						$selector = (isset($SEL_ENVIO_ARRAY[$env_id['ENV_ID']])) ? $SEL_ENVIO_ARRAY[$env_id['ENV_ID']]['CLAENV_MOD'] : 'N';
						$this->_LOG->log("Selector Inicial para ".$env_id['ENV_ID']." es: $selector");
						$env_id['CLAENV_MOD'] = ($selector == 'S') ? 'S' : $env_id['CLAENV_MOD'];
						unset($env_id['CLA_ID']);
						$SEL_ENVIO_ARRAY[$env_id['ENV_ID']] = $env_id;
					}
				}
			}
			$this->_LOG->log(print_r($SEL_ENVIO_ARRAY,true));						
			$this->_SESION->setVariable('ENV_ID',$SEL_ENVIO_ARRAY);
			
		}
	}

?>