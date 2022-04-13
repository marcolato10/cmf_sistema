<?php

class PaginaPurso extends Pagina{
	public $_HEADER;
	public $_FOOTER;
	public $_RESOLUCION;
	
	public $PREFIJO_SCHEMA = 'rso.';
	
	
	
	
	public function onLoad(){}
	
	public function out(){
		global $PATH_TPL;
		
		
		$this->_HEADER = new XTemplate("Sistema/paginas/plantillas/header.html");
		$this->_FOOTER = new XTemplate("Sistema/paginas/plantillas/footer.html");
		$JS = "\n";
		$STYLE = "\n";
		$text = $this->_TEMPLATE->text('main');
		
		

				               
		$this->_HEADER->assign('ACTIVO_PASO'.$this->PASO,'activo');
		$this->_HEADER->assign('DOC_ID',$this->_SESION->getVariable('DOC_ID'));
		$this->_HEADER->assign('DOC_VERSION',$this->_SESION->getVariable('DOC_VERSION'));
		$this->_HEADER->assign('NOMBRE_APLICACION',(string)$this->_VAR_GLOBAL->proyecto);		
		
		
		//$this->_HEADER->parse('header.menu_gral');
		if($this->_VAR_GLOBAL->usa_plantilla_gral == 'SI'){
			if($this->_SESION->getVariable('RES_ID')){
				$this->_HEADER->assign('RES_ID',$this->_SESION->getVariable('RES_ID'));
				$this->_HEADER->parse('header.div_resID');
			}
			
			if($this->_AMBIENTE == 'TEST'){
				$this->_HEADER->parse('header.modo_edicion');
			}
			
			//
			$this->_HEADER->parse('header');
			$txt_header = $this->_HEADER->text('header');
		}
			$text = $this->cargarMensajesAyuda($text);
			$text = $this->cargarEditarInline($text);
		
		
		if($this->_VAR_GLOBAL->usa_plantilla_gral == 'SI'){
			$this->_FOOTER->parse('footer');
			$txt_footer = $this->_FOOTER->text('footer');
			echo $txt_header;
			echo $text;
			echo $txt_footer;
		}
	}
	
	public function funciones($param){
			$supuesto_json  = parent::funciones($param, false);
	
		try{	
			$json = json_decode($supuesto_json);
			//Sigue es json
			if(isset($json->CAMBIA)){
				foreach($json->CAMBIA as $key => $html){
					$json->CAMBIA->$key = $this->cargarMensajesAyuda($json->CAMBIA->$key);
					$json->CAMBIA->$key = $this->cargarEditarInline($json->CAMBIA->$key);
				}
				$supuesto_json = json_encode($json);
			}
		}catch(Exception $e){
			//echo 'ssssssssssss';
		}
		echo $supuesto_json;
	}
	
	public function cargarMensajesAyuda($text){
		$debug = false;
		$pos = 0;
		$i=0;
		$total = strlen($text);
		$TEXT_INICIAL = $text;
	try{
			$db = new SQLite3('Sistema/bd/ayuda.php');
			//$stmt = $db->exec('CREATE TABLE ayuda_resolucion ( ID_OBJECT CHAR(100) PRIMARY KEY,TEXTO_AYUDA CHAR(2000));');
			//$stmt = $db->exec('INSERT INTO ayuda_resolucion VALUES ("input_tipoResolucion","De acuerdo al tipo de resolucion se debe seleccionar la clasificacion :)");');
			preg_match_all("|<label(.*)label-paso1(.*)[^>]+>(.*)</[^>]+>|U",$text,$out, PREG_PATTERN_ORDER);
			//print_r($out);
			if($debug){
				
				echo "<pre>";print_r($out);echo "</pre>";exit();
				
			}
			foreach($out[0] as $key => $linea){
				$texto = $out[3][$key];
				$id_bd = strtolower($texto);
				$id_bd = html_entity_decode ($id_bd,ENT_COMPAT | ENT_HTML401,'UTF-8');
				$id_bd = 'help_'.str_replace(array("á","é","í","ó","ú",' ',"(",")"),array("a ","e","i","o","u",'_','_','_'),$id_bd);
				$stmt = $db->prepare('SELECT ID_OBJECT, TEXTO_AYUDA FROM ayuda_resolucion WHERE ID_OBJECT = "'.$id_bd .'";');
				$result = $stmt->execute();
				$cantidad = 0;
				$html_ayuda = '';
				$TEXTO_AYUDA = '';
				while ($row = $result->fetchArray()){
					$TEXTO_AYUDA = $row['TEXTO_AYUDA'];
					$html_ayuda = '<img style="cursor:pointer"  onclick="Tip(\''.str_replace("\r\n","",$TEXTO_AYUDA).'\',TITLE,\'Ayuda\',WIDTH,300,SHADOW, true, STICKY, 1, CLOSEBTN, true);" src="Sistema/img/ayuda.png"/>';
				}

				$nueva_linea = str_replace( ">".$texto."<" , ">".$texto.$html_ayuda.'<img onclick="fun_editarComentarioBD(this)" id="'.$id_bd.'" style="display:none;cursor:pointer" class="help" src="Sistema/img/comentario.png"/><div id="txt_'.$id_bd.'" style="display:none">'.$TEXTO_AYUDA.'</div><',$linea);
				$text = str_replace($linea,$nueva_linea,$text);
				//echo "BUSCA: $linea\n";
				//echo "REMPLAZA: $nueva_linea\n\n\n";
			}
			//exit();						
		}catch(Exception $e){
			print_r($e);
		}
		
		
		
		
		return $text;
		
	}
	
	
	public function cargarEditarInline($text){
		$debug = false;
		$pos = 0;
		$i=0;
		$total = strlen($text);
		$TEXT_INICIAL = $text;
		try{
			preg_match_all("|<span(.*)editar-inline(.*)[^>]+>(.*)</[^>]+>|U",$text,$out, PREG_PATTERN_ORDER);			
			foreach($out[0] as $key => $registro){				
				$text = str_replace('class="editar-inline">'.$out[3][$key], 'class="editar-inline">'.$out[3][$key]."<span onclick=\"fun_editarObject(this)\" class=\"editar\" style=\"display:none;cursor:pointer\"><img src=\"Sistema/img/editar.png\" /></span>",$text);
			}
										
		}catch(Exception $e){
			print_r($e);
		}
		//$text = $TEXT_INICIAL;
		//echo $text;
		try{
			preg_match_all("|<label(.*)editar-inline(.*)>(.*)<|U",$text,$out2, PREG_PATTERN_ORDER);
			
			foreach($out2[0] as $key => $registro){
				//echo substr($out2[0][$key],0,strlen($out2[0][$key])-1)."\n";
				//echo $out2[0][$key]."<span onclick=\"fun_editarObject(this)\" class=\"editar\" style=\"display:none;cursor:pointer\"><img src=\"Sistema/img/editar.png\" /></span><\n";
				//exit();	
				$text = str_replace(substr($out2[0][$key],0,strlen($out2[0][$key])-1), substr($out2[0][$key],0,strlen($out2[0][$key])-1)."<span onclick=\"fun_editarObject(this)\" class=\"editar\" style=\"display:none;cursor:pointer\"><img src=\"Sistema/img/editar.png\" /></span>",$text);
			}
			if($debug){
				
				echo "<pre>";print_r($out2);echo "</pre>";exit();
				
			}
		}catch(Exception $e){
			print_r($e);
		}
		//$text = $TEXT_INICIAL;
		return $text;	

		
	}	
	
	public function persistenciaSesionRescatar(){
		$this->claveEncriptada = $this->_SESION->getVariable('claveEncriptada');
		$this->guardaSesion = $this->_SESION->getVariable('claveGuardada');
		$this->_LOG->log('Se rescatan valores de persistencia');
	}
	
	public function persistenciaSesionSetear(){		
		$this->_SESION->setVariable('claveEncriptada',$this->claveEncriptada); 
		$this->_SESION->setVariable('claveGuardada',$this->guardaSesion);
		$this->_LOG->log('Se setean algunos valores como:');
		
	}
	
	
}

?>