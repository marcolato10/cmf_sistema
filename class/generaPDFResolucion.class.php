<?php
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	ini_set("display_errors", 1); 
	
	require_once('fpdf16/fpdf.php');
	require_once('fpdi_1.5.4/fpdi.php');
	require_once('phpqrcode/qrlib.php');
	
	
		
	class generaPDFResolucion{
		
		
		public $_FIRMA_IMG = true;
		//public $_ES_FINAL = false;
		public $_POSICION_FIRMA = 'NP';
		//public $NUMERO_OFICIO = 'Sin Número';
		//public $NUMERO_SGD = 'SINNUMEROSGD';
		//public $NUMERO_FOLIO = 'SINFOLIONOFIRMADO';
		//public $VISTA_PREVIA = 'N';
		//public $DOC_ID;
		//public $DOC_VERSION;
		//public $CARGO;
		//public $USUARIO;
		//public $AMBIENTE;
		//public $RETORNA_PDF = false;
		public $INICIO_PAGINA_ANEXO;
		private $HTML;
		private $NUMERO_RESOLUCION = false;
		private $CARGO_RESOLUCION;
		public $FOLIO_RESOLUCION = 'RES-0001-16-00001-A';
		public $LINK_VALIDACION = 'http://www.cmfchile.cl/institucional/validar/validar.php';
		public $LINK_RESOLUCION;
		public $NUMERO_SGD = 'SINNUMEROSGD';
		public $GENERADA_PARA_FIRMA = false;
		public $TIPO;
		public $debug = false;
		public $usr_debug = '';
		public $cargo_debug = '';
		public $_SESION;
		public $AMBIENTE;
		private $SANGRIA = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		public $_ORA;
		private $LINK_IMAGEN_FIRMA = NULL;
		public $CARACTERES = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		
		
		public function __construct($SESION, $ORA = NULL){
			$this->_SESION = $SESION;
			$this->_ORA = $ORA;
			$this->_POSICION_FIRMA = ($this->_SESION->getVariable('_POSICION_FIRMA')) ? $this->_SESION->getVariable('_POSICION_FIRMA') : $this->_POSICION_FIRMA;
		}
		
		private function generaHTML(){
			try{
				if(isset($this->_LOG))
				$this->_LOG->log('INICIO generaHTML');
				$meses = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
				$TEMPLATE = new XTemplate('Sistema/paginas/plantillas/vistaPreviaResolucion.html');
				$RES_REFERENCIA = $this->limpiarTexto($this->_SESION->getVariable('RES_REFERENCIA'));
				$RES_VISTOS = $this->limpiarTexto($this->_SESION->getVariable('RES_VISTOS'));
				$RES_CONSIDERANDO = $this->limpiarTexto($this->_SESION->getVariable('RES_CONSIDERANDO'));
				$RES_RESUELVO = $this->limpiarTexto($this->_SESION->getVariable('RES_RESUELVO'));
				$RES_COMUNIQUESE = $this->limpiarTexto($this->_SESION->getVariable('RES_COMUNIQUESE'));
				
				
				if($this->NUMERO_RESOLUCION !== false){
					$RAND = rand ( 10000 , 99999 );
					$suma = $this->NUMERO_RESOLUCION.date('y').$RAND;
					$suma = array_sum(str_split($suma));
					$suma = $suma%26;
					$VERIFICADOR = $this->CARACTERES[$suma];
					$this->LINK_VALIDACION = ($this->AMBIENTE == 'DESA') ? 'http://svsweb4.svs.local/institucional/validar/validar.php' : $this->LINK_VALIDACION;
					$this->LINK_VALIDACION = ($this->AMBIENTE == 'TEST') ? 'http://svsweb6.svs.local/institucional/validar/validar.php' : $this->LINK_VALIDACION;
					$this->LINK_VALIDACION = ($this->AMBIENTE == 'PROD') ? 'http://www.svs.cl/institucional/validar/validar.php' : $this->LINK_VALIDACION;
					
					$this->FOLIO_RESOLUCION = 'RES-'.$this->NUMERO_RESOLUCION.'-'.date('y').'-'.$RAND.'-'.$VERIFICADOR;			
					$this->LINK_RESOLUCION = $this->LINK_VALIDACION.'?folio='.$this->FOLIO_RESOLUCION.'&val='.substr(md5(base64_encode($this->LINK_VALIDACION.$this->FOLIO_RESOLUCION)),0,5);
					if(isset($this->_LOG))
					$this->_LOG->log('SE GENERA EL FOLIO: '.$this->FOLIO_RESOLUCION);
					//echo $this->FOLIO_RESOLUCION;
					//exit();
					$TEMPLATE->assign('NUMERO_RESOLUCION',$this->NUMERO_RESOLUCION);
				}else{
					$TEMPLATE->assign('NUMERO_RESOLUCION','Sin Firma');	
					$this->LINK_RESOLUCION = 'http://www.cmfchile.cl/institucional/validar/validar.php';
				}
				
				$TEMPLATE->assign('TIPO',($this->_SESION->getVariable('TIPRES_ID') == 'exenta') ? 'EXENTA' : 'AFECTA' );
				$TEMPLATE->assign('DIA',date('d'));
				$TEMPLATE->assign('MES',$meses[date("m")-1]);
				$TEMPLATE->assign('ANO',date('Y'));
				
				
				$TEMPLATE->assign('RES_REFERENCIA',$RES_REFERENCIA);
				$RES_VISTOS = str_replace('<p>','<p>'.$this->SANGRIA,$RES_VISTOS);
				$RES_CONSIDERANDO = str_replace('<p>','<p>'.$this->SANGRIA,$RES_CONSIDERANDO);
				$RES_RESUELVO = str_replace('<p>','<p>'.$this->SANGRIA,$RES_RESUELVO);
				
				$TEMPLATE->assign('RES_VISTOS',$RES_VISTOS);
				$TEMPLATE->assign('RES_CONSIDERANDO',$RES_CONSIDERANDO);
				$TEMPLATE->assign('RES_RESUELVO',$RES_RESUELVO);
				$TEMPLATE->assign('RES_COMUNIQUESE',$RES_COMUNIQUESE);
				$TEMPLATE->parse('main');
				$HTML = $TEMPLATE->text('main');						
				$HTML = str_replace('/intranet/aplic/pures/','../pures/',$HTML);
				$HTML = str_replace('\"','"',$HTML);
				//$HTML = str_replace('/intranet/aplic/purso/','../purso/',$HTML);
				//echo $HTML; exit();
				
				
				
				preg_match_all("|src=\"/intranet/aplic/purso/index\.php\?pagina\=paginas\.upload&amp;(.*)&amp;img=(.*)&amp;seq=([0123456789]*)&amp;ext=(.*)\"|U",
				$HTML,
				$imagenes, PREG_SET_ORDER);
				
				//print_r($imagenes);
				
				//<img alt="" height="239" src="/intranet/aplic/purso/index.php?pagina=paginas.upload&amp;funcion=verImg&amp;img=560c542744deacd40269da61b3ab3d5d&amp;seq=16&amp;ext=jpg" width="313" />
				
				
				
				
				//echo $HTML; exit();
				foreach($imagenes as $imagen){
					//Acá se realizan movimientos de imagenes para asegurar que se puedan ver en el pdf
					//print_r($imagen);
					$MD5 = $imagen[2];
					$SEQ = $imagen[3];
					$EXT = $imagen[4];
					
					//capturar el ancho y alto de la imagen y despues reemplazarlo por estilos
					if(is_numeric ($this->_SESION->getVariable('RES_ID'))){
						
						$res_id = $this->_SESION->getVariable('RES_ID');
						$res_version = $this->_SESION->getVariable('RES_VERSION');
						$archivos_sesion = $this->_SESION->getVariable('IMAGEN_EDITOR');
						if(isset($archivos_sesion[$MD5])){
							$archivos_sesion[$MD5] = array('RUTA' => '/tmp/tmp_ckeditor/img/'.$MD5);
						}				
						
						if(!(isset($archivos_sesion[$MD5]['RES_ID']) && is_numeric($archivos_sesion[$MD5]['RES_ID']))){
							//Esto quiere decir que se debe actualizar en la base de datos
							$bind = array(':p_id' => $SEQ, ':p_res_id' => $res_id, ':p_res_version' => $res_version);
							$this->_ORA->ejecutaFunc('rso.RSO_IMAGEN_PKG.fun_actualizarImagen',$bind);
							$this->_ORA->Commit();
							$archivos_sesion[$MD5]['RES_ID'] = $res_id;
							$archivos_sesion[$MD5]['RES_VERSION'] = $res_version;
							$this->_SESION->setVariable('IMAGEN_EDITOR', $archivos_sesion);
						}
						
						
						
					}
					
					if(!file_exists('/tmp/tmp_ckeditor/img/'.$MD5)){
						$bind = array(':p_ima_id' => $SEQ);
						$cursor = $this->_ORA->retornaCursor('rso.RSO_IMAGEN_PKG.fun_getImagen','function',$bind);
						$data = $this->_ORA->FetchArray($cursor);
						if($data['IMA_RUTA'] == '/tmp/tmp_ckeditor/img/'.$MD5){
							
							file_put_contents('/tmp/tmp_ckeditor/img/'.$MD5,$data['IMA_BLOB']->load());
							
						}
					}
					
					
					/*
					se debe considerar el formato de la imaghen
					
					function png2jpg($originalFile, $outputFile, $quality) {
			$image = imagecreatefrompng($originalFile);
			imagejpeg($image, $outputFile, $quality);
			imagedestroy($image);
		}
					*/
					
					
					$contenido = file_get_contents('/tmp/tmp_ckeditor/img/'.$MD5);
					file_put_contents("/tmp/tmp_ckeditor/img/$MD5.$EXT",$contenido);			
					$HTML = str_replace($imagen[0],'src="/tmp/tmp_ckeditor/img/'.$MD5.'.'.$EXT.'"',$HTML);
					//echo $imagen[0];
					//exit();
				
				}
				
				
				
				
				
				
				
				
				
				
				
				//echo $HTML; exit();
				
				
				
				preg_match_all("|<img(.*)/[^>]+>|U",
				$HTML,
				$imagenes, PREG_SET_ORDER);
				foreach($imagenes as $img){
				
					$height = NULL;
					$width = NULL;
					$txt_img = $img[0];
					$txt_img_aux = $txt_img;
					//echo $txt_img;
					preg_match_all("|height=\"([0123456789]*)\"|U",
					$txt_img,
					$prop_img, PREG_SET_ORDER);
					if(count($prop_img) > 0){
						$height = $prop_img[0][1];
						//echo 'h|'.$height.'|';
						
					}
					

					preg_match_all("|width=\"([0123456789]*)\"|U",
					$txt_img,
					$prop_img, PREG_SET_ORDER);
					if(count($prop_img) > 0){
						$width = $prop_img[0][1];
						//echo 'w|'.$width.'|';
					}
					if($height != NULL && $width != NULL){
						$txt_img_aux = str_replace('width="'.$width.'"',"",$txt_img_aux);
						$txt_img_aux = str_replace('height="'.$height.'"',"",$txt_img_aux);
						$txt_img_aux = str_replace(' />','style="height:'.$height.'; width:'.$width.';"  />',$txt_img_aux);
						//$HTML = str_replace($txt_img, $txt_img_aux,$HTML);
						
						preg_match_all("|src=\"(.*)\"|U",
						$txt_img_aux,
						$ruta, PREG_SET_ORDER);
						foreach($ruta as $ruta_img){
							list($ancho, $alto) = getimagesize($ruta_img[1]);
							$image_p = imagecreatetruecolor($width, $height);
							$image = imagecreatefromjpeg($ruta_img[1]);
							imagecopyresampled($image_p, $image, 0, 0, 0, 0, 
											 $width, $height, $ancho, $alto);
							imagejpeg($image_p,$ruta_img[1],100);

						}
					

						
						
						
						
					}
					
				
				}
				
				//Tratamiento de tablas
				//echo $HTML;
				$HTML = str_replace("\n","{__SALTO__}",$HTML);
				$HTML = str_replace("\r","{__RETORNO__}",$HTML);
				
				preg_match_all("|<table(.*)[^>]+>(.*)[^>]</table>|U",
					$HTML,
					$tablas, PREG_SET_ORDER);
				//print_r($tablas);
				foreach($tablas as $tab){
					$texto = $tab[0];
					preg_match_all("|<table(.*)style=\"(.*)\"(.*)[^</]</table>|U",
						$texto,
						$style, PREG_SET_ORDER);
						//print_r($style);
						//echo 'sss';
						//exit();
						if(count($style)>0){
							//print_r($style); 	exit();
							$style_txt =  $style[0][2];
							$style_txt = str_replace("\n","",$style_txt);
							$style_txt = str_replace("\r","",$style_txt);
							$style_txt = explode(";",$style_txt);
							$width = "";
							$height = "";
							foreach($style_txt as $st){
								list($css,$valor) = explode(":",$st);
								switch(trim(strtolower($css))){
									case 'width':
										$width_valor = str_replace("px","",trim($valor));
										$valor = ($width_valor * 100) / 580;	
										$width = 'width="'.$valor.'%"';															
										$width_valor = $valor;
										break;
									case 'height':																					
										$height= 'height="'.$valor.'"';
										$height_valor = $valor;
										break;							
								}						
							}
							
							$TABLE = str_replace('style="'.$style[0][2].'"',"$width $height",$style[0][0]);
							//echo $TABLE; exit();
							//acá se debe seguir tratando los td
							
							
							preg_match_all("|<td(.*)style=\"(.*)\"(.*)>(.*)</td>|U",
							$TABLE,
							$td, PREG_SET_ORDER);
							foreach($td as $td_uno){
								
								//print_r($td_uno); exit();
								
								$style_txt =  $td_uno[2];
								$style_txt = explode(";",$style_txt);
							
								$width = "";
								$height = "";
								foreach($style_txt as $st){
									list($css,$valor) = explode(":",$st);
									switch(trim(strtolower($css))){
										case 'width':
											$width_valor = str_replace("px","",trim($width_valor));
											$width_valor = ($width_valor == 0) ? 1: $width_valor;
											$valor = str_replace("px","",trim($valor));
											$valor = ($valor * 100) / $width_valor;									
											$width = 'width="'.$valor.'%"';
											break;
										case 'height':
											$height_valor = str_replace("px","",trim($height_valor));
											$valor = str_replace("px","",trim($valor));
											if($height_valor != 0)
											$valor = ($valor * 100) / $height_valor;
											$height = 'height="'.$valor.'%"';
											break;							
									}						
								}
								$TD = str_replace('style="'.$td_uno[2].'"',"$width $height",$td_uno[0]);
								$TABLE = str_replace($td_uno[0], $TD, $TABLE);
							}
							//print_r($td);
							
							
							
							$HTML = str_replace($style[0][0],$TABLE,$HTML);
							
						}
				}
				
				$HTML = str_replace("{__SALTO__}","\n",$HTML);
				$HTML = str_replace("{__RETORNO__}","\r",$HTML);				
				$this->HTML = $HTML;
			}catch(Exception $e){
				print_r($e);			
			}
		}
		
		
		
		
	function insertar_firma($__USUARIO, $__CARGO){
		
		
	
			if($this->_FIRMA_IMG){
				if(isset($__CARGO) && strlen($__CARGO) > 1){
					$bind = array(":p_usuario" => $__USUARIO,":p_rol" => strtolower($__CARGO));
					$cursor = $this->_ORA->retornaCursor("FEL_ROL_FIRMA_PKG.fun_getImagenFirma","function",$bind);
					$data = $this->_ORA->FetchArray($cursor);
					
					if(isset($data['FEL_IMAGEN_FIRMA']) && is_object($data['FEL_IMAGEN_FIRMA'])){
						$imagen = $data['FEL_IMAGEN_FIRMA']->load();
					}
				}else{
					$imagen = file_get_contents('Sistema/img/firma_blanco.jpg');
				}
			}else{
				$imagen = file_get_contents('Sistema/img/firma_blanco.jpg');
			}
			
			$temp_file = tempnam(sys_get_temp_dir(), 'rubrica_fir');
			unlink($temp_file);
			$temp_file = $temp_file.'.jpg';
			file_put_contents($temp_file,$imagen);
			$img2 = imagecreatefromjpeg($temp_file); //rubrica
			
			
			$black = imagecolorallocate($img2,0,0,0);
			$dest = imagecreatefrompng('Sistema/img/logo_firma.png');
			imagealphablending($dest, TRUE);
			imagesavealpha($dest, FALSE);
			
			
			imagecopymerge($img2,$dest, 10, 10, 0, 0, 130, 125, 80);
			unlink($temp_file);
			$temp_file = tempnam(sys_get_temp_dir(), 'rubrica_fir');
			unlink($temp_file);
			$temp_file = $temp_file.'.jpg';
			imagedestroy($dest);
			imagejpeg($img2,$temp_file,100 );
			imagedestroy($img2);
			return $temp_file;
		
	}
		
		
		private function generarFirmaTemporal(){
			echo "No tiene multi firma";
		}
		
		private function firmaMultiple(){
			if(is_numeric($this->_SESION->getVariable('ARBPRO_ID'))){
				$bind = array(':cla_id' => $this->_SESION->getVariable('ARBPRO_ID'));
				$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getArbolPropiedades','function',$bind);
				$DATOS_ARBOL =  $this->_ORA->FetchArray($cursor);
				if($DATOS_ARBOL['ARBPRO_FIRMA_MULT'] == 'SI'){
					return true;
				}
			}
			return false;
		}
		
		public function getHTML(){
			return $this->HTML;
		}
		
		
		
		
		public function setNumeroResolucion($num){
			$this->NUMERO_RESOLUCION = $num;
		}
		
		
		
		
		public function setCargoResolucion($cargo){
			$this->CARGO_RESOLUCION = $cargo;
		}	
		

		
		public function getPDF(){
			
			$bind = array(':p_res_id' => $this->_SESION->getVariable('RES_ID'));
				if($this->_ORA->ejecutaFunc('rso.RSO_FIRMANTES_PKG.fun_existeArchivoFirmar',$bind) == 'S'){
					$bind = array(':res_id' => $this->_SESION->getVariable('RES_ID'));
					$cursor = $this->_ORA->retornaCursor('rso.RSO_FIRMANTES_PKG.fun_ultimoPDFFirmado','function',$bind);
					$data = $this->_ORA->FetchArray($cursor);
					
					$temp_file = tempnam(sys_get_temp_dir(), 'rso_fir');
					file_put_contents($temp_file,$data['RES_BLOB_FIRMADO']->load());
					$size = filesize($temp_file);
					
					
					
					
					
					
					
					return array($data['RES_BLOB_FIRMADO']->load(),$size);
				}
			
			
			
			
			
			
			
			$PDF_ARC = $this->_SESION->getVariable('archivo_pdf_subido');
			$ARCHIVO = tempnam('', 'resol_');
			if($PDF_ARC === false){
				if(isset($this->_LOG))
				$this->_LOG->log('PDF_ARC es falso :p');
				$this->generaHTML();
				
				$this->HTML = str_replace('style="list-style-type: lower-alpha;"','TYPE=a',$this->HTML);
				
				file_put_contents($ARCHIVO,$this->HTML);
				$CONF = dirname(__FILE__).'/../config/ps.conf';
				system("html2ps -f $CONF -OD ".$ARCHIVO." > ".$ARCHIVO.".ps");
				system("ps2pdf -dPDFSETTINGS=/prepress -dColorImageFilter=/FlateEncode ".$ARCHIVO.".ps  ".$ARCHIVO.".pdf");
			}else{
				
				
				if($this->GENERADA_PARA_FIRMA){
					$RAND = rand ( 10000 , 99999 );
					$suma = $this->NUMERO_RESOLUCION.date('y').$RAND;
					$suma = array_sum(str_split($suma));
					$suma = $suma%26;
					$VERIFICADOR = $this->CARACTERES[$suma];
					
					$this->LINK_VALIDACION = ($this->AMBIENTE == 'DESA') ? 'http://svsweb4.svs.local/institucional/validar/validar.php' : $this->LINK_VALIDACION;
					$this->LINK_VALIDACION = ($this->AMBIENTE == 'TEST') ? 'http://svsweb6.svs.local/institucional/validar/validar.php' : $this->LINK_VALIDACION;
					$this->LINK_VALIDACION = ($this->AMBIENTE == 'PROD') ? 'http://www.svs.cl/institucional/validar/validar.php' : $this->LINK_VALIDACION;
					
					$this->FOLIO_RESOLUCION = 'RES-'.$this->NUMERO_RESOLUCION.'-'.date('y').'-'.$RAND.'-'.$VERIFICADOR;			
					$this->LINK_RESOLUCION = $this->LINK_VALIDACION.'?folio='.$this->FOLIO_RESOLUCION.'&val='.substr(md5(base64_encode($this->LINK_VALIDACION.$this->FOLIO_RESOLUCION)),0,5);;
				}else{
					$this->LINK_RESOLUCION = 'http://www.cmfchile.cl/institucional/validar/validar.php';
				}
				file_put_contents($ARCHIVO.".pdf",$PDF_ARC);
			}
			
			$this->agregarLogoFirmaNroFecha($ARCHIVO.".pdf");
			
			//$this->agregarFooter($ARCHIVO.".pdf");


			$adjuntos = $this->_SESION->getVariable("RSO_ADJUNTO");
			if($adjuntos!== false ){
				require_once('Sistema/class/adjunto.class.php');
							
				$ARR_AJD = array();
				$ARR_AJD[] = $ARCHIVO.".pdf";
				
				foreach($adjuntos as $adj){
					$ADJUNTO = new Adjunto();
					$ADJUNTO->setControl($this);
					$cont = $ADJUNTO->getAdjunto($adj['ID']);		
					$ARCHIVO_ADJ = tempnam('', 'resol_adj_');
					unlink($ARCHIVO_ADJ);
					$ARCHIVO_ADJ.=".pdf";
					file_put_contents($ARCHIVO_ADJ,$cont);
					$ARR_AJD[] = $ARCHIVO_ADJ;
				}
				
				$link = $this->mergePDF($ARR_AJD);
				foreach($ARR_AJD as $ruta){
					unlink($ruta);
				}
				
				file_put_contents($ARCHIVO.".pdf",file_get_contents($link));
				unlink($link);						
			}
				
				
			
			

			
			
			
			
			
			$this->agregarFooter($ARCHIVO.".pdf");
			
			
			$contents = file_get_contents($ARCHIVO.".pdf");
			$size = filesize($ARCHIVO.".pdf");
			@unlink($ARCHIVO.".ps");
			unlink($ARCHIVO);
			unlink($ARCHIVO.".pdf");
			//unlink($this->LINK_IMAGEN_FIRMA);
			return array($contents,$size);
		}
		
		
		private function agregarLogoFirmaNroFecha($archivo){
			try{
			
				$pdf = new FPDI_R();
				$pdf->NUMERO_OFICIO = $this->NUMERO_OFICIO;
				$pdf->NUMERO_SGD = (strlen($this->NUMERO_SGD) > 0 ) ? $this->NUMERO_SGD : 'SINNUMEROSGD';
				$pdf->NUMERO_FOLIO = $this->NUMERO_FOLIO;
				$pdf->SIN_FOOTER = true;
				//header('Content-type: application/pdf');
				//echo file_get_contents($archivo);
				//echo $archivo;
				//exit();
				$pageCount = $pdf->setSourceFile($archivo);
				$pdf->TOTAL_PAGINAS = $pageCount;
				$this->INICIO_PAGINA_ANEXO = $pdf->TOTAL_PAGINAS+1;
				
				
				
				$pdf->SetMargins(30, 25 , 30);
				
				for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
					$templateId = $pdf->importPage($pageNo);
					$size = $pdf->getTemplateSize($templateId);
					if ($size['w'] > $size['h']) {
						$pdf->AddPage('L', array($size['w'], $size['h']));
					} else {
						$pdf->AddPage('P', array($size['w'], $size['h']));
					}
			
									
					$pdf->useTemplate($templateId);
				}
				if($this->_POSICION_FIRMA == 'NP'){
						
					if ($size['w'] > $size['h']) {
						$pdf->AddPage('L', array($size['w'], $size['h']));
					} else {
						$pdf->AddPage('P', array($size['w'], $size['h']));
					}
					$this->INICIO_PAGINA_ANEXO++;
						
				}
				
				
				$this->_SESION->setVariable('INICIO_PAGINA_ANEXO', $this->INICIO_PAGINA_ANEXO);
				if(is_numeric($this->_SESION->getVariable('ARBPRO_ID'))){
		
					$bind = array(':cla_id' => $this->_SESION->getVariable('ARBPRO_ID'));
					$cursor = $this->_ORA->retornaCursor($this->PREFIJO_SCHEMA.'RSO_CLASIFICACION_PKG.fun_getArbolPropiedades','function',$bind);
					$DATOS_ARBOL =  $this->_ORA->FetchArray($cursor);
					if($DATOS_ARBOL['ARBPRO_FIRMA_MULT'] == 'SI'){
					//	echo __LINE__."<br>";
						$FIMANTES = $this->_SESION->getVariable('FIRMANTES');
						$TOTAL_FIRMANTES = count($FIMANTES);
						$cantidad = 0;
						if(count($FIMANTES) === 1){	
					
							list($CODIGO_USR, $CARGO_USR) = explode('|-|',$FIMANTES[0]);
							$imagen_firma = $this->insertar_firma($CODIGO_USR, $CARGO_USR);
							list($X,$Y) = $this->retornaXY(1,1,$pdf);
						
							
							
							$pdf->Image($imagen_firma, $X, $Y, 100);
							if(!$this->GENERADA_PARA_FIRMA){
								$imagen_firma = 'Sistema/img/x.gif';
								$imagen_firma = 'Sistema/img/firma_blanco_multi.jpg';
								$pdf->Image($imagen_firma, $X, $Y, 100);
							}
							
							
							if(is_numeric($this->_SESION->getVariable('RES_ID'))){
									
									$bind = array(
											':p_RES_ID' => $this->_SESION->getVariable('RES_ID'),
											':p_RES_FIRMA' => $CODIGO_USR,
											':p_RES_CARGO_FIRMA' => $CARGO_USR,
											':p_RES_POSX' => $X,
											':p_RES_POSY' => $Y,
											':p_RES_PAGFIR' => $pdf->TOTAL_PAGINAS
													);
									$this->_ORA->ejecutaFunc('rso.RSO_FIRMANTES_PKG.fun_actualizaPosicionFirma',$bind);
									$this->_ORA->Commit();
								
								}
							
							$bind = array(":p_usuario" => $this->_SESION->USUARIO,":p_rol" => strtolower($CARGO_USR));
							
							$cursor = $this->_ORA->retornaCursor("FEL_ROL_FIRMA_PKG.fun_getImagenFirma","function",$bind);
							$data = $this->_ORA->FetchArray($cursor);
							$NOMBRE_USER = ucwords (strtolower($data['NOMBRE_USUARIO']));
							if(!$this->_FIRMA_IMG){
								$X_AUX = $X;
								$Y_AUX = $Y;
								$pdf->AddFont('Prettygirlfree','','Prettygirlfree.php');
								$pdf->SetFont('Prettygirlfree','',30);
								$pdf->setXY($X+5,$Y+5);
								$pdf->MultiCell(100, 5, utf8_decode($NOMBRE_USER),0,'C');
								$X = $X_AUX;
								$Y = $Y_AUX;
							}
							
							$pdf->AddFont('Verdana','','verdana.php');
							$pdf->SetFont('Verdana','',10);
							$pdf->SetXY($X+10,$Y+15);
							$cargo_formula = $this->limpiaFormula($data['FEL_ROL_PIE']);
							$pdf->MultiCell(100, 5, utf8_decode($NOMBRE_USER."\n".$cargo_formula ),0,'C');

						}else{
							
							
							
							
							$cantidad = 0;
							foreach($FIMANTES as $fun_firmante){
								$cantidad++;
								list($CODIGO_USR, $CARGO_USR) = explode('|-|',$fun_firmante);
								$imagen_firma = $this->insertar_firma($CODIGO_USR, $CARGO_USR);
								list($X,$Y) = $this->retornaXY(count($FIMANTES),$cantidad, $pdf);
								$pdf->Image($imagen_firma, $X, $Y, 100);
								if(!$this->GENERADA_PARA_FIRMA){
									$imagen_firma = 'Sistema/img/x.gif';
									$imagen_firma = 'Sistema/img/firma_blanco_multi.jpg';
									$pdf->Image($imagen_firma, $X, $Y, 100);
								}
								
								if(is_numeric($this->_SESION->getVariable('RES_ID'))){
									
									$bind = array(
											':p_RES_ID' => $this->_SESION->getVariable('RES_ID'),
											':p_RES_FIRMA' => $CODIGO_USR,
											':p_RES_CARGO_FIRMA' => $CARGO_USR,
											':p_RES_POSX' => $X,
											':p_RES_POSY' => $Y,
											':p_RES_PAGFIR' => $pdf->TOTAL_PAGINAS
													);
									$this->_ORA->ejecutaFunc('rso.RSO_FIRMANTES_PKG.fun_actualizaPosicionFirma',$bind);
									$this->_ORA->Commit();
								
								}
  
  
  
								
								
								$X_AUX = $X;
								$Y_AUX = $Y;
								
								$bind = array(":p_usuario" => $CODIGO_USR,":p_rol" => strtolower($CARGO_USR));
							
								$cursor = $this->_ORA->retornaCursor("FEL_ROL_FIRMA_PKG.fun_getImagenFirma","function",$bind);
								$data = $this->_ORA->FetchArray($cursor);
								
								
								$NOMBRE_USER = mb_convert_case($data['NOMBRE_USUARIO'], MB_CASE_TITLE, "UTF-8");
								
								
								//$NOMBRE_USER = ucwords (strtolower($data['NOMBRE_USUARIO']));
								//echo $NOMBRE_USER;
								if(!$this->_FIRMA_IMG){
									$X_AUX = $X;
									$Y_AUX = $Y;
									$pdf->AddFont('Prettygirlfree','','Prettygirlfree.php');
									$pdf->SetFont('Prettygirlfree','',30);
									$pdf->setXY($X+5,$Y+5);
									$pdf->MultiCell(100, 5, utf8_decode($NOMBRE_USER),0,'C');
									$X = $X_AUX;
									$Y = $Y_AUX;
								}
								
								
								
								$pdf->AddFont('Verdana','','verdana.php');
								$pdf->SetFont('Verdana','',10);
								$pdf->SetXY($X+10,$Y+15);
								$cargo_formula = $this->limpiaFormula(mb_convert_case($data['FEL_ROL_PIE'], MB_CASE_TITLE, "UTF-8"));
								$pdf->MultiCell(100, 5, utf8_decode($NOMBRE_USER."\n".$cargo_formula ),0,'C');
								$pdf->setXY($X_AUX,$Y_AUX);
								
								

							
							
									
							}
						
						}
					
					}else{
						//No es multifirma
						$X = 50;
						switch($this->_POSICION_FIRMA){
							case "AR";
								$Y = 55;
								break;
							case "AB";
								$Y = 200;
								break;
							case "CE";
								$Y = 140;
								break;
						}
						
						if($this->NUMERO_RESOLUCION !== false){
						
						//list($CODIGO_USR, $CARGO_USR) = explode('|-|',$FIMANTES[0]);
							$CARGO_USR = $this->CARGO_RESOLUCION;
							$CODIGO_USR = $this->_SESION->USUARIO;
							
					
							$imagen_firma = $this->insertar_firma($CODIGO_USR, $CARGO_USR);
							list($X,$Y) = $this->retornaXY(1,1,$pdf);
							$pdf->Image($imagen_firma, $X, $Y, 100);
							$bind = array(":p_usuario" => $this->_SESION->USUARIO,":p_rol" => strtolower($CARGO_USR));
							
							$cursor = $this->_ORA->retornaCursor("FEL_ROL_FIRMA_PKG.fun_getImagenFirma","function",$bind);
							$data = $this->_ORA->FetchArray($cursor);
							$NOMBRE_USER = ucwords (strtolower($data['NOMBRE_USUARIO']));
							if(!$this->_FIRMA_IMG){
								$X_AUX = $X;
								$Y_AUX = $Y;
								$pdf->AddFont('Prettygirlfree','','Prettygirlfree.php');
								$pdf->SetFont('Prettygirlfree','',30);
								$pdf->setXY($X+5,$Y+5);
								$pdf->MultiCell(100, 5, utf8_decode($NOMBRE_USER),0,'C');
								$X = $X_AUX;
								$Y = $Y_AUX;
							}
							
							$pdf->AddFont('Verdana','','verdana.php');
							$pdf->SetFont('Verdana','',10);
							$pdf->SetXY($X+10,$Y+15);
							$cargo_formula = $this->limpiaFormula($data['FEL_ROL_PIE']);
							$pdf->MultiCell(100, 5, utf8_decode($NOMBRE_USER."\n".$cargo_formula ),0,'C');
						}else{
						
							$imagen_firma = 'Sistema/img/temp.jpg';
							list($X,$Y) = $this->retornaXY(1,1,$pdf);
							$pdf->Image($imagen_firma, $X, $Y, 100);
							//echo 'ss';
							//exit();
						}

						
						
						
						
						
						
						
						
						
						
						
					}
				}else{
				
					$X = 50;
					switch($this->_POSICION_FIRMA){
						case "AR";
							$Y = 55;
							break;
						case "AB";
							$Y = 200;
							break;
						case "CE";
							$Y = 140;
							break;
					}
					$imagen_firma = 'Sistema/img/temp.jpg';
					list($X,$Y) = $this->retornaXY(1,1,$pdf);
					$pdf->Image($imagen_firma, $X, $Y, 100);
							
				
				}
				
				
				
				
				
				
				$ARCHIVO2 = tempnam('', 'resol_adj_');
				unlink($ARCHIVO2);
				$pdf->Output($ARCHIVO2.".pdf", 'F');
				unlink ($archivo);
				copy($ARCHIVO2.".pdf", $archivo);
				unlink ($ARCHIVO2.".pdf");
				
				
				return;
				
				
				//[PROPIO]$ruta = $this->insertar_firma($__USUARIO,$__CARGO);
				//[PROPIO]$pdf->Image($ruta, $X+20, $Y, 100); //(arriba)
 
				//[PROPIO]$pdf->AddFont('Verdana','','verdana.php');
				//[PROPIO]$pdf->SetFont('Verdana','',10);
				//[PROPIO]$pdf->SetXY($X+15,$Y+23);
				//[PROPIO]$bind = array(":p_usuario" => $__USUARIO,":p_rol" => strtolower($__CARGO));
				//[PROPIO]$cursor = $this->_ORA->retornaCursor("FEL_ROL_FIRMA_PKG.fun_getImagenFirma","function",$bind);
				//[PROPIO]$data = $this->_ORA->FetchArray($cursor);
				
				//[PROPIO]if(isset($data['FEL_IMAGEN_FIRMA']) && is_object($data['FEL_IMAGEN_FIRMA'])){
					//[PROPIO]$NOMBRE_USER = ucwords (strtolower($data['NOMBRE_USUARIO']));
					//[PROPIO]$cargo_formula = ucwords (strtolower($data['FEL_ROL_PIE']));
					//[PROPIO]$cargo_formula = str_replace( array('Á','É','Í','Ó','Ú'), array('á','é','í','ó','ú'),$cargo_formula);
					//[PROPIO]$cargo_formula = str_replace('Para El','para el',$cargo_formula);
					//[PROPIO]$cargo_formula = str_replace('De La','de la',$cargo_formula);
					//[PROPIO]$cargo_formula = str_replace('(s)','(S)',$cargo_formula);
					//[PROPIO]$cargo_formula = str_replace(' Del ',' del ',$cargo_formula);
					//[PROPIO]$cargo_formula = str_replace(' De ',' de ',$cargo_formula);
					//[PROPIO]$cargo_formula = str_replace(' E ',' e ',$cargo_formula);
					//[PROPIO]$cargo_formula = str_replace(' Y ',' y ',$cargo_formula);
					//[PROPIO]$pdf->MultiCell(100, 5, utf8_decode($NOMBRE_USER."\n".$cargo_formula ),0,'C');
				//[PROPIO]}
						
				
				//[PROPIO]unlink($ruta);
				
				
				
				
				
				
				
				
				
				
				/*
				$ARCHIVO2 = tempnam('', 'resol_adj_');
				unlink($ARCHIVO2);
				$pdf->Output($ARCHIVO2.".pdf", 'F');
				unlink ($archivo);
				copy($ARCHIVO2.".pdf", $archivo);
				unlink ($ARCHIVO2.".pdf");
					
				*/
			}catch(Exception $e){
				print_r($e);
			}
		}
		
		public function retornaXY($cantidad_firmantes, $posicion, $pdf){
			if($cantidad_firmantes === 1){
				$X = 60;
				switch($this->_POSICION_FIRMA){
					case "AR";
						$Y = 55;
						break;
					case "AB";
						$Y = 200;
						break;
					case "CE";
						$Y = 140;
						break;
					case "NP";
						$Y = 120;
						break;
						
				}
				
			}else{
				if($posicion === 1){
					$filas = ceil ($cantidad_firmantes / 2);
					$filas = $filas -1;
					$X = 5;
					
					$arriba_por = $filas * 40;
					switch($this->_POSICION_FIRMA){
						case "AR";
							$Y = 55 - $arriba_por;
							break;
						case "AB";
							$Y = 220 - $arriba_por - 10;
							break;
						case "CE";
							$Y = 140 - $arriba_por;
							break;
						case "NP";
							$Y = 120- $arriba_por;;
							break;
					}
				}else{
					if ( $posicion & 1 ) {
						
						$X = 5;
						$Y = $pdf->getY()+40;
						
					
					}else{
						$X = $pdf->getX()+105;
						$Y = $pdf->getY();
					
					}
				
				}
			
			
			}
		
		
			return array($X,$Y);
		}
		
		public function limpiaFormula($formula){
			$cargo_formula = ucwords (strtolower($formula));
			$cargo_formula = str_replace( array('Á','É','Í','Ó','Ú'), array('á','é','í','ó','ú'),$cargo_formula);
			$cargo_formula = str_replace('Para El','para el',$cargo_formula);
			$cargo_formula = str_replace('De La','de la',$cargo_formula);
			$cargo_formula = str_replace('(s)','(S)',$cargo_formula);
			$cargo_formula = str_replace(' Del ',' del ',$cargo_formula);
			$cargo_formula = str_replace(' De ',' de ',$cargo_formula);
			$cargo_formula = str_replace(' E ',' e ',$cargo_formula);
			$cargo_formula = str_replace(' Y ',' y ',$cargo_formula);
			return $cargo_formula;
		}
		
		
		private function agregarFooter($archivo){
			try{
					
					$pdf = new FPDI_R();	
					$pdf->FOLIO_RESOLUCION	= $this->FOLIO_RESOLUCION;
					$pdf->INICIO_PAGINA_ANEXO	= $this->INICIO_PAGINA_ANEXO;
					$pdf->LINK_VALIDACION	= $this->LINK_VALIDACION;
					$pdf->LINK_RESOLUCION	= $this->LINK_RESOLUCION;
					$pdf->NUMERO_SGD		= $this->NUMERO_SGD;
					if($this->NUMERO_RESOLUCION !== false){
						$pdf->NUMERO_RESOLUCION = $this->NUMERO_RESOLUCION;
					}else{
						$pdf->NUMERO_RESOLUCION = 'Sin número';
					}
					
					$pdf->TIPO = ($this->_SESION->getVariable('TIPRES_ID') == 'exenta') ? 'EXENTA' : 'AFECTA' ;;
					
					$pageCount = $pdf->setSourceFile($archivo);
					$pdf->TOTAL_PAGINAS = $pageCount;
					for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
						$templateId = $pdf->importPage($pageNo);
						$size = $pdf->getTemplateSize($templateId);
			
						if ($size['w'] > $size['h']) {
							$pdf->AddPage('L', array($size['w'], $size['h']));
						} else {
							$pdf->AddPage('P', array($size['w'], $size['h']));
						}		
						
						$pdf->useTemplate($templateId);			
					}
					
					/* $pdf->AddPage();
					$pageCount = $pdf->setSourceFile($archivo);
					$tplIdx = $pdf->importPage(1);
					$pdf->useTemplate($tplIdx, 10, 10, 200);
					*/
					$ARCHIVO2 = tempnam('', 'resol_adj_');
					unlink($ARCHIVO2);
					$pdf->Output($ARCHIVO2.".pdf", 'F');
					unlink ($archivo);
					copy($ARCHIVO2.".pdf", $archivo);
					unlink ($ARCHIVO2.".pdf");
			

				//$pdf->SetPrintHeader(false);
				//$pdf->SetPrintFooter(false);
				
					/*$pageCount = $pdf->setSourceFile($archivo);
					$tplIdx = $pdf->importPage(1, '/MediaBox');
					$pdf->addPage();
					$pdf->useTemplate($tplIdx, 10, 10, 90);*/
				//	}
			}catch(Exception $e){
				print_r($e);
			}
			
		}
		
		
		/*
		
		
		include('../lib/full/qrlib.php'); 
		include('config.php'); 

		// how to save PNG codes to server 
		 
		$tempDir = EXAMPLE_TMP_SERVERPATH; 
		 
		$codeContents = 'This Goes From File'; 
		 
		// we need to generate filename somehow,  
		// with md5 or with database ID used to obtains $codeContents... 
		$fileName = '005_file_'.md5($codeContents).'.png'; 
		 
		$pngAbsoluteFilePath = $tempDir.$fileName; 
		$urlRelativeFilePath = EXAMPLE_TMP_URLRELPATH.$fileName; 
		 
		// generating 
		if (!file_exists($pngAbsoluteFilePath)) { 
			QRcode::png($codeContents, $pngAbsoluteFilePath); 
			echo 'File generated!'; 
			echo '<hr />'; 
		} else { 
			echo 'File already generated! We can use this cached file to speed up site on common codes!'; 
			echo '<hr />'; 
		} 
		 
		echo 'Server PNG File: '.$pngAbsoluteFilePath; 
		echo '<hr />'; 
		 
		// displaying 
		echo '<img src="'.$urlRelativeFilePath.'" />'; 
		*/
		
		private function mergePDF($files){
			
			try{
		
				$ARCHIVO = tempnam('', 'resol_adj_');
				unlink($ARCHIVO);
				$ARCHIVO.=".pdf";
				$LINEA_IMPLODE = implode(' ',$files);
				//system('convert -density 400 -quality 100 '.$LINEA_IMPLODE.' '.$ARCHIVO);
				system('pdftk '.$LINEA_IMPLODE.' cat output '.$ARCHIVO);
				//$PDF_MERGE_ARRAY = array();
				return $ARCHIVO;
					
			//$ARCHIVO = tempnam('', 'resol_adj_');
			//unlink($ARCHIVO);
			//$pdf->Output($ARCHIVO.".pdf", 'F');		
		
		
		
	/*
			$pdf = new FPDI();
			//$pdf->SetPrintHeader(false);
			//$pdf->SetPrintFooter(false);
			
			foreach ($files as $file) {
				// get the page count
				$pageCount = $pdf->setSourceFile($file);
				// iterate through all pages
			
				for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
					// import a page
					$templateId = $pdf->importPage($pageNo);
					// get the size of the imported page
					$size = $pdf->getTemplateSize($templateId);
			
					// create a page (landscape or portrait depending on the imported page size)
					if ($size['w'] > $size['h']) {
						$pdf->AddPage('L', array($size['w'], $size['h']));
					} else {
						$pdf->AddPage('P', array($size['w'], $size['h']));
					}
			
					// use the imported page
					$pdf->useTemplate($templateId);
			
				}
				

			}
			//echo 'ss';
			//exit();
			$ARCHIVO = tempnam('', 'resol_adj_');
			unlink($ARCHIVO);
			$pdf->Output($ARCHIVO.".pdf", 'F');
			
			return $ARCHIVO.".pdf";*/
			}catch(Exception $e){
				print_r($e);
			}
		
		}
		
		
		private function limpiarTexto($texto){
			//$texto = stripslashes ($texto);
			//$texto = str_replace('<p>','',$texto);
			//$texto = str_replace("</p>",'<br>',$texto);
			//$texto = str_replace("<br>\n--|--|--",'',$texto.'--|--|--');
			//$texto = str_replace("--|--|--",'',$texto);
			//$texto = str_replace("\n",'',$texto);		
			$texto = str_replace("<table",'<div align="left"><table',$texto);
			$texto = str_replace("table>",'table></div>',$texto);
			return $texto;
		}
	}


	class FPDI_R extends FPDI{
		public $LINK_VALIDACION;
		public $SIN_FOOTER= false;
		public $FOLIO_RESOLUCION;
		public $LINK_RESOLUCION;
		public $NUMERO_RESOLUCION;
		public $NUMERO_SGD;
		public $TOTAL_PAGINAS;
		public $INICIO_PAGINA_ANEXO;
		
		
		
		
		function Footer() {
			if(!$this->SIN_FOOTER){

		
			$this->SetY(10);
			$pos = 25;
			$y = $this->getY();
			$RUTA = dirname (__FILE__);
			if($this->page < $this->INICIO_PAGINA_ANEXO){
				$this->Image($RUTA."/../img/logocmf.png",$pos,$y,70);
			}else{
				$this->SetY(5);
				$pos = 10;
				$y = $this->getY();
				$this->Image($RUTA."/../img/cmf-agua.jpg",$pos,$y,30);
			}
			//$this->Image($RUTA."/../img/fondo_doctojpg.jpg",0,100,216);
			//$this->SetXY(0,70);
			
			//$this->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
				
				
				

			if($this->page === 1) {
				$this->Ln();
				$this->SetFont('Times','B',9);
				$X_INICIAL = 124;
				$Y_INICIAL = 31;
				
				
				$this->setXY($X_INICIAL,$Y_INICIAL);
				$TIPO  = $this->TIPO;
				$this->Cell(0,0,utf8_decode('RESOLUCION '.$TIPO.':'),0,0,'L');

				$this->setX($X_INICIAL+40);
				$this->Cell(0,0,utf8_decode($this->NUMERO_RESOLUCION),0,0,'L');
				$this->setXY($X_INICIAL,$Y_INICIAL+4);
				  
				$this->Cell(0,0,utf8_decode('Santiago, '.date('d').' de '.$this->meses(date('m')).' de '.date('Y')),0,0,'L');
			}
			
			
			
			
				$QR = tempnam('', 'resol_qr_');
				unlink($QR);
				//$this->LINK_RESOLUCION = $this->LINK_VALIDACION.'?folio='.$this->FOLIO_RESOLUCION.'&val='.md5(base64_encode($this->LINK_VALIDACION.$this->FOLIO_RESOLUCION));
				QRcode::png($this->LINK_RESOLUCION/*$this->LINK_VALIDACION.'?folio='.$this->FOLIO_RESOLUCION.'&val='.md5(base64_encode($this->LINK_VALIDACION.$this->FOLIO_RESOLUCION))*/, $QR.".png",QR_ECLEVEL_L, 1);
			
				$pos = 25;
				if($this->page > 0) {
					
					$this->SetY(-25);
					$y = $this->getY();
					$this->Image($QR.".png",$pos,$y);
					$this->SetY(-20);
					$this->SetFont('times', 'I', 11); 
					$this->SetTextColor(0,0,0);
					$this->SetX(45);
					$this->Write(0, utf8_decode("Para validar ir a ".$this->LINK_VALIDACION), '', 0, 'C');
					$this->SetXY(45,-15);
					//$this->SetY(-15);
					$this->Write(0, utf8_decode("FOLIO: ".$this->FOLIO_RESOLUCION), '', 0, 'C');
					$this->setX(-15+120);
					$this->Cell(0,0,utf8_decode('SGD: '.$this->NUMERO_SGD),0,0,'L');
					
					if($this->page > 1){
						$this->SetY(-10);			
						$this->SetFont('Arial','I',8);
						$this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/'.$this->TOTAL_PAGINAS,0,0,'R');
					}
					

				}  //end of if
				unlink($QR.".png");
			}
		} // end of footer
		
		
		public function meses($mes){
		$MES_R = '';
		switch($mes){
			case '01':
				$MES_R = 'enero';
				break;
			case '02':
				$MES_R = 'febrero';
				break;
			case '03':
				$MES_R = 'marzo';
				break;
			case '04':
				$MES_R = 'abril';
				break;
			case '05':
				$MES_R = 'mayo';
				break;
			case '06':
				$MES_R = 'junio';
				break;
			case '07':
				$MES_R = 'julio';
				break;
			case '08':
				$MES_R = 'agosto';
				break;
			case '09':
				$MES_R = 'septiembre';
				break;
			case '10':
				$MES_R = 'octubre';
				break;
			case '11':
				$MES_R = 'noviembre';
				break;
			case '12':
				$MES_R = 'diciembre';
				break;
		}
		return $MES_R;
	}
	}
	
	


	function ImageTTFCenter($image, $text, $font, $size, $angle = 45) 
	{
		$xi = imagesx($image);
		$yi = imagesy($image);
		$box = imagettfbbox($size, $angle, $font, $text);
		$xr = abs(max($box[2], $box[4]));
		$yr = abs(max($box[5], $box[7]));
		$x = intval(($xi - $xr) / 2);
		$y = intval(($yi + $yr) / 2);
		return array($x, $y);
	}


?>