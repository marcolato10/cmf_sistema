<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1); 
include('Sistema/class/paginapurso.class.php');
include('Sistema/class/claseSistema.class.php');
include('Sistema/class/resolucion.class.php');
include('Sistema/class/modulo.class.php');
include('Sistema/class/propiedades.class.php');
include('Sistema/class/guardar.class.php');
include('Sistema/class/clienteWs.class.php');

include('Sistema/class/vista/paso1.class.php');
include('Sistema/class/vista/paso2.class.php');
include('Sistema/class/vista/paso3.class.php');
include('Sistema/class/vista/paso4.class.php');
include('Sistema/config/config.php');


class redactar extends PaginaPurso{

	public $MENSAJES = array();
	public $ALERT = array();
	public $CAMBIA = array();	
	public $OPEN = array();	
	public $CLOSE = array();	
	public $HIDE = array();	
	public $SHOW = array();	
	public $CLASS_ADD = array();	
	public $CLASS_REMOVE = array();	
	
	
	
	public $ES_VISACION = false;
	
	
	
	
	public function onLoad(){
		//print_r($this);
		//exit();
			$this->_RESOLUCION = new Resolucion($this);
			if(!$this->_RESOLUCION->isValidoModificar()){
				echo "El documento al que estas intentando ingresar necesita de privilegios";
				exit();
			}
	}
	
	public function main(){
		try{
			//$this->_RESOLUCION = new Resolucion($this);			
			$this->_TEMPLATE->assign('DISPLAY_PASO1','none');
			$this->_TEMPLATE->assign('DISPLAY_PASO2','none');
			$this->_TEMPLATE->assign('DISPLAY_PASO3','none');
			$this->_TEMPLATE->assign('DISPLAY_PASO4','none');
			//echo '-'.$this->_RESOLUCION->PASO_SELECCIONADO.'-';
			//exit();
			$this->_TEMPLATE->assign('DISPLAY_PASO'.$this->_RESOLUCION->PASO_SELECCIONADO,'block');
			$this->_TEMPLATE->assign('ACTIVO_PASO'.$this->_RESOLUCION->PASO_SELECCIONADO,'activo');
			$this->_TEMPLATE->assign('PASO_SELECCIONADO', $this->_RESOLUCION->PASO_SELECCIONADO);
			
			
			$paso1 = new Paso1();
			$paso1->setControl($this);
			$paso1->generarHTML();
			$paso2 = new Paso2();
			$paso2->setControl($this);
			$paso2->generarHTML();
			$paso3 = new Paso3();
			$paso3->setControl($this);
			$paso3->generarHTML();		
			$paso4 = new Paso4();
			$paso4->setControl($this);
			$paso4->generarHTML();	
			if($this->_RESOLUCION->RES_ID !== NULL){					
				$this->_TEMPLATE->assign('DISPLAY_BOTON_ANULAR', 'inline');				
			}else{
				$this->_TEMPLATE->assign('DISPLAY_BOTON_ANULAR', 'none');
			}
			$this->_TEMPLATE->parse('main.anular');	
			
			$this->_TEMPLATE->parse('main');
			//echo $this->_SESION->getVariable('PRI_ID');
		}catch(Exception $e){
			print_r($e);
		}
	}					
	
	//Eventos de paso1
	public function fun_setTipoResolucion(){
		return $this->_RESOLUCION->setTipoResolucion();
	}
	
	public function fun_setClasificacion(){
		return $this->_RESOLUCION->setClasificacion();
	}
	
	public function fun_eliminarFirmante(){
		$CAMBIA = array();
		$MENSAJES = array();
		$VAL = array();
		$json = array();
		$json['RESULTADO'] = 'OK';
		
		$FIRMANTES = $this->_SESION->getVariable('FIRMANTES');
		$FIRMANTES = (is_array($FIRMANTES)) ? $FIRMANTES : array();
		//$FIRMANTES[] = $_POST['firmante'];
		$INDEX_FIRMANTE = array();
		foreach($FIRMANTES as $FIRMADOR){
			
			if($_POST['firmante'] != $FIRMADOR){
				$f_usu  = explode('|-|',$FIRMADOR);
				
				$USUARIOS = $this->_ORA->Select("SELECT ep_usuario, TRIM(EP_ALIAS) || ' ' || TRIM(EP_APE_PAT) || ' ' || TRIM(EP_APE_MAT) NOMBRE FROM EP_FUN_TODOS WHERE EP_VIGENTE = 'S' AND EP_USUARIO = '".$f_usu[0]."'"); //cuidado inj
				$NOMBRES = '';
				while($D_USUARIOS = $this->_ORA->fetchArray($USUARIOS)){
					
					//--
					$bind_u = array(':usuario' => $D_USUARIOS['EP_USUARIO'] , ':dis'=> 2);
					$ROLES = $this->_ORA->retornaCursor('FED.FEL_ROL_FIRMA_PKG.get_rol_para_firmar','procedure',$bind_u);
					$ROL_USUARIO = '';
					
					while($data_roles = $this->_ORA->FetchArray($ROLES) ){
						if($data_roles['FEL_ROL_COD_DESCRIP'] == $f_usu[1]){
							$ROL_USUARIO = $data_roles['FEL_DESCRIPCION'];
						}
					}
					
					//--
					
					
					
					
					
					$NOMBRES =  $D_USUARIOS['NOMBRE'].' ('.$ROL_USUARIO.')';
					$INDEX_FIRMANTE[$D_USUARIOS['EP_USUARIO'].'|-|'.$f_usu[1]] = $D_USUARIOS['EP_USUARIO'].'|-|'.$f_usu[1];
				}
			
				$this->_TEMPLATE->assign('FIRMANTE',$NOMBRES);
				$this->_TEMPLATE->assign('ORDEN_FIRMANTE',$f_usu[0]);
				$this->_TEMPLATE->assign('EP_USUARIO',$f_usu[0].'|-|'.$f_usu[1]);
				$this->_TEMPLATE->parse('main.paso2.firma_multiple.div_listadoFirmantes.firmante');
			}
		}
		$FIRMANTES = array();
		foreach($INDEX_FIRMANTE as $f){
			$FIRMANTES[] = $f;
		}
		$this->_TEMPLATE->parse('main.paso2.firma_multiple.div_listadoFirmantes');
		$this->_SESION->SetVariable('FIRMANTES',$FIRMANTES);
		$CAMBIA['#div_listadoFirmantes'] = $this->_TEMPLATE->text('main.paso2.firma_multiple.div_listadoFirmantes');
		/*$MENSAJES[] = 'La Clasificacion es HOJA: '.$_POST['CLA_ID'];
		$VAL['#input_clasificacion'] = $_POST['CLA_ID'];
		$CAMBIA['#div_tabla_propiedades_clasificaciones'] = $this->_TEMPLATE->text('main.paso1.div_clasificacion.tabla_propiedades_clasificaciones');
		$MENSAJES[] = 'No es Hoja';
		$VAL['#input_clasificacion'] = '';*/
		$json['CAMBIA'] = $CAMBIA;
		$json['VAL'] = $VAL;			
		return json_encode($json);
	}
	
	
	public function fun_ordenaFirmante(){
		
		$CAMBIA = array();
		$MENSAJES = array();
		$VAL = array();
		$json = array();
		$json['RESULTADO'] = 'OK';
		$FIRMANTES_AUX = array();
		$FIRMANTES = $this->_SESION->getVariable('FIRMANTES');
		$FIRMANTES = (is_array($FIRMANTES)) ? $FIRMANTES : array();
		foreach($_POST['liFirmante'] as $usr){
			foreach($FIRMANTES as $usr_actual){
				list($USU_FIR,$ROL_FIR) = explode('|-|',$usr_actual);
				if($usr == $USU_FIR){
					$FIRMANTES_AUX[] = $USU_FIR.'|-|'.$ROL_FIR;
				}
			}
		}
		$this->_SESION->SetVariable('FIRMANTES',$FIRMANTES_AUX);
		return json_encode($json);
	
	}
	
	public function fun_agregarFirmante(){
		
		$CAMBIA = array();
		$MENSAJES = array();
		$VAL = array();
		$json = array();
		$json['RESULTADO'] = 'OK';
		
		$FIRMANTES = $this->_SESION->getVariable('FIRMANTES');
		$FIRMANTES = (is_array($FIRMANTES)) ? $FIRMANTES : array();
		
		
		
		/*
		$resultado = array_search($_POST['firmante'], $FIRMANTES);
		if($resultado === false){
			$bind = array(
				':res_id' => $this->_SESION->getVariable('RES'),
				':res_firma',
				':res_incluye'
			);
			$this->_ORA->ejecutaFunc('RSO.RSO_FIRMANTES_PKG.fun_setFirmante',$bind);
		
		}
		*/
		
		list($USU_FIR,$ROL_FIR) = explode('|-|',$_POST['firmante']);
		$FIRMANTES[] = $USU_FIR.'|-|'.$ROL_FIR;
		$INDEX_FIRMANTE = array();
		foreach($FIRMANTES as $FIRMADOR){
			if(!isset($INDEX_FIRMANTE[$FIRMADOR])){
				list($USU_FIR_S,$ROL_FIR_S) = explode('|-|',$FIRMADOR);
				$USUARIOS = $this->_ORA->Select("SELECT ep_usuario, TRIM(EP_ALIAS) || ' ' || TRIM(EP_APE_PAT) || ' ' || TRIM(EP_APE_MAT) NOMBRE FROM EP_FUN_TODOS WHERE EP_VIGENTE = 'S' AND EP_USUARIO = '".$USU_FIR_S."'"); //cuidado inj
				$NOMBRES = '';
				while($D_USUARIOS = $this->_ORA->fetchArray($USUARIOS)){
					
					$bind_u = array(':usuario' => $D_USUARIOS['EP_USUARIO'] , ':dis'=> 2);
					$ROLES = $this->_ORA->retornaCursor('FED.FEL_ROL_FIRMA_PKG.get_rol_para_firmar','procedure',$bind_u);
					$ROL_USUARIO = '';
					while($data_roles = $this->_ORA->FetchArray($ROLES) ){
						if($data_roles['FEL_ROL_COD_DESCRIP'] == $ROL_FIR_S){
							$ROL_USUARIO = $data_roles['FEL_DESCRIPCION'];
						}
					}
				
					
					
					$NOMBRES =  $D_USUARIOS['NOMBRE'].' ('.$ROL_USUARIO.')';
					
					
					//revisar el index-firmante
					$INDEX_FIRMANTE[$D_USUARIOS['EP_USUARIO'].'|-|'.$ROL_FIR_S] = $D_USUARIOS['EP_USUARIO'].'|-|'.$ROL_FIR_S;
				}
				
				$this->_TEMPLATE->assign('FIRMANTE',$NOMBRES);
				$this->_TEMPLATE->assign('ORDEN_FIRMANTE',$USU_FIR_S);
				$this->_TEMPLATE->assign('EP_USUARIO',$USU_FIR_S.'|-|'.$ROL_FIR_S);
				$this->_TEMPLATE->parse('main.paso2.firma_multiple.div_listadoFirmantes.firmante');
			}
		}

		
		$FIRMANTES = array();
		foreach($INDEX_FIRMANTE as $f){
			$FIRMANTES[] = $f;
		}
		$this->_TEMPLATE->parse('main.paso2.firma_multiple.div_listadoFirmantes');
		$this->_SESION->SetVariable('FIRMANTES',$FIRMANTES);
		$CAMBIA['#div_listadoFirmantes'] = $this->_TEMPLATE->text('main.paso2.firma_multiple.div_listadoFirmantes');
		/*$MENSAJES[] = 'La Clasificacion es HOJA: '.$_POST['CLA_ID'];
		$VAL['#input_clasificacion'] = $_POST['CLA_ID'];
		$CAMBIA['#div_tabla_propiedades_clasificaciones'] = $this->_TEMPLATE->text('main.paso1.div_clasificacion.tabla_propiedades_clasificaciones');
		$MENSAJES[] = 'No es Hoja';
		$VAL['#input_clasificacion'] = '';*/
		$json['CAMBIA'] = $CAMBIA;
		$json['VAL'] = $VAL;			
		return json_encode($json);
	}
	
	public function fun_seleccionarClasificacion(){
		return $this->_RESOLUCION->seleccionarClasificacion();
	}
	
	public function fun_setClasificacion2(){
		//print_r($this->_RESOLUCION);
		//exit();
		return $this->_RESOLUCION->setClasificacion2();
	}
	
	public function fun_agregarClasificacion(){
		return $this->_RESOLUCION->agregarClasificacion();
	}
	
	public function fun_eliminarClasificacion(){
		return $this->_RESOLUCION->eliminarClasificacion();
	}
	
	public function fun_buscarClasificacion(){	
		return $this->_RESOLUCION->buscarClasificacion();
	}
	
	public function fun_buscarFiscalizado(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->buscarFiscalizado();
	}
	
	
	public function fun_getDestinatariosTipo(){
		
        return $this->_RESOLUCION->getDestinatariosTipo();
	}
	
	public function fun_agregarSeleccionadoDestinatario(){
		return $this->_RESOLUCION->agregarSeleccionadoDestinatario();
	}

	
	
	public function fun_eliminarSeleccionadoDestinatario(){				
		return $this->_RESOLUCION->DESTINATARIO_OBJ->eliminarSeleccionadoDestinatario();
	}
	
	public function fun_agregarOtro(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->agregarOtro();
	}
	
	public function fun_agregarFiscalizadoParaOtro(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->agregarFiscalizadoParaOtro();
	}
	
	public function fun_editarDestinatario(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->editarDestinatario();
	}
	
	public function fun_mostrarSeilUsuario(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->mostrarSeilUsuario();
	}
	
	public function fun_guardarUsuariosSeleccionados(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->guardarUsuariosSeleccionados();
	}
	//PASO2
	public function fun_setEnvio(){
		return $this->_RESOLUCION->setEnvio();
	}
	
	public function fun_agregarOtroEnvio(){
		return $this->_RESOLUCION->getHtmlEnvio();
	}
	
	public function fun_jurisprudencia(){
		return $this->_RESOLUCION->setJurisprudencia();
	}
	
	public function fun_setDistribucion(){
		return $this->_RESOLUCION->setDistribucion();
	}
	
	public function fun_setPublicacion(){
		return $this->_RESOLUCION->setPublicacion();
	}
	
	public function fun_setPrivacidad(){
		return $this->_RESOLUCION->setPrivacidad();
	}
	
	
	
	public function fun_setMulta(){
		return $this->_RESOLUCION->setMulta();
	}
	
	public function fun_setPago(){
		return $this->_RESOLUCION->setPago();
	}
	
	public function fun_cambioAtributoModuloInput(){
		return $this->_RESOLUCION->MODULO_OBJ->cambioAtributoModuloInput();
	}
	
	public function fun_cambioAtributoModuloSelect(){
		return $this->_RESOLUCION->MODULO_OBJ->cambioAtributoModuloSelect();
	}	
	
	public function fun_changeReferencia(){
		return $this->_RESOLUCION->setReferencia();
	}
	
	public function fun_changeVistos(){
		return $this->_RESOLUCION->setVistos();
	}
	
	public function fun_changeConsiderando(){
		return $this->_RESOLUCION->setConsiderando();
	}
	
	public function fun_changeResuelvo(){
		return $this->_RESOLUCION->setResuelvo();
	}
	
	public function fun_changeComuniquese(){
		return $this->_RESOLUCION->setComuniquese();
	}
	
	public function click_agregarOtroRegistro(){
		$modulo = new Modulo();
		$modulo->setControl($this);
		return $modulo->agregarOtroRegistro();
	}
	
	public function fun_editarObjeto(){
		$propiedad = new Propiedades();
		$propiedad->setControl($this);
		return $propiedad->guardar();
	}
	
	public function fun_editarAtributo(){
		$propiedad = new Propiedades();
		$propiedad->setControl($this);
		return $propiedad->agregarAtributo();
	}
	
	public function click_agregarOtroModulo(){
		$propiedad = new Propiedades();
		$propiedad->setControl($this);
		return $propiedad->crearModulo();
	}
	
	public function click_eliminarOtroRegistro(){
		$modulo = new Modulo();
		$modulo->setControl($this);
		return $modulo->eliminarOtroRegistro();
	}
	
	public function fun_guardaOtrosUsuarioEntidad(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->guardaOtrosUsuarioEntidad();
	}
	
	public function fun_cargoDestinatario(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->cargoDestinatario();
	}
	
	public function fun_dropSubirAdjunto(){
		return $this->_RESOLUCION->ADJUNTO_OBJ->dropSubirAdjunto();
	}
	
	public function  adjunto(){
		$this->_RESOLUCION->ADJUNTO_OBJ->verAdjunto();
	}
	
	public function click_eliminarAdjunto(){
		return $this->_RESOLUCION->ADJUNTO_OBJ->eliminarAdjunto();
	}
	
	public function usr_priv(){
		return $this->_RESOLUCION->fun_usuariosVigentesPrivacidad();
	}
	
	public function fun_setPrivacidadUsr(){
		return $this->_RESOLUCION->setPrivacidadUsr();
	}
	
	public function fun_enviarResolucionArchivo(){
		//print_r($_FILES);
		$JSON = array();
		if(isset($_FILES) && isset($_FILES['file_adjuntoComentario'])){
			$FILE = $_FILES['file_adjuntoComentario'];	

			$temp_file = tempnam(sys_get_temp_dir(), 'RSO_');
			unlink($temp_file);
			move_uploaded_file($FILE['tmp_name'],$temp_file);
			$FILE['tmp_name'] = $temp_file;
			$this->_SESION->setVariable('ARCHIVO_ADJUNTO_ENVIAR',$FILE);
			$JSON['RESPUESTA'] = 'OK';
		}else{
			$JSON['RESPUESTA'] = 'NOK';
		}
		return json_encode($JSON);
		/*Array
(
    [file_adjuntoComentario] => Array
        (
            [name] => Archivo de Prueba SVS.pdf
            [type] => application/zip
            [tmp_name] => /tmp/phpDO7j4c
            [error] => 0
            [size] => 88516
        )

)*/
	}
	
	
	public function fun_enviarResolucion(){
		$this->_SESION->setVariable('usuario_enviar',$_POST['usr_enviar']);
		$this->_SESION->setVariable('comentario_enviar',$_POST['textarea_comentario']);
		$this->_SESION->setVariable('privado',$_POST['privado']);
		$this->_SESION->setVariable('notificacion',$_POST['notificacion']);
		$this->_SESION->setVariable('notificacion_int',$_POST['notificacion_int']);
		$this->_SESION->setVariable('notificacion_copia',$_POST['usuarios_copia']);
		$this->ES_VISACION = true;
		//Acá se guarda y despues se verifica si se debe avanzar el worflow
		return $this->click_guardarResolucion();
	}
	
	
	
	
	
	public function fun_anular(){
		return $this->_RESOLUCION->anular();
	}
	
	
		
	
	public function fun_editarComentario(){
		
		$json = array();
		$json['RESULTADO'] = 'OK';
		$MENSAJES = array();
		$CAMBIA = array();
		//	$CAMBIA['#arbol_propiedades'] = $this->CLASIFICACION_OBJ->dibujaPropiedades();
			
		
		$db = new SQLite3('Sistema/bd/ayuda.php');
		$stmt = $db->prepare('SELECT ID_OBJECT FROM ayuda_resolucion WHERE ID_OBJECT = "'.$_POST['id_comentario'].'";');
		$result = $stmt->execute();
		$cantidad = 0;
		while ($row = $result->fetchArray()){
			$cantidad++;
		}
		if($cantidad === 0){
			$db->exec('INSERT INTO ayuda_resolucion (ID_OBJECT, TEXTO_AYUDA) VALUES ("'.$_POST['id_comentario'].'","'.$_POST['textarea_comentarioEditar'].'");');
		}else{
			$db->exec('UPDATE ayuda_resolucion SET TEXTO_AYUDA = "'.$_POST['textarea_comentarioEditar'].'" where ID_OBJECT = "'.$_POST['id_comentario'].'"');
			
		}
		$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			return json_encode($json);
	}
	
	public function click_guardarResolucion(){										
		$json = array();
		$json['RESULTADO'] = 'OK';
		$paso = $_POST['paso'];
		$this->MENSAJES[] = 'Inicia click_guardarResolucion';
		//print_r($this->_SESION);
		
		$guardar = new Guardar();
		$guardar->setControl($this);
		$res = $guardar->GuardarResolucion($this->ES_VISACION);
		
		$bind  = array(':p_RES_ID' =>$this->_SESION->getVariable('RES_ID') ,
						':p_RES_VERSION' => $this->_SESION->getVariable('RES_VERSION'),
						':p_POSRUB_USUARIO' => $this->_SESION->USUARIO,
						':p_POSRUB_POSICION' =>$this->_SESION->getVariable('_POSICION_FIRMA') );
		$this->_ORA->ejecutaFunc('rso.RSO_POSICION_RUBRICA_PKG.fun_insertPosicionFirma',$bind);
		
		
		$bind  = array(':p_RES_ID' => $this->_SESION->getVariable('RES_ID') ,
						':p_USUARIO' => $this->_SESION->USUARIO,
						':p_VERSION' => 2);

		$this->_ORA->ejecutaFunc('rso.RSO_VERSION_PKG.fun_setVersion',$bind);
		$this->_ORA->Commit();
		
		/*
		
		$alert[] = array('title' => 'Requiere Pago',
										 'html' => $this->HTML_SALIDA,
										 'callback' =>  'fun_salirResolucion();');
										 
		*/
		
		
		
		
		//Acá se debe subir el archivo al mensaje.-
		if(isset($_POST['adjunto']) && $_POST['adjunto'] == 'S'){
			$this->MENSAJES[] = 'Existe el post del adjunto'; 
			$file = $this->_SESION->getVariable('ARCHIVO_ADJUNTO_ENVIAR');
			if(is_array($file)){
				$this->MENSAJES[] = 'File es arreglo'; 
				$blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
				$blob->WriteTemporary(file_get_contents($file['tmp_name']),OCI_TEMP_BLOB);
				$bind = array(':p_vis_id' => (string)$res->ID_VISACION,':p_blob' => $blob, ':p_vis_mime' => $file['type'],':p_vis_nombre' => $file['name']);
				$this->MENSAJES[] = 'Bind:'.print_r($bind,true); 
				$this->_ORA->ejecutaFunc('RSO_GUARDAR_PKG.fun_guardarVisacionArchivo',$bind);
				$this->_ORA->Commit();
				$this->MENSAJES[] = 'Ya commit'; 
			}
							
		}
		
		$_OPCION_SUBIR_PDF = $this->_SESION->getVariable('_OPCION_SUBIR_PDF');
		$this->_LOG->log(__METHOD__.' ('.__LINE__.') La $_OPCION_SUBIR_PDF: '.$_OPCION_SUBIR_PDF);
		if($_OPCION_SUBIR_PDF){
			$md5_file = $this->_SESION->getVariable('archivo_pdf_subido_md5');
			$this->_LOG->log(__METHOD__.' ('.__LINE__.') $md5_file: '.$md5_file);
			$bind = array(
				':P_RES_ID' => $this->_SESION->getVariable('RES_ID'),
				':P_RES_VERSION' => $this->_SESION->getVariable('RES_VERSION'));
			$this->_LOG->log(__METHOD__.' ('.__LINE__.') $bind: '.print_r($bind,true));
			$md5_file_bd = $this->_ORA->ejecutaFunc('RSO.RSO_OPCION_PDF_PKG.fun_getUltimiMd5Pdf',$bind);
			$this->_LOG->log(__METHOD__.' ('.__LINE__.') $md5_file_bd: '.$md5_file_bd);
			if($md5_file_bd != $md5_file){
				$archivo_pdf_subido = $this->_SESION->getVariable('archivo_pdf_subido');
				if($archivo_pdf_subido != false){
					//guardar con archivo
					$this->_LOG->log(__METHOD__.' ('.__LINE__.') $archivo_pdf_subido: ');
					$blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
					$blob->WriteTemporary($archivo_pdf_subido,OCI_TEMP_BLOB);
					
					
					$bind = array(
						':p_res_id' => $this->_SESION->getVariable('RES_ID'),
						':p_res_version' => $this->_SESION->getVariable('RES_VERSION'),
						':p_opcpdf_usuario' => $this->_SESION->getUsuario(),
						':p_opcpdf_archivo' => $blob,
						':p_opcpdf_md5' => $md5_file
					);
					$this->_LOG->log(__METHOD__.' ('.__LINE__.') $bind: '.print_r($bind,true));
					$this->_ORA->ejecutaFunc('RSO.RSO_OPCION_PDF_PKG.fun_setArchivoPdf',$bind);
					$this->_ORA->Commit();
					$this->_LOG->log(__METHOD__.' ('.__LINE__.') Despues de la funcion fun_setArchivoPdf');
		
		
				}else{
					//eliminar archivo
				}
			}
		}else{
			if(isset($this->_RESOLUCION->_VERSION_RSO) && $this->_RESOLUCION->_VERSION_RSO == 'v2'){
				$this->_SESION->setVariable('archivo_pdf_subido_md5',false);
				$this->_SESION->setVariable('archivo_pdf_subido',false);
				$this->_SESION->setVariable('_OPCION_SUBIR_PDF',false);
				
				$bind = array(
					':P_RES_ID' => $this->_SESION->getVariable('RES_ID'),
					':P_RES_VERSION' => $this->_SESION->getVariable('RES_VERSION'));
				$this->_ORA->ejecutaFunc('RSO.RSO_OPCION_PDF_PKG.fun_setNoOpcionPdf',$bind);
				$this->_ORA->Commit();
			}
		}
		
		

		$ALERTA_SALIDA = array();
		if((string)$res->ESTADO == 'OK'){		 
			 $this->CAMBIA["#res_id"] = "<strong>Caso:</strong>".$res->RES_ID;
			 $ALERTA_SALIDA['title'] = 'Exito';
			 $ALERTA_SALIDA['html'] = 'La Resolución caso '.$res->RES_ID.' se ha guardado con Éxito';
		}else{
			 $ALERTA_SALIDA['title'] = 'Error';
			 $ALERTA_SALIDA['html'] = 'Ocurrió un Error al momento de Guardar la Resolucion';
		}
		
		
		
		
		
		
		
		$this->SHOW = (is_array($this->SHOW)) ? $this->SHOW : array();
		$this->SHOW['#boton_anular_doc'] = 'show';
		$json['MENSAJES'] =  $this->MENSAJES;
		$json['CAMBIA'] = $this->CAMBIA;
		$json['CLOSE'] = $this->CLOSE;
		$json['HIDE'] = $this->HIDE;
		$json['SHOW'] = $this->SHOW;
		$json['ALERT'] = $this->ALERT;
		$json['CLASS_REMOVE'] = $this->CLASS_REMOVE;
		$json['CLASS_ADD'] = $this->CLASS_ADD;
		if($this->ES_VISACION){
			//$json['CALLBACK'][] = "window.location.href = 'index.php?pagina=paginas.salirRedaccion'";
			
			if($this->_SESION->getVariable('PRI_ID') == 'reser' && $this->fun_esNoElectronico()){
				$ALERTA_SALIDA['html'] = "<strong>Atención:</strong><br><br>Resolución Reservada que se envia impresa. Una vez que se firme la resolución, se irá a la bandeja del (de la ) redactor(a), para que imprima la resolución y la lleve a Oficina de Partes";
			}			
			$ALERTA_SALIDA['callback'] = 'fun_salirResolucion();';
		}
		$this->ALERT[] = $ALERTA_SALIDA;
		$json['ALERT'] = $this->ALERT;
		return json_encode($json);				
	}
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function fun_validarPaso(){		
		$json = array();
		$json['RESULTADO'] = 'OK';
		$paso = $_POST['paso'];
		$this->_SESION->setVariable('PASO_SELECCIONADO',$paso);
		$this->_RESOLUCION->PASO_SELECCIONADO = $paso;
		$this->MENSAJES[] = 'Inicia fun_validarPaso con Paso '.$paso;	
		
		
		if($paso == 1){
			if($this->valida1()){
				$this->verPaso($paso);
			}
		}
		
		if($paso == 2){
			if($this->valida2()){
				$this->verPaso($paso);
			}
		}
		
		if($paso == 3){
			if($this->valida3()){
				$this->verPaso($paso);
			}
		}
		
		if($paso == 4){
			if($this->valida4()){
				$this->verPaso($paso);
			}
		}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
	/*	
		
		if($paso == 4){
			$envio = $this->_SESION->getVariable('ENV_ID');
			$this->MENSAJES[] = print_r($envio,true);
			if(is_array($envio) && count($envio)> 0){
				$this->MENSAJES[] = 'Envio';
				$this->HIDE['#div_principalPaso2'] = 'hide';
				$this->HIDE['#div_principalPaso1'] = 'hide';
				$this->SHOW['#div_principalPaso4'] = 'show';
				$this->HIDE['#div_principalPaso3'] = 'hide';
			}else{
				$this->MENSAJES[] = '***NO**** existe tipo de envio';
				$this->ALERT[] = 'Debe seleccionar el tipo de envio';
			}
		}
		
		if($paso == 3){
			$envio = $this->_SESION->getVariable('ENV_ID');
			$this->MENSAJES[] = print_r($envio,true);
			if(is_array($envio) && count($envio)> 0){
				$this->MENSAJES[] = 'Envio';
				$this->HIDE['#div_principalPaso2'] = 'hide';
				$this->HIDE['#div_principalPaso1'] = 'hide';
				$this->HIDE['#div_principalPaso4'] = 'hide';
				$this->SHOW['#div_principalPaso3'] = 'show';
			}else{
				$this->MENSAJES[] = '***NO**** existe tipo de envio';
				$this->ALERT[] = 'Debe seleccionar el tipo de envio';
			}
		}
		
		*/


		
		
		
		
		
		$json['MENSAJES'] =  $this->MENSAJES;
		$json['CAMBIA'] = $this->CAMBIA;
		//$json['OPEN'] = $OPEN;
		$json['CLOSE'] = $this->CLOSE;
		$json['HIDE'] = $this->HIDE;
		$json['SHOW'] = $this->SHOW;
		$json['ALERT'] = $this->ALERT;
		$json['CALLBACK'] = $this->CALLBACK;
		$json['CLASS_REMOVE'] = $this->CLASS_REMOVE;
		$json['VAL'] = array("#input_hiddenPasoSeleccionado" =>$this->_RESOLUCION->PASO_SELECCIONADO);
		$json['CLASS_ADD'] = $this->CLASS_ADD;
		
		
		return json_encode($json);
		
	}
	
	public function fun_opcionSubirPDF(){
		$json = array();
		$this->_SESION->setVariable('_OPCION_SUBIR_PDF',true);
		$json['RESULTADO'] = 'OK';
		$json['CAMBIA']['#a_pdf'] = '<img id="img_pdf" src="Sistema/img/lapiz.png" />Volver a Redactar';
		$json['HIDE']['#div_paso3'] = 'hide';
		$json['SHOW']['#file_subirPDF'] ='show';
		$json['CALLBACK'][] ="$('#iframePDF').attr('src','index.php?pagina=paginas.vistaPreviaResolucion&vista.pdf');";
		return json_encode($json);
	}
	
	public function fun_opcionRedactar(){
		$json = array();
		$this->_SESION->setVariable('_OPCION_SUBIR_PDF',false);
		$json['RESULTADO'] = 'OK';
		$json['CAMBIA']['#a_pdf'] = '<img id="img_pdf" src="Sistema/img/pdf.png" />Subir un PDF';
		$json['HIDE']['#file_subirPDF'] = 'hide';
		$json['SHOW']['#div_paso3'] ='show';
		return json_encode($json);
	}
	
	
	public function valida1(){
		return true;
	}
	
	public function valida2(){
		$count = $this->_SESION->getVariable('CLA_ID');
		$count = (is_array($count)) ? $count : array();
		if(count($count) > 0){
			$this->MENSAJES[] = 'Existe al menos una clasificacion';
		}else{
			$this->MENSAJES[] = 'No se ha seleccionado clasificación';
			$this->ALERT[] = 'Debe seleccionar al menos una Clasificación';
			return false;
		}					
		return true;
	}
	
	public function valida3(){
		if($this->valida2()){
			
			$CANTIDAD_DESTINATARIOS = 0;
			$DEST = $this->_SESION->getVariable('DESTINATARIO');
			$TOTAL_PAPEL = 0;
			$TOTAL_ELECTRONICO = 0;
			if(is_array($DEST) && count($DEST) > 0){
				foreach($DEST as $DEST_TE){
					$CANTIDAD_DESTINATARIOS = $CANTIDAD_DESTINATARIOS + count($DEST_TE);
					foreach($DEST_TE as $DEST_UNICO){

						if($DEST_UNICO['DES_TIPO_ENT'] == 'PUGEN' || trim($DEST_UNICO['DES_TIPO_ENT']) == 'PUPUB'){
							
							if(isset($DEST_UNICO['DES_DIRECCION']) && strlen(trim($DEST_UNICO['DES_DIRECCION'])) > 0){
								$TOTAL_PAPEL++;
							}
							if(isset($DEST_UNICO['DES_CORREO']) && strlen(trim($DEST_UNICO['DES_CORREO'])) > 0){
								$TOTAL_ELECTRONICO++;
							}

						}else{
							if(isset($DEST_UNICO['DES_DIRECCION']) && strlen(trim($DEST_UNICO['DES_DIRECCION'])) > 0){
								$TOTAL_PAPEL++;
							}
							if(isset($DEST_UNICO['USUARIOS_SEIL']) && count($DEST_UNICO['USUARIOS_SEIL']) > 0){
								$TOTAL_ELECTRONICO++;
							}
						}
					}
				}
			}
			
			
			$envio = $this->_SESION->getVariable('ENV_ID');
			$distribucion = $this->_SESION->getVariable('DIS_ID');
			//print_r($distribucion);
			//sindi
			//inmed
			//difer
			//deman
			$this->MENSAJES[] = print_r($envio,true);
			$se_envia = true;
			$hay_papel = 0;
			$hay_electronico = 0;
			if(is_array($envio) && count($envio)> 0){
				$this->MENSAJES[] = 'Envio: '.print_r($envio,true);
				//print_r($envio);
				foreach($envio  as $envio_txt){
					if($envio_txt['ENV_ID'] == 'noen' && count($envio) > 1) {
						$this->ALERT[] = 'Al seleccionar "No se envía" no debe seleccionar otro tipo de envío';
						return false;
					}
					if($envio_txt['ENV_ID'] == 'noen'){
						
					}
					if($envio_txt['ENV_ID'] == 'certi'){
						$hay_papel++;
					}
					if($envio_txt['ENV_ID'] == 'corre'){
						$hay_papel++;
					}
					if($envio_txt['ENV_ID'] == 'mano'){
						$hay_papel++;
					}
					if($envio_txt['ENV_ID'] == 'elect'){
						$hay_electronico++;
					}
					
					
					
					
					if($envio_txt['ENV_ID'] == 'noen'){
						$se_envia = false;
					}
					
					
					
					if($CANTIDAD_DESTINATARIOS === 0 && $envio_txt['ENV_ID'] != 'noen'){
						$this->ALERT[] = 'Seleccionó tipo de envio y no existen destinatarios para enviar';
						return false;
					}
				}
				
				//verificando si hay error en el envio por mano
				
				if($hay_electronico > 0){
					if($CANTIDAD_DESTINATARIOS != $TOTAL_ELECTRONICO){
						//No todos los destintarios tienen medio de envio electrónico						
						$this->ALERT[] = '<font color="red">¡Atención!</font> Se ha seleccionado como medio de envio electrónico, no todos los destinatarios tienen posibilidad de ese envío.';
					}
				}
				
				if($hay_papel > 0){
					if($CANTIDAD_DESTINATARIOS != $TOTAL_PAPEL){
						//No todos los destintarios tienen medio de envio electrónico
						$this->ALERT[] = '<font color="red">¡Atención!</font> Se ha seleccionado como medio de envio papel, no todos los destinatarios tienen domicilio registrado.';
					}
				}
				
				
				
				
				
				//verificando si hay error en el envio electronico
				
				
				
				if($se_envia && $distribucion == 'sindi'){
					$this->ALERT[] = 'La Resolución esta configurada sin distribución, debe escoger "No se envía"';
					return false;
				}
				if($CANTIDAD_DESTINATARIOS > 0 && !$se_envia){
					$this->ALERT[] = '<font color="red">¡Atención!</font> Contiene destinatarios pero no será distribuida (se encuentra configurada "Sin Distribución")';
				}
				
				if(count($this->ALERT) > 1){
					$ALERTA = implode("<br/>",$this->ALERT);
					$this->ALERT = array();
					$this->ALERT[] = $ALERTA;
				}
			}else{
				$this->MENSAJES[] = '***NO**** existe tipo de envio';
				$this->ALERT[] = 'Debe seleccionar el tipo de envio';
				return false;
			}
			return true;
		}else{
			return false;
		}
	}
	
	public function valida4(){
		
		return ($this->valida2() && $this->valida3());
	}
	
	public function verPaso($ver_paso){				
		for($i=1;$i <= CANTIDAD_PASOS; $i++){
			if($ver_paso == $i){
				$this->SHOW['#div_principalPaso'.$i] = 'show';
				$this->CLASS_ADD['li.op'.$i] = 'activo';
			}else{
				$this->HIDE['#div_principalPaso'.$i] = 'hide';
				$this->CLASS_REMOVE['li.op'.$i] = 'activo';
			}
		}
		$this->CALLBACK[] = "$('#iframePDF').attr('src','index.php?pagina=paginas.vistaPreviaResolucion&vista.pdf');";
	}
	
	
	
	public function fun_esNoElectronico(){
			$tipos_de_envio = $this->_SESION->getVariable('ENV_ID');
			if(isset($tipos_de_envio['elect'])){
				unset($tipos_de_envio['elect']);
			}
			return (count($tipos_de_envio) > 0) ? true : false;
	}
	
	public function fun_pasteWord(){
		$HTML_INI = stripslashes($_POST['html']);
		$HTML = preg_replace("/<img[^>]+\>/i", "", $HTML_INI);
		if(trim($HTML) == ""){
			$HTML =$HTML_INI;
		}
		return $HTML;
	}
	
	
}
?>