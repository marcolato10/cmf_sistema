<?php

	class Paso3 extends ClaseSistema{
		
		//Esto es como si reemplazara el main

		
		
		
		
		public function generarHTML(){
			
			if(isset($this->_RESOLUCION->_VERSION_RSO) && $this->_RESOLUCION->_VERSION_RSO == 'v2'){
				$bind = array(
					':P_RES_ID' => $this->_SESION->getVariable('RES_ID'),
					':P_RES_VERSION' => $this->_SESION->getVariable('RES_VERSION'));
				$tieneOpcionPDF = $this->_ORA->ejecutaFunc('RSO.RSO_OPCION_PDF_PKG.fun_getIsOpcionPdf',$bind);
				if($tieneOpcionPDF == 'S'){
					$this->_TEMPLATE->assign('DISPLAY_div_paso3','none');
					$this->_TEMPLATE->assign('DISPLAY_file_subirPDF','block');
					$this->_TEMPLATE->assign('SRC_IFRAME_PDF','index.php?pagina=paginas.vistaPreviaResolucion&vista.pdf');
					$this->_TEMPLATE->assign('DISPLAY_iframePDF','block');
					$this->_TEMPLATE->assign('HTML_SUBIR_PDF','<img id="img_pdf" src="Sistema/img/lapiz.png" />Volver a Redactar</a>');
				}else{
					$this->_TEMPLATE->assign('DISPLAY_div_paso3','block');
					$this->_TEMPLATE->assign('DISPLAY_file_subirPDF','none');
					$this->_TEMPLATE->assign('HTML_SUBIR_PDF','<img id="img_pdf" src="Sistema/img/pdf.png" />Subir un PDF</a>');
				}
			
			
			//DISPLAY_div_paso3
			//DISPLAY_file_subirPDF
			}else{
				$this->_TEMPLATE->assign('DISPLAY_div_paso3','block');
				$this->_TEMPLATE->assign('DISPLAY_file_subirPDF','none');
			}	

			/*
			json['RESULTADO'] = 'OK';
			$json['CAMBIA']['#a_pdf'] = '<img id="img_pdf" src="Sistema/img/lapiz.png" />Volver a Redactar';
			$json['HIDE']['#div_paso3'] = 'hide';
			$json['SHOW']['#file_subirPDF'] ='show';
			*/



			$this->_TEMPLATE->assign('RES_REFERENCIA',stripslashes ($this->_RESOLUCION->RES_REFERENCIA));
			$this->_TEMPLATE->assign('RES_VISTOS',stripslashes ($this->_RESOLUCION->RES_VISTOS));
			$this->_TEMPLATE->assign('RES_CONSIDERANDO',stripslashes ($this->_RESOLUCION->RES_CONSIDERANDO));
			$this->_TEMPLATE->assign('RES_RESUELVO',stripslashes ($this->_RESOLUCION->RES_RESUELVO));
			$this->_TEMPLATE->assign('RES_COMUNIQUESE',stripslashes ($this->_RESOLUCION->RES_COMUNIQUESE));
			$this->_TEMPLATE->assign('CONTENTEDITABLE','true');
			$bind = array(':p_usuario' => $this->_SESION->USUARIO);
			$tiene = $this->_ORA->ejecutaFunc('RSO.RSO_OPCION_PDF_PKG.fun_usuarioPuedeSubirPdf',$bind);
			if($tiene == 'S'){
			$this->_TEMPLATE->parse('main.paso3.pdf');
			}
			$this->_TEMPLATE->parse('main.paso3');
		}
		
		

	}

?>