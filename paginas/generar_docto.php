<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1); 

require_once("class/correo.class.php");
include('Sistema/class/paginapurso.class.php');
include('Sistema/class/claseSistema.class.php');
include('Sistema/class/resolucion.class.php');
include('Sistema/class/modulo.class.php');
include('Sistema/class/propiedades.class.php');
include('Sistema/class/guardar.class.php');
include('Sistema/class/clienteWs.class.php');
include('Sistema/class/vista/paso4.class.php');

include_once ( "class/conexion_ora.class.php" );
include_once ( "svslib/connection.php" );	
        
include('Sistema/class/certificado.class.php');


		


class generar_docto extends Pagina{

  
    public $CUERPO_CERTIFICADO;
    public $RSO_ADJUNTO;



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
            
            $this->_CERTIFICADO = new Certificado($this);

            $this->_RESOLUCION = new Resolucion($this);
			if(!$this->_RESOLUCION->isValidoModificar()){
				echo "El documento al que estas intentando ingresar necesita de privilegios";
				exit();
			}
	
    }
    
    public function main(){
    
    
        
        //iniciamos los adjuntos para que no se acumulen
        $this->RSO_ADJUNTO = array();
        $this->_SESION->setVariable('RSO_ADJUNTO',$RSO_ADJUNTO);
        

        $accion = $_GET['accion'];
        $padre  = $_GET['padre']; 
        $tipo   = $_GET['tipo'];

        
        //inicio la accion en el cerificado 
        $this->_SESION->setVariable('ACCION_CERTIFICADO',$accion);
        $this->_SESION->setVariable('TIPO_CERTIFICADO',$tipo);
        
        
        if(isset($_GET['accion'])) {
            if($_GET['accion'] == 'M'){
                $wf  = $_GET['wf']; 
                
                header ("Location: index.php?pagina=paginas.modificar_docto&tipo=".$tipo."&wf=".$wf."&accion=M");

            }else if($accion === 'N'){
                if(isset($_GET['padre']) and isset($_GET['tipo'])){
                    $this->fun_nuevo_certificado($padre,$tipo);
                }else{
                    echo "El padre o tipo no esta definido."; 
                    exit();    
                }
            }else{
                echo "La accion enviada no existe."; 
                exit(); 
            }
        }else{
            echo "No existe accion definida";
            exit();
        }   
        
        

    }

    
    //||||||||||||||||||||||||||||||||||||  CERTIFICADOS NUEVOS ||||||||||||||||||||||||||||||||||
    //ml ::: funcion que nos lleva al nuevo certificado para ser creado
    public function fun_nuevo_certificado($padre,$tipo){

        try{

            $idCCertificado                 = $this->_ORA->ejecutaFunc("utl_frm_html.seq_get",array(':caso_seq'=>'CASO_SEQ'));
        
            /* respuesta de los modulos que se deben mostrar */
            $cuerpo_usa_plantilla           = $this->_CERTIFICADO->cuerpo_usa_plantilla($padre,$tipo);    
            $cuerpo_selecciona_pdf          = $this->_CERTIFICADO->cuerpo_selecciona_pdf($padre,$tipo); 
            $adjuntar_expediente            = $this->_CERTIFICADO->adjuntar_expediente($padre,$tipo);
            $resultado_tipo_envios          = $this->_CERTIFICADO->fun_tipo_envio_html(); 
            $disponible_dato_sensible_si    = $this->_CERTIFICADO->fun_chequea_datos_sensibles('SI',$tipo);
            $disponible_dato_sensible_no    = $this->_CERTIFICADO->fun_chequea_datos_sensibles('NO',$tipo);
            
            
           

            $resultado_privacidad           = $this->_CERTIFICADO->html_privacidad($tipo);
            $cadena                         = $this->_CERTIFICADO->privacidad_cadena($tipo);
    
            $this->_SESION->setVariable('MI_DOC_ID',$idCCertificado);
            $this->cargarExpedientes($padre); //se puede mejorar
            
            
            $this->_TEMPLATE->assign('miCCertificado',$idCCertificado);
            $this->_TEMPLATE->assign('padre',$padre);
            $this->_TEMPLATE->parse('main.padre');
    
            $this->_TEMPLATE->assign('resultado_tipo_envio',$resultado_tipo_envios);
            $this->_TEMPLATE->assign('disponible_dato_sensible_si',$disponible_dato_sensible_si);
            $this->_TEMPLATE->assign('disponible_dato_sensible_no',$disponible_dato_sensible_no);
            $this->_TEMPLATE->assign('resultado_privacidad',$resultado_privacidad);
            
            //var_dump($cadena);
            $this->_TEMPLATE->assign('cadena',$cadena);
            $this->_TEMPLATE->parse('main.existe_privacidad');
            
            

            /* validamos si debe mostrarse en el modulo CUERPO la seccion usa plantilla este*/
            if($cuerpo_usa_plantilla == 0){
                //$resultado_usa_plantilla = $this->html_usa_plantilla();
                $resultado_usa_plantilla = $this->_CERTIFICADO->html_usa_plantilla();
                
                $listado = "";
                //$plantillas = $this->fun_listar_plantillas('certificado');
                $plantillas = $this->_CERTIFICADO->fun_listar_plantillas('certificado');
                foreach($plantillas as $key => $datos){
                    $listado .= '<div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tipoPlantilla" id="tipoPlantilla" value="'.$plantillas[$key]['PLA_ID'].'" onclick="cargarCuerpo(this.value)">&nbsp;<label class="form-check-label">'.$plantillas[$key]['PLA_NOMBRE'].'</label></div>&nbsp;&nbsp;';    
                }
    
    
                $this->_TEMPLATE->assign('usa_plantilla',$resultado_usa_plantilla);
                $this->_TEMPLATE->assign('plantilla_disponible',$listado);
                
                $this->_TEMPLATE->parse('main.usa_plantilla.plantillas_disponibles');
                $this->_TEMPLATE->parse('main.usa_plantilla'); 
            }
            
            /* validamos si debe mostrarse en el modulo CUERPO la seccion selecciona pdf este*/
            if($cuerpo_selecciona_pdf == 0){
                //$resultado_selecciona_pdf = $this->html_selecciona_pdf();
                $resultado_selecciona_pdf = $this->_CERTIFICADO->html_selecciona_pdf();
                $this->_TEMPLATE->assign('selecciona_pdf',$resultado_selecciona_pdf);
                $this->_TEMPLATE->parse('main.selecciona_pdf');
            }
            
            
            if($adjuntar_expediente == 0){ //este
                //$resultado_adjuntar_expediente = $this->html_adjuntar_expediente();
                $resultado_adjuntar_expediente = $this->_CERTIFICADO->html_adjuntar_expediente();
                $this->_TEMPLATE->assign('btn_ver_expediente',$resultado_adjuntar_expediente);
                $this->_TEMPLATE->parse('main.btn_ver_expediente');
            }




            //ocultamos el div de la vista previa    
            $this->_TEMPLATE->assign('DISPLAY_div_vistaPrevia','none');
            $this->_TEMPLATE->parse('main.div_vistaPrevia');
            //ocultamos btn aCertificado
            $this->_TEMPLATE->assign('DISPLAY_btnACertificado','none');
            $this->_TEMPLATE->parse('main.btn_aCertificado');
            //mostramos el contenido del certificado
            $this->_TEMPLATE->assign('DISPLAY_div_contentCertificado','block');
            $this->_TEMPLATE->parse('main.div_cotentCertificado');
            


            $cursor = $this->_ORA->retornaCursor('WEB_OBTENER_DATOS.GetGruposEntidadesRSO','procedure');
            $grupo_aux = NULL;
			while($data = $this->_ORA->FetchArray($cursor)){
				$this->_TEMPLATE->assign('PARA',$data);
				if($grupo_aux != $data['GRUPO']){
					$this->_TEMPLATE->parse('main.paso1.option_para.grupo');
					$this->_TEMPLATE->parse('main.paso1.option_copia.grupo');
					$grupo_aux = $data['GRUPO'];
				}			
				$this->_TEMPLATE->parse('main.paso1.option_para');
				$this->_TEMPLATE->parse('main.paso1.option_copia');
			}
			$this->_TEMPLATE->parse('main.paso1.div_buscarFiscalizado');
			$this->_TEMPLATE->parse('main.paso1');
            
            
       

        }catch (Exception $e){
            $this->util->mailError($e);
        }
    }
    public function fun_getDestinatariosTipo(){
		return $this->_RESOLUCION->getDestinatariosTipo();
    }
    public function fun_eliminarSeleccionadoDestinatario(){				
		return $this->_RESOLUCION->DESTINATARIO_OBJ->eliminarSeleccionadoDestinatario();
	}
    public function fun_editarDestinatario(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->editarDestinatario();
	}
    public function fun_agregarSeleccionadoDestinatario(){
		return $this->_RESOLUCION->agregarSeleccionadoDestinatario();
	}
    public function fun_agregarFiscalizadoParaOtro(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->agregarFiscalizadoParaOtro();
	}
	public function fun_mostrarSeilUsuario(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->mostrarSeilUsuario();
	}
    //obs: ver modal con los expedientes asociados
    public function fun_verExpediente($param){
        $padre = $param['padre'];
        return $this->_RESOLUCION->verExpediente($padre);
    }
    public function fun_agregarOtro(){
		return $this->_RESOLUCION->DESTINATARIO_OBJ->agregarOtro();
	}
    public function fun_dropSubirAdjunto(){
		return $this->_RESOLUCION->ADJUNTO_OBJ->dropSubirAdjunto();
	}
    public function click_eliminarAdjunto(){
		return $this->_RESOLUCION->ADJUNTO_OBJ->eliminarAdjunto();
	}
    public function cargarExpedientes($padre){
			
        try{
            //Acá me falta estebecer seguridad para saber que los archivos del expediente padre tengo acceso
            $bind = array(':padre' => $padre);
            $cursor = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getExpediente','function',$bind);
            
            $arr_exp = array();
            $num = 0;
            /*if($this->SOLO_VER === FALSE){
                $this->_TEMPLATE->parse($this->PARSER_ANTERIOR.'.subir1');
            }*/
            while($row = $this->_ORA->FetchArray($cursor)){
                //print_r($row);
                $array = array();
                $bind_v = array(':id' => $row['ID_SISTEMA']);
                
                $cursor_variable = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getVariablesFuncion','function',$bind_v);
                while($row_var = $this->_ORA->FetchArray($cursor_variable)){
                    $array[] = $row[$row_var['WFA_VARIABLE']];		
                }				
            
                $obj = $this->ejecutarFuncionXml($row['WFA_PACKAGE'],$row['WFA_FUNCION_XML'],$array );	
                
                //print_r($obj);
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
                $exp = array('BGCOLOR'=>$color,
                    'WFA_ID_REFERENCIAL'=> $row['WFA_ID_REFERENCIAL'],
                    'WFA_TIPO'=>$row['WFA_TIPO'],
                    'ID_SISTEMA'=>$row['ID_SISTEMA'],
                    'ID_DOC'=>$row['WFA_ID_DOCUMENTO'],
                    'FECHA'=>$row['WFA_FECHA'],
                    'DESCRIPCION'=>$nombre,
                    'SGD'=>$sgd,
                    'VER'=>$ver);
                
                $exp['VAL'] = substr(md5(md5($exp['VAL'])),3,5);
            
            
            
                $this->_TEMPLATE->assign('EXP',$exp);
                //if($this->SOLO_VER === FALSE){
                    //if($this->esPdf($row)){
                        $this->_TEMPLATE->parse('main.registro_expediente_fecha.subir2');
                    //}
                //}
                $this->_TEMPLATE->parse('main.registro_expediente_fecha');
                $num++;
            }
        }catch (Exception $e){
            $this->util->mailError($e);
        }
    }
    protected function ejecutarFuncionXml($package,$funcion, $variable){
        
        try{

            $cant = count($variable);
            
            $bindPkg = array();
            foreach($variable as $key => $var){				
                $bindPkg[":var$key"] = $var;
            }
            
            if(strtolower($package) == 'wfa_doctos_pkg'){
                $package = 'wfa_doctos_doc2_pkg';
            }
            $xml = $this->_ORA->ejecutaFunc($package.".".$funcion,$bindPkg);

            
            try{
                $xml2=$this->htmlentities_entities($xml);
                //$obj = new SimpleXMLElement(utf8_encode($xml2));
                //echo  "<!--".$xml2."-->";
                $xml2 = str_replace('==&sec','==&amp;sec',$xml2);
                //echo  "<!--".$xml2."-->";
                $obj = new SimpleXMLElement($xml2);
            }catch(Exception $e){	
                $obj = NULL;
            }
            return $obj;

        }catch (Exception $e){
            $this->util->mailError($e);
        }
    }
    public function htmlentities_entities($xml) {
        
        try{
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
        }catch (Exception $e){
            $this->util->mailError($e);
        }
    } 
    public function get_html_translation_table_CP1252($type) {
        try{

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
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }
    } 
    /* obs: funcion que muestra la respuesta al guardar certificado */
    public function fun_respuesta($param){

        try{

           $wf = $this->_SESION->getVariable("MI_DOC_ID"); //wf
           $json = array();
           $MENSAJES = array();
           $CAMBIA = array();	
           $OPEN = array();			
           
           $respuesta = $param['respuesta'];   
            if($respuesta == 'NOK'){
                $errores = $param['errores'];
                $mensaje_respuesta = 'El certificado tiene '.$errores.' error(es).';                
                $botonera_respuesta ='';
            }else if($respuesta == 'OK'){
                $mensaje_respuesta = 'El certificado se guardó correctamente generando caso WF: '.$wf; 
                $botonera_respuesta = '<div class="secBotonera">
                                        <a class="alink" href="javascript:void(0)" id="btnFormCerrar" onclick="accionBtnFormCerrar();">Cerrar</a>
                                    </div>';               
            }else{
                $mensaje_respuesta = 'Esta acción no existe.';
                $botonera_respuesta = '';
            }
            
			$json['RESULTADO'] = 'OK';			
			$MENSAJES[] = $mensaje_respuesta;
           
            $this->_TEMPLATE->assign('botonera_respuesta',$botonera_respuesta);            
            $this->_TEMPLATE->assign('mensaje_respuesta',$mensaje_respuesta);
			$this->_TEMPLATE->parse('main.div_respuesta');
			
            
            $CAMBIA['#div_respuesta'] = $this->_TEMPLATE->text('main.div_respuesta');
			$OPEN['#div_respuesta'] = 'open';
			$json['MENSAJES'] =  $MENSAJES;
			$json['CAMBIA'] = $CAMBIA;
			$json['OPEN'] = $OPEN;
			return json_encode($json);	
           
        }catch (Exception $e){
            $this->util->mailError($e);
        }
            
    }




//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
// >>>>>>>>>>>>>>>>>>>>>>> Funciones usadas para crear nuevo certificado <<<<<<<<<<<<<<<<<<<<<<<<<
//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    
    //ml ::: agrega certificado desde el formulario CREAR
    public function fun_agregar_certificado(){

        try{  

            //validamos que venga archivo adjunto
            if(isset($_FILES['file']['tmp_name']) and $_POST['p_usarPlantillaSINO'] == 'NO'){
                //var_dump("el pdf debe ser agregado");
                $blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob->WriteTemporary(file_get_contents($_FILES['file']['tmp_name']),OCI_TEMP_BLOB);
                $RES_REFERENCIA= null;
                $agregarPDF = "OK"; //para saber si debemos agregar el PDF adjunto
            }else{
                //var_dump("el pdf es null");
                $blob=null;
                $RES_REFERENCIA = $this->_ORA->NewDescriptor(); 
                $RES_REFERENCIA->WriteTemporary($_POST['p_cuerpo'],OCI_TEMP_CLOB);
                $agregarPDF= "NOK";
            }

            //DESTINATARIOS
            $arrayMiTipo                = explode("_,", substr($_POST["p_arrayMiTipo"], 0, -1));
            $arrayDestinatario          = explode("_,", substr($_POST["p_arrayDestinatario"], 0, -1)); 
            $arrayCargoDestinatario     = explode("_,", substr($_POST["p_arrayCargoDestinatario"], 0, -1)); 
            $arrayDireccion             = explode("_,", substr($_POST["p_arrayDireccion"], 0, -1)); 
            $arrayCorreo                = explode("_,", substr($_POST["p_arrayCorreo"], 0, -1)); 
            $arrayMiNombreDes           = explode("_,", substr($_POST["p_arrayMiNombreDes"], 0, -1)); 
           
            $arrayMiMedioEnvio          = explode("_,", substr($_POST["p_arrayMiMedioEnvio"], 0, -1)); 

            //echo "<pre>"; var_dump($arrayMiNombreDes); echo "</pre>";exit();


            $dataDestinatario = array (
                'destinatario' => $arrayDestinatario ,
                'cargo' => $arrayCargoDestinatario ,
                'direccion' => $arrayDireccion,
                'correo' => $arrayCorreo,
                'tipo' => $arrayMiTipo,
                'con_copia' => 'NO',
                'nombre' =>  $arrayMiNombreDes,
                'medio_envio' => $arrayMiMedioEnvio 
            );


            //DESTINATARIOS COPIA
            $arrayMiTipoCopia = explode("_,", substr($_POST["p_arrayMiTipoCopia"], 0, -1)); 
            $arrayCopia= explode("_,", substr($_POST["p_arrayCopia"], 0, -1)); 
            $arrayCargoCopia= explode("_,", substr($_POST["p_arrayCargoCopia"], 0, -1)); 
            $arrayCopiaDireccion= explode("_,", substr($_POST["p_arrayCopiaDireccion"], 0, -1)); 
            $arrayCopiaCorreo= explode("_,", substr($_POST["p_arrayCopiaCorreo"], 0, -1)); 
            $arrayMiNombreDesCopia = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1)); 
            $arrayMiMedioEnvioCopia = explode("_,", substr($_POST["p_arrayMiMedioEnvioCopia"], 0, -1)); 


            $dataCopia = array (
                'destinatario' => $arrayCopia ,
                'cargo' => $arrayCargoCopia ,
                'direccion' => $arrayCopiaDireccion,
                'correo' => $arrayCopiaCorreo,
                'tipo' => $arrayMiTipoCopia,
                'con_copia' => 'SI',
                'nombre' => $arrayMiNombreDesCopia,
                'medio_envio' => $arrayMiMedioEnvioCopia
            );




            $p_medio_envio = 'SEIL';
            //$p_id =$this->_ORA->ejecutaFunc("utl_frm_html.seq_get",array(':caso_seq'=>'CASO_SEQ'));
            $p_id =$_POST['p_miccertificado'];
    
            //parametros adjuntos
            $gde_documento_doc_version = 0; //sera 0 en caso que es 1era vez 
            $adj_usuario =$this->_SESION->USUARIO; 
            $adjuntos = $this->_SESION->getVariable("RSO_ADJUNTO");
            
            
            //$p_cuerpo = "'".$_POST['p_cuerpo']."'";
            
            
            $p_tipo_envio= $_POST['p_tipo_envio'];
            $p_doc_datos_sensibles=$_POST['p_datosensibleSINO'];
            $p_doc_usa_plantilla=$_POST['p_usarPlantillaSINO'];
            $p_gde_pri_id=$_POST['p_privacidadTipo'];
            $p_doc_redactor = $this->_SESION->USUARIO;
            $gde_tipdoc_id = 'certificado';
            $p_gde_estdoc_id='redac';//1era vez "redaccion" despues "visacion"    
            //$p_doc_enviado_a='enviado a de prueba'; //no se guarda con esta accion, debe ser en la accion envia a alguien
            $p_doc_caso_padre=$_POST['p_padre'];
            $p_doc_version = 0; //sera 0 en caso que es 1era vez
            $p_doc_genera_version='SI'; //queda en SI porque es la primera version
            $p_doc_ultima_version=0;
    
    
            $p_gde_dis_secuencia=1; // debe revisar claudio     

           
            $dataCertificado = array (
                'id' => $p_id,
                'version' => $p_doc_version ,
                'datos_sensibles' => $p_doc_datos_sensibles,
                'usa_plantilla' => $p_doc_usa_plantilla,
                'tipo_doc' => $gde_tipdoc_id,
                'secuencia' => $p_gde_dis_secuencia,
                'estado_doc' => $p_gde_estdoc_id,
                'privacidad' => $p_gde_pri_id,
                'genera_version' => $p_doc_genera_version,
                'caso_padre' => $p_doc_caso_padre,
                'redactor' => $p_doc_redactor,
                'ultima_version' => $p_doc_ultima_version,
                'cuerpo' => $RES_REFERENCIA,
                'enviado_a' => null,
                'tipo_envio' => $p_tipo_envio
            );

            //$this->fun_agregar_nuevo_certificado($dataCertificado);
            $this->_CERTIFICADO->fun_agregar_nuevo_certificado($dataCertificado);
       

            /*
            ml: Agregamos el  PDF .blob en caso que venga adjunto
            observacion: se puede optimizar ya que no me deja enviar data null en el insert del documento por eso 
            se agregó por separado */
            if($agregarPDF == 'OK'){
                $bind =  array(":p_id"=> $p_id,":p_doc_pdf" => $blob);
                $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_ACTUALIZAR_PDF",$bind);
                $this->_ORA->Commit();
            }



            //||||||||||||||||||||||||||||||||AGREGAR DESTINATARIO y COPIA si existe|||||||||||||||||||||||||||||||||||||||||||
            //se agrega destinatario siempre y cuando exista destinatario por agregar 
            if($_POST["p_arrayDestinatario"] != ""){
                //llamar la funcion agregar destinatario
                $this->_CERTIFICADO->fun_agregar_destinatario($arrayDestinatario,$arrayCargoDestinatario,$p_id,$p_doc_version,$arrayDireccion,$arrayCorreo,$arrayMiMedioEnvio,$arrayMiTipo,$arrayMiNombreDes);
                
                if($_POST["p_arrayCopia"] != ""){
                //llamar la funcion agregar copia
                $this->_CERTIFICADO->fun_agregar_copia($arrayCopia,$arrayCargoCopia,$p_id,$p_doc_version,$arrayCopiaDireccion,$arrayCopiaCorreo,$arrayMiMedioEnvioCopia,$arrayMiTipoCopia,$arrayMiNombreDesCopia);
                }
            } 
            //||||||||||

  
            $bindTipo =  array(
                ":p_tipo_doc_id" => $gde_tipdoc_id 
            );
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_TIPO_DOCUMENTO_PKG.FUN_TIPO_DOCUMENTO_GET",'function', $bindTipo);
            if ($cursor) {
                while($tipo = $this->_ORA->FetchArray($cursor)){ 
                    
                    $tipo['TIPDOC_ID']=$tipo['TIPDOC_ID'];
                    $tipo['TIPDOC_GRABA_SDG']=$tipo['TIPDOC_GRABA_SDG'];
                    $tipo['TIPDOC_USA_PLANTILLA']=$tipo['TIPDOC_USA_PLANTILLA'];
                    $tipo['TIPDOC_SUBE_PDF']=$tipo['TIPDOC_SUBE_PDF'];
                    $tipo['TIPDOC_TIPO_DOCTO_SGD']=$tipo['TIPDOC_TIPO_DOCTO_SGD'];
                    $tipo['TIPDOC_SELECCIONA_PRIV']=$tipo['TIPDOC_SELECCIONA_PRIV'];
                    $tipo['TIPDOC_TIENE_ADJ']=$tipo['TIPDOC_TIENE_ADJ'];
                    $tipo['TIPDOC_TIENE_DEST']=$tipo['TIPDOC_TIENE_DEST'];
                    $tipo['TIPDOC_CIERRA_CASO']=$tipo['TIPDOC_CIERRA_CASO'];
                    $tipo['TIPDOC_AVANZA_ROL']=$tipo['TIPDOC_AVANZA_ROL'];
                    $tipo['TIPDOC_CIERRA_PADRE']=$tipo['TIPDOC_CIERRA_PADRE'];
                    $tipo['TIPDOC_TIENE_NUMERACION']=$tipo['TIPDOC_TIENE_NUMERACION'];
                    $tipo['TIPDOC_TIENE_FECHA']=$tipo['TIPDOC_TIENE_FECHA'];
                    $tipo['TIPDOC_FUNCION_NUMERACION']=$tipo['TIPDOC_FUNCION_NUMERACION'];
                    $tipo['TIPDOC_TIENE_FECHA']=$tipo['TIPDOC_TIENE_FECHA'];
                    $tipo['TPDOC_TIENE_VALIDADOR']=$tipo['TPDOC_TIENE_VALIDADOR'];
                    $tipo['TIPDOC_POSICION_X_NUM']=$tipo['TIPDOC_POSICION_X_NUM'];
                    $tipo['TIPDOC_POSICION_Y_NUM']=$tipo['TIPDOC_POSICION_Y_NUM'];
                    $tipo['TIPDOC_TITULO']=$tipo['TIPDOC_TITULO'];
                    $tipo['TIPDOC_LABEL_NUMERO']=$tipo['TIPDOC_LABEL_NUMERO'];
                    $tipo['TIPDOC_NUM_PROC']=$tipo['TIPDOC_NUM_PROC'];


                $res_tipo[]=$tipo;

                }			
                $this->_ORA->FreeStatement($cursor);
            }       

      

            $TIPO_DOCUMENTO = $res_tipo[0]['TIPDOC_TITULO']; //'CERTIFICADO'; //se debe obtener de la tabla GDE_TIPO_DOCUMENTO >> columna TIPDOC_NUM_PROC filtrando por el tipo de documento (TIPDOC_ID)
            $NUMERO_PROCESO = $res_tipo[0]['TIPDOC_NUM_PROC']; //'64'; //se debe obtener de la tabla GDE_TIPO_DOCUMENTO >> columna TIPDOC_TITULO filtrando por el tipo de documento (TIPDOC_ID)

      

            //|||||||||||||||||||||||| CREAMOS EL PROCESO ||||||||||||||||||||||||||||    
            //$this->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);
            $this->_CERTIFICADO->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);
         
            //ml: Aqui agreamos los expedientes adjuntos
            //mlp: falta validar si ya existen los expedientes que se van a guardar
            if($adjuntos!== false){
                foreach($adjuntos as $adj){
                    $id_adjunto = mt_rand(); //generamos un id aleatorio (validar que sea asi)
                    $bindAdjunto =  array(
                        ":p_id" => $id_adjunto, 
                        ":p_adj_usuario"=> $adj_usuario ,
                        ":p_adj_archivo_exp" => $adj['ID'],    
                        ":p_gde_docu_doc_id"=>$p_id,
                        ":p_gde_docu_doc_version" => $gde_documento_doc_version
                    );
                    
                    //$this->_ORA->ejecutaProc("GDE.GDE_TIPO_DOCUMENTO_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
                    $this->_ORA->ejecutaProc("GDE.GDE_ADJUNTOS_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
                    $this->_ORA->Commit();
                }
            }

            return "OK";
        
        }catch (Exception $e){
            $this->util->mailError($e);
       } 
            
    }
    //ml[OK] :: validamos formato del archivo adjunto en el cuerpo
    public function fun_validar_archivo(){

        try{  
            $file = $_POST['p_file'];
            if (strpos(strtolower($file), '.pdf') !== FALSE ){
            
                //var_dump("el nombre es: ".$ARCHIVO_NAME);
                // var_dump(strpos(strtolower($file), '.pdf'));
                // var_dump("el archivo es formato pdf");      
                return "OK";
            }else{
                var_dump("el archivo es otro formato");
                $json['ALERT'][] = 'Sólo se pueden adjuntar archivos de tipo PDF';

                return "NOK";
            }
        }catch (Exception $e){
            $this->util->mailError($e);
       }
          
    }
    //ml[OK] :: cargamos el cuerpo de la plantilla seleccionada
    public function fun_cargar_cuerpo_plantilla(){
        
        try{  

            $p_plantilla = $_POST['p_plantilla'];
            //$plantilla = $this->fun_obtener_plantilla_get($_POST['p_plantilla']);
            $plantilla = $this->_CERTIFICADO->fun_obtener_plantilla_get($p_plantilla);
            //$this->fun_chequea_cuerpo($plantilla[0]['PLA_CUERPO']);     
            //$this->_TEMPLATE->assign('r_cuerpo',$plantilla[0]['PLA_CUERPO']);
            //$this->_TEMPLATE->parse('main.cuerpo_certificado');

            //var_dump($plantilla[0]['PLA_CUERPO']);    
            return  $plantilla[0]['PLA_CUERPO'];

        }catch (Exception $e){
            $this->util->mailError($e);
       }
    }
    
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||| EBVIAR VB |||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    
    //ml[OK] :: mostramos el modal de enviar a vb
    public function fun_enviar_vb(){
        
        $json = array();
        $MENSAJES = array();
        $CAMBIA = array();	
        $OPEN = array();			
        
        $unidad = ($unidad == null) ? $this->_SESION->UNIDAD : $unidad;
        $cursorPara = $this->_ORA->retornaCursor("WFA.WFA_USR.getNombresUsrsUnidad","function",array(":unidad" => $unidad));
        $dataPara = $this->_ORA->FetchAll($cursorPara);        
        
        $cursorAllUsuarios = $this->_ORA->retornaCursor("WFA.WFA_USR.getAllUsuarios","function",array(":p_caso_id" => null,":p_origen" => null ));
        $dataCopia = $this->_ORA->FetchAll($cursorAllUsuarios);    
        //$dataTodos = $this->_ORA->FetchAll($cursorAllUsuarios);        

        $listar_para = '';
        foreach($dataPara as $miPara){
            $listar_para.= '<option value="'.$miPara['EP_USUARIO'].'">'.$miPara['EP_NOMBRES'].'</option>';
        }
        $listar_copia = '';
        foreach($dataCopia as $miCopia){
            $listar_copia.= '<option value="'.$miCopia['EP_USUARIO'].'">'.$miCopia['EP_NOMBRES'].' '.$miCopia['EP_APE_PAT'].' '.$miCopia['EP_APE_MAT'].'</option>';
        }
        $listar_todos = '';
        foreach($dataCopia as $todos){
            $listar_todos.= '<option value="'.$todos['EP_USUARIO'].'">'.$todos['EP_NOMBRES'].' '.$todos['EP_APE_PAT'].' '.$todos['EP_APE_MAT'].'</option>';
        }

        $cursorDivision = $this->_ORA->retornaCursor("wfa.WFA_USR.getUnidades","function");
        $dataDivision = $this->_ORA->FetchAll($cursorDivision);
        $listar_division = '';
        foreach($dataDivision as $miDivision){
            $listar_division.= '<option value="'.$miDivision['EP_COD_DEPTO'].'">'.$miDivision['EP_DEPDESC'].'</option>';
        }
        
        $mensaje_respuesta = 'Esta acción no existe.';
       
        $json['RESULTADO'] = 'OK';			
        $MENSAJES[] = $mensaje_respuesta;
        
        $this->_TEMPLATE->assign('division_OtraUnidadEnviarVB',$listar_division);
        $this->_TEMPLATE->assign('buscar_todos',$listar_todos);
        $this->_TEMPLATE->assign('para_enviarvb',$listar_para);
        $this->_TEMPLATE->assign('copia_enviarvb',$listar_copia);
        $this->_TEMPLATE->assign('cuerpo_enviarvb',$mensaje_respuesta);
        $this->_TEMPLATE->parse('main.div_enviarvb');
         
         
        $CAMBIA['#div_enviarvb'] = $this->_TEMPLATE->text('main.div_enviarvb');
        $OPEN['#div_enviarvb'] = 'open';
        $json['MENSAJES'] =  $MENSAJES;
        $json['CAMBIA'] = $CAMBIA;
        $json['OPEN'] = $OPEN;
        return json_encode($json);		

    }
    //ml :: correo de notificación 
    public function fun_enviar_correo($correo_para,$correo_copia,$NUMERO_CASO,$comentario,$usuario_desde){


        $correo = new Correo();
        $correo->ORA = $this->_ORA;
        $correo->APLIC = 'PUGDE';
        $correo->FIRMADO = false;
        $correo->DESDE_NOMBRE = 'Documentos Electrónicos';
        $correo->ASUNTO = 'Derivación de Caso: '.$NUMERO_CASO;
        $correo->TEXTO =  'Estimado (a) Funcionario (a):
        Se ha Asignado el caso '.$NUMERO_CASO.' con siguiente comentario:
        '.$comentario.'
        Atentamente, '.$usuario_desde.'
        ';
        
        $correo->setPara($correo_para);
        $correo->setCopia($correo_copia);
        $correo->enviar();

    } 
    //ml ::: enviamos a VB desde pestaña MI UNIDAD
    public function fun_agregar_enviar_vb(){
    
       try{

                //validamos que venga archivo adjunto en el cuerpo
                if(isset($_FILES['file']['tmp_name']) and $_POST['p_usarPlantillaSINO'] == 'NO'){
                    //var_dump("el pdf debe ser agregado");
                    $blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                    $blob->WriteTemporary(file_get_contents($_FILES['file']['tmp_name']),OCI_TEMP_BLOB);
                    $RES_REFERENCIA= null;
                    $agregarPDF = "OK"; //para saber si debemos agregar el PDF adjunto
                }else{
                    //var_dump("el pdf es null");
                    $blob=null;
                    $RES_REFERENCIA = $this->_ORA->NewDescriptor(); 
                    $RES_REFERENCIA->WriteTemporary($_POST['p_cuerpo'],OCI_TEMP_CLOB);
                    $agregarPDF= "NOK";
                }




                $arrayMiTipo                = explode("_,", substr($_POST["p_arrayMiTipo"], 0, -1));
                $arrayMiTipoCopia           = explode("_,", substr($_POST["p_arrayMiTipoCopia"], 0, -1)); 
                $arrayCopia                 = explode("_,", substr($_POST["p_arrayCopia"], 0, -1)); 
                $arrayDestinatario          = explode("_,", substr($_POST["p_arrayDestinatario"], 0, -1)); 
                $arrayCargoCopia            = explode("_,", substr($_POST["p_arrayCargoCopia"], 0, -1)); 
                $arrayCargoDestinatario     = explode("_,", substr($_POST["p_arrayCargoDestinatario"], 0, -1)); 
                $arrayCopiaDireccion        = explode("_,", substr($_POST["p_arrayCopiaDireccion"], 0, -1)); 
                $arrayCopiaCorreo           = explode("_,", substr($_POST["p_arrayCopiaCorreo"], 0, -1)); 
                $arrayDireccion             = explode("_,", substr($_POST["p_arrayDireccion"], 0, -1)); 
                $arrayCorreo                = explode("_,", substr($_POST["p_arrayCorreo"], 0, -1)); 
                $arrayMiNombreDes           = explode("_,", substr($_POST["p_arrayMiNombreDes"], 0, -1)); 
                $arrayMiNombreDesCopia      = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1));

                $arrayMiMedioEnvio          = explode("_,", substr($_POST["p_arrayMiMedioEnvio"], 0, -1)); 
                $arrayMiMedioEnvioCopia     = explode("_,", substr($_POST["p_arrayMiMedioEnvioCopia"], 0, -1)); 


                $p_medio_envio              = 'SEIL';
                //$p_id =$this->_ORA->ejecutaFunc("utl_frm_html.seq_get",array(':caso_seq'=>'CASO_SEQ'));
                $p_id                       = $_POST['p_miccertificado'];
                //parametros adjuntos
                $gde_documento_doc_version  = 0; //sera 0 en caso que es 1era vez 
                $adj_usuario                = $this->_SESION->USUARIO; 
                $adjuntos                   = $this->_SESION->getVariable("RSO_ADJUNTO");
                
                
                $p_tipo_envio           = $_POST['p_tipo_envio'];
                $p_doc_datos_sensibles  = $_POST['p_datosensibleSINO'];
                $p_doc_usa_plantilla    = $_POST['p_usarPlantillaSINO'];
                $p_gde_pri_id           = $_POST['p_privacidadTipo'];
                $p_doc_redactor         = $this->_SESION->USUARIO;
                $gde_tipdoc_id          = 'certificado';
                $p_gde_estdoc_id        = 'redac';//1era vez "redaccion" despues "visacion"    
                $p_doc_enviado_a        = $_POST['p_paraVB']; 
                $p_doc_caso_padre       = $_POST['p_padre'];
                $p_doc_version          = 0; //sera 0 en caso que es 1era vez
                $p_doc_genera_version   = 'SI';
                $p_doc_ultima_version   = 0;
                $p_gde_dis_secuencia    = 1; // debe revisar claudio     

                /*    
                $bind =  array(":p_id"=> $p_id,
                ":p_doc_version"=> $p_doc_version ,
                ":p_doc_datos_sensibles"=>$p_doc_datos_sensibles,
                ":p_doc_usa_plantilla"=>$p_doc_usa_plantilla,
                ":p_gde_tipdoc_id"=>$gde_tipdoc_id,
                ":p_gde_dis_secuencia"=>$p_gde_dis_secuencia,
                ":p_gde_estdoc_id"=>$p_gde_estdoc_id,
                ":p_gde_pri_id"=>$p_gde_pri_id,
                ":p_doc_genera_version"=> $p_doc_genera_version,
                ":p_doc_caso_padre"=> $p_doc_caso_padre,
                ":p_doc_redactor"=>$p_doc_redactor,
                ":p_doc_ultima_version"=>$p_doc_ultima_version,
                ":p_doc_cuerpo"=> $RES_REFERENCIA,
                ":p_doc_enviado_a"=>$p_doc_enviado_a,
                ":p_tipenv_id"=>$p_tipo_envio
                //,":p_doc_pdf" => null

                );
                $this->_ORA->ejecutaProc("GDE.GDE_TIPO_DOCUMENTO_PKG.PRC_AGREGAR_CERTIFICADO",$bind);
                $this->_ORA->Commit();
                */

                $dataCertificado = array (
                    'id' => $p_id,
                    'version' => $p_doc_version ,
                    'datos_sensibles' => $p_doc_datos_sensibles,
                    'usa_plantilla' => $p_doc_usa_plantilla,
                    'tipo_doc' => $gde_tipdoc_id,
                    'secuencia' => $p_gde_dis_secuencia,
                    'estado_doc' => $p_gde_estdoc_id,
                    'privacidad' => $p_gde_pri_id,
                    'genera_version' => $p_doc_genera_version,
                    'caso_padre' => $p_doc_caso_padre,
                    'redactor' => $p_doc_redactor,
                    'ultima_version' => $p_doc_ultima_version,
                    'cuerpo' => $RES_REFERENCIA,
                    'enviado_a' => $p_doc_enviado_a,
                    'tipo_envio' => $p_tipo_envio
                );
    
                //$this->fun_agregar_nuevo_certificado($dataCertificado);
                $this->_CERTIFICADO->fun_agregar_nuevo_certificado($dataCertificado);
               

                /*
                ml: Agregamos el  PDF .blob en caso que venga adjunto
                observacion: se puede optimizar ya que no me deja enviar data null en el insert del documento por eso 
                se agregó por separado */
                if($agregarPDF == 'OK'){
                    $bind =  array(":p_id"=> $p_id,":p_doc_pdf" => $blob);
                    $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_ACTUALIZAR_PDF",$bind);
                    $this->_ORA->Commit();
                }


              

            
                //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
                //|||||||||||||||||||||||||||||||||   AGREGAR VISACION ENVIO VB - MI UNIDAD ||||||||||||||||||||||||||||||||||||
                //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


                
                $p_comentario   = $_POST['p_comentarioVB']; //comentario visacion enviar vb
                $p_paraVB       = $_POST['p_paraVB'];//usuario para enviar vb
                $p_visacionVB   = $_POST['p_visacionVB'];
                //$p_visacionVB='SI';
                
                //$p_vis_id = mt_rand();//validar como agregar este campo (id tabla visaciones)
                $p_vis_id       = 0;
                $p_vis_id       = $this->fun_agregar_visacion_vb($p_vis_id,$p_id,$p_doc_version,$p_paraVB,$p_comentario,$p_visacionVB);        
            
                //validamos que venga archivo adjunto en envioVB
                if(isset($_FILES['file2']['tmp_name'])){
                    $blob2 = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                    $blob2->WriteTemporary(file_get_contents($_FILES['file2']['tmp_name']),OCI_TEMP_BLOB);
                    $bindVisacion =  array(":p_vis_id"=> $p_vis_id,":p_vis_adjunto" => $blob2);
                    $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_ACTUALIZAR_ADJUNTO",$bindVisacion);
                    $this->_ORA->Commit();
                }


             

                //||||||||||||||||||||||||||||||||AGREGAR DESTINATARIO y COPIA si existe|||||||||||||||||||||||||||||||||||||||||||||||||||
                //se agrega destinatario siempre y cuando exista destinatario por agregar 
                if($_POST["p_arrayDestinatario"] != ""){
                    //llamar la funcion agregar destinatario
                    $this->_CERTIFICADO->fun_agregar_destinatario($arrayDestinatario,$arrayCargoDestinatario,$p_id,$p_doc_version,$arrayDireccion,$arrayCorreo,$arrayMiMedioEnvio,$arrayMiTipo,$arrayMiNombreDes);
                    if($_POST["p_arrayCopia"] != ""){
                    //llamar la funcion agregar copia
                    $this->_CERTIFICADO->fun_agregar_copia($arrayCopia,$arrayCargoCopia,$p_id,$p_doc_version,$arrayCopiaDireccion,$arrayCopiaCorreo,$arrayMiMedioEnvioCopia,$arrayMiTipoCopia,$arrayMiNombreDesCopia);
                    }
                } 
                //||||||||||


               


                $bindTipo =  array(
                    ":p_tipo_doc_id" => $gde_tipdoc_id 
                );
                $cursor = $this->_ORA->retornaCursor("GDE.GDE_TIPO_DOCUMENTO_PKG.FUN_TIPO_DOCUMENTO_GET",'function', $bindTipo);
                if ($cursor) {
                    while($tipo = $this->_ORA->FetchArray($cursor)){ 
                        
                        $tipo['TIPDOC_ID']=$tipo['TIPDOC_ID'];
                        $tipo['TIPDOC_GRABA_SDG']=$tipo['TIPDOC_GRABA_SDG'];
                        $tipo['TIPDOC_USA_PLANTILLA']=$tipo['TIPDOC_USA_PLANTILLA'];
                        $tipo['TIPDOC_SUBE_PDF']=$tipo['TIPDOC_SUBE_PDF'];
                        $tipo['TIPDOC_TIPO_DOCTO_SGD']=$tipo['TIPDOC_TIPO_DOCTO_SGD'];
                        $tipo['TIPDOC_SELECCIONA_PRIV']=$tipo['TIPDOC_SELECCIONA_PRIV'];
                        $tipo['TIPDOC_TIENE_ADJ']=$tipo['TIPDOC_TIENE_ADJ'];
                        $tipo['TIPDOC_TIENE_DEST']=$tipo['TIPDOC_TIENE_DEST'];
                        $tipo['TIPDOC_CIERRA_CASO']=$tipo['TIPDOC_CIERRA_CASO'];
                        $tipo['TIPDOC_AVANZA_ROL']=$tipo['TIPDOC_AVANZA_ROL'];
                        $tipo['TIPDOC_CIERRA_PADRE']=$tipo['TIPDOC_CIERRA_PADRE'];
                        $tipo['TIPDOC_TIENE_NUMERACION']=$tipo['TIPDOC_TIENE_NUMERACION'];
                        $tipo['TIPDOC_TIENE_FECHA']=$tipo['TIPDOC_TIENE_FECHA'];
                        $tipo['TIPDOC_FUNCION_NUMERACION']=$tipo['TIPDOC_FUNCION_NUMERACION'];
                        $tipo['TIPDOC_TIENE_FECHA']=$tipo['TIPDOC_TIENE_FECHA'];
                        $tipo['TPDOC_TIENE_VALIDADOR']=$tipo['TPDOC_TIENE_VALIDADOR'];
                        $tipo['TIPDOC_POSICION_X_NUM']=$tipo['TIPDOC_POSICION_X_NUM'];
                        $tipo['TIPDOC_POSICION_Y_NUM']=$tipo['TIPDOC_POSICION_Y_NUM'];
                        $tipo['TIPDOC_TITULO']=$tipo['TIPDOC_TITULO'];
                        $tipo['TIPDOC_LABEL_NUMERO']=$tipo['TIPDOC_LABEL_NUMERO'];
                        $tipo['TIPDOC_NUM_PROC']=$tipo['TIPDOC_NUM_PROC'];


                    $res_tipo[]=$tipo;

                    }			
                    $this->_ORA->FreeStatement($cursor);
                }       

        

                $TIPO_DOCUMENTO = $res_tipo[0]['TIPDOC_TITULO']; //'CERTIFICADO'; //se debe obtener de la tabla GDE_TIPO_DOCUMENTO >> columna TIPDOC_NUM_PROC filtrando por el tipo de documento (TIPDOC_ID)
                $NUMERO_PROCESO = $res_tipo[0]['TIPDOC_NUM_PROC']; //'64'; //se debe obtener de la tabla GDE_TIPO_DOCUMENTO >> columna TIPDOC_TITULO filtrando por el tipo de documento (TIPDOC_ID)

        

                //|||||||||||||||||||||||| CREAMOS EL PROCESO ||||||||||||||||||||||||||||    
                //$this->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);
                $this->_CERTIFICADO->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);
            
                //ml: Aqui agreamos los expedientes adjuntos
                //mlp: falta validar si ya existen los expedientes que se van a guardar 
                if($adjuntos !== false){
                    foreach($adjuntos as $adj){
                        $id_adjunto = mt_rand(); //generamos un id aleatorio (validar que sea asi)
                        $bindAdjunto =  array(
                            ":p_id" => $id_adjunto, 
                            ":p_adj_usuario"=> $adj_usuario ,
                            ":p_adj_archivo_exp" => $adj['ID'],    
                            ":p_gde_docu_doc_id"=>$p_id,
                            ":p_gde_docu_doc_version" => $gde_documento_doc_version
                        );
                        
                        //$this->_ORA->ejecutaProc("GDE.GDE_TIPO_DOCUMENTO_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
                        $this->_ORA->ejecutaProc("GDE.GDE_ADJUNTOS_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
                        //$this->_ORA->Commit();//estaba comentado
                    }
                }

                //hasta aqui funciona perfecto 
                //OBSERVACIONES
                /*
                1.- No agerga los nombres en la distribucion , porque no le estan llegando
                2.- El correo no puede buscar valores null , error: No existe correo para null
                3.- Solo agrega copia en caso que exista destinatario (modulo destinatario) VALIDAR
                */

                      
                $usuario_desde =$this->_SESION->USUARIO;//usuario desde enviar a vb otra unidad 
                $usuarioPara = $_POST['p_paraVB']; 
                $comentario = $_POST['p_comentarioVB']; 
            

                
                $bindPara = array(':usuario' => $usuarioPara);                            
                $correo_para = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bindPara);  
                $usuarioCopiaEVB = explode(',',$_POST['p_copiaVB']);
                $correo_copia = array();
                if(isset($usuarioCopiaEVB) and $usuarioCopiaEVB[0] != "null"){
                    for ($i=0;$i<count($usuarioCopiaEVB);$i++) { 
                        $bind = array(':usuario' => $usuarioCopiaEVB[$i]);
                        $correo_copia[$i] = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind); 
                    }   
                    $copia_correo = implode(",", $correo_copia); 
                }else{
                    $copia_correo = null;
                }

                //enviamos el correo //esta tirando error email null 
                $this->fun_enviar_correo($correo_para,$copia_correo,$p_id,$comentario,$usuario_desde);     

                //derivamos a la bandeja de WF [DESCOMENTAR]
                $this->setAsignar($usuarioPara, $_POST['p_comentarioVB'],$p_id);
                $this->_ORA->Commit();

                return "OK";
                exit();

            }catch(Exception $e){

                $this->_LOG->error(print_r($e));

            }

    }
    //ml ::: enviamos a VB desde pestaña TODAS
    public function fun_enviarvb_todas(){

      

        try{

            //validamos que venga archivo adjunto en el cuerpo
            if(isset($_FILES['file']['tmp_name']) and $_POST['p_usarPlantillaSINO'] == 'NO'){
                //var_dump("el pdf debe ser agregado");
                $blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob->WriteTemporary(file_get_contents($_FILES['file']['tmp_name']),OCI_TEMP_BLOB);
                $RES_REFERENCIA= null;
                $agregarPDF = "OK"; //para saber si debemos agregar el PDF adjunto
            }else{
                //var_dump("el pdf es null");
                $blob=null;
                $RES_REFERENCIA = $this->_ORA->NewDescriptor(); 
                $RES_REFERENCIA->WriteTemporary($_POST['p_cuerpo'],OCI_TEMP_CLOB);
                $agregarPDF= "NOK";
            }




            $arrayMiTipo            = explode("_,", substr($_POST["p_arrayMiTipo"], 0, -1));
            $arrayMiTipoCopia       = explode("_,", substr($_POST["p_arrayMiTipoCopia"], 0, -1)); 
            $arrayCopia             = explode("_,", substr($_POST["p_arrayCopia"], 0, -1)); 
            $arrayDestinatario      = explode("_,", substr($_POST["p_arrayDestinatario"], 0, -1)); 
            $arrayCargoCopia        = explode("_,", substr($_POST["p_arrayCargoCopia"], 0, -1)); 
            $arrayCargoDestinatario = explode("_,", substr($_POST["p_arrayCargoDestinatario"], 0, -1)); 
            $arrayCopiaDireccion    = explode("_,", substr($_POST["p_arrayCopiaDireccion"], 0, -1)); 
            $arrayCopiaCorreo       = explode("_,", substr($_POST["p_arrayCopiaCorreo"], 0, -1)); 
            $arrayDireccion         = explode("_,", substr($_POST["p_arrayDireccion"], 0, -1)); 
            $arrayCorreo            = explode("_,", substr($_POST["p_arrayCorreo"], 0, -1)); 
            $arrayMiNombreDes       = explode("_,", substr($_POST["p_arrayMiNombreDes"], 0, -1)); 
            $arrayMiNombreDesCopia  = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1)); 
            
            $arrayMiMedioEnvio          = explode("_,", substr($_POST["p_arrayMiMedioEnvio"], 0, -1)); 
            $arrayMiMedioEnvioCopia     = explode("_,", substr($_POST["p_arrayMiMedioEnvioCopia"], 0, -1));


            //$p_id =$this->_ORA->ejecutaFunc("utl_frm_html.seq_get",array(':caso_seq'=>'CASO_SEQ'));
            $p_id                   = $_POST['p_miccertificado'];
            $p_medio_envio          = 'SEIL';
            //parametros adjuntos
            $gde_documento_doc_version = 0; //sera 0 en caso que es 1era vez 
            $adj_usuario            = $this->_SESION->USUARIO; 
            $adjuntos               = $this->_SESION->getVariable("RSO_ADJUNTO");
            
            
            
            $p_tipo_envio= $_POST['p_tipo_envio'];
            $p_doc_datos_sensibles=$_POST['p_datosensibleSINO'];
            $p_doc_usa_plantilla=$_POST['p_usarPlantillaSINO'];
            $p_gde_pri_id=$_POST['p_privacidadTipo'];
            $p_doc_redactor = $this->_SESION->USUARIO;
            $gde_tipdoc_id = 'certificado';
            $p_gde_estdoc_id='redac';//1era vez "redaccion" despues "visacion"    
            $p_doc_enviado_a= $_POST['p_paraVBTodos']; 
            $p_doc_caso_padre=$_POST['p_padre'];
            $p_doc_version = 0; //sera 0 en caso que es 1era vez
            $p_doc_genera_version='SI';
            $p_doc_ultima_version=0;


            $p_gde_dis_secuencia=1; // debe revisar claudio     

            $dataCertificado = array (
                'id' => $p_id,
                'version' => $p_doc_version ,
                'datos_sensibles' => $p_doc_datos_sensibles,
                'usa_plantilla' => $p_doc_usa_plantilla,
                'tipo_doc' => $gde_tipdoc_id,
                'secuencia' => $p_gde_dis_secuencia,
                'estado_doc' => $p_gde_estdoc_id,
                'privacidad' => $p_gde_pri_id,
                'genera_version' => $p_doc_genera_version,
                'caso_padre' => $p_doc_caso_padre,
                'redactor' => $p_doc_redactor,
                'ultima_version' => $p_doc_ultima_version,
                'cuerpo' => $RES_REFERENCIA,
                'enviado_a' => $p_doc_enviado_a,
                'tipo_envio' => $p_tipo_envio
            );

            //$this->fun_agregar_nuevo_certificado($dataCertificado);
            $this->_CERTIFICADO->fun_agregar_nuevo_certificado($dataCertificado);

            
            /*
            ml: Agregamos el  PDF .blob en caso que venga adjunto
            observacion: se puede optimizar ya que no me deja enviar data null en el insert del documento por eso 
            se agregó por separado */
            if($agregarPDF == 'OK'){
                $bind =  array(":p_id"=> $p_id,":p_doc_pdf" => $blob);
                $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_ACTUALIZAR_PDF",$bind);
                $this->_ORA->Commit();
            }



            //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
            //|||||||||||||||||||||||||||||||||   AGREGAR VISACION ENVIO VB - MI UNIDAD ||||||||||||||||||||||||||||||||||||
            //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


                
            $p_comentario= $_POST['p_comentarioVBTodos']; //comentario visacion enviar vb
            $p_paraVB= $_POST['p_paraVBTodos'];//usuario para enviar vb
            $p_visacionVB= $_POST['p_visacionVBTodos'];
            //$p_visacionVB='SI';
            
            //$p_vis_id = mt_rand();//validar como agregar este campo (id tabla visaciones)
            $p_vis_id = 0;
            $p_vis_id = $this->fun_agregar_visacion_vb($p_vis_id,$p_id,$p_doc_version,$p_paraVB,$p_comentario,$p_visacionVB);        
        
            //validamos que venga archivo adjunto en envioVB
            if(isset($_FILES['file4']['tmp_name'])){
                $blob2 = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob2->WriteTemporary(file_get_contents($_FILES['file4']['tmp_name']),OCI_TEMP_BLOB);
                $bindVisacion =  array(":p_vis_id"=> $p_vis_id,":p_vis_adjunto" => $blob2);
                $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_ACTUALIZAR_ADJUNTO",$bindVisacion);
                $this->_ORA->Commit();
            }

            
            //||||||||||||||||||||||||||||||||AGREGAR DESTINATARIO y COPIA si existe|||||||||||||||||||||||||||||||||||||||||||||||||||
                //se agrega destinatario siempre y cuando exista destinatario por agregar 
                if($_POST["p_arrayDestinatario"] != ""){
                    //llamar la funcion agregar destinatario
                    $this->_CERTIFICADO->fun_agregar_destinatario(
                        $arrayDestinatario,
                        $arrayCargoDestinatario,
                        $p_id,
                        $p_doc_version,
                        $arrayDireccion,
                        $arrayCorreo,
                        $arrayMiMedioEnvio,
                        $arrayMiTipo,
                        $arrayMiNombreDes);
                    
                    if($_POST["p_arrayCopia"] != ""){
                    //llamar la funcion agregar copia
                    $this->_CERTIFICADO->fun_agregar_copia(
                        $arrayCopia,
                        $arrayCargoCopia,
                        $p_id,
                        $p_doc_version,
                        $arrayCopiaDireccion,
                        $arrayCopiaCorreo,
                        $arrayMiMedioEnvioCopia,
                        $arrayMiTipoCopia,
                        $arrayMiNombreDesCopia);
                    }
                } 
                //||||||||||


                $bindTipo =  array(
                    ":p_tipo_doc_id" => $gde_tipdoc_id 
                );
                $cursor = $this->_ORA->retornaCursor("GDE.GDE_TIPO_DOCUMENTO_PKG.FUN_TIPO_DOCUMENTO_GET",'function', $bindTipo);
                if ($cursor) {
                    while($tipo = $this->_ORA->FetchArray($cursor)){ 
                        
                        $tipo['TIPDOC_ID']=$tipo['TIPDOC_ID'];
                        $tipo['TIPDOC_GRABA_SDG']=$tipo['TIPDOC_GRABA_SDG'];
                        $tipo['TIPDOC_USA_PLANTILLA']=$tipo['TIPDOC_USA_PLANTILLA'];
                        $tipo['TIPDOC_SUBE_PDF']=$tipo['TIPDOC_SUBE_PDF'];
                        $tipo['TIPDOC_TIPO_DOCTO_SGD']=$tipo['TIPDOC_TIPO_DOCTO_SGD'];
                        $tipo['TIPDOC_SELECCIONA_PRIV']=$tipo['TIPDOC_SELECCIONA_PRIV'];
                        $tipo['TIPDOC_TIENE_ADJ']=$tipo['TIPDOC_TIENE_ADJ'];
                        $tipo['TIPDOC_TIENE_DEST']=$tipo['TIPDOC_TIENE_DEST'];
                        $tipo['TIPDOC_CIERRA_CASO']=$tipo['TIPDOC_CIERRA_CASO'];
                        $tipo['TIPDOC_AVANZA_ROL']=$tipo['TIPDOC_AVANZA_ROL'];
                        $tipo['TIPDOC_CIERRA_PADRE']=$tipo['TIPDOC_CIERRA_PADRE'];
                        $tipo['TIPDOC_TIENE_NUMERACION']=$tipo['TIPDOC_TIENE_NUMERACION'];
                        $tipo['TIPDOC_TIENE_FECHA']=$tipo['TIPDOC_TIENE_FECHA'];
                        $tipo['TIPDOC_FUNCION_NUMERACION']=$tipo['TIPDOC_FUNCION_NUMERACION'];
                        $tipo['TIPDOC_TIENE_FECHA']=$tipo['TIPDOC_TIENE_FECHA'];
                        $tipo['TPDOC_TIENE_VALIDADOR']=$tipo['TPDOC_TIENE_VALIDADOR'];
                        $tipo['TIPDOC_POSICION_X_NUM']=$tipo['TIPDOC_POSICION_X_NUM'];
                        $tipo['TIPDOC_POSICION_Y_NUM']=$tipo['TIPDOC_POSICION_Y_NUM'];
                        $tipo['TIPDOC_TITULO']=$tipo['TIPDOC_TITULO'];
                        $tipo['TIPDOC_LABEL_NUMERO']=$tipo['TIPDOC_LABEL_NUMERO'];
                        $tipo['TIPDOC_NUM_PROC']=$tipo['TIPDOC_NUM_PROC'];


                    $res_tipo[]=$tipo;

                    }			
                    $this->_ORA->FreeStatement($cursor);
                }       

        

                $TIPO_DOCUMENTO = $res_tipo[0]['TIPDOC_TITULO']; //'CERTIFICADO'; //se debe obtener de la tabla GDE_TIPO_DOCUMENTO >> columna TIPDOC_NUM_PROC filtrando por el tipo de documento (TIPDOC_ID)
                $NUMERO_PROCESO = $res_tipo[0]['TIPDOC_NUM_PROC']; //'64'; //se debe obtener de la tabla GDE_TIPO_DOCUMENTO >> columna TIPDOC_TITULO filtrando por el tipo de documento (TIPDOC_ID)

        

                //|||||||||||||||||||||||| CREAMOS EL PROCESO ||||||||||||||||||||||||||||    
                //$this->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);
                $this->_CERTIFICADO->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);
            
                //ml: Aqui agreamos los expedientes adjuntos
                //mlp: falta validar si ya existen los expedientes que se van a guardar 
                if($adjuntos !== false){
                    foreach($adjuntos as $adj){
                        $id_adjunto = mt_rand(); //generamos un id aleatorio (validar que sea asi)
                        $bindAdjunto =  array(
                            ":p_id" => $id_adjunto, 
                            ":p_adj_usuario"=> $adj_usuario ,
                            ":p_adj_archivo_exp" => $adj['ID'],    
                            ":p_gde_docu_doc_id"=>$p_id,
                            ":p_gde_docu_doc_version" => $gde_documento_doc_version
                        );
                        
                        //$this->_ORA->ejecutaProc("GDE.GDE_TIPO_DOCUMENTO_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
                        $this->_ORA->ejecutaProc("GDE.GDE_ADJUNTOS_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
                        //$this->_ORA->Commit();
                    }
                }


                


                      
                $usuario_desde =$this->_SESION->USUARIO;//usuario desde enviar a vb otra unidad 
                $usuarioPara = $_POST['p_paraVBTodos']; 
                $comentario = $_POST['p_comentarioVBTodos']; 
            

                
                $bindPara = array(':usuario' => $usuarioPara);                            
                $correo_para = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bindPara);  
                $usuarioCopiaEVB = explode(',',$_POST['p_copiaVBTodos']);
                $correo_copia = array();
                if(isset($usuarioCopiaEVB) and $usuarioCopiaEVB[0] != "null"){
                    for ($i=0;$i<count($usuarioCopiaEVB);$i++) { 
                        $bind = array(':usuario' => $usuarioCopiaEVB[$i]);
                        $correo_copia[$i] = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind); 
                    }   
                    $copia_correo = implode(",", $correo_copia); 
                }else{
                    $copia_correo = null;
                }

                //enviamos el correo 
                $this->fun_enviar_correo($correo_para,$copia_correo,$p_id,$comentario,$usuario_desde);     

                //derivamos a la bandeja de WF [DESCOMENTAR]
                $this->setAsignar($usuarioPara, $_POST['p_comentarioVBTodos'],$p_id);
                $this->_ORA->Commit();

                return "OK";




        }catch(Exception $e){

            $this->_LOG->error(print_r($e));

        }


    }

    //ml: metodo para agregar bitacoras
    //obs: se puede adaptar para que sea dinamico el comentario , solo se esta usando para el crear
    //habria que pasarle como parametro el comentario, para asi usarlo en mas lugares
    /*public function fun_agregar_bitacora(){

        $comentario = "Se agrega un nuevo certificado";
        $bind = array(':caso'=>$NUMERO_CASO, ':usuario' => $this->_SESION->USUARIO, ':desde' => $this->_SESION->USUARIO, ':msg' => $comentario );
        $this->_ORA->ejecutaFunc("wfa.wf_rso_pkg.fun_bitacora", $bind);
        $this->_LOG->log("Bitacora en el WF: ".$NUMERO_CASO.' con bind '.print_r($bind,true)); 

    }*/

  
    //ml: metodo para derivarlo a otra bandeja de WF
    public function setAsignar($usuario, $comentario,$NUMERO_CASO){
            
        try{

            $comentario = strip_tags($comentario);
            $bind = array(':caso'=>$NUMERO_CASO, ':usuario' => $this->_SESION->USUARIO, ':desde' => $usuario, ':msg' => $comentario );
            $this->_ORA->ejecutaFunc("wfa.wf_rso_pkg.fun_bitacora", $bind);
            $this->_LOG->log("Bitacora en el WF: ".$NUMERO_CASO.' con bind '.print_r($bind,true)); 
            
            $bind = array(':caso'=>$NUMERO_CASO, ':usuario' => $usuario);
            $this->_ORA->ejecutaProc("wfa.wf_rso_pkg.fun_asignar", $bind);
            $this->_LOG->log("Se asigna el WF: ".$NUMERO_CASO.' con bind '.print_r($bind,true));

        }catch(Exception $e){
            $this->_LOG->error(print_r($e));
        }
    }
    //ml: agregamos la visacion del enviar vb
    public function fun_agregar_visacion_vb($p_vis_id,$p_id,$p_doc_version,$p_paraVB,$p_comentarioVB,$p_visacionVB){

        try{     
            $p_usuario =$this->_SESION->USUARIO; 
            //$p_id =$this->_ORA->ejecutaFunc("utl_frm_html.seq_get",array(':caso_seq'=>'CASO_SEQ'));
            //$p_id =1;
            //$p_doc_version = 1; //sera 0 en caso que es 1era vez
            //$p_paraVB= $_POST['p_paraVB'];
            //$p_copiaVB= $_POST['p_copiaVB']; //SE DEBE ENVIAR CUN CORREO    
            //$p_visacionVB= $_POST['p_visacionVB'];
            //$p_visacionVB='SI';
            $RES_REFERENCIA = $this->_ORA->NewDescriptor(); 
            $RES_REFERENCIA->WriteTemporary($p_comentarioVB,OCI_TEMP_CLOB);

            //var_dump($p_paraVB."//".$p_copiaVB."//".$p_visacionVB."//".$p_comentarioVB);
            //var_dump($blob."//".$agregarPDF);
            //exit();

            $bind =  array(":p_vis_id"=> $p_vis_id,
            ":p_doc_id"=> $p_id,
            ":p_doc_version"=> $p_doc_version ,
            ":p_vis_usuario"=> $p_usuario ,
            ":p_usuario_hacia"=> $p_paraVB,
            ":p_vis_vb"=>$p_visacionVB,
            ":p_vis_comentario"=>$RES_REFERENCIA
            );
            
            //$this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.FUN_AGREGAR_VISACION",$bind);
            $result = $this->_ORA->ejecutaFunc("GDE.GDE_VISACIONES_PKG.FUN_AGREGAR_VISACION", $bind);
            $this->_ORA->Commit();
            
            /*
            if($agregarPDF == 'OK'){
                var_dump("le dio ok");
                $bindVisacion =  array(":p_vis_id"=> $p_vis_id,":p_vis_adjunto" => $blob);
                $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_ACTUALIZAR_ADJUNTO",$bindVisacion);
                $this->_ORA->Commit();
            }else{
                var_dump('le dio NOK');
            }    
            */

            //retornamos el id de la visacion creada
            return $result;

        }catch (Exception $e){
            $this->util->mailError($e);
       }
    }
    //ml[OK] ::cargamos el combo de los PARA en el formulario de OTRA UNIDAD    
    public function fun_cargar_para_otra_unidad(){
        
        $json = array();
        $MENSAJES = array();
        $CAMBIA = array();	
        $OPEN = array();	
      
        $unidad=$_POST['unidad'];
        $cursor = $this->_ORA->retornaCursor("WFA.WFA_USR.getNombresUsrsUnidad","function",array(":unidad" => $unidad));
        $data = $this->_ORA->FetchAll($cursor);           


        $cursorAllUsuarios = $this->_ORA->retornaCursor("WFA.WFA_USR.getAllUsuarios","function",array(":p_caso_id" => null,":p_origen" => null ));
        $dataCopia = $this->_ORA->FetchAll($cursorAllUsuarios);    
        

        $listar_para = '';
        foreach($data as $miPara){
            $listar_para.= '<option value="'.$miPara['EP_USUARIO'].'">'.$miPara['EP_NOMBRES'].'</option>';
        }
        

  

        $listar_copia = '';
        foreach($dataCopia as $miCopia){
            $listar_copia.= '<option value="'.$miCopia['EP_USUARIO'].'">'.$miCopia['EP_NOMBRES'].' '.$miCopia['EP_APE_PAT'].' '.$miCopia['EP_APE_MAT'].'</option>';
        }
        
        //$mensaje_respuesta = 'Esta acción no existe.';
         $json['RESULTADO'] = 'OK';	
         $json['LISTADO'] =  $listar_para;	
         $json['LISTADO_COPIA'] =  $listar_copia;
        
        
         //$MENSAJES[] = $mensaje_respuesta;
         //$this->_TEMPLATE->assign('para_OtraUnidadEnviarVB',$listar_para);
         //$this->_TEMPLATE->parse('main.div_enviarvb');
         
         
         //$CAMBIA['#div_enviarvb'] = $this->_TEMPLATE->text('main.div_enviarvb');
         //$OPEN['#div_enviarvb'] = 'open';
         //$json['MENSAJES'] =  $MENSAJES;
         //$json['CAMBIA'] = $CAMBIA;
         //$json['OPEN'] = $OPEN;
         return json_encode($json);		

    }
    //ml ::: enviar a VB para la pestaña OTRA UNIDAD
    public function fun_agregar_otraunidad_evb(){

     
        try{

        
            //validamos que venga archivo adjunto
            if(isset($_FILES['file']['tmp_name']) and $_POST['p_usarPlantillaSINO'] == 'NO'){
                //var_dump("el pdf debe ser agregado");
                $blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob->WriteTemporary(file_get_contents($_FILES['file']['tmp_name']),OCI_TEMP_BLOB);
                $RES_REFERENCIA= null;
                $agregarPDF = "OK"; //para saber si debemos agregar el PDF adjunto
            }else{
                //var_dump("el pdf es null");
                $blob=null;
                $RES_REFERENCIA = $this->_ORA->NewDescriptor(); 
                $RES_REFERENCIA->WriteTemporary($_POST['p_cuerpo'],OCI_TEMP_CLOB);
                $agregarPDF= "NOK";
            }
        
        
            $arrayMiTipo                = explode("_,", substr($_POST["p_arrayMiTipo"], 0, -1));
            $arrayMiTipoCopia           = explode("_,", substr($_POST["p_arrayMiTipoCopia"], 0, -1)); 
            $arrayCopia                 = explode("_,", substr($_POST["p_arrayCopia"], 0, -1)); 
            $arrayDestinatario          = explode("_,", substr($_POST["p_arrayDestinatario"], 0, -1)); 
            $arrayCargoCopia            = explode("_,", substr($_POST["p_arrayCargoCopia"], 0, -1)); 
            $arrayCargoDestinatario     = explode("_,", substr($_POST["p_arrayCargoDestinatario"], 0, -1)); 
            $arrayCopiaDireccion        = explode("_,", substr($_POST["p_arrayCopiaDireccion"], 0, -1)); 
            $arrayCopiaCorreo           = explode("_,", substr($_POST["p_arrayCopiaCorreo"], 0, -1)); 
            $arrayDireccion             = explode("_,", substr($_POST["p_arrayDireccion"], 0, -1)); 
            $arrayCorreo                = explode("_,", substr($_POST["p_arrayCorreo"], 0, -1)); 
            $p_medio_envio              = 'SEIL';
            //$p_id                     = $this->_ORA->ejecutaFunc("utl_frm_html.seq_get",array(':caso_seq'=>'CASO_SEQ'));
            $p_id                       = $_POST['p_miccertificado'];
            $arrayMiNombreDes           = explode("_,", substr($_POST["p_arrayMiNombreDes"], 0, -1));   
            $arrayMiNombreDesCopia      = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1));


            $arrayMiMedioEnvio          = explode("_,", substr($_POST["p_arrayMiMedioEnvio"], 0, -1)); 
            $arrayMiMedioEnvioCopia     = explode("_,", substr($_POST["p_arrayMiMedioEnvioCopia"], 0, -1)); 

            //parametros adjuntos
            $gde_documento_doc_version  = 0; //sera 0 en caso que es 1era vez 
            $adj_usuario                = $this->_SESION->USUARIO; 
            $adjuntos                   = $this->_SESION->getVariable("RSO_ADJUNTO");
            
            //parametros certificado    
            $p_tipo_envio           = $_POST['p_tipo_envio'];
            $p_doc_datos_sensibles  = $_POST['p_datosensibleSINO'];
            $p_doc_usa_plantilla    = $_POST['p_usarPlantillaSINO'];
            $p_gde_pri_id           = $_POST['p_privacidadTipo'];
            $p_doc_redactor         = $this->_SESION->USUARIO;
            $gde_tipdoc_id          = 'certificado';
            $p_gde_estdoc_id        = 'redac';//1era vez "redaccion" despues "visacion"    
            $p_doc_enviado_a        = $_POST['p_otraUnidadParaVB']; 
            $p_doc_caso_padre       = $_POST['p_padre'];
            $p_doc_version          = 0; //sera 0 en caso que es 1era vez
            $p_doc_genera_version   = 'SI';
            $p_doc_ultima_version   = 0;
            $p_gde_dis_secuencia    = 1; // debe revisar claudio //ESTE SE ELIMINO     
    
                
            $dataCertificado = array (
                'id' => $p_id,
                'version' => $p_doc_version ,
                'datos_sensibles' => $p_doc_datos_sensibles,
                'usa_plantilla' => $p_doc_usa_plantilla,
                'tipo_doc' => $gde_tipdoc_id,
                'secuencia' => $p_gde_dis_secuencia,
                'estado_doc' => $p_gde_estdoc_id,
                'privacidad' => $p_gde_pri_id,
                'genera_version' => $p_doc_genera_version,
                'caso_padre' => $p_doc_caso_padre,
                'redactor' => $p_doc_redactor,
                'ultima_version' => $p_doc_ultima_version,
                'cuerpo' => $RES_REFERENCIA,
                'enviado_a' => $p_doc_enviado_a,
                'tipo_envio' => $p_tipo_envio
            );

            //$this->fun_agregar_nuevo_certificado($dataCertificado);
            $this->_CERTIFICADO->fun_agregar_nuevo_certificado($dataCertificado);
            
        
            /*
            ml: Agregamos el  PDF .blob en caso que venga adjunto
            observacion: se puede optimizar ya que no me deja enviar data null en el insert del documento por eso 
            se agregó por separado */
            if($agregarPDF == 'OK'){
                $bind =  array(":p_id"=> $p_id,":p_doc_pdf" => $blob);
                $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_ACTUALIZAR_PDF",$bind);
                $this->_ORA->Commit();
            }

            //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
            //|||||||||||||||||||||||||||||||||   AGREGAR VISACION ENVIO VB  |||||||||||||||||||||||||||||||||||||||||||||||
            //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

            //var_dump($_FILES['file3']['tmp_name']);        
            //validamos que venga archivo adjunto en envioVB
            
            //$p_visacionVB   = 'SI';
            $p_visacionVB   = $_POST['p_otraUnidadVisacionVB'];
            $p_comentario   = $_POST['p_otraUnidadComentarioVB']; //comentario visacion enviar vb
            $p_paraVB       = $_POST['p_otraUnidadParaVB'];//usuario para enviar vb
            //$p_copiaVB =  $_POST['p_otraUnidadCopiaVB'];
           

            //var_dump($p_visacionVB."//".$p_comentario."//". $p_paraVB."//".$p_visacionVB."//".$p_doc_version."//".$p_id);
            //echo "<pre>";var_dump($blob3);echo "</pre>";
            //echo "<pre>";var_dump($agregarPDF3);echo "</pre>";
            
        
            //$p_vis_id = mt_rand();//validar como agregar este campo (id tabla visaciones)
            $p_vis_id = 0;
            $p_vis_id = $this->fun_agregar_visacion_vb($p_vis_id,$p_id,$p_doc_version,$p_paraVB,$p_comentario,$p_visacionVB);
            
            //validamos que venga archivo adjunto en envioVB
            if(isset($_FILES['file3']['tmp_name'])){
                $blob3 = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob3->WriteTemporary(file_get_contents($_FILES['file3']['tmp_name']),OCI_TEMP_BLOB);
                $bindVisacion =  array(":p_vis_id"=> $p_vis_id,":p_vis_adjunto" => $blob3);
                $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_ACTUALIZAR_ADJUNTO",$bindVisacion);
                $this->_ORA->Commit();
            }
           

            //||||||||||||||||||||||||||||||||AGREGAR DESTINATARIO y COPIA si existe|||||||||||||||||||||||||||||||||||||||||||
            //se agrega destinatario siempre y cuando exista destinatario por agregar 
            if($_POST["p_arrayDestinatario"] != ""){
                //llamar la funcion agregar destinatario
                $this->_CERTIFICADO->fun_agregar_destinatario(
                    $arrayDestinatario,
                    $arrayCargoDestinatario,
                    $p_id,
                    $p_doc_version,
                    $arrayDireccion,
                    $arrayCorreo,
                    $arrayMiMedioEnvio,
                    $arrayMiTipo,
                    $arrayMiNombreDes);
                if($_POST["p_arrayCopia"] != ""){
                //llamar la funcion agregar copia
                $this->_CERTIFICADO->fun_agregar_copia($arrayCopia,$arrayCargoCopia,$p_id,$p_doc_version,$arrayCopiaDireccion,$arrayCopiaCorreo,$arrayMiMedioEnvioCopia,$arrayMiTipoCopia,$arrayMiNombreDesCopia);
                }
            } 
            //||||||||||


            $bindTipo =  array(
                ":p_tipo_doc_id" => $gde_tipdoc_id 
            );
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_TIPO_DOCUMENTO_PKG.FUN_TIPO_DOCUMENTO_GET",'function', $bindTipo);
            if ($cursor) {
                while($tipo = $this->_ORA->FetchArray($cursor)){ 
                    
                    $tipo['TIPDOC_ID']=$tipo['TIPDOC_ID'];
                    $tipo['TIPDOC_GRABA_SDG']=$tipo['TIPDOC_GRABA_SDG'];
                    $tipo['TIPDOC_USA_PLANTILLA']=$tipo['TIPDOC_USA_PLANTILLA'];
                    $tipo['TIPDOC_SUBE_PDF']=$tipo['TIPDOC_SUBE_PDF'];
                    $tipo['TIPDOC_TIPO_DOCTO_SGD']=$tipo['TIPDOC_TIPO_DOCTO_SGD'];
                    $tipo['TIPDOC_SELECCIONA_PRIV']=$tipo['TIPDOC_SELECCIONA_PRIV'];
                    $tipo['TIPDOC_TIENE_ADJ']=$tipo['TIPDOC_TIENE_ADJ'];
                    $tipo['TIPDOC_TIENE_DEST']=$tipo['TIPDOC_TIENE_DEST'];
                    $tipo['TIPDOC_CIERRA_CASO']=$tipo['TIPDOC_CIERRA_CASO'];
                    $tipo['TIPDOC_AVANZA_ROL']=$tipo['TIPDOC_AVANZA_ROL'];
                    $tipo['TIPDOC_CIERRA_PADRE']=$tipo['TIPDOC_CIERRA_PADRE'];
                    $tipo['TIPDOC_TIENE_NUMERACION']=$tipo['TIPDOC_TIENE_NUMERACION'];
                    $tipo['TIPDOC_TIENE_FECHA']=$tipo['TIPDOC_TIENE_FECHA'];
                    $tipo['TIPDOC_FUNCION_NUMERACION']=$tipo['TIPDOC_FUNCION_NUMERACION'];
                    $tipo['TIPDOC_TIENE_FECHA']=$tipo['TIPDOC_TIENE_FECHA'];
                    $tipo['TPDOC_TIENE_VALIDADOR']=$tipo['TPDOC_TIENE_VALIDADOR'];
                    $tipo['TIPDOC_POSICION_X_NUM']=$tipo['TIPDOC_POSICION_X_NUM'];
                    $tipo['TIPDOC_POSICION_Y_NUM']=$tipo['TIPDOC_POSICION_Y_NUM'];
                    $tipo['TIPDOC_TITULO']=$tipo['TIPDOC_TITULO'];
                    $tipo['TIPDOC_LABEL_NUMERO']=$tipo['TIPDOC_LABEL_NUMERO'];
                    $tipo['TIPDOC_NUM_PROC']=$tipo['TIPDOC_NUM_PROC'];


                $res_tipo[]=$tipo;

                }			
                $this->_ORA->FreeStatement($cursor);
            }       

  

            $TIPO_DOCUMENTO = $res_tipo[0]['TIPDOC_TITULO']; //'CERTIFICADO'; //se debe obtener de la tabla GDE_TIPO_DOCUMENTO >> columna TIPDOC_NUM_PROC filtrando por el tipo de documento (TIPDOC_ID)
            $NUMERO_PROCESO = $res_tipo[0]['TIPDOC_NUM_PROC']; //'64'; //se debe obtener de la tabla GDE_TIPO_DOCUMENTO >> columna TIPDOC_TITULO filtrando por el tipo de documento (TIPDOC_ID)

  

            //|||||||||||||||||||||||| CREAMOS EL PROCESO ||||||||||||||||||||||||||||    
            //$this->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);
            $this->_CERTIFICADO->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);
     
            //ml: Aqui agreamos los expedientes adjuntos
            //mlp: falta validar si ya existen los expedientes que se van a guardar
            if($adjuntos!== false){
                foreach($adjuntos as $adj){
                    $id_adjunto = mt_rand(); //generamos un id aleatorio (validar que sea asi)
                    $bindAdjunto =  array(
                        ":p_id" => $id_adjunto, 
                        ":p_adj_usuario"=> $adj_usuario ,
                        ":p_adj_archivo_exp" => $adj['ID'],    
                        ":p_gde_docu_doc_id"=>$p_id,
                        ":p_gde_docu_doc_version" => $gde_documento_doc_version
                    );
                    
                    //$this->_ORA->ejecutaProc("GDE.GDE_TIPO_DOCUMENTO_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
                    $this->_ORA->ejecutaProc("GDE.GDE_ADJUNTOS_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
                    //$this->_ORA->Commit();
                }
            }

            
            $usuario_desde =$this->_SESION->USUARIO;//usuario desde enviar a vb otra unidad 
            $usuarioPara = $_POST['p_otraUnidadParaVB'];//usuario para enviar vb otra unidad
            $comentario = $_POST['p_otraUnidadComentarioVB']; 
       
            $bindPara = array(':usuario' => $usuarioPara);                            
            $correo_para = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bindPara);  
            $usuarioCopiaEVB = explode(',',$_POST['p_otraUnidadCopiaVB']);
            $correo_copia = array();
            if(isset($usuarioCopiaEVB) and $usuarioCopiaEVB[0] != "null"){
                for ($i=0;$i<count($usuarioCopiaEVB);$i++) { 
                    $bind = array(':usuario' => $usuarioCopiaEVB[$i]);
                    $correo_copia[$i] = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind); 
                }   
                $copia_correo = implode(",", $correo_copia); 
            }else{
                $copia_correo = null;
            }

            //enviamos el correo 
            $this->fun_enviar_correo($correo_para,$copia_correo,$p_id,$comentario,$usuario_desde);      
            
            //derivamos a la bandeja de WF [DESCOMENTAR]
            $this->setAsignar($usuarioPara, $_POST['p_otraUnidadComentarioVB'],$p_id);
            $this->_ORA->Commit();
            
            return "OK";      

        }catch(Exception $e){

            $this->_LOG->error(print_r($e));

        }


    } 
    
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    //realizamos la busqueda de enviar a vb  /// NOI SE ESTA OCUPANDO
    public function fun_buscar_evb(){

        try{
            $json = array();
            $bind = array(":p_caso_id" => null, ":p_origen" =>null);
            //print_r($bind);
            $cursor = $this->_ORA->retornaCursor('WFA.WFA_USR.getAllUsuarios','function',$bind);			
            if ($cursor) {
                while($data = $this->_ORA->FetchArray($cursor)){
                    //print_r($data);
                    $IDENTIFICACION = array();
                    $IDENTIFICACION[] = $data['nombre_persona'];
                    $IDENTIFICACION[] = $data['ep_usuario'];																	
                    //$json[] = array('id' => implode(']SEPARA[',$IDENTIFICACION),'label' => trim($data['FISCALIZADO'])." [".trim($data['TIPO_ENTIDAD']."]"));
                }			    
                $this->_ORA->FreeStatement($cursor);
            }
            
            return $IDENTIFICACION;
            //return json_encode($json);
        }catch (Exception $e){
            $this->util->mailError($e);
        }
    }
    /* validamos si corresponde agregar el modulo PRIVACIDAD*/ //NO SE ESTA OCUPANDO
    public function tiene_privacidad($padre,$tipo){
        
        try{  
            $bind = array(":p1"=>$tipo, ":p2" =>$padre);
            $result = $this->_ORA->ejecutaFunc("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_existe_privacidad", $bind);
            
            //var_dump($result);exit();    
            return $result;   

        }catch (Exception $e){
            $this->util->mailError($e);
       }
    }

   
}    







