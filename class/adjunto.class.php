<?php
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	ini_set("display_errors", 1); 
	require_once('class/archivo.class.php');
	
	class Adjunto extends ClaseSistema{
		public $CON_ELIMINAR = true;
		
		public function dropSubirAdjunto(){
			


			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();																		
													
			$tipo = $_POST['v_tipo'];
			$id = $_POST['v_id'];
			$MENSAJES[] = "La var: tipo es $tipo";
			$MENSAJES[] = "La var: id es $id";
			
		
			$nombre = $this->getColumnaArchivoExpediente('Nombre', $id);
			$hash = $this->getColumnaArchivoExpediente('Hash', $id);
			//$nombreArchivo = $this->getColumnaArchivoExpediente('Nombre_Archivo', $id);
			$nombreArchivo = $this->getColumnaArchivoExpediente('Nombre', $id);
			$mime = $this->getColumnaArchivoExpediente('Mime', $id);

			//print_r($nombre."//".$hash."//".$mime);print("<br>");exit();
			
		    

			$MENSAJES[] = "La var: nombre es $nombre";
			$MENSAJES[] = "La var: hash es $hash";
			$MENSAJES[] = "La var: Nombre_Archivo es $nombreArchivo";
			$MENSAJES[] = "La var: Mime es $mime";
			

			


			// como identificador del archivo se dejará la variable hash... lo que no permitirá adjuntar 2 veces el mismo archivo 
			
			if (strpos(strtolower(nombre), '.pdf') !== FALSE || strpos(strtolower($nombreArchivo), '.pdf') !== FALSE || strpos(strtolower($mime), '/pdf') !== FALSE ){
			
			
				$data = array('ID'=>$id, 'ADJ_NOMBRE'=>$nombre, 'CODIGO'=>NULL, 'LINK'=>NULL, 'TEMP'=>NULL, 'MIME'=>NULL,'ADJ_HASH'=>$hash,'SGD'=>NULL,'TIPO'=>'exp','GRABADO'=>'N');
				$adjuntos = $this->_SESION->getVariable("RSO_ADJUNTO");
				if(!is_array($adjuntos)){
						$adjuntos = array();
				}
				$adjuntos[$hash] = $data;
				$this->_SESION->setVariable("RSO_ADJUNTO",$adjuntos);
			
			}else{
				$json['ALERT'][] = 'Sólo se pueden adjuntar archivos de tipo PDF';
			}
			
			$CAMBIA["#div_adjuntosResolucion"] = $this->dibujaHTML();
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;

			//var_dump($json);exit();


			return json_encode($json);
		}
		
		public function eliminarAdjunto(){
			$json = array();
			$json['RESULTADO'] = 'OK';
			$MENSAJES = array();
			$CAMBIA = array();																		
													
			
			$adjuntos = $this->_SESION->getVariable("RSO_ADJUNTO");
			if(!is_array($adjuntos)){
					$adjuntos = array();
			}

			unset($adjuntos[$_POST['id']]);

			$this->_SESION->setVariable("RSO_ADJUNTO",$adjuntos);
			$CAMBIA["#div_adjuntosResolucion"] = $this->dibujaHTML();
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
		}
		
		public function dibujaHTML(){
			$adjuntos = $this->_SESION->getVariable("RSO_ADJUNTO");
			
			if($adjuntos!== false){
				foreach($adjuntos as $adj){
					$adj['VAL'] = substr(md5(md5($adj['ID'])),3,5);
					$this->_TEMPLATE->assign('ADJ',$adj);
					if($this->CON_ELIMINAR){
						$this->_TEMPLATE->parse('main.div_adjuntosResolucion.adjunto.eliminar');
					}
					$this->_TEMPLATE->parse('main.div_adjuntosResolucion.adjunto');
				}
			}
			$this->_TEMPLATE->parse('main.div_adjuntosResolucion');
			return $this->_TEMPLATE->text('main.div_adjuntosResolucion');
		}
		
		
		
		public function getAdjunto($numero_expediente){
			try{
				$row = $this->getExpedienteDocumento($numero_expediente);	
				//echo "<pre>";print_r($row);echo "</pre>";	exit();
				$bind_v = array(':id' => $row['ID_SISTEMA']);
				$cursor_variable = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getVariablesFuncion','function',$bind_v);
				$array = array();
				while($row_var = $this->_ORA->FetchArray($cursor_variable)){
					$array[] = $row[$row_var['WFA_VARIABLE']];		
				}
		
				//echo "<pre>";print_r($array);echo "</pre>";	exit();
				$xml = $this->ejecutarFuncionXml($row['WFA_PACKAGE'],$row['WFA_FUNCION_XML'],$array );
			
				if ((string)$xml->Link == ""){
					$obj = $this->ejecutarFuncionArchivo($row['WFA_PACKAGE'],$row['WFA_FUNCION_ARCHIVO'],$array);
					return $obj->load();
				}else{
					
					/*					
					$link = $this->quitarHttpLink($xml->Link);
				  	$object = new Archivo($link);
				  	//2010.03.12 JLSV: Se cambia para poder ver los xml de respuesta en paso 4.
				  	$object->setBajar(FALSE);			  
					ob_start();
				  	$object->getArchivo();
					$xml = ob_get_contents();					
					ob_end_clean();
			
					$xml = str_replace("&ndash;","-",$xml);
					$xml = str_replace("&aacute;","á",$xml);
					$xml = str_replace("&eacute;","é",$xml);
					$xml = str_replace("&racute;","í",$xml);
					$xml = str_replace("&oacute;","ó",$xml);
					$xml = str_replace("&uacute;","ú",$xml);
					$xml = str_replace("&Aacute;","Á",$xml);
					$xml = str_replace("&Eacute;","É",$xml);
					$xml = str_replace("&Iacute;","Í",$xml);
					$xml = str_replace("&Oacute;","Ó",$xml);
					$xml = str_replace("&Uacute;","Ú",$xml);										
					echo $xml;
					exit();
					*/
				}
			}catch(Exception $e){
				print_r($e);
			}		
		}


				
		
		public function verAdjunto(){
			try{
				if(substr(md5(md5($_GET['exp'])),3,5) == $_GET['val']){
					$row = $this->getExpedienteDocumento($_GET['exp']);	
					//echo "<pre>";print_r($row);echo "</pre>";	exit();
					$bind_v = array(':id' => $row['ID_SISTEMA']);
					$cursor_variable = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getVariablesFuncion','function',$bind_v);
					$array = array();
					while($row_var = $this->_ORA->FetchArray($cursor_variable)){
						$array[] = $row[$row_var['WFA_VARIABLE']];		
					}
			
					//echo "<pre>";print_r($array);echo "</pre>";	exit();
					$xml = $this->ejecutarFuncionXml($row['WFA_PACKAGE'],$row['WFA_FUNCION_XML'],$array );
				
					if ((string)$xml->Link == ""){
						$obj = $this->ejecutarFuncionArchivo($row['WFA_PACKAGE'],$row['WFA_FUNCION_ARCHIVO'],$array);
						header('Content-type:'.$xml->Mime);
						header('Content-Disposition: filename="'.($xml->Nombre).'"');
						echo $obj->load();
						exit();
					}else{
						if($row['ID_SISTEMA'] == 'sgd'){
							 header ("Location: ".(string)$xml->Link);
						 }else{
											
							$link = $this->quitarHttpLink($xml->Link);
							$object = new Archivo($link);
							//2010.03.12 JLSV: Se cambia para poder ver los xml de respuesta en paso 4.
							$object->setBajar(FALSE);			  
							ob_start();
							$object->getArchivo();
							$xml = ob_get_contents();					
							ob_end_clean();
					
							$xml = str_replace("&ndash;","-",$xml);
							$xml = str_replace("&aacute;","á",$xml);
							$xml = str_replace("&eacute;","é",$xml);
							$xml = str_replace("&racute;","í",$xml);
							$xml = str_replace("&oacute;","ó",$xml);
							$xml = str_replace("&uacute;","ú",$xml);
							$xml = str_replace("&Aacute;","Á",$xml);
							$xml = str_replace("&Eacute;","É",$xml);
							$xml = str_replace("&Iacute;","Í",$xml);
							$xml = str_replace("&Oacute;","Ó",$xml);
							$xml = str_replace("&Uacute;","Ú",$xml);										
							echo $xml;
							exit();
						}
					}
				}else{
					echo "Error al momento de intentar de ver un archivo (problemas de seguridad)";
				}
			}catch(Exception $e){
				print_r($e);
			}		
		}
		
		
		
		
		
		
		
		
		
		
		
		
		//------------------------------------------------------------------------------------------------------
		// --------------------------FUNCIONES DE EXPEDIENTE ---------------------------------------------------
		//------------------------------------------------------------------------------------------------------
		
		protected function  getColumnaArchivoExpediente($columna, $id){
			$bind = array(":columna" => $columna, ":id" => $id);
			return $this->_ORA->ejecutaFunc("WFA_DOCTOS_PKG.getColumna",$bind);				
		}
		
		protected function ejecutarFuncionXml($package,$funcion, $variable){
			$cant = count($variable);			
			$bindPkg = array();
			foreach($variable as $key => $var){				
				$bindPkg[":var$key"] = $var;
			}			
			$xml = $this->_ORA->ejecutaFunc($package.".".$funcion,$bindPkg);			
			try{
				$xml2=$this->htmlentities_entities($xml);
				//$obj = new SimpleXMLElement(utf8_encode($xml2));
				$obj = new SimpleXMLElement(($xml2));
			}catch(Exception $e){	
				$obj = NULL;
			}
			return $obj;			
		}
		
		public function getExpedienteDocumento($id){
			
			$bind = array(':id' => $id);
			$cursor = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getDocumento','function',$bind);
			return $this->_ORA->FetchArray($cursor);
		}
		
		public function ejecutarFuncionArchivo($package,$funcion, $variable){
			$cant = count($variable);			
			$bindPkg = array();
			foreach($variable as $key => $var){				
				$bindPkg[":var$key"] = $var;
			}
		
			$blob = $this->_ORA->ejecutaFunc($package.".".$funcion,$bindPkg,'BLOB');
			return $blob;
		}
		//--------------------------------------------------------------------------------------------------------
		
		
		
		
		
		
		
		
		
		
		//--------------------------------------------------------------------------------------------------------
		//-----------------------------------OTRAS DE CONVERSION -------------------------------------------------
		//--------------------------------------------------------------------------------------------------------
		
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
		
		
		private function quitarHttpLink($link){
	  		preg_match_all('/http:\/\/[0-9]*.[0-9]*.[0-9]*.[0-9]*(\/.*)/',$link,$out, PREG_PATTERN_ORDER,$off);
			$link = "/web/SVS_website".$out[1][0];
			return $link;
	  }
		
		//--------------------------------------------------------------------------------------------------------
		
		
	}

?>