<?php
	require_once('Sistema/class/expediente.class.php');
	class Paso4 extends ClaseSistema{
		
		//Esto es como si reemplazara el main

		
		public function generarHTML(){		
			$this->_RESOLUCION->ADJUNTO_OBJ->dibujaHTML();
			
			
			$expediente = new Expediente();
			$expediente->setControl($this);
			$expediente->CASO_PADRE = $this->_SESION->getVariable('RES_CASO_PADRE');
			$expediente->SOLO_VER = FALSE;
			$expediente->PARSER_ANTERIOR = 'main.paso4';
			$expediente->dibujarExpedientes();
			
			
			/** REVISAR EL EXPEDIENTE **/	
			/*$bind = array(':padre' => $this->_SESION->getVariable('RES_CASO_PADRE'));
			$cursor = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getExpediente','function',$bind);
			$principal = false;
			$nivel = 1;
			$retroceso = "";
			$cerrar = true;
			$array_folder = array();
			$XML_VARIABLE  = new XMLWriter();
			$XML_VARIABLE->openMemory();
			$XML_VARIABLE->setIndent(true);
			$XML_VARIABLE->startElement("ul");
			$XML_VARIABLE->writeAttribute("id", 'archivosSugerencia');
			$XML_VARIABLE->writeAttribute("class", 'filetree');
			
			
			while($row = $this->_ORA->FetchArray($cursor)){
				$bind_v = array(':id' => $row['ID_SISTEMA']);
				$cursor_variable = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getVariablesFuncion','function',$bind_v);
				$array = array();
				while($row_var = $this->_ORA->FetchArray($cursor_variable)){
					$array[] = $row[$row_var['WFA_VARIABLE']];		
				}	
				$obj = $this->ejecutarFuncionXml($row['WFA_PACKAGE'],$row['WFA_FUNCION_XML'],$array );
				
				$nombre = ($row['WFA_SUBTIPO']=='oficio') ? 'Oficio' : $obj->Nombre;				
				$tipo = ($row['WFA_SUBTIPO']=='adjunto_oficio') ? 'Adj. ' : "";
				
				$ver = "index.php?pagina=paginas.redactar&funcion=adjunto&exp=".$row['WFA_ID_DOCUMENTO'];
				$XML_VARIABLE->startElement("li");
					$XML_VARIABLE->startElement("span");
					$XML_VARIABLE->writeAttribute("class", 'draggable drag');
						$XML_VARIABLE->startElement("input");
						$XML_VARIABLE->writeAttribute("type", 'hidden');
						$XML_VARIABLE->writeAttribute("class", 'id_doc');
						$XML_VARIABLE->writeAttribute("value", $row['WFA_ID_DOCUMENTO']);
						$XML_VARIABLE->endElement();
						
						$XML_VARIABLE->startElement("input");
						$XML_VARIABLE->writeAttribute("type", 'hidden');
						$XML_VARIABLE->writeAttribute("class", 'id_sistema');
						$XML_VARIABLE->writeAttribute("value", $row['ID_SISTEMA']);
						$XML_VARIABLE->endElement();
						
						$XML_VARIABLE->startElement("input");
						$XML_VARIABLE->writeAttribute("type", 'hidden');
						$XML_VARIABLE->writeAttribute("class", 'id');
						$XML_VARIABLE->writeAttribute("value", $row['WFA_ID_REFERENCIAL']);
						$XML_VARIABLE->endElement();
						
						$XML_VARIABLE->startElement("input");
						$XML_VARIABLE->writeAttribute("type", 'hidden');
						$XML_VARIABLE->writeAttribute("class", 'tipo');
						$XML_VARIABLE->writeAttribute("value", $row['WFA_TIPO']);
						$XML_VARIABLE->endElement();
						
						
						
						$XML_VARIABLE->startElement("span");
						$XML_VARIABLE->writeAttribute("class", 'file');
							$XML_VARIABLE->startElement("img");
							$XML_VARIABLE->writeAttribute("src", '/biblioteca/images/jquery/treeview/file.gif');
							$XML_VARIABLE->endElement();
							
							$XML_VARIABLE->startElement("span");
							$XML_VARIABLE->text(":::".$tipo." ".$nombre);
							$XML_VARIABLE->endElement();								
						
							$XML_VARIABLE->startElement("span");
							$XML_VARIABLE->writeAttribute("style", 'cursor:pointer');
							$XML_VARIABLE->writeAttribute("onclick", "fun_dropSubirAdjunto('".$row['WFA_TIPO']."','".$row['WFA_ID_DOCUMENTO']."')");
								$XML_VARIABLE->startElement("img");
								$XML_VARIABLE->writeAttribute("src", 'Sistema/img/subir.png');
								$XML_VARIABLE->writeAttribute("width", '10');
								$XML_VARIABLE->writeAttribute("height", '14');
								$XML_VARIABLE->text("[Subir]   ");
								$XML_VARIABLE->endElement();
								
								$XML_VARIABLE->startElement("a");
								$XML_VARIABLE->writeAttribute("style", 'cursor:pointer');
								$XML_VARIABLE->writeAttribute("target", '_blank');
								$XML_VARIABLE->writeAttribute("href", $ver);
								
									$XML_VARIABLE->startElement("img");
									$XML_VARIABLE->writeAttribute("src", 'Sistema/img/doc.png');			
									$XML_VARIABLE->endElement();
								$XML_VARIABLE->endElement();		
						
						
							$XML_VARIABLE->endElement();
							
				
						$XML_VARIABLE->endElement();
					$XML_VARIABLE->endElement();
				$XML_VARIABLE->endElement();						
			}
				
			
			$XML_VARIABLE->endElement();
												
			
			$TXT_DATOS_VARIABLES = $XML_VARIABLE->outputMemory(true);
			$this->_TEMPLATE->assign('XML_EXPEDIENTE',$TXT_DATOS_VARIABLES);
				
			//echo 	$TXT_DATOS_VARIABLES;
				
			$this->cargarExpedientes();*/
			//echo $this->_SESION->getVariable('INSTANCIA');
			if($this->_SESION->getVariable('INSTANCIA') != 'VISAR'){
				$this->_TEMPLATE->parse('main.paso4.boton_enviar');
			}
			
			$this->_TEMPLATE->parse('main.paso4');
			
		}
		
		/*
		
		public function cargarExpedientes(){
			
			//Acá me falta estebecer seguridad para saber que los archivos del expediente padre tengo acceso
			
			$bind = array(':padre' => $this->_SESION->getVariable('RES_CASO_PADRE'));
			$cursor = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getExpediente','function',$bind);
			
			$arr_exp = array();
			$num = 0;
			while($row = $this->_ORA->FetchArray($cursor)){
				//print_r($row);
				$array = array();
				$bind_v = array(':id' => $row['ID_SISTEMA']);
				
				$cursor_variable = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getVariablesFuncion','function',$bind_v);
				while($row_var = $this->_ORA->FetchArray($cursor_variable)){
					$array[] = $row[$row_var['WFA_VARIABLE']];		
				}				

			
				$obj = $this->ejecutarFuncionXml($row['WFA_PACKAGE'],$row['WFA_FUNCION_XML'],$array );	
				
				
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
				$exp = array('BGCOLOR'=>$color,'WFA_ID_REFERENCIAL'=> $row['WFA_ID_REFERENCIAL'],'WFA_TIPO'=>$row['WFA_TIPO'],'ID_SISTEMA'=>$row['ID_SISTEMA'],'ID_DOC'=>$row['WFA_ID_DOCUMENTO'],'FECHA'=>$row['WFA_FECHA'],'DESCRIPCION'=>$nombre,'SGD'=>$sgd,'VER'=>$ver);
				

				$this->_TEMPLATE->assign('EXP',$exp);
				$this->_TEMPLATE->parse('main.paso4.registro_expediente_fecha');
				$num++;
			}
			//{EXP.
		//	print_r($arr_exp);

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
		} */
		

	}

?>