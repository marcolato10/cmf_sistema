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
include('Sistema/class/certificado.class.php');
include('Sistema/class/correoCertificado.class.php');


require_once('fpdf16/fpdf.php');
require_once('fpdi_1.5.4/fpdi.php');
require_once('Sistema/dompdf/autoload.inc.php');
use Dompdf\Dompdf;

include_once ( "class/conexion_ora.class.php" );
include_once ( "svslib/connection.php" );	


class modificar_docto extends Pagina{
    public $CON_ELIMINAR = true;
    public $DESTINATARIO;
    public $WF_CERTIFICADO;
    public $TIPO_CERTIFICADO;
    public $ULTIMA_VERSION;
   


    public function onLoad(){
        
        $this->_CERTIFICADO = new Certificado($this);
        $this->_CORREOCERTIFICADO = new correoCertificado($this);
        
        $this->_RESOLUCION = new Resolucion($this);
        if(!$this->_RESOLUCION->isValidoModificar()){
            echo "El documento al que estas intentando ingresar necesita de privilegios";
            exit();
        }
    }
    public function main(){

        try{ 


            $wf  = $_GET['wf']; 
            $tipo   = $_GET['tipo'];
            $this->WF_CERTIFICADO  = $_GET['wf']; 
            $this->TIPO_CERTIFICADO   = $_GET['tipo'];
            
            //echo "<pre>";var_dump("tipo certificado es: ".$this->TIPO_CERTIFICADO);echo "</pre>";exit();
            
            $this->DESTINATARIO = array();
            $this->_SESION->setVariable('DESTINATARIO',$DESTINATARIOS);
            
            //$certificado_uv = $this->fun_listar_ultima_version($wf,$tipo);
            $certificado_uv = $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);
            $this->ULTIMA_VERSION = $certificado_uv[0]['DOC_VERSION'];
            

            

            $this->_SESION->setVariable('VERSION_CERTIFICADO',$certificado_uv[0]['DOC_VERSION']);
            $this->_SESION->setVariable('WF',$wf);
            $this->_SESION->setVariable('CASO_PADRE',$certificado_uv[0]['DOC_CASO_PADRE']);
            $this->_SESION->setVariable('ACCION_CERTIFICADO','M');
            $this->_SESION->setVariable('TIPO_CERTIFICADO',$tipo);
            

            //INICIALIZAMOS LOS ESTADOS DE LOS MODULOS
            $this->_SESION->setVariable('ESTADO_CUERPO',1);
            $this->_SESION->setVariable('ESTADO_PRIVACIDAD',1);
            $this->_SESION->setVariable('ESTADO_DESTINATARIO', 1);
            $this->_SESION->setVariable('ESTADO_EXPEDIENTE', 1);
            
            //INICIALIZAMOS CONTROL DESTINATARIOS
            $eliminados = array();
            $this->_SESION->setVariable('CTL_ELIMINADOS',$eliminados);

            //mostrar titulo de formulario
            $titulo_formulario = "<h1>Modificar Certificado (WF : ".$wf.")</h1>";
            $this->_TEMPLATE->assign('titulo_formulario',$titulo_formulario);
            $this->_TEMPLATE->parse('main.titulo_formulario');

            //ocultamos el div de la vista previa    
            $this->_TEMPLATE->assign('DISPLAY_div_vistaPrevia','none');
            $this->_TEMPLATE->parse('main.div_vistaPrevia');
            //ocultamos btn aCertificado
            $this->_TEMPLATE->assign('DISPLAY_btnACertificado','none');
            $this->_TEMPLATE->parse('main.btn_aCertificado');
            //mostramos el contenido del certificado
            $this->_TEMPLATE->assign('DISPLAY_div_contentCertificado','block');
            $this->_TEMPLATE->parse('main.div_cotentCertificado');
        
            

        
            //echo "aqui modificamos el certificado";
            $this->modificar_certificado($wf,$tipo);

        }catch (Exception $e){
            $this->util->mailError($e);
        }  

    }




    public function modificar_certificado($wf,$tipo){

        try{ 

            //$certificado = $this->fun_listar_certificado($wf,$tipo);
            //$certificado =  $this->fun_listar_ultima_version($wf,$tipo);
            $certificado =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);
            //echo "<pre>";var_dump($certificado);echo "</pre>";exit();    


            //destinatarios
            $this->fun_chequear_destinatario($wf,$tipo);
            //privacidad
            $this->fun_chequea_datos_sensibles($certificado[0]['DOC_DATOS_SENSIBLES'],$certificado[0]['GDE_PRIVACIDAD_PRI_ID'],$wf,$tipo);
            //cuerpo
            $this->fun_chequea_usa_plantilla($certificado[0]['DOC_USA_PLANTILLA']);    
            //expediente
            $this->fun_chequear_expediente($certificado[0]['DOC_CASO_PADRE'],$tipo,$wf);
            //versiones
            $this->fun_chequear_versiones_wf($wf,$tipo);

            //posit comentarios certificado
            //$visaciones = $this->fun_listar_visaciones($wf,$tipo);
            //$comentarios = $this->fun_html_posit_comentarios($visaciones);
            $this->fun_html_posit_comentarios($wf,$tipo);

            //$comentarios = 'ESTE ES UN COMENTARIO DE PRUEBA';
            //$this->_TEMPLATE->assign('comentarios_certificado',$comentarios); 
            //$this->_TEMPLATE->parse('main.div_comentarios_certificado');


            //ml: validamos si se debe  mostrar o no el btn firmar
            $mostrar_btn_firmar = $this->fun_existe_tipo_firma();
            if($mostrar_btn_firmar == 'SI'){
                $html_btn_firmar = '<a class="btn btn-warning btn-mk" href="javascript:void(0)" id="btnFormFirmar" onclick="accionInicioFirmar();">Firmar</a>'; 
                $this->_TEMPLATE->assign('mostrar_btn_firmar',$html_btn_firmar); 
                $this->_TEMPLATE->parse('main.mostrar_btn_firmar');
            }

        }catch (Exception $e){
            $this->util->mailError($e);
        }  

    }


    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // ||||||||||||||||||||||||||||||||||||||||  VISACIONES  |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    
    public function fun_html_posit_comentarios($wf,$tipo){

        try{ 

            $bind = array(":p_tipo"=>$tipo, ":p_wf" =>$wf);
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_VISACIONES_PKG.fun_lista_visaciones_wf",'function', $bind);
            $registros =$this->_ORA->FetchAll($cursor);
            $total = count($registros);
            $DIS = '';
            $i=0;
        
            //var_dump($registros);exit();

            //$this->_SESION->setVariable('LISTADO_VISACIONES',array());
            foreach($registros as $data){
                $i++;
                $this->_TEMPLATE->assign('DISPLAY',$DIS);
                $this->_TEMPLATE->assign('NUMERO',$i);
                $this->_TEMPLATE->assign('NUMERO_MAS',$i+1);
                $this->_TEMPLATE->assign('NUMERO_MENOS',$i-1);
                $this->_TEMPLATE->assign('DESDE',$data['VIS_USUARIO']);
                $this->_TEMPLATE->assign('HACIA',$data['VIS_USUARIO_HACIA']);
                $this->_TEMPLATE->assign('FECHA_CHAR',$data['VIS_FECHA']);
                
                $this->_TEMPLATE->assign('COMENTARIO',nl2br(isset($data['VIS_COMENTARIO']) ? $data['VIS_COMENTARIO'] : 'Sin Comentario'));
                
                if($i < $total){
                    $this->_TEMPLATE->parse('main.comentariosVisar.comentario.siguiente');
                }
                
                if($i > 1){
                    $this->_TEMPLATE->parse('main.comentariosVisar.comentario.anterior');
                }
                
                /*
                if(isset($data['VIS_ADJUNTO'])){
                    $array_listado = $this->_SESION->getVariable('LISTADO_VISACIONES');
                    $array_listado = (is_array($array_listado)) ? $array_listado : array();
                    $TOKEN_VISACION = md5(time().$data['VIS_ID']);
                    //$data['ADJ_BIN']= $data['VIS_ADJUNTO']->load();
                    $array_listado[$TOKEN_VISACION] = $data;					
                    $this->_SESION->setVariable('LISTADO_VISACIONES',$array_listado);
                    $this->_TEMPLATE->assign('TOKEN_VISACION',$TOKEN_VISACION);
                    $this->_TEMPLATE->parse('main.comentariosVisar.comentario.adjunto_comentario');
                }
                */
                
                
                $this->_TEMPLATE->parse('main.comentariosVisar.comentario');
                $DIS = 'none';
                
            }

            $this->_TEMPLATE->parse('main.comentariosVisar');
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }   
            
    }


    public function fun_listar_visaciones($wf,$tipo){

        try{ 

            $bind = array(":p_tipo"=>$tipo, ":p_wf" =>$wf);
            try{
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_VISACIONES_PKG.fun_lista_visaciones_wf",'function', $bind);
                if ($cursor) {
                    while($r = $this->_ORA->FetchArray($cursor)){ 
                        $r['VIS_ID']=$r['VIS_ID'];
                        $r['VIS_USUARIO']=$r['VIS_USUARIO'];
                        $r['VIS_USUARIO_HACIA']=$r['VIS_USUARIO_HACIA'];
                        $r['VIS_VB']=$r['VIS_VB'];
                        $r['VIS_FECHA']=$r['VIS_FECHA'];
                        $r['VIS_COMENTARIO']=$r['VIS_COMENTARIO']->load();
                        $r['DOC_VERSION']=$r['DIC_VERSION'];
                        $r['DOC_DATOS_SENCIBLES']=$r['DOC_DATOS_SENCIBLES'];
                        $r['doc_usa_plantilla']=$r['doc_usa_plantilla'];
                        $r['doc_pdf']=$r['doc_pdf'];
                        $r['doc_cuerpo']=$r['doc_cuerpo'];
                        $r['doc_ultima_version']=$r['doc_ultima_version'];
                        $r['doc_fecha']=$r['doc_fecha'];
                        $r['doc_redactor']=$r['doc_redactor'];
                        $r['doc_enviado_a']=$r['doc_enviado_a'];
                        $r['doc_genera_version']=$r['doc_genera_version'];
                        $r['doc_caso_padre']=$r['doc_caso_padre'];


                        $visaciones[]=$r;
                    
                    }			
                    $this->_ORA->FreeStatement($cursor);
                }
            
            }catch (Exception $e){
                print("hay un error");
            }
            return $visaciones;

        }catch (Exception $e){
            $this->util->mailError($e);
        }   

    }

    


    
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // ||||||||||||||||||||||||||||||||||||||||  CUERPO  |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    
    
    public function fun_cargar_inicial_cuerpo(){

        try{ 
            
            $wf = $this->_SESION->getVariable("WF");
            $version = $this->_SESION->getVariable("VERSION_CERTIFICADO"); 
            $mi_certificado = $this->fun_listar_certificado_xversion($wf,$version);

            if($mi_certificado[0]['DOC_PDF']){
                return "PDF";    
            }else{
                //echo "no existe pdf";
                $this->_TEMPLATE->assign('r_cuerpo',$mi_certificado[0]['DOC_CUERPO']);
                $this->_TEMPLATE->parse('main.cuerpo_certificado');
                return "CUERPO";
            }
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }  
        
    }

    

      //ml: chequeamos el usa plantilla para poder cargar respuesta en la vista
      public function  fun_chequea_usa_plantilla($res){
       
        try{

            $wf = $this->_SESION->getVariable("WF");
            $version = $this->_SESION->getVariable("VERSION_CERTIFICADO"); 
            $mi_certificado = $this->fun_listar_certificado_xversion($wf,$version);



          

            $listado = "";
            //$plantillas = $this->fun_listar_plantillas('certificado');
            $plantillas = $this->_CERTIFICADO->fun_listar_plantillas('certificado');
            foreach($plantillas as $key => $datos){
                $listado .= '<li><input type="radio" name="tipoPlantilla" id="tipoPlantilla" value="'.$plantillas[$key]['PLA_ID'].'" onclick="cargarCuerpo(this.value)">'.$plantillas[$key]['PLA_NOMBRE'].'</li>';    
            }  
            $this->_TEMPLATE->assign('plantilla_disponible',$listado);
            $this->_TEMPLATE->parse('main.usa_plantilla.plantillas_disponibles');
            
        
            //aqui cargamos la data en el cuerpo si es que tiene cuerpo sin pdf
            if(!$mi_certificado[0]['DOC_PDF']){
                //var_dump($mi_certificado[0]['DOC_CUERPO']->load());exit();
                if($mi_certificado[0]['DOC_CUERPO']){
                    $this->_TEMPLATE->assign('r_cuerpo',$mi_certificado[0]['DOC_CUERPO']->load()); 
                }else{
                    $this->_TEMPLATE->assign('r_cuerpo','ATENCIÓN: Esta información no existe.'); 
                }    
                $this->_TEMPLATE->parse('main.usa_plantilla.cuerpo_certificado');
            }



            //if(strtoupper($res) == 'SI'){
            
            
                //$this->_TEMPLATE->assign('r_usa_plantilla_si','checked');
                //$this->_TEMPLATE->assign('r_usa_plantilla_no','');
                
            $opcion = strtoupper($res);
            $html_con_plantilla = $this->fun_mostrar_plantilla($opcion);
            $this->_TEMPLATE->assign('mostrar_opcion_cuerpo', $html_con_plantilla);
            $this->_TEMPLATE->parse('main.usa_plantilla');

            //}else{
                //$this->_TEMPLATE->assign('r_usa_plantilla_no','checked');
                //$this->_TEMPLATE->assign('r_usa_plantilla_si','');

            /*  $opcion = 'NO';
                $html_sin_plantilla = $this->fun_mostrar_plantilla($opcion);//cambiar por NO
                $this->_TEMPLATE->assign('mostrar_opcion_cuerpo', $html_sin_plantilla);
                $this->_TEMPLATE->parse('main.usa_plantilla');
            }*/

        }catch (Exception $e){
            $this->util->mailError($e);
        }  

    }


 
    public function fun_mostrar_plantilla($opcion){
        
        try{

            $html='<div style="clear:both">
            <iframe id="iframeVistaPrevia" name="iframeVistaPrevia" title="Previsualizacion" src="index.php?pagina=paginas.modificar_docto&funcion=fun_cargar_pdf&opcion='.$opcion.'" frameborder="1" width="100%px" height="500px" scrolling="auto"></iframe>
            </div>';
            
            return $html;

        }catch (Exception $e){
            $this->util->mailError($e);
        }
    }

    //ml: nos permite activar el usa plantilla antes de intentar modificar el cuerpo
    public function fun_activar_usa_plantilla(){

        try{

            $wf = $this->_SESION->getVariable("WF");
            $version = $this->_SESION->getVariable("VERSION_CERTIFICADO"); 
            $mi_certificado = $this->fun_listar_certificado_xversion($wf,$version);

            $respuesta = $mi_certificado[0]['DOC_USA_PLANTILLA'];   

            return $respuesta;

        }catch (Exception $e){
            $this->util->mailError($e);
        }

    }


    //ml: cargamos el documento PDF en el iframe del cuerpo
    public function fun_cargar_pdf(){
        
        try{

            //var_dump($_GET['opcion']);exit();

            $wf = $this->_SESION->getVariable("WF");
            $version = $this->_SESION->getVariable("VERSION_CERTIFICADO"); 
            $mi_certificado = $this->fun_listar_certificado_xversion($wf,$version);

            $gde_tipdoc_id = 'certificado';
            $res_tipo = $this->fun_get_tipo_documento($gde_tipdoc_id);
            $TITULO_DOCUMENTO = $res_tipo[0]['TIPDOC_TITULO'];
            $X_DOCUMENTO = $res_tipo[0]['TIPDOC_POSICION_X_NUM'];
            $Y_DOCUMENTO = $res_tipo[0]['TIPDOC_POSICION_Y_NUM'];

            $pdf = new FPDI_R();
            
        

            if($mi_certificado[0]['DOC_USA_PLANTILLA'] == 'NO' ){
                
                //print_r("xxx no usa plantilla");
                
                if(isset($mi_certificado[0]['DOC_CUERPO'])){ 
                    
                    $html = $this->fun_armar_html_pdf();
                    $dompdf = new Dompdf();
                    $dompdf->loadHtml($html);
                    $dompdf->render();
            
                    $PDF_ARC = $dompdf->output();
                    $pdf = new FPDI_R();
                    $pdf->generarPDF($PDF_ARC,$TITULO_DOCUMENTO,$X_DOCUMENTO,$Y_DOCUMENTO);
                
                }else{
                    //print_r("xxx usa plantilla");
                    //print_r($mi_certificado[0]['DOC_PDF']->load());//exit();
                    $pdf->generarPDF($mi_certificado[0]['DOC_PDF']->load(),$TITULO_DOCUMENTO,$X_DOCUMENTO,$Y_DOCUMENTO);
                }

            
            
            }else{
            
                $html = $this->fun_armar_html_pdf();
                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->render();
        
                $PDF_ARC = $dompdf->output();
                $pdf = new FPDI_R();
                $pdf->generarPDF($PDF_ARC,$TITULO_DOCUMENTO,$X_DOCUMENTO,$Y_DOCUMENTO);
        
            }
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }
    }

    //ml: arma pdf con data de cuerpo
    public function fun_armar_html_pdf(){
        
        try{

            $wf = $this->_SESION->getVariable("WF");
            $version = $this->_SESION->getVariable("VERSION_CERTIFICADO"); 
            $mi_certificado = $this->fun_listar_certificado_xversion($wf,$version);
            
            


            $RUTA = dirname (__FILE__)."/../img/logocmf.png";
            $html = "<html>
            <head>
                <style type='text/css'>
                        body {
                            margin: 0;
                            padding: 0;
                            font-family: Verdana, Arial, Helvetica, sans-serif;
                        }
                        #cabecera {
                            margin: 20px;
                            margin-right: 100px;
                            padding: 0;
                            border-bottom: #0000ff solid thin;
                        }
                        #pie {
                            clear: both;
                            margin: 1em;
                            padding: 1em 0 0 0;
                            text-align: center;
                            border-top: #0000ff solid thin;
                            font-size: 80%;
                        }
                        #contenido {
                            
                            margin-left: 50px;
                            margin-right: 100px;
                        }
                        
                        #contenedor {
                            width: 800px;
                            margin: 0 auto;
                            padding: 0;
                        }
                        table {
                            width:500px;
                            cellpadding:5px;
                            cellspacing:5px;
                            border: 1px solid #000;
                        }
                        td {
                            border-width: 1px;
                        }
                        thead td, thead th {
                            border-width: 1px 1px 1px 1px;
                        }
                </style>
            </head>
            <body><div id='contenedor'>";
            
            if($mi_certificado[0]['DOC_CUERPO']){
                $html .=  '<div id="contenido">'. $this->limpiarTexto($mi_certificado[0]['DOC_CUERPO']->load());
            }else{
                $html .=  '<div id="contenido"> ATENCIÓN: Esta información no existe.';
            }
        

        
            $html .= '</div></div></body></html>';   
            
            return $html;
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }
    }

 


     //ml: obtenemos el tipo de documento
     public function fun_get_tipo_documento($gde_tipdoc_id){
        
        try{

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
                    $tipo['TIPDOC_TIENE_ENV']=$tipo['TIPDOC_TIENE_ENV'];
                    $tipo['TIPDOC_APFIRMA']=$tipo['TIPDOC_APFIRMA'];
        
        
                $res_tipo[]=$tipo;
        
                }			
                $this->_ORA->FreeStatement($cursor);
            }  
            
            return $res_tipo;
            
        }catch (Exception $e){
            $this->util->mailError($e);
        }

    }


    //ml: cargamos el cuerpo de la plantilla seleccionada
    public function fun_cargar_cuerpo_plantilla(){

        try{
            
            $plantilla = $this->fun_obtener_plantilla_get($_POST['p_plantilla']);
            //$this->fun_chequea_cuerpo($plantilla[0]['PLA_CUERPO']);     
            //$this->_TEMPLATE->assign('r_cuerpo',$plantilla[0]['PLA_CUERPO']);
            //$this->_TEMPLATE->parse('main.cuerpo_certificado');

            //var_dump($plantilla[0]['PLA_CUERPO']);    
            return  $plantilla[0]['PLA_CUERPO'];
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }    
    }

    //ml obtenemos la plantilla por el id
    public function fun_obtener_plantilla_get($id){
        
        try{
         
            $bind = array(":p_id"=>$id);
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_PLANTILLA_PKG.FUN_PLANTILLA_DOC_GET",'function', $bind);  
            if($cursor) {
                while($r = $this->_ORA->FetchArray($cursor)){ 
                    $r['PLA_ID']=$r['PLA_ID'];
                    $r['PLA_NOMBRE']=$r['PLA_NOMBRE'];
                    $r['PLA_CUERPO']=$r['PLA_CUERPO']->load();
                    $r['PLA_USUARIO']=$r['PLA_USUARIO'];
                    $r['PLA_VIGENTE']=$r['PLA_VIGENTE'];
                    $r['GDE_TIPOS_DOCUMENTO_TIPDOC_ID']=$r['GDE_TIPOS_DOCUMENTO_TIPDOC_ID'];
                    $plantillas[]=$r;
                }
                $this->_ORA->FreeStatement($cursor);
            }

            return $plantillas;
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }
    }

    //Cambiamos el estado en el CUERPO
    public function fun_cambia_estado_cuerpo(){
       
        try{
            
            $opcion = $_GET['estado'];
            if(isset($_GET['estado'])){
                $this->_SESION->setVariable('ESTADO_CUERPO',$opcion);
                $MENSAJES[] = 'SE CAMBIA ESTADO DE CUERPO :'.$opcion;
            }

            $json = array();
            $MENSAJES = array();

            $json['MENSAJES'] =  $MENSAJES;
            $json['RESULTADO'] = 'OK';
            return json_encode($json);	
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }
    }
    //Cambiamos el estado en PRIVACIDAD
    public function fun_cambia_estado_privacidad(){
       
        try{
        
            $opcion = $_GET['estado'];

            if(isset($_GET['estado'])){
                $this->_SESION->setVariable('ESTADO_PRIVACIDAD',$opcion);
                $MENSAJES[] = 'SE CAMBIA ESTADO DE PRIVACIDAD :'.$opcion;
            }

            $json = array();
            $MENSAJES = array();

            $json['MENSAJES'] =  $MENSAJES;
            $json['RESULTADO'] = 'OK';
            return json_encode($json);	
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }    
    }



    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //ml: controlamos lo destinatarios eliminados 
    public function fun_controlar_eliminados(){

        try{

            $cantidad = count($this->_SESION->getVariable('CTL_ELIMINADOS'));
            $arrayEliminados = array();
            
            if($cantidad == 0){
                $arrayEliminados[0] = $_POST['rut']; 
            }else{
                $arrayEliminados = $this->_SESION->getVariable('CTL_ELIMINADOS');
                $arrayEliminados[$cantidad+1]= $_POST['rut']; 
            }

            $arrayEliminados = array_unique($arrayEliminados);

            $this->_SESION->setVariable('CTL_ELIMINADOS', $arrayEliminados);
            $arrayEliminados = $this->_SESION->getVariable('CTL_ELIMINADOS');

            //var_dump($arrayEliminados);
      
        }catch (Exception $e){
            $this->util->mailError($e);
        }    

    }


    //Cambiamos el estado en DESTINATARIOS
    public function fun_cambia_estado_destinatario(){
       
        try{

            $opcion = $_GET['estado'];

            if(isset($_GET['estado'])){
                $this->_SESION->setVariable('ESTADO_DESTINATARIO',$opcion);
                $MENSAJES[] = 'SE CAMBIA ESTADO DE DESTINATARIOS :'.$opcion;
            }

            $json = array();
            $MENSAJES = array();

            $json['MENSAJES'] =  $MENSAJES;
            $json['RESULTADO'] = 'OK';
            return json_encode($json);	
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }  

    }

   

    //Cambiamos el estado en EXPEDIENTES
    public function fun_cambia_estado_expediente(){
       
        try{
            $opcion = $_GET['estado'];

            if(isset($_GET['estado'])){
                $this->_SESION->setVariable('ESTADO_EXPEDIENTE',$opcion);
                $MENSAJES[] = 'SE CAMBIA ESTADO DE EXPEDIENTES :'.$opcion;
            }

            $json = array();
            $MENSAJES = array();

            $json['MENSAJES'] =  $MENSAJES;
            $json['RESULTADO'] = 'OK';
            return json_encode($json);
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }

    }

    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||    DESTINATARIOS / DISTRIBUCION  ||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    
    public function fun_chequear_destinatario($wf,$tipo){

        try{

            //$certificado_uv =  $this->fun_listar_ultima_version($wf,$tipo);//certificado ultima version
            $certificado_uv =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);//certificado ultima version

            $distribucionCN = $this->fun_get_distribucion($wf,$certificado_uv,'NO');//distribucion 
            $distribucionCS = $this->fun_get_distribucion($wf,$certificado_uv,'SI');//distribucion copia 
        
            /*
            echo "<pre>";var_dump($distribucionCN);echo "</pre>";
            echo "||||||||||||||||||||||||||||||||||||||||||||";
            echo "<pre>";var_dump($distribucionCS);echo "</pre>";
            exit();
            */


        
            $tipo_envio = $certificado_uv[0]['TIPENV_ID'];
            //var_dump($tipo_envio);exit();
            if($tipo_envio){
                $resultado_tipo_envios = $this->fun_tipo_envio_html($tipo_envio); 
                $this->_TEMPLATE->assign('resultado_tipo_envio',$resultado_tipo_envios);
            }

            $misFiscalizados ='';
            if(isset($distribucionCN)){
                foreach($distribucionCN as $disCN){
                
                    if($disCN['DIS_DIRECCION'] == 'undefined'){
                        $direccionCN = '';
                    }else{
                        $direccionCN = $disCN['DIS_DIRECCION'];
                    }


                    if($disCN['DIS_CORREO'] == 'undefined'){
                        $correoCN = '';
                    }else{
                        $correoCN = $disCN['DIS_CORREO'];
                    }


                    $escopia = "NO";
                    $misFiscalizados .= '<tr id="tr_'.$disCN['DIS_RUT'].'">
                        <td width="5%"></td>
                        <td width="95%">
                            <span class="item1">'.$disCN['TIPO_ENTIDAD'].'</span>&nbsp;&nbsp;&nbsp;
                            <label for="input_cargoFiscalizadoLista_'.$disCN['DIS_RUT'].'"> Cargo ocupado </label> :
                                <input type="text" class="textboxCargo" id="input_cargoFiscalizadoLista_'.$disCN['DIS_RUT'].'" onblur="fun_cargoDestinatario(this,'.$disCN['DIS_RUT'].')" value="'.$disCN['DIS_CARGO'].'" name="input_cargoFiscalizadoLista_'.$disCN['DIS_RUT'].'">
                            <br>
                            <span style="font-weight:100;font-size:10px;" id="">'.$disCN['DIS_NOMBRE'].'</span><span id="span_usuarioSeil"></span> <br>
                            <input type="hidden" class="miNombreDes" name="miNombreDes[]" id="miNombreDes_'.$disCN['DIS_RUT'].'" value="'.$disCN['DIS_NOMBRE'].'">
                            <img width="18px" id="" alt="" src="Sistema/img/sobre.png"> <span style="font-weight:100;font-size:9px;" id="">'. $direccionCN.'</span><br />
                            <input type="hidden" class="miDireccion" name="miDireccion[]" id="miDireccion_'.$disCN['DIS_RUT'].'" value="'.$disCN['DIS_DIRECCION'].'">

                            <img width="18px" id="" alt="" src="Sistema/img/arroba.png"> <span style="font-weight:100;font-size:9px;" id="">'.$correoCN.'</span><br />
                            <input type="hidden" class="miCorreo" name="miCorreo[]" id="miCorreo_'.$disCN['DIS_RUT'].'" value="'.$disCN['DIS_CORREO'].'">

                            <input type="hidden" class="miTipo" name="miTipo[]" id="miTipo_'.$disCN['DIS_RUT'].'" value="'.$disCN['TIPO_ENTIDAD'].'">
                            <input type="hidden" class="miDestinatario" name="miDestinatario[]" id="miDestinatario_'.$disCN['DIS_RUT'].'" value="'.$disCN['DIS_RUT'].'">
                            <div style="display: table-cell">
                                <a style="cursor:pointer" onClick="fun_eliminarSeleccionadoDestinatarioM('.$disCN['DIS_RUT'].',\''.$escopia.'\');" class="btn btn-sm btn-warning"><img src="Sistema/img/eliminar-destinatario.png" />Eliminar&nbsp;Destinatario</a>
                            </div>
                        </td>
                    </tr>';
                }
            }

            $misCopias ='';
            if(isset($distribucionCS)){
                foreach($distribucionCS as $disCS){

                    if($disCS['DIS_DIRECCION'] == 'undefined'){
                        $direccionCS = '';
                    }else{
                        $direccionCS = $disCS['DIS_DIRECCION'];
                    }

                    if($disCS['DIS_CORREO'] == 'undefined'){
                        $correoCS = '';
                    }else{
                        $correoCS = $disCS['DIS_CORREO'];
                    }

                    $escopia = "SI";
                    $misCopias .= '<tr id="trc_'.$disCS['DIS_RUT'].'">
                    <td width="5%"></td>
                    <td width="95%"><span class="item1">'.$disCS['TIPO_ENTIDAD'].'</span>&nbsp;&nbsp;&nbsp;
                    <label for="input_cargoFiscalizadoLista_'.$disCS['DIS_RUT'].'">Cargo ocupado</label> :
                    <input type="text" class="textboxCargo" id="input_cargoFiscalizadoLista_'.$disCS['DIS_RUT'].'" onblur="fun_cargoDestinatario(this,'.$disCS['DIS_RUT'].')" value="'.$disCS['DIS_CARGO'].'" name="input_cargoFiscalizadoLista_'.$disCS['DIS_RUT'].'">
                    <br>

                    <span style="font-weight:100;font-size:10px;" id="">'.$disCS['DIS_NOMBRE'].'</span><span id="span_usuarioSeil"></span> <br>
                    <input type="hidden" class="miNombreDesCopia" name="miNombreDesCopia[]" id="miNombreDesCopia_'.$disCS['DIS_RUT'].'" value="'.$disCS['DIS_NOMBRE'].'">    

                    <img width="18px" id="" alt="" src="Sistema/img/sobre.png"> <span style="font-weight:100;font-size:9px;" id="">'.$direccionCS.'</span><br />
                    <input type="hidden" class="miCopiaDireccion" name="miCopiaDireccion[]" id="miCopiaDireccion_'.$disCS['DIS_RUT'].'" value="'.$disCS['DIS_DIRECCION'].'">
                    <img width="18px" id="" alt="" src="Sistema/img/arroba.png"> <span style="font-weight:100;font-size:9px;" id="">
                    '.$correoCS.'</span><br />
                    <input type="hidden" class="miCopiaCorreo" name="miCopiaCorreo[]" id="miCopiaCorreo_'.$disCS['DIS_RUT'].'" value="'.$disCS['DIS_CORREO'].'">
                    


                    <input type="hidden" class="miTipoCopia" name="miTipoCopia[]" id="miTipoCopia_'.$disCS['DIS_RUT'].'" value="'.$disCS['TIPO_ENTIDAD'].'">
                    <input type="hidden" class="miCopia" name="miCopia[]" id="miCopia_'.$disCS['DIS_RUT'].'" value="'.$disCS['DIS_RUT'].'">

                    <div style="display: table-cell">
                        <a style="cursor:pointer" onClick="fun_eliminarSeleccionadoDestinatarioM('.$disCS['DIS_RUT'].',\''.$escopia.'\');" class="btn btn-sm btn-warning"><img src="Sistema/img/eliminar-destinatario.png" />Eliminar&nbsp;Destinatario</a>
                    </div>
                    
                    <br></td></tr>';
                    
                    

                }
            }

            
                    
            $this->_TEMPLATE->assign('MIS_FISCALIZADOS',$misFiscalizados);//mostramos los destinatarios que tiene el certificado 
            $this->_TEMPLATE->parse('main.paso1.div_listaDistribucion');
            $this->_TEMPLATE->assign('MIS_COPIAS',$misCopias);//mostramos los destinatarios copia que tiene el certificado       
            $this->_TEMPLATE->parse('main.paso1.div_listaDistribucion_copia');
            
        
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

    //ml: obtener distribucion x rut y copia
    public function fun_get_distribucion($wf,$certificado,$copia){

        try{
        
            $version = $certificado[0]['DOC_VERSION'];
            $bind = array(
                    ":p_doc_id"=>$wf, 
                    ":p_version" =>$version,
                    ":p_copia" =>$copia
                );
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_DISTRIBUCION_PKG.fun_distribucion_get",'function', $bind);


                    if ($cursor) {
                        while($r = $this->_ORA->FetchArray($cursor)){ 
                            $r['DIS_SECUENCIA']=$r['DIS_SECUENCIA'];
                            $r['DIS_RUT']=$r['DIS_RUT'];
                            $r['DIS_DV']=$r['DIS_DV'];
                            $r['TIPO_ENTIDAD']=$r['TIPO_ENTIDAD'];
                            $r['DIS_CARGO']=$r['DIS_CARGO'];
                            $r['DIS_DIRECCION']=$r['DIS_DIRECCION'];
                            $r['DIS_CORREO']=$r['DIS_CORREO'];
                            $r['DIS_CON_COPIA']=$r['DIS_CON_COPIA'];
                            $r['DIS_MEDIO_ENVIO']=$r['DIS_MEDIO_ENVIO'];
                            $r['DOC_ID']=$r['DOC_ID'];
                            $r['DOC_VERSIO']=$r['DOC_VERSION'];
                            $r['DIS_NOMBRE']=$r['DIS_NOMBRE'];

                            $resDISTRIBUCION[]=$r;    
                        }
                        $this->_ORA->FreeStatement($cursor);
                    }    
                    
            return $resDISTRIBUCION;

        }catch (Exception $e){
            $this->util->mailError($e);
        }    

    }            

    //ml eliminar distribucion segun la version
    public function fun_eliminar_distribucion($wf,$rut,$version,$tipo_entidad){
            
        try{
            //print_r("PASO 4: fun_eliminar_distribucion");

            $bindDistribucion =  array(":p_rut"=> $rut ,":p_version" => $version,":p_wf"=>$wf, ":p_tipo_entidad"=>$tipo_entidad);
            $this->_ORA->ejecutaProc("GDE.GDE_DISTRIBUCION_PKG.PRC_ELIMINAR_DISTRIBUCION",$bindDistribucion);
            $this->_ORA->Commit();
            
        }catch (Exception $e){
            $this->util->mailError($e);
        }    

    }


    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||    PRIVACIDAD   |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


    //ml: chequeamos datos sensibles para poder cargar respuesta en la vista
    public function  fun_chequea_datos_sensibles($res_sensibles,$res_privacidad,$wf,$tipo){

        try{

            if(strtoupper($res_sensibles) == 'SI'){
                $this->_TEMPLATE->assign('r_dato_sensible_si','checked');
                $this->_TEMPLATE->assign('r_dato_sensible_no','');
                $resp_chequeo_privacidad = $this->fun_chequear_privacidad_wf($wf,$tipo);
                $this->_TEMPLATE->assign('resultado_privacidad',$resp_chequeo_privacidad);
                
                $cadena = $this->fun_privacidad_cadena($tipo);
                $this->_TEMPLATE->assign('cadena',$cadena);
                
                $this->_TEMPLATE->parse('main.existe_privacidad');
            }else if(strtoupper($res_sensibles) == 'NO'){
                $this->_TEMPLATE->assign('r_dato_sensible_no','checked');
                $this->_TEMPLATE->assign('r_dato_sensible_si','');
                $resp_chequeo_privacidad = $this->fun_chequear_privacidad_wf($wf,$tipo);
                $this->_TEMPLATE->assign('resultado_privacidad',$resp_chequeo_privacidad);
                
                $cadena = $this->fun_privacidad_cadena($tipo);
                $this->_TEMPLATE->assign('cadena',$cadena);

                $this->_TEMPLATE->parse('main.existe_privacidad');
            }else{
                $this->_TEMPLATE->assign('r_dato_sensible_no','');
                $this->_TEMPLATE->assign('r_dato_sensible_si','');
                $resp_chequeo_privacidad = $this->fun_chequear_privacidad_wf($wf,$tipo);
                $this->_TEMPLATE->assign('resultado_privacidad',$resp_chequeo_privacidad);
                $cadena = $this->fun_privacidad_cadena($tipo);
                $this->_TEMPLATE->assign('cadena',$cadena);
                $this->_TEMPLATE->parse('main.existe_privacidad');
            }
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }    

    }

    //ml: dejamos seleccionada las opciones que estan en la bbdd , de mi privacidad 
    public function fun_chequear_privacidad_wf($wf,$tipo){
    
        try{

            $privacidades = $this->fun_listar_privacidad_asociada($tipo);
            //$mi_certificado = $this->fun_listar_certificado($wf,$tipo);
            //$mi_certificado =  $this->fun_listar_ultima_version($wf,$tipo);//certificado ultima version
            $mi_certificado =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);//certificado ultima version

            
            $mi_privacidad = $mi_certificado[0]['GDE_PRIVACIDAD_PRI_ID'];
            $html_privacidad ='
            <div class="sec1Destinatario">
                <label class="textoPregunta" for="DocContieneDatosSenciblesPersonales">Privacidad :</label>
            </div>
            
            <div class="sec1Destinatario">
            <div class="errorModificar" id="errorPrivacidad">(*) Usted debe seleccionar una opción</div>';
            foreach($privacidades as $key => $datos){
                $html_privacidad .='<div class="form-check form-check-inline">';
                $html_privacidad .="<label class='form-check-label'>".$privacidades[$key]['PRI_NOMBRE']." (Tipo ".$privacidades[$key]['PRI_TRAD_GDOC'].")</label>&nbsp;";
                
                if($mi_privacidad == 'publi'){
                    if($privacidades[$key]['PRI_ID'] == $mi_privacidad){
                        $html_privacidad .="<input class='form-check-input' type='radio' id='privacidadTipo_".$privacidades[$key]['PRI_ID']."' name='privacidadTipo' value='".$privacidades[$key]['PRI_ID']."' onclick='fun_cambiar_estado_privacidad(2)' checked >";
                    }else{
                        $html_privacidad .="<input class='form-check-input' type='radio' id='privacidadTipo_".$privacidades[$key]['PRI_ID']."' name='privacidadTipo' value='".$privacidades[$key]['PRI_ID']."' onclick='fun_cambiar_estado_privacidad(2)'  disabled>";
                    }
                }else{
                    if($privacidades[$key]['PRI_ID'] == $mi_privacidad){
                        $html_privacidad .="<input class='form-check-input' type='radio' id='privacidadTipo_".$privacidades[$key]['PRI_ID']."' name='privacidadTipo' value='".$privacidades[$key]['PRI_ID']."' onclick='fun_cambiar_estado_privacidad(2)' checked >";
                    }else{
                        if($privacidades[$key]['PRI_ID'] == 'publi'){
                            $html_privacidad .="<input class='form-check-input' type='radio' id='privacidadTipo_".$privacidades[$key]['PRI_ID']."' name='privacidadTipo' value='".$privacidades[$key]['PRI_ID']."' onclick='fun_cambiar_estado_privacidad(2)'  disabled>";
                        }else{
                            $html_privacidad .="<input class='form-check-input' type='radio' id='privacidadTipo_".$privacidades[$key]['PRI_ID']."' name='privacidadTipo' value='".$privacidades[$key]['PRI_ID']."' onclick='fun_cambiar_estado_privacidad(2)'>";
                        }
                    }
                }
                $html_privacidad .='</div>&nbsp;&nbsp;';    
            }
            $html_privacidad .="</div>";
        
            return $html_privacidad;
        
        }catch (Exception $e){
            $this->util->mailError($e);
        }    

    }    


    //ml Me lista los tipos de DOCUMENTOS asociados por TIPODOC_ID para mi privacidad 
    public function fun_listar_privacidad_asociada($p_tipo){
        
        
        try{
            $bind = array(":p1"=>$p_tipo);
            //$cursor = $this->_ORA->retornaCursor("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_lista_privacidad_asociada",'function', $bind);
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_PRIVACIDAD_PKG.fun_lista_privacidad_asociada",'function', $bind);
            //var_dump($cursor);exit();
            if ($cursor) {
                    while($r = $this->_ORA->FetchArray($cursor)){ 
                    $r['TIPO_DOC_ID']=$r['TIPO_DOC_ID'];
                    $r['PRI_NOMBRE']=$r['PRI_NOMBRE'];
                    $r['PRI_TRAD_GDOC']=$r['PRI_TRAD_GDOC'];
                    $resultado2[]=$r;
                    }			
                    $this->_ORA->FreeStatement($cursor);
            }
            
            return  $resultado2;
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar privacidad asociada");
            exit();
        }

    }

       


    //ml: lista el certificado por id y tipo
    public function fun_listar_certificado($wf,$tipo){
        
        try{

            $bind = array(":p_wf"=>$wf, ":p_tipo" =>$tipo);
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_DOCUMENTO_PKG.fun_listar_certificado_wf",'function', $bind);
        
        
                    if ($cursor) {
                        while($r = $this->_ORA->FetchArray($cursor)){ 
                            $r['DOC_ID']=$r['DOC_ID'];
                            $r['DOC_VERSION']=$r['DOC_VERSION'];
                            $r['DOC_DATOS_SENSIBLES']=$r['DOC_DATOS_SENSIBLES'];
                            $r['DOC_USA_PLANTILLA']=$r['DOC_USA_PLANTILLA'];
                            $r['DOC_PDF']=$r['DOC_PDF'];
                            $r['DOC_FECHA']=$r['DOC_FECHA'];
                            $r['DOC_REDACTOR']=$r['DOC_REDACTOR'];
                            $r['DOC_ENVIADO_A']=$r['DOC_ENVIADO_A'];
                            $r['GDE_TIPOS_DOCUMENTO_TIPDOC_ID']=$r['GDE_TIPOS_DOCUMENTO_TIPDOC_ID'];
                            //$r['GDE_DISTRIBUCION_DIS_SECUENCIA']=$r['GDE_DISTRIBUCION_DIS_SECUENCIA'];
                            $r['GDE_ESTADO_DOCUMENTO_ESTDOC_ID']=$r['GDE_ESTADO_DOCUMENTO_ESTDOC_ID'];
                            $r['GDE_PRIVACIDAD_PRI_ID']=$r['GDE_PRIVACIDAD_PRI_ID'];
                            $r['DOC_GENERA_VERSION']=$r['DOC_GENERA_VERSION'];
                            $r['DOC_CASO_PADRE']=$r['DOC_CASO_PADRE']; 
                            //$r['DOC_CUERPO']=$r['DOC_CUERPO']->load(); 
                            
        
                            $resCertificado[]=$r;    
                        }
                        $this->_ORA->FreeStatement($cursor);
                    }    
                    
            return $resCertificado;
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar privacidad asociada");
            exit();
        }
    }
    


    public function fun_privacidad_cadena($tipo){
        
        try{

            $resultado2 = $this->fun_listar_privacidad_asociada($tipo);
            $cantidad_datos = count($resultado2);
            //var_dump($resultado2);exit();
            foreach($resultado2 as $key => $datos){
                $array_identificados[$key] = $resultado2[$key]['PRI_ID']; 
            }
            $cadena =  implode("_",$array_identificados);
            
            return $cadena;
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar privacidad asociada");
            exit();
        }

    }

   
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||    expediente / adjuntos  |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    public function fun_chequear_expediente($padre,$tipo,$wf){
        
        try{
       
            /*
            1.- hay que obtener el id padre del documento //OK
            2.- pasarlo como parametro a la etiqueta para que liste los expedientes asociados a ese padre //ok
            3.- hay que listar los expedientes asignados al certificado
            4.- en el grabar hay que controlar las versiones de los expedientes segun el usuario
            */
            

            //$certificado_uv =  $this->fun_listar_ultima_version($wf,$tipo);//certificado ultima version
            $certificado_uv =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);//certificado ultima version
            $this->fun_listar_adjuntos($certificado_uv); //revisar:: listamos los adjuntos asocicados

            $this->_TEMPLATE->assign('wf',$wf);
            $this->_TEMPLATE->assign('padre',$padre);
            $this->_TEMPLATE->assign('tipo',$tipo);
            $this->_TEMPLATE->parse('main.padre');
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar privacidad asociada");
            exit();
        }
    }

    public function fun_verExpediente($param){
        
        try{
            $padre = $param['padre'];
            return $this->_RESOLUCION->verExpediente($padre);
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar privacidad asociada");
            exit();
        }
    }


    //ml: lista los archivos adjuntos asociados al docuemnto y el tipo doc
    public function fun_obtener_adjuntos($wf,$tipo){
        
        try{
            $bind = array(":p_doc_id"=>$wf, ":p_tipo" =>$tipo);
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_ADJUNTOS_PKG.fun_lista_archivos_adjuntos",'function', $bind);
			if ($cursor) {
			 	 while($r = $this->_ORA->FetchArray($cursor)){ 
					$r['ADJ_ID']=$r['ADJ_ID'];
                    $r['ADJ_USUARIO']=$r['ADJ_USUARIO'];
                    $r['ADJ_FECHA']=$r['ADJ_FECHA'];
                    $r['ADJ_ARCHIVO_EXP']=$r['ADJ_ARCHIVO_EXP'];
                    $r['GDE_DOCUMENTO_DOC_VERSION']=$r['GDE_DOCUMENTO_DOC_VERSION'];
                    $r['GDE_DOCUMENTO_DOC_ID']=$r['GDE_DOCUMENTO_DOC_ID'];
                    
                    $resultado2[]=$r;

			 	 }			
			 	 $this->_ORA->FreeStatement($cursor);
			}

            return $resultado2;
           
		}catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
        
    }
    

    protected function  getColumnaArchivoExpediente($columna, $id){
        try{

            $bind = array(":columna" => $columna, ":id" => $id);
            return $this->_ORA->ejecutaFunc("WFA_DOCTOS_PKG.getColumna",$bind);				
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }


    //listamos los adjuntos asociados al certificado por version 
    public function fun_listar_adjuntos($certificado_uv){
        
        try{

            $this->_SESION->setVariable("RSO_ADJUNTO",'');
            //$adjuntos = $this->_SESION->getVariable("RSO_ADJUNTO");
            //echo "<pre>";var_dump($adjuntos);echo "</pre>";exit();


            $version = $certificado_uv[0]['DOC_VERSION'];

            

            $wf = $this->_SESION->getVariable('WF');    
            $misAdjuntos = $this->fun_listar_adj_ultima_version($wf,$version);
        

            $CASO_PADRE =  $this->_SESION->getVariable('CASO_PADRE');   
            
            //echo "<pre>";var_dump($misAdjuntos);echo "</pre>";exit();

            $x=0;
            if($misAdjuntos!== false and isset($misAdjuntos)){
                foreach($misAdjuntos as $adj){ 

                    $mostrar[$x]['VAL'] = substr(md5(md5($adj['ADJ_ARCHIVO_EXP'])),3,5);
                    $mostrar[$x]['ID'] = $adj['ADJ_ARCHIVO_EXP'];
                    $mostrar[$x]['ADJ_NOMBRE'] = $this->getColumnaArchivoExpediente('Nombre', $adj['ADJ_ARCHIVO_EXP']); 
                    $mostrar[$x]['ADJ_HASH'] = $this->getColumnaArchivoExpediente('Hash', $adj['ADJ_ARCHIVO_EXP']);
                    $mostrar[$x]['MIME'] = $this->getColumnaArchivoExpediente('Mime', $adj['ADJ_ARCHIVO_EXP']);
                    
                    
                    
                    $data = array(
                        'ID'=>$mostrar[$x]['ID'], 
                        'ADJ_NOMBRE'=>$mostrar[$x]['ADJ_NOMBRE'], 
                        'CODIGO'=>NULL, 
                        'LINK'=>NULL, 
                        'TEMP'=>NULL, 
                        'MIME'=>NULL,
                        'ADJ_HASH'=>$mostrar[$x]['ADJ_HASH'],
                        'SGD'=>NULL,
                        'TIPO'=>'exp',
                        'GRABADO'=>'N'
                    );

                    $adjuntos = $this->_SESION->getVariable("RSO_ADJUNTO");
                    if(!is_array($adjuntos)){
                            $adjuntos = array();
                    }
                    $adjuntos[$mostrar[$x]['ADJ_HASH']] = $data;
                    $this->_SESION->setVariable("RSO_ADJUNTO",$adjuntos);
                    
                    
                    $this->_TEMPLATE->assign('ADJ',$mostrar[$x]);
                    if($this->CON_ELIMINAR){
                        $this->_TEMPLATE->parse('main.div_adjuntosResolucion.adjunto.eliminar');
                    }
                    $this->_TEMPLATE->parse('main.div_adjuntosResolucion.adjunto');
                    $x++;
                
                }
            }   


            //echo "<pre>";var_dump($adjuntos);echo "</pre>";exit();

            $this->_TEMPLATE->parse('main.div_adjuntosResolucion');
            return $this->_TEMPLATE->text('main.div_adjuntosResolucion');

        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }    
		
    }

 



   
    //ml: obtenemos todos los adjuntos de la ultima version     
    public function fun_listar_adj_ultima_version($wf,$version){

        try{

            $bind = array(":p_doc_id"=>$wf, 
                        ":p_gde_docu_doc_version" =>$version
                    );
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_ADJUNTOS_PKG.fun_lista_adj_ultima_version",'function', $bind);
            if ($cursor) {
                    while($r = $this->_ORA->FetchArray($cursor)){ 
                        
                    $r['ADJ_ID']=$r['ADJ_ID']; 
                    $r['ADJ_USUARIO']=$r['ADJ_USUARIO']; 
                    $r['ADJ_FECHA']=$r['ADJ_FECHA'];
                    $r['ADJ_ARCHIVO_EXP']=$r['ADJ_ARCHIVO_EXP'];
                    $r['GDE_DOCUMENTO_DOC_VERSION']=$r['GDE_DOCUMENTO_DOC_VERSION'];
                    $r['GDE_DOCUMENTO_DOC_ID']=$r['GDE_DOCUMENTO_DOC_ID'];     
                    $resAdjuntos[]=$r;    
                }
                $this->_ORA->FreeStatement($cursor);
            }    
                    
            return $resAdjuntos;

        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }   
    }


    //solo modifica el certificado
    public function fun_actualizar_adjuntos($wf,$tipo,$version,$adj_usuario){

        try{
        
            //var_dump("PASO 3: estoy en el modificar adjuntos");print("<br>");
            $adjuntos = $this->_SESION->getVariable("RSO_ADJUNTO");
            $misAdjuntos = $this->fun_mis_adjuntos_xversion($adj_usuario,$wf,$version);//mis adjuntos x version

            
            //IDENTIFICAMOS LOS ADJUNTOS QUE ELIMINO Y HACEMOS LA ELIMINACION POR VERSION
            if($misAdjuntos){
                $contador=0;
                for($x=0; $x<count($misAdjuntos); $x++){
                    //print_r($misAdjuntos[$x]['ADJ_ARCHIVO_EXP']);print("<br>"); 
                    if($adjuntos){//revisar
                        foreach($adjuntos as $key => $datos){
                            if($misAdjuntos[$x]['ADJ_ARCHIVO_EXP'] == $adjuntos[$key]['ID']){
                                $contador++;
                            }
                        }
                    }
                    if($contador == 0){
                        //print_r("Este ID se debe eliminar: ".$misAdjuntos[$x]['ADJ_ARCHIVO_EXP']);print("<br>");
                        $adj_archivo_exp = $misAdjuntos[$x]['ADJ_ARCHIVO_EXP'];
                        $this->fun_eliminar_adjunto($adj_archivo_exp,$version,$wf,$adj_usuario);
                    }
                    //print_r("mi contador de ".$misAdjuntos[$x]['ADJ_ARCHIVO_EXP']." es :".$contador);print("<br>"); 
                    $contador=0;
                }
            }

            //IDENTIFICAMOS LOS ADJUNTOS QUE AGREGO Y LOS AGREGAMOS POR VERSION 
            if($adjuntos){
                //var_dump("definido /// recorro /// modifico");
                foreach($adjuntos as $key => $datos){
                    //validamos si existe en la bbdd
                    $idAdjunto = $adjuntos[$key]['ID'];
                    $existe = $this->fun_existe_adjunto($wf,$version,$adj_usuario,$idAdjunto);
                    if($existe == 0){
                        //agregamos el adjunto
                        //var_dump("NO EXISTE, por lo tanto se agrega");print("<br>");
                        $this->fun_agregar_adjunto($adj_usuario,$wf,$idAdjunto,$version);
                    }
                } 
            }/*else{
                var_dump("NO definido /// elimino todo");print("<br>");
                var_dump($adjuntos);print("<br>");
            }*/

        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }

    //eliminamos los adjuntos segun la version 
    public function fun_eliminar_adjunto($adj_archivo_exp,$version,$wf,$adj_usuario){
        
        try{

            $bindAdjunto =  array(
                ":p_adj_usuario"=> $adj_usuario ,
                ":p_adj_archivo_exp" => $adj_archivo_exp,    
                ":p_gde_docu_doc_id"=>$wf,
                ":p_gde_docu_doc_version" => $version
            );
            
            $this->_ORA->ejecutaProc("GDE.GDE_ADJUNTOS_PKG.PRC_ELIMINAR_ADJUNTO",$bindAdjunto);
            $this->_ORA->Commit();
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }


    //obtenemos todos los adjuntos segun la version
    public function fun_mis_adjuntos_xversion($adj_usuario,$wf,$version){

        try{
        
            $bind = array(":p_doc_id"=>$wf, 
                    ":p_gde_docu_doc_version" =>$version,
                    ":p_adj_usuario" =>$adj_usuario
                );
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_ADJUNTOS_PKG.fun_lista_adjuntos_xversion",'function', $bind);
            if ($cursor) {
                    while($r = $this->_ORA->FetchArray($cursor)){ 
                        
                    $r['ADJ_ID']=$r['ADJ_ID']; 
                    $r['ADJ_USUARIO']=$r['ADJ_USUARIO']; 
                    $r['ADJ_FECHA']=$r['ADJ_FECHA'];
                    $r['ADJ_ARCHIVO_EXP']=$r['ADJ_ARCHIVO_EXP'];
                    $r['GDE_DOCUMENTO_DOC_VERSION']=$r['GDE_DOCUMENTO_DOC_VERSION'];
                    $r['GDE_DOCUMENTO_DOC_ID']=$r['GDE_DOCUMENTO_DOC_ID'];     
                    $resAdjuntos[]=$r;    
                }
                $this->_ORA->FreeStatement($cursor);
            }    
                    
            return $resAdjuntos;
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }


    //agregamos los adjunto 
    public function fun_agregar_adjunto($adj_usuario,$wf,$adj_id,$version){
        
        try{
        
            $id_adjunto = mt_rand(); //generamos un id aleatorio (validar que sea asi)
            $bindAdjunto =  array(
                ":p_id" => $id_adjunto, 
                ":p_adj_usuario"=> $adj_usuario ,
                ":p_adj_archivo_exp" => $adj_id,    
                ":p_gde_docu_doc_id"=>$wf,
                ":p_gde_docu_doc_version" => $version
            );
            
            $this->_ORA->ejecutaProc("GDE.GDE_ADJUNTOS_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
            $this->_ORA->Commit();
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }



    //buscamos si existe el adjunto 
    public function fun_existe_adjunto($wf,$version,$adj_usuario,$idAdjunto){

        try{
        
            $bind = array(":p_doc_id"=>$wf, 
                ":p_doc_version" =>$version,
                ":p_adj_usuario" =>$adj_usuario,
                ":p_adj_archivo_exp" =>$idAdjunto
                );
            $result = $this->_ORA->ejecutaFunc("GDE.GDE_ADJUNTOS_PKG.fun_existe_adjunto", $bind);
            
            //var_dump($result);exit();    
            return $result;   
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }        
    }


    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //inicio de la modificacion

 

    //ml: sabemos quien es el dueño del caso
    public function fun_get_dueno_caso($wf, $tipo){
        
        try{
            
            //$certificado_uv =  $this->fun_listar_ultima_version($wf,$tipo);   
            $certificado_uv =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);   
        
            if(isset($certificado_uv[0]['DOC_ENVIADO_A']) && $certificado_uv[0]['DOC_ENVIADO_A']){    
                return $certificado_uv[0]['DOC_ENVIADO_A'];
            }else if(isset($certificado_uv[0]['DOC_REDACTOR']) && $certificado_uv[0]['DOC_REDACTOR']){
                return $certificado_uv[0]['DOC_REDACTOR'];
            }else{
                return 'NE';//no exsite ni redactor , ni enviado a
            }

        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }

    //ml: sabemos si hay que modificar o versionar 
    public function fun_modifica_o_versiona($wf, $tipo){
        
        try{

            $mi_usuario     = $this->_SESION->USUARIO; 
            //$certificado_uv =  $this->fun_listar_ultima_version($wf,$tipo);
            $certificado_uv =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);

            /**
             * GENERA_NUEVA_VERSION : autorizado para generar una nueva version 
             * USUARIO_NO_AUTORIZADO : usuario no autorizado hacer cambios
             * MODIFICA_VERSION : autorizado para modificar version
             * SIN_DUEÑO : El caso no tiene dueño asignado
             * GV_NO_DEFINIDA : GENERA VERSION NO DEFINIDA
             */

            if(isset($certificado_uv[0]['DOC_ENVIADO_A']) && $certificado_uv[0]['DOC_ENVIADO_A']){  
                if($mi_usuario == $certificado_uv[0]['DOC_ENVIADO_A']){ //
                    //DUEÑO DEL CASO ES ENVIADO A
                    if($certificado_uv[0]['DOC_GENERA_VERSION'] == 'SI'){
                        return 'GENERA_NUEVA_VERSION';//genera nueva version 
                    }else if($certificado_uv[0]['DOC_GENERA_VERSION'] == 'NO'){
                        return 'MODIFICAR_VERSION';//modifica la version
                    }else{
                        return 'GV_NO_DEFINIDA';//GENERA VERSION NO DEFINIDO
                    }
                }else{
                    //USUARIO NO AUTORIZADO // OTRO DUEÑO DEL CASO
                    return "USUARIO_NO_AUTORIZADO";
                }
            }else if(isset($certificado_uv[0]['DOC_REDACTOR']) && $certificado_uv[0]['DOC_REDACTOR']){
                //EL DUEÑO DEL CASO ES EL REDACTOR
                if($mi_usuario == $certificado_uv[0]['DOC_REDACTOR']){
                    //USUARIO AUTORIZADO PARA MODIFICAR  
                    return 'MODIFICAR_VERSION';//modifica la version
                }else{//USUARIO NO AUTORIZADO
                    return 'USUARIO_NO_AUTORIZADO';
                }
            }else{
                return 'SIN_DUEÑO';//DUEÑO DEL CASO NO DEFINIDO
            }
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
        
    }
  

     //ml: sabemos si hay que modificar o versionar OLD
     public function fun_modifica_o_versiona_evb($wf, $tipo){
        
        try{

            $mi_usuario     = $this->_SESION->USUARIO; 
            //$certificado_uv =  $this->fun_listar_ultima_version($wf,$tipo);
            $certificado_uv =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);
            $estado_cambios = 'SI';
            //$con_cambios = 'NO';

            if(isset($certificado_uv[0]['DOC_ENVIADO_A']) && $certificado_uv[0]['DOC_ENVIADO_A']){  
                if($mi_usuario == $certificado_uv[0]['DOC_ENVIADO_A']){ //
                    
                    //aqui deberia controlar si se hizo algun cambio en el formulario
                    //usaremos una variable de sesion , un estado
                    //en caso de hacer cambio se debe agregar nueva version
                    //en caso de no hacer cambios solo se editan los cambios de evb  
                    //actualizar campo genera version para ambos casos, se debe usar como informacion y no como condicion  
                    
                    if($estado_cambios == 'SI'){//SI SE HICIERON CAMBIOS
                        
                    }else if($estado_cambios == 'NO'){//NO SE HICIERON CAMBIOS

                    }else{//NO EXISTE ESTA OPCION

                    }
                }else{
                    //usuario no autorizado a hacer cambios
                    //este caso no deberia presentarse , despues controlar segun CLAUDIO
                }
            }else if(isset($certificado_uv[0]['DOC_REDACTOR']) && $certificado_uv[0]['DOC_REDACTOR']){
                
                if($mi_usuario == $certificado_uv[0]['DOC_REDACTOR']){ //
                    //aqui deberia controlar si se hizo algun cambio en el formulario
                    //usaremos una variable de sesion , un estado
                    //en caso de hacer cambio se debe agregar nueva version
                    //en caso de no hacer cambios solo se editan los cambios de evb  
                    //actualizar campo genera version para ambos casos, se debe usar como informacion y no como condicion  
                }else{
                    //usuario no autorizado a hacer cambios
                    //este caso no deberia presentarse , despues controlar segun CLAUDIO
                }
            }else{
                //print_r("NEX");
                return 'NEX';//no exsite ni redactor , ni enviado a //NO SE HACE NADA
            }
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }



    //ml: sabemos si hay que modificar o versionar OLD
     public function fun_modifica_o_versiona_evb_OLD($wf, $tipo){
        
        try{

            $mi_usuario     = $this->_SESION->USUARIO; 
            //$certificado_uv =  $this->fun_listar_ultima_version($wf,$tipo);
            $certificado_uv =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);
            
            if(isset($certificado_uv[0]['DOC_ENVIADO_A']) && $certificado_uv[0]['DOC_ENVIADO_A']){  
                if($mi_usuario == $certificado_uv[0]['DOC_ENVIADO_A']){ //
                    /* 1.- El dueño es el enviado_a
                    2.- si doc_genera version es SI hay que versionar
                    3.- si doc_genera version es NO hay que modificar */
                    if($certificado_uv[0]['DOC_GENERA_VERSION'] == 'SI'){
                        return 'GNV';//genera nueva version 
                    }else if($certificado_uv[0]['DOC_GENERA_VERSION'] == 'NO'){
                        return 'MV';//modifica la version
                    }else{
                        //print_r("NEX");
                        return 'NEX';//no exite
                    }
                }else{
                    if($certificado_uv[0]['DOC_GENERA_VERSION'] == 'SI'){
                        //print_r("NADA");
                        return 'NADA';//genera nueva version 
                    }else if($certificado_uv[0]['DOC_GENERA_VERSION'] == 'NO'){
                        //print_r("NADA");
                        return 'NADA';//modifica la version
                    }else{
                        //print_r("NADA");
                        return 'NEX';//no exite
                    }
                }
            }else if(isset($certificado_uv[0]['DOC_REDACTOR']) && $certificado_uv[0]['DOC_REDACTOR']){
                
                if($mi_usuario == $certificado_uv[0]['DOC_REDACTOR']){ //
                    /* 1.- El dueño es el redactor
                    2.- si doc_genera version es SI hay que versionar
                    3.- si doc_genera version es NO hay que modificar */
                    if($certificado_uv[0]['DOC_GENERA_VERSION'] == 'SI'){
                        return 'MV-EVB';//modifica y asigna version a enviado_a 
                    }else if($certificado_uv[0]['DOC_GENERA_VERSION'] == 'NO'){
                        return 'MV';//modifica la version
                    }else{
                        //print_r("NEX");
                        return 'NEX';//no exite
                    }
                }else{
                    if($certificado_uv[0]['DOC_GENERA_VERSION'] == 'SI'){
                        //print_r("NADA");
                        return 'NADA';//genera nueva version 
                    }else if($certificado_uv[0]['DOC_GENERA_VERSION'] == 'NO'){
                        //print_r("NADA");
                        return 'NADA';//NO DEBERIA DARSE EL CASO
                    }else{
                        //print_r("NEX");
                        return 'NEX';//no exite
                    }
                }
            }else{
                //print_r("NEX");
                return 'NEX';//no exsite ni redactor , ni enviado a //NO SE HACE NADA
            }

        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }



   


   
    public function fun_modificar_certificado(){

        try{
        
            /* ///////////////// AQUI ESTOY      
            $destinatario = $this->_SESION->getVariable('DESTINATARIO');
            $eliminados = $this->_SESION->getVariable('CTL_ELIMINADOS');
            $arrayDestinatario          = explode("_,", substr($_POST["p_arrayDestinatario"], 0, -1)); 
            $arrayDestinatarioE     = explode("_,", substr($_POST["p_arrayDestinatarioE"], 0, -1));
            //var_dump($destinatario);
            var_dump($eliminados);
            var_dump($arrayDestinatario);
            exit();
            */

            //print_r("PASO 1: fun_modificar_certificado");print("<br>");
            
            $mi_usuario             = $this->_SESION->USUARIO; 
            $wf                     = $_POST['p_wf'];//doc_id 
            $tipo                   = $_POST['p_tipo'];//tipo_certificado
            $p_medio_envio          = 'SEIL';
            $p_doc_datos_sensibles  = $_POST['p_datosensibleSINO'];
            $p_gde_pri_id           = $_POST['p_privacidadTipo'];
            $p_doc_caso_padre       = $_POST['p_padre'];
            $p_tipo_envio           = $_POST['p_tipo_envio'];
            
            //data destinatario
            $arrayDestinatario          = explode("_,", substr($_POST["p_arrayDestinatario"], 0, -1)); 
            $arrayCargoDestinatario     = explode("_,", substr($_POST["p_arrayCargoDestinatario"], 0, -1)); 
            $arrayDireccion             = explode("_,", substr($_POST["p_arrayDireccion"], 0, -1)); 
            $arrayCorreo                = explode("_,", substr($_POST["p_arrayCorreo"], 0, -1));
            $arrayMiTipo                = explode("_,", substr($_POST["p_arrayMiTipo"], 0, -1)); 
            $arrayMiNombreDes           = explode("_,", substr($_POST["p_arrayMiNombreDes"], 0, -1)); 
        
            $arrayMiMedioEnvio          = explode("_,", substr($_POST["p_arrayMiMedioEnvio"], 0, -1)); 

        
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

            

            


            //data destinatario copia
            $arrayCopia             = explode("_,", substr($_POST["p_arrayCopia"], 0, -1)); 
            $arrayCargoCopia        = explode("_,", substr($_POST["p_arrayCargoCopia"], 0, -1)); 
            $arrayCopiaDireccion    = explode("_,", substr($_POST["p_arrayCopiaDireccion"], 0, -1)); 
            $arrayCopiaCorreo       = explode("_,", substr($_POST["p_arrayCopiaCorreo"], 0, -1)); 
            $arrayMiTipoCopia       = explode("_,", substr($_POST["p_arrayMiTipoCopia"], 0, -1)); 
            $arrayMiNombreDesCopia  = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1)); 

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

            
            //data destinatario a eliminar
            $arrayDestinatarioE     = explode("_,", substr($_POST["p_arrayDestinatarioE"], 0, -1)); 
            $arrayDestinatarioCE    = explode("_,", substr($_POST["p_arrayDestinatarioCE"], 0, -1)); 


        

            
            //obtenemos la ultima version del certificado
            //$certificado_uv     = $this->fun_listar_ultima_version($wf,$tipo);
            $certificado_uv     = $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);
            $genera_version     = $certificado_uv[0]['DOC_GENERA_VERSION'];
            $accion_caso        = $this->fun_modifica_o_versiona($wf, $tipo);  
            //echo "<pre>"; var_dump($accion_caso); echo "</pre>"; 
            
            $dataModificar = array (
                'medio_envio' => $p_medio_envio,
                'datos_sensibles' => $p_doc_datos_sensibles,
                'privacidad' => $p_gde_pri_id,
                'caso_padre' => $p_doc_caso_padre,
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1,
                'privacidad_anterior' => $certificado_uv[0]['GDE_PRIVACIDAD_PRI_ID'],
                'datos_sensibles_anterior' => $certificado_uv[0]['DOC_DATOS_SENSIBLES'],
                'certificado_uv' => $certificado_uv,
                'tipo_envio' => $p_tipo_envio
            );


            
            /**
             *CONTROLAR CAMBIOS EN DIFERENTTES MODULOS 
            *
            *
            * 
            */

            $this->_SESION->setVariable('ESTADO_DESTINATARIO', 2);
            //$this->_SESION->setVariable('ESTADO_PRIVACIDAD', 2);
            $this->_SESION->setVariable('ESTADO_EXPEDIENTE', 2);
            
                
            $estadoCuerpo       = $this->_SESION->getVariable('ESTADO_CUERPO'); 
            $estadoDestinatario = $this->_SESION->getVariable('ESTADO_DESTINATARIO'); 
            $estadoPrivacidad   = $this->_SESION->getVariable('ESTADO_PRIVACIDAD');
            $estadoExpediente   = $this->_SESION->getVariable('ESTADO_EXPEDIENTE');     
            
            //var_dump($estadoCuerpo."//".$estadoDestinatario."//".$estadoPrivacidad."//".$estadoExpediente);    
            //var_dump($estadoCuerpo."//".$estadoPrivacidad);    
            

            $dataCuerpo = array (
                'usar_plantilla' =>  $_POST['p_usarPlantillaSINO'],
                'usar_plantilla_anterior' =>  $certificado_uv[0]['DOC_USA_PLANTILLA'], 
                'file' => $_FILES,
                'pdf_anterior' => $certificado_uv[0]['DOC_PDF'],
                'cuerpo' => $_POST['p_cuerpo'],
                'cuerpo_anterior' => $certificado_uv[0]['DOC_CUERPO'],
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1

            );
            $dataDestinatarioControl = array(
                    'arrayDestinatario' => $dataDestinatario,
                    'arrayCopia' => $dataCopia,
                    'arrayDestinatarioE' => $arrayDestinatarioE,
                    'arrayDestinatarioCE' => $arrayDestinatarioCE,
                    'tipo_version' => $accion_caso,
                    'version' => $certificado_uv[0]['DOC_VERSION'],
                    'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
            );
            $dataExpediente = array(
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
            );  


            //echo "<pre>";var_dump($dataDestinatarioControl['arrayDestinatario']);echo "</pre>"; exit();
            //var_dump($dataDestinatarioControl['arrayDestinatarioE']); exit();
            //var_dump($dataDestinatarioControl['arrayCopia']); exit();
            //var_dump($dataCuerpo); exit();
            //var_dump($dataExpediente);exit();


            //controlamos CERTIFICADO
            $this->fun_chequear_certificado($dataModificar);   
            //CONTROLAMOS MODULO CUERPO    
            $this->fun_chequear_cuerpo($dataCuerpo);   
            //CONTROLAMOS MODULO EXPEDIENTES
            $this->fun_chequear_estado_expediente($dataExpediente);
            //CONTROLAMOS LOS DESTINATARIOS (y DESTINATARIO COPIA)
            $this->fun_chequear_estado_destinatario($dataDestinatarioControl);   


            return "OK";
            exit(); 
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
        
    }

    //ml: que acciuon se hace en el modificar
   /* public function fun_accion_modificar_certificado($accion,$dataModificar){

        /* 
         * GENERA_NUEVA_VERSION : autorizado para generar una nueva version 
         * USUARIO_NO_AUTORIZADO : usuario no autorizado hacer cambios
         * MODIFICAR_VERSION : autorizado para modificar version
         * SIN_DUEÑO : El caso no tiene dueño asignado
         * GV_NO_DEFINIDA : GENERA VERSION NO DEFINIDA
        */

     /*   switch ($accion) {
            case 'GENERA_NUEVA_VERSION':
                print_r('PASO :: USTED PUEDE GENERAR UNA NUEVA VERSION');
                //$this->fun_genera_nueva_version($dataModificar);
                //$this->fun_chequear_cuerpo($dataModificar);
                break;
            case 'MODIFICAR_VERSION':
                print_r("USTED PUEDE MODIFICAR LA VERSION ACTUAL");
                break;
            case 'USUARIO_NO_AUTORIZADO':
                print_r("USTED NO ESTA AUTORIZADO HACER CAMBIOS");
                break;
            case 'SIN_DUEÑO':
                print_r("ESTA VERSION NO TIENE DUEÑO DEFINIDO");
                break;
            case 'GV_NO_DEFINIDA':
                print_r("ESTA VERSION NO TIENE UN GENERA VERSION DEFINIDO");
                break;
        }

    }*/

    
    public function fun_actualizar_cuerpo($dataModificar){

        try{
        
            $wf = $this->_SESION->getVariable('WF');
            $tipo_certificado = $this->_SESION->getVariable('TIPO_CERTIFICADO');
        

            if(isset($dataModificar['file']['file']['tmp_name']) and $dataModificar['usar_plantilla'] == 'NO'){
                
                print_r('PASO :: CUERPO CON ADJUNTO');
                $blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob->WriteTemporary(file_get_contents($dataModificar['file']['file']['tmp_name']),OCI_TEMP_BLOB);
                $RES_REFERENCIA= null;
                $agregarPDF = "OK"; //para saber si debemos agregar el PDF adjunto
                $identifica = 2;//se agrega el blob 

                $bind =  array(
                    ":p_id"=> $wf,
                    ":p_doc_pdf" => $blob,
                    ":p_version" => $version,
                    ":p_identifica" => $identifica
                );
                $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_ACTUALIZAR_PDF_XVERSION",$bind); //hay que crear
                $this->_ORA->Commit();

            }else{
                print_r('PASO :: CUERPO SIN ADJUNTO');
                $blob=null;
                $RES_REFERENCIA = $this->_ORA->NewDescriptor(); 
                $RES_REFERENCIA->WriteTemporary($dataModificar['p_cuerpo'],OCI_TEMP_CLOB);
                $agregarPDF= "NOK";
                $identifica = 1; //hay que eliminar el blob 

                $bind =  array(
                    ":p_id"=> $wf,
                    ":p_version" => $version,
                    ":p_identifica" => $identifica,
                    ":p_cuerpo" => $RES_REFERENCIA,
                    ":p_usa_plantilla" => $_POST['p_usarPlantillaSINO'] 
                    
                );
                $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_BORRAR_PDF_XVERSION",$bind); 
                $this->_ORA->Commit();

            }
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
       
    }


 

    //ml: conservamos el cuerpo segun la version
    public function fun_conservar_cuerpo($dataModificar){
        
        try{

            $wf = $this->_SESION->getVariable('WF');
            $tipo_certificado = $this->_SESION->getVariable('TIPO_CERTIFICADO');
            $doc_pdf = $dataModificar['certificado_uv'][0]['DOC_PDF'];     
            $version = $dataModificar['version']; //ultima version
            $cuerpo = $dataModificar['cuerpo']; 
            $usa_plantilla = $dataModificar['certificado_uv'][0]['DOC_USA_PLANTILLA']; 


            if(isset($doc_pdf)){ //existe pdf
                
                $identifica = 2; //se conserva el blob de la version anterior
                $mi_blob = $doc_pdf->load();
                $blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob->WriteTemporary($mi_blob,OCI_TEMP_BLOB);
                $bind =  array(
                    ":p_id"=> $wf,
                    ":p_doc_pdf" => $blob,
                    ":p_version" => $version,
                    ":p_identifica" => $identifica
                );
                $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_ACTUALIZAR_PDF_XVERSION",$bind); //hay que crear
                $this->_ORA->Commit();     
                
                
                
            }else{
                
                    $identifica = 1;
                    $bind =  array(
                        ":p_id"=> $wf,
                        ":p_version" => $version,
                        ":p_identifica" => $identifica,
                        ":p_cuerpo" => $cuerpo,
                        ":p_usa_plantilla" => $usa_plantilla 
                        
                    );
                    $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_BORRAR_PDF_XVERSION",$bind); 
                    $this->_ORA->Commit();
                
                }
            
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }

    }




    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||| CHEQUEAR DIFERENTES MODULOS |||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
   


    //ML: CHEQUEAMOS LOS EXPEDIENTES :: acciones a realizar segun estado del modulo
    public function fun_chequear_estado_expediente($dataExpediente){

        try{

            //echo "<pre>";var_dump("PASO :: CONTROL DE EXPEDIENTES");echo"</pre>";
            $wf                 = $this->_SESION->getVariable('WF');
            $tipo_certificado   = $this->_SESION->getVariable('TIPO_CERTIFICADO');
            $estadoExpediente   = $this->_SESION->getVariable('ESTADO_EXPEDIENTE');
            $mi_usuario         = $this->_SESION->USUARIO; 

            //identificamos la version que vamos a trabajar
            if($dataExpediente['tipo_version'] == 'GENERA_NUEVA_VERSION'){
                $version = $dataExpediente['nueva_version'];
            }else if($dataExpediente['tipo_version'] == 'MODIFICAR_VERSION'){
                $version = $dataExpediente['version'];
            }

            switch ($estadoExpediente) {
                case 1: 
                    //NO SE HIZO CAMBIOS EN LOS DESTINATARIOS
                    if($dataCertificado['tipo_version'] == 'GENERA_NUEVA_VERSION'){
                        $this->fun_actualizar_adjuntos($wf,$tipo_certificado,$version,$mi_usuario); 
                    }    
                break;
                
                case 2:
                
                    //SE HICIERON CAMBIOS EN LOS DESTINATARIOS
                    $this->fun_actualizar_adjuntos($wf,$tipo_certificado,$version,$mi_usuario); 
                
                break;    
            }        

        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }    


    }
    
    //ML: CHEQUEAMOS LOS DESTINATARIOS :: acciones a realizar segun estado del modulo
    public function fun_chequear_estado_destinatario($dataDestinatario){

        try{
        
            //echo "<pre>";var_dump("PASO :: CONTROL DE DESTINATARIOS");echo"</pre>";
            $wf                 = $this->_SESION->getVariable('WF');
            $tipo_certificado   = $this->_SESION->getVariable('TIPO_CERTIFICADO');
            $estadoDestinatario   = $this->_SESION->getVariable('ESTADO_DESTINATARIO');
            
            //identificamos la version que vamos a trabajar
            if($dataDestinatario['tipo_version'] == 'GENERA_NUEVA_VERSION'){
                $version = $dataDestinatario['nueva_version'];
            }else if($dataDestinatario['tipo_version'] == 'MODIFICAR_VERSION'){
                $version = $dataDestinatario['version'];
            }

            //echo "<pre>";var_dump($dataCopia);echo"</pre>";exit();
            //echo "<pre>";var_dump($dataDestinatario);echo"</pre>";exit();

            switch ($estadoDestinatario) {
                case 1: 
                    //NO SE HIZO CAMBIOS EN LOS DESTINATARIOS
                    if($dataCertificado['tipo_version'] == 'GENERA_NUEVA_VERSION'){
                        //GENERAMOS UNA NUEVA VERSION SIN CAMBIOS

                        $arrayDestinatarioE2    = $dataDestinatario['arrayDestinatarioE'];
                        $dataDestinatario2      = $dataDestinatario['arrayDestinatario'];
                        $arrayDestinatarioCE2    = $dataDestinatario['arrayDestinatarioCE'];
                        $dataCopia2              = $dataDestinatario['arrayCopia'];
                        
                        if($dataDestinatario2){
                            $this->fun_actualizar_destinatarios($wf,$version,$arrayDestinatarioE2,$dataDestinatario2);
                        }
                        if($dataCopia2){
                            $this->fun_actualizar_destinatarios($wf,$version,$arrayDestinatarioCE2,$dataCopia2);
                        }

                    }
                    break;      
                
                case 2:
                    //SE HICIERON CAMBIOS EN LOS DESTINATARIOS
                    //echo "<pre>";var_dump("PASO :: ENTRAMOS AL ESTADO 2 " );echo"</pre>";
                    //echo "<pre>";var_dump($dataDestinatario);echo"</pre>";exit();

                    $arrayDestinatarioE2     = $dataDestinatario['arrayDestinatarioE'];
                    $dataDestinatario2       = $dataDestinatario['arrayDestinatario'];
                    $arrayDestinatarioCE2    = $dataDestinatario['arrayDestinatarioCE'];
                    $dataCopia2              = $dataDestinatario['arrayCopia'];
                    
                    if($dataDestinatario2){
                        $this->fun_actualizar_destinatarios($wf,$version,$arrayDestinatarioE2,$dataDestinatario2);
                    }
                    if($dataCopia2){
                        $this->fun_actualizar_destinatarios($wf,$version,$arrayDestinatarioCE2,$dataCopia2);
                    }
                    
                    break;     
            } 
            
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }

    
    //ML: CHEQUEAMOS EL CERTIFICADO [PRIVACIDAD]:: acciones a realizar segun estado del modulo
    //estadoPrivacidad :: es el estado de la seccion si se hizo cambios o no  
    public function fun_chequear_certificado($dataCertificado){
        
        try{
        
            //fun_genera_nueva_version
            //var_dump($dataCertificado);exit();
            //var_dump($dataCertificado['tipo_envio']);exit();

            $wf                 = $this->_SESION->getVariable('WF');
            $tipo_certificado   = $this->_SESION->getVariable('TIPO_CERTIFICADO');
            $estadoPrivacidad   = $this->_SESION->getVariable('ESTADO_PRIVACIDAD');
            
            //identificamos la version que vamos a trabajar
            if($dataCertificado['tipo_version'] == 'GENERA_NUEVA_VERSION'){
                $version = $dataCertificado['nueva_version'];
            }else if($dataCertificado['tipo_version'] == 'MODIFICAR_VERSION'){
                $version = $dataCertificado['version'];
            }

            //definimos usar_plantilla
            if($dataCertificado['usar_plantilla'] == 'undefined'){
                $usar_plantilla = $dataCertificado['certificado_uv'][0]['DOC_USA_PLANTILLA'];
            }else{
                $usar_plantilla = $dataCertificado['usar_plantilla'];
            }    

            //var_dump($dataCertificado);exit();
            //var_dump($estadoPrivacidad);exit();

            switch ($estadoPrivacidad) {
                case 1: 
                    //print_r('PASO :: PRIVACIDAD NO SE HIZO CAMBIOS');
                    if($dataCertificado['tipo_version'] == 'GENERA_NUEVA_VERSION'){
                        //generamos nueva version con los datos antiguos

                        $dataNuevaVersion = array(
                            "datos_sensibles" => $dataCertificado['datos_sensibles_anterior'],
                            "privacidad" => $dataCertificado['privacidad_anterior'],
                            "wf" => $wf,
                            "tipo_certificado" => $tipo_certificado,
                            "caso_padre" => $dataCertificado['caso_padre'],
                            "version" => $version,
                            "usa_plantilla" => $usar_plantilla,
                            "tipo_envio" => $dataCertificado['tipo_envio']
                        );
        
                        $this->fun_agregar_version2($dataNuevaVersion);

                    }
                break;      
                
                case 2:
                    //echo "<pre>";var_dump('PASO ::ESTADO 2');echo'</pre>'; 
                    //echo "<pre>"; var_dump('PASO :: TIPO VERSION'.$dataCertificado['tipo_version']);print('<br>'); echo "</pre>";
                    if($dataCertificado['tipo_version'] == 'GENERA_NUEVA_VERSION'){
                        $dataNuevaVersion = array(
                            "datos_sensibles" => $dataCertificado['datos_sensibles'],
                            "privacidad" => $dataCertificado['privacidad'],
                            "wf" => $wf,
                            "tipo_certificado" => $tipo_certificado,
                            "caso_padre" => $dataCertificado['caso_padre'],
                            "version" => $version,
                            "usa_plantilla" => $usar_plantilla,
                            "tipo_envio" => $dataCertificado['tipo_envio']
                        );
        
                        $this->fun_agregar_version2($dataNuevaVersion);
                    
                        
                    }else if($dataCertificado['tipo_version'] == 'MODIFICAR_VERSION'){
                            //echo "<pre>";var_dump('PASO :: hay que modifica la version actual :'.$version);echo "</pre>";
                            //modifica la version actual
                            $tipo_envio = $dataCertificado['tipo_envio'];
                            $p_doc_datos_sensibles = $dataCertificado['datos_sensibles'];
                            $p_gde_pri_id = $dataCertificado['privacidad'];
                            $p_wf = $wf;
                            $this->fun_actualizar_certificado($p_doc_datos_sensibles,$p_gde_pri_id,$p_wf,$version,$tipo_envio);   

                    }   
                        
                        

                break;    
            }        
            
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }


    //ml: CHEQUEAMOS EL CUERPO DEL CERTIFICADO :: acciones a realizar segun estado del modulo
    public function fun_chequear_cuerpo($dataCuerpo){

        try{

            $estado             = $this->_SESION->getVariable('ESTADO_CUERPO'); 
            $wf                 = $this->_SESION->getVariable('WF');
            $tipo_certificado   = $this->_SESION->getVariable('TIPO_CERTIFICADO');
            $doc_pdf            = $dataCuerpo['certificado_uv'][0]['DOC_PDF'];     
            $cuerpo_anterior    = $dataCuerpo['cuerpo_anterior']; 
            $pdf_anterior       = $dataCuerpo['pdf_anterior'];

            //definimos el campo usa plantilla
            if($dataCuerpo['usar_plantilla'] == 'undefined'){
                $usar_plantilla  = $dataCuerpo['usar_plantilla_anterior'];
            }else{
                $usar_plantilla  = $dataCuerpo['usar_plantilla'];
            }
            
            //identificamos la version que vamos a trabajar
            if($dataCuerpo['tipo_version'] == 'GENERA_NUEVA_VERSION'){
                $version = $dataCuerpo['nueva_version'];
            }else if($dataCuerpo['tipo_version'] == 'MODIFICAR_VERSION'){
                $version = $dataCuerpo['version'];
            }
        

            //print_r('USA_PLANTILLA :: '.$usar_plantilla);
            //print_r('VERSION :: '.$version);
            //var_dump($dataCuerpo);exit();
            //var_dump($dataCuerpo['file']);exit();
            //var_dump("ESTADO::".$estado);


        
            switch ($estado) {
                case 1: 
                    //print_r('PASO :: CUERPO NO SE HIZO CAMBIOS');
                    //$this->fun_conservar_cuerpo($dataModificar);
                    //solo necesito actualizar en el caso que sea nueva version    
                    if($dataCuerpo['tipo_version'] =='GENERA_NUEVA_VERSION'){
                        if(isset($pdf_anterior)){ //existe pdf
                            
                            $identifica = 2; //se conserva el blob de la version anterior
                            $mi_blob = $pdf_anterior->load();
                            $blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                            $blob->WriteTemporary($mi_blob,OCI_TEMP_BLOB);
                            $bind =  array(
                                ":p_id"=> $wf,
                                ":p_doc_pdf" => $blob,
                                ":p_version" => $version,
                                ":p_identifica" => $identifica,
                                ":p_usa_pantilla" => $usar_plantilla 
                            );
                            $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_ACTUALIZAR_CUERPO",$bind); //hay que crear
                            $this->_ORA->Commit();     
                            
                            
                            
                        }else{
                                $identifica = 1;
                                $bind =  array(
                                    ":p_id"=> $wf,
                                    ":p_version" => $version,
                                    ":p_identifica" => $identifica,
                                    ":p_cuerpo" => $cuerpo_anterior,
                                    ":p_usa_plantilla" => $usar_plantilla 
                                );
                                $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_BORRAR_PDF_XVERSION",$bind); 
                                $this->_ORA->Commit();
                            
                            }
                    }    

                    
                    break;
                    
                
                //SE HICIERON CAMBIOS EN EL CUERPO    
                case 2:
                    //print_r("PASO :: CUERPO CON CAMBIOS");
                    //$this->fun_actualizar_cuerpo($dataModificar,$version);
                    
                    
                    //verificamos si el cambio es un archivo adjunto
                    if(isset($dataCuerpo['file']['file']['tmp_name']) and $dataCuerpo['usar_plantilla'] == 'NO'){
                        
                        //print_r('PASO :: CUERPO CON ADJUNTO');
                        $blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                        $blob->WriteTemporary(file_get_contents($dataCuerpo['file']['file']['tmp_name']),OCI_TEMP_BLOB);
                        $RES_REFERENCIA= null;
                        $agregarPDF = "OK"; //para saber si debemos agregar el PDF adjunto
                        $identifica = 2;//se agrega el blob 
            
                        $bind =  array(
                            ":p_id"=> $wf,
                            ":p_doc_pdf" => $blob,
                            ":p_version" => $version,
                            ":p_identifica" => $identifica,
                            ":p_usa_pantilla" => $usar_plantilla 
                        );
                        $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_ACTUALIZAR_CUERPO",$bind); //hay que crear
                        $this->_ORA->Commit();
            
                    }else{//en caso contrario es un cuerpo nuevo
                        
                        $blob=null;
                        $RES_REFERENCIA = $this->_ORA->NewDescriptor(); 
                        $RES_REFERENCIA->WriteTemporary($dataCuerpo['cuerpo'],OCI_TEMP_CLOB);
                        $agregarPDF= "NOK";
                        $identifica = 1; //hay que eliminar el blob 
            
                        $bind =  array(
                            ":p_id"=> $wf,
                            ":p_version" => $version,
                            ":p_identifica" => $identifica,
                            ":p_cuerpo" => $RES_REFERENCIA,
                            ":p_usa_plantilla" => $usar_plantilla 
                            
                        );
                        $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_BORRAR_PDF_XVERSION",$bind); 
                        $this->_ORA->Commit();
            
                    }




                    break;
                
            }
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
        
        
    }


    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
   


    //ml: funcion para generar nueva version del caso
    /*public function fun_genera_nueva_version($dataModificar){
        
        $version = $this->_SESION->getVariable('VERSION_CERTIFICADO')+1;//pasa a ser una nueva version
        $wf = $this->_SESION->getVariable('WF');
        $tipo_certificado = $this->_SESION->getVariable('TIPO_CERTIFICADO');

        if($dataModificar['usar_plantilla'] == 'undefined'){
            $usa_plantilla = $dataModificar['certificado_uv'][0]['DOC_USA_PLANTILLA'];
        }else{
            $usa_plantilla = $dataModificar['usar_plantilla'];
        }    

        $dataNuevaVersion = array(
            "datos_sensibles" => $dataModificar['datos_sensibles'],
            "privacidad" => $dataModificar['privacidad'],
            "wf" => $wf,
            "tipo_certificado" => $tipo_certificado,
            "caso_padre" => $dataModificar['caso_padre'],
            "version" => $version,
            "usa_plantilla" => $usa_plantilla
        );

        $this->fun_agregar_version2($dataNuevaVersion);   

    
            
    }
    */
 

    public function fun_agregar_version2($dataVersion){

        try{

            //var_dump($dataVersion); exit();

            $p_id                   =  $dataVersion['wf'];
            $ultima_version         =  $dataVersion['version'];//para que pase a ser una nueva version
            $redactor               =  $this->_SESION->USUARIO; 
            $datos_sensibles        =  $dataVersion['datos_sensibles'];
            $privacidad             =  $dataVersion['privacidad'];
            $tipo_certificado       =  $dataVersion['tipo_certificado'];
            $caso_padre             =  $dataVersion['caso_padre'];
            $genera_version         = 'SI'; 
            $estado_doc_id          = 'visac';
            $dis_secuencia          =  1; 
            $usa_plantilla          =  $dataVersion['usa_plantilla'];
            $tipo_envio             =  $dataVersion['tipo_envio'];
            
    
            $bind =  array( ":p_id"=> $p_id,
                            ":p_doc_version"=> $ultima_version,//ok
                            ":p_doc_datos_sensibles"=>$datos_sensibles,//ok
                            ":p_doc_usa_plantilla"=>$usa_plantilla,
                            ":p_gde_tipdoc_id"=>$tipo_certificado,//ok
                            ":p_gde_dis_secuencia"=>$dis_secuencia,
                            ":p_gde_estdoc_id"=>$estado_doc_id,// ??
                            ":p_gde_pri_id"=>$privacidad,//ok
                            ":p_doc_genera_version"=> $genera_version,//ok
                            ":p_doc_caso_padre"=> $caso_padre,//ok
                            ":p_doc_redactor"=>$redactor,//0k
                            //":p_doc_enviado_a"=>$p_doc_enviado_a,
                            ":p_doc_ultima_version"=>$ultima_version,//0k
                            ":p_tipo_envio"=>$tipo_envio
                            //":p_doc_cuerpo"=> $RES_REFERENCIA
                            //,":p_doc_pdf" => null
                            );
            $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_AGREGAR_CERTIFICADO3",$bind);
            $this->_ORA->Commit();

        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
        
     }



    //ml: aqui actualizamos el destinatario o lo agregamos si corresponde
    public function fun_actualizar_destinatarios($wf,$version,$arrayDestinatarioE,$dataDestinatario){
        

        try{

            //echo "<pre>";print_r("PASO ::  QUEMREMOS ACTUALIZAR DESTINATARIOS");echo("</pre>");    
            //echo "<pre>";var_dump($arrayDestinatarioE);echo "</pre>"; exit();                               
            //print_r(count($dataDestinatario['destinatario']));                    
            //echo "<pre>";var_dump($dataDestinatario);echo "</pre>";exit();                               

            //$p_medio_envio = 'SEIL'; //hay que validar este valor
            foreach($dataDestinatario['destinatario'] as $key => $rut){
                if (in_array($rut, $arrayDestinatarioE)){
                    //print_r("PASO 3.1 :  tenemos que eliminar");print("<br>");   
                    
                    //print_r("existe :".$rut);print('<br>');
                    //para eliminar hay que tener en cuenta el tipo de entidad 
                    // 1.- porque un usuario puede ser de mas de una entidad
                    $tipo_entidad = $dataDestinatario['tipo'][$key];
                    $this->fun_eliminar_distribucion($wf,$rut,$version,$tipo_entidad); //REVISAR


                }else{
                    //print_r("PASO 3.2 :  fun_actualizar_destinatarios");print("<br>");   
                    //actualizamos debemos tener en cuenta
                    //1.- debemos actualizar si existe en la bbdd segun la version
                    //2.- en caso de que no exista en la version debemos agregarlo     
                    //3.- en ambos caso hay que tener en cuenta el tipo de entidad 

                    
                    $tipo_entidad   = $dataDestinatario['tipo'][$key];
                    $cargo          = $dataDestinatario['cargo'][$key];
                    $direccion      = $dataDestinatario['direccion'][$key];
                    $correo         = $dataDestinatario['correo'][$key];
                    $con_copia      = $dataDestinatario['con_copia'];
                    $nombre         = $dataDestinatario['nombre'][$key];
                    $p_medio_envio  = $dataDestinatario['medio_envio'][$key];
            
                    //print_r($nombre."//".$rut."//".$wf."//".$version."//".$tipo_entidad."//".$cargo."//".$direccion."//".$correo."//".$con_copia."//".$nombre);print("<br>");
                    
                    
                    $bind = array(":p_doc_id"=> $wf,
                            ":p_version" => $version,
                            ":p_tipo_entidad" => $tipo_entidad,
                            ":p_rut" => $rut
                        );
                    
                    $existe = $this->_ORA->ejecutaFunc("GDE.gde_distribucion_pkg.fun_existe_distribucion", $bind);
                    
                    //print_r("Existe ? : ".$existe);print("<br>");
                    

                    if($existe > 0){
                        //print_r("PASO 3.2.1: entro a actualizar destinatario");print("<br>");
                        //existe por lo tanto hay que actualizar // REVISAR
                        $this->fun_accion_actualizar_destinatarios($wf,$rut,$cargo,$direccion,$correo,$tipo_entidad,$p_medio_envio,$version,$con_copia,$nombre);

                    }else{
                        //print_r("PASO 3.2.2: entro a agregar destinatario");print("<br>");
                        //no existe por lo tanto hay que agregarlo // REVISAR
                        $this->fun_agregar_destinatario($wf,$rut,$cargo,$direccion,$correo,$tipo_entidad,$p_medio_envio,$version,$con_copia,$nombre);
                    }

                    

                }   
            }        
            
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }

    //ml: existe destinatario
    public function fun_existe_destinatarios($wf,$version,$tipo_entidad,$rut){

        try{

            $bind = array(
                ":p_doc_id "=> $wf,
                ":p_version" => $version,
                ":p_tipo_entidad" => $tipo_entidad,
                ":p_rut" => $rut);

            $cursor = $this->_ORA->retornaCursor("GDE.GDE_DISTRIBUCION_PKG.fun_existe_distribucion",'function', $bind);
            if ($cursor) {
                    while($r = $this->_ORA->FetchArray($cursor)){ 
                    $r['cantidad']=$r['cantidad']; 
                    $respuesta[]=$r;    
                }
                $this->_ORA->FreeStatement($cursor);
            }    
                    
            return $respuesta;
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }

    //ml: accion de actualizar destinatarios
    public function fun_accion_actualizar_destinatarios($wf,$rut,$cargo,$direccion,$correo,$tipo_entidad,$medio_envio,$version,$con_copia,$nombre){
       
        try{
        
            $bind =  array(
                ":p_rut" => $rut,
                ":p_correo" => $correo,
                ":p_cargo" => $cargo,
                ":p_version" => $version,
                ":p_direccion" => $direccion,
                ":p_medio_envio" => $medio_envio,
                ":p_tipo_entidad" => $tipo_entidad,
                ":p_con_copia" => $con_copia,
                ":p_nombre" => $nombre,
                ":p_doc_id" => $wf
            );

            $this->_ORA->ejecutaProc("GDE.GDE_DISTRIBUCION_PKG.PRC_ACTUALIZAR_DISTRIBUCION",$bind);
            $this->_ORA->Commit();
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }

    public function fun_agregar_destinatario($wf,$rutDestinatario,$cargoDestinatario,
            $direccion,$correo,$tipo_entidad,$p_medio_envio,$version,$con_copia,$nombre){

        try{        

            $dis_secuencia = mt_rand(); //validar que sea asi  
            $bindDistribucion =  array(
                ":p_dis_secuencia"=> $dis_secuencia,
                ":p_doc_id"=>$wf,
                ":p_doc_version"=>$version,
                ":p_dis_cargo"=>$cargoDestinatario,
                ":p_dis_rut"=>$rutDestinatario,
                ":p_dis_con_copia"=>$con_copia,
                ":p_dis_direccion"=>$direccion,
                ":p_dis_correo"=>$correo,
                ":p_dis_medio_envio"=>$p_medio_envio,
                ":p_dis_dv"=>$this->fun_digito_verificador($rutDestinatario),
                ":p_dis_tipo_entidad"=>$tipo_entidad,
                ":p_dis_nombre"=>$nombre
            );    
            $this->_ORA->ejecutaProc("GDE.GDE_DISTRIBUCION_PKG.PRC_AGREGAR_DISTRIBUCION",$bindDistribucion);
            $this->_ORA->Commit();

        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }

    }


     //ml: metodo para agregar nueva version de destinatarios [NO SE ESTA OCUPANDO]
    public function fun_agregar_nv_destinatario($wf,$arrayDestinatario,$arrayCargoDestinatario,$arrayDireccion,$arrayCorreo,$arrayMiTipo,$p_medio_envio,$version,$con_copia){

        try{

            $p_doc_version = $version;

            /*echo "<pre>"; var_dump($wf); echo "</pre>";
            echo "<pre>"; var_dump($version); echo "</pre>";
            echo "<pre>"; var_dump($arrayDestinatario); echo "</pre>";
            echo "<pre>"; var_dump($arrayCargoDestinatario); echo "</pre>";
            echo "<pre>"; var_dump($arrayDireccion); echo "</pre>";
            echo "<pre>"; var_dump($arrayCorreo); echo "</pre>";
            echo "<pre>"; var_dump($arrayMiTipo); echo "</pre>";
            */
            //exit();


                //||||||||||||| AGREGAMOS LA DISTRIBUCION |||||||||||||||||||||||
                if((count($arrayDestinatario)===count($arrayCargoDestinatario)) and count($arrayCargoDestinatario)>0){
                    print_r("PASO 4: PUEDE AGREGAR DESTINATARIOS");print("<br>");
                    for($x=0;$x<count($arrayDestinatario);$x++){
                        if($arrayDestinatario[$x] != "" and $arrayDestinatario[$x] != null and $arrayDestinatario[$x] != 'undefined'){
                            $dis_secuencia = mt_rand(); //validar que sea asi
                            if($arrayCorreo[$x] == 'undefined'){
                                $correoDestinatario = null;
                            }else{
                                $correoDestinatario = $arrayCorreo[$x];
                            }

                            //print_r("agregamos a :" .$arrayDestinatario[$x] );print("<br>");

                            /*
                            $bindDistribucion =  array(":p_dis_secuencia"=> $dis_secuencia,
                            ":p_doc_id"=>$wf,
                            ":p_doc_version"=>$p_doc_version,
                            ":p_dis_cargo"=>$arrayCargoDestinatario[$x],
                            ":p_dis_rut"=>$arrayDestinatario[$x],
                            ":p_dis_con_copia"=>$con_copia,
                            ":p_dis_direccion"=>$arrayDireccion[$x],
                            ":p_dis_correo"=>$correoDestinatario,
                            ":p_dis_medio_envio"=>$p_medio_envio,
                            ":p_dis_dv"=>$this->fun_digito_verificador($arrayDestinatario[$x]),
                            ":p_dis_tipo_entidad"=>$arrayMiTipo[$x]
                            );    
                            $this->_ORA->ejecutaProc("GDE.GDE_DISTRIBUCION_PKG.PRC_AGREGAR_DISTRIBUCION",$bindDistribucion);
                            $this->_ORA->Commit();
                            */
                        }
                    }    
                }
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
        
    }


   

    //ml: ACTUALIZAMOS EL CERTIFICADO de la version EVB
    public function fun_actualizar_cert_evb($data){
        
        try{
        
            $bind =  array(":p_id"=> $data['wf'],
            ":p_dato_sensible" => $data['datos_sensibles'],
            ":p_gde_pri_id" => $data['privacidad'],
            ":p_version" => $data['version'],
            ":p_enviado_a" => $data['enviado_a']
            );
            
            $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_ACTUALIZAR_CERT_EVB",$bind);
            $this->_ORA->Commit();
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }    
        
    }

    //ACTUALIZAMOS EL CERTIFICADO de la version
    public function fun_actualizar_certificado($p_doc_datos_sensibles,$p_gde_pri_id,$p_wf,$version,$tipo_envio){
        
        try{

            $bind =  array(":p_id" => $p_wf,
                        ":p_dato_sensible" => $p_doc_datos_sensibles,
                        ":p_gde_pri_id" => $p_gde_pri_id,
                        ":p_version" => $version,
                        ":p_tipo_envio" => $tipo_envio 
                        );
            
            $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_ACTUALIZAR_CERTIFICADO",$bind);
            $this->_ORA->Commit();
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }




//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

       //ML:PENDIENTE >> aqui debemos agregar una nueva version
    public function fun_agregar_version($p_doc_datos_sensibles,$p_gde_pri_id,$wf,$tipo,$p_doc_caso_padre,$version){

       try{

            //$p_id = $certificado[0]['DOC_ID']; 
            $p_id = $wf;
            $ultima_version =  $version;//para que pase a ser una nueva version
            //$adj_usuario = "CULLOA"; 
            $adj_usuario =$this->_SESION->USUARIO; 
            $p_doc_datos_sensibles = $p_doc_datos_sensibles;
            $p_gde_pri_id = $p_gde_pri_id;
            $tipo = $tipo;
            $p_doc_caso_padre = $p_doc_caso_padre;
            $p_doc_genera_version = 'SI'; 
            $p_gde_estdoc_id = 'visac';
            $p_gde_dis_secuencia = 1; 

            

            $bind =  array(":p_id"=> $p_id,
            ":p_doc_version"=> $ultima_version,//ok
            ":p_doc_datos_sensibles"=>$p_doc_datos_sensibles,//ok
            //":p_doc_usa_plantilla"=>$p_doc_usa_plantilla,
            ":p_gde_tipdoc_id"=>$tipo,//ok
            ":p_gde_dis_secuencia"=>$p_gde_dis_secuencia,
            ":p_gde_estdoc_id"=>$p_gde_estdoc_id,// ??
            ":p_gde_pri_id"=>$p_gde_pri_id,//ok
            ":p_doc_genera_version"=> $p_doc_genera_version,//ok
            ":p_doc_caso_padre"=> $p_doc_caso_padre,//ok
            ":p_doc_redactor"=>$adj_usuario,//0k
            //":p_doc_enviado_a"=>$p_doc_enviado_a,
            ":p_doc_ultima_version"=>$ultima_version//0k
            //":p_doc_cuerpo"=> $RES_REFERENCIA
            //,":p_doc_pdf" => null

            );
            $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_AGREGAR_CERTIFICADO2",$bind);
            $this->_ORA->Commit();
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
       
    }


    //ml: agregamos nueva version EVB modificar
    public function fun_agregar_version_evb($data){

        try{

            //$p_id = $certificado[0]['DOC_ID']; 
            $p_id = $data['wf'];
            $ultima_version =  $data['version'];//para que pase a ser una nueva version
            //$adj_usuario = "CULLOA"; 
            $adj_usuario =$this->_SESION->USUARIO; 
            $p_doc_datos_sensibles = $data['datos_sensibles'];
            $p_gde_pri_id = $data['privacidad'];
            $tipo = $data['tipo_certificado'];
            $p_doc_caso_padre = $data['caso_padre'];
            $p_doc_genera_version = 'SI'; 
            $p_gde_estdoc_id = 'visac';
            $p_gde_dis_secuencia = 1; 
            $p_doc_enviado_a = $data['enviado_a'];
            
    
            $bind =  array(":p_id"=> $p_id,
            ":p_doc_version"=> $ultima_version,//ok
            ":p_doc_datos_sensibles"=>$p_doc_datos_sensibles,//ok
            //":p_doc_usa_plantilla"=>$p_doc_usa_plantilla,
            ":p_gde_tipdoc_id"=>$tipo,//ok
            ":p_gde_dis_secuencia"=>$p_gde_dis_secuencia,
            ":p_gde_estdoc_id"=>$p_gde_estdoc_id,// ??
            ":p_gde_pri_id"=>$p_gde_pri_id,//ok
            ":p_doc_genera_version"=> $p_doc_genera_version,//ok
            ":p_doc_caso_padre"=> $p_doc_caso_padre,//ok
            ":p_doc_redactor"=>$adj_usuario,//0k
            ":p_doc_enviado_a"=>$p_doc_enviado_a,
            ":p_doc_ultima_version"=>$ultima_version//0k
            //":p_doc_cuerpo"=> $RES_REFERENCIA
            //,":p_doc_pdf" => null
    
            );
            $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_AGREGAR_CERTIFICADO_EVB",$bind);
            $this->_ORA->Commit();
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
        
     }
 


    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||| VER VERSIONES ||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
   

    //ml: chequeamos todas las versiones del certificado
    public function fun_chequear_versiones_wf($wf,$tipo){

        try{
            $todas = $this->fun_listar_certificado($wf,$tipo);
            //echo"<pre>";var_dump($todas);echo"<pre>";exit();
            $listado = "";
            if(isset($todas) && !empty($todas) && is_array($todas)){
                foreach($todas as $key => $datos){
                    $listado .= "<li>Versión ".$todas[$key]['DOC_VERSION'].' ('.$todas[$key]['DOC_FECHA'].")<a href='javascript:void(0)' id='btnVerVersion' name='btnVerVersion' class='btnVerVersion' onclick='click_verVersion(".$todas[$key]['DOC_ID'].",".$todas[$key]['DOC_VERSION'].");'>Ver versión</a></li>";    
                    }
            }

            $this->_TEMPLATE->assign('listado_versiones',$listado);
            $this->_TEMPLATE->parse('main.listado_versiones');
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }

    //funcion para ver la version seleccionada
    public function fun_ver_version($param){
        $json = array();
        $MENSAJES = array();
        $CAMBIA = array();	
        $OPEN = array();			
        
        $wf = $param['id'];
        $version = $param['version'];
        $certificado = $this->fun_listar_certificado_xversion($wf,$version); 
        $mensaje_version = 'mi certificado es '.$certificado[0]['DOC_ID'].' y la version que estoy revisando es '.$certificado[0]['DOC_VERSION'].'estanos ready';
       
         
        $json['RESULTADO'] = 'OK';			
        $MENSAJES[] = $mensaje_version;
        
        $this->_TEMPLATE->assign('version_version',$certificado[0]['DOC_VERSION']);
        $this->_TEMPLATE->assign('version_usuario',$certificado[0]['DOC_REDACTOR']);
        $this->_TEMPLATE->assign('version_fecha',$certificado[0]['DOC_FECHA']);
        $this->_TEMPLATE->parse('main.div_ver_version');
         
         
         $CAMBIA['#div_ver_version'] = $this->_TEMPLATE->text('main.div_ver_version');
         $OPEN['#div_ver_version'] = 'open';
         $json['MENSAJES'] =  $MENSAJES;
         $json['CAMBIA'] = $CAMBIA;
         $json['OPEN'] = $OPEN;
         return json_encode($json);		
    }

    //listamos el certificado por la version
    public function fun_listar_certificado_xversion($wf,$version){

        try{
        
            $bind = array(":p_wf"=>$wf, ":p_version" =>$version);
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_DOCUMENTO_PKG.fun_listar_cert_xversion",'function', $bind);
        
        
                    if ($cursor) {
                        while($r = $this->_ORA->FetchArray($cursor)){ 
                            $r['DOC_ID']=$r['DOC_ID'];
                            $r['DOC_VERSION']=$r['DOC_VERSION'];
                            $r['DOC_DATOS_SENSIBLES']=$r['DOC_DATOS_SENSIBLES'];
                            $r['DOC_USA_PLANTILLA']=$r['DOC_USA_PLANTILLA'];
                            $r['DOC_PDF']=$r['DOC_PDF'];
                            $r['DOC_FECHA']=$r['DOC_FECHA'];
                            $r['DOC_REDACTOR']=$r['DOC_REDACTOR'];
                            $r['DOC_ENVIADO_A']=$r['DOC_ENVIADO_A'];
                            $r['GDE_TIPOS_DOCUMENTO_TIPDOC_ID']=$r['GDE_TIPOS_DOCUMENTO_TIPDOC_ID'];
                            $r['GDE_DISTRIBUCION_DIS_SECUENCIA']=$r['GDE_DISTRIBUCION_DIS_SECUENCIA'];
                            $r['GDE_ESTADO_DOCUMENTO_ESTDOC_ID']=$r['GDE_ESTADO_DOCUMENTO_ESTDOC_ID'];
                            $r['GDE_PRIVACIDAD_PRI_ID']=$r['GDE_PRIVACIDAD_PRI_ID'];
                            $r['DOC_GENERA_VERSION']=$r['DOC_GENERA_VERSION'];
                            $r['DOC_CASO_PADRE']=$r['DOC_CASO_PADRE'];
                            $r['DOC_ULTIMA_VERSION']=$r['DOC_ULTIMA_VERSION'];
                            $r['DOC_CUERPO']=$r['DOC_CUERPO']; 
                            
        
                            $resCertificado[]=$r;    
                        }
                        $this->_ORA->FreeStatement($cursor);
                    }    
                    
            return $resCertificado;

        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }

    }



    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||  METODOS UTILES ||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

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

    //ml: funcion para detectar el DV del RUT
    function fun_digito_verificador($r){
        $s=1;
        for($m=0;$r!=0;$r/=10)
            $s=($s+$r%10*(9-$m++%6))%11;
        //echo 'El digito verificador es: ',chr($s?$s+47:75);
        return chr($s?$s+47:75);
    }


    
    public function fun_respuesta_evb($param){
      
        $json = array();
        $MENSAJES = array();
        $CAMBIA = array();	
        $OPEN = array();			
        
         
        $respuesta = $param['respuesta'];   
         if($respuesta == 'NOK'){
             $errores = $param['errores'];
             $mensaje_respuesta = 'Estimado usuario, debe seleccionar los campos obligatorios, el certificado tiene '.$errores.' error(es).';                
             $botonera_respuesta ='';
         }else if($respuesta == 'OK'){
             $mensaje_respuesta = 'El envío se realizó correctamente.'; 
             $botonera_respuesta = '<div class="secBotonera">
                                     <a class="alink" href="javascript:void(0)" id="btnFormCerrar" onclick="accionBtnCerrarEVB();">Cerrar</a>
                                 </div>';               
         }else{
             $mensaje_respuesta = 'Acción no definida.';
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
 }

     public function fun_respuesta($param){
      
        $json = array();
        $MENSAJES = array();
        $CAMBIA = array();	
        $OPEN = array();			
        
         
        $respuesta = $param['respuesta'];   
         if($respuesta == 'NOK'){
             $errores = $param['errores'];
             $mensaje_respuesta = 'Estimado usuario, debe seleccionar los campos obligatorios, el certificado tiene '.$errores.' error(es).';                
             $botonera_respuesta ='';
         }else if($respuesta == 'OK'){
             $mensaje_respuesta = 'El certificado se guardó correctamente.'; 
             $botonera_respuesta = '<div class="secBotonera">
                                     <button class="btn btn-warning" type="button" id="btnFormCerrar" name="btnFormCerrar" onclick="accionBtnFormCerrarM();">OK</button>
                                 </div>';               
         }else{
             $mensaje_respuesta = 'Acción no definida.';
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
 }


    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||| ENVIAR VB |||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    
    //ml: mostramos el modal de enviar a vb
    public function fun_mostrar_modal_evb(){
        
        $tipo_certificado = $this->_SESION->getVariable('TIPO_CERTIFICADO');
        $wf = $this->_SESION->getVariable("WF");
       
        $accion_caso = $this->fun_modifica_o_versiona($wf, $tipo_certificado);
        $existen_cambios = $this->fun_verificar_exiten_cambios();

        if($accion_caso == 'MODIFICAR_VERSION'){//es el mismo usuario 
            $opciones_visaciones = '<option value="SI">De acuerdo</option>';
        }else if($accion_caso == 'GENERA_NUEVA_VERSION'){
            if($existen_cambios == 'SI'){
                $opciones_visaciones = '<option value="SI">De acuerdo</option>';
            }else{
                $opciones_visaciones = '<option value="SI">De acuerdo</option>   			
                                    <option value="NO">En desacuerdo</option>			
                                    <option value="IN">Indiferente</option>';
            }
        }else{//USUARIO NO AUTORIZADO 
            $opciones_visaciones = '<option value="SI">De acuerdo (Usuario No autorizado)</option>';
        }

        
        
        $json = array();
        $MENSAJES = array();
        $CAMBIA = array();	
        $OPEN = array();			
        
        $unidad = ($unidad == null) ? $this->_SESION->UNIDAD : $unidad;
        $cursorPara = $this->_ORA->retornaCursor("WFA.WFA_USR.getNombresUsrsUnidad","function",array(":unidad" => $unidad));
        $dataPara = $this->_ORA->FetchAll($cursorPara);        

        
        $cursorAllUsuarios = $this->_ORA->retornaCursor("WFA.WFA_USR.getAllUsuarios","function",array(":p_caso_id" => null,":p_origen" => null ));
        $dataCopia = $this->_ORA->FetchAll($cursorAllUsuarios);    
        
        
        $listar_para = '';
        foreach($dataPara as $miPara){
            $listar_para.= '<option value="'.$miPara['EP_USUARIO'].'">'.$miPara['EP_NOMBRES'].'</option>';
        }
        $listar_copia = '';
        foreach($dataCopia as $miCopia){
            $listar_copia.= '<option value="'.$miCopia['EP_USUARIO'].'">'.$miCopia['EP_NOMBRES'].' '.$miCopia['EP_APE_PAT'].' '.$miCopia['EP_APE_MAT'].'</option>';
        }



        $cursorDivision = $this->_ORA->retornaCursor("wfa.WFA_USR.getUnidades","function");
        $dataDivision = $this->_ORA->FetchAll($cursorDivision);
        $listar_division = '';
        foreach($dataDivision as $miDivision){
            $listar_division.= '<option value="'.$miDivision['EP_COD_DEPTO'].'">'.$miDivision['EP_DEPDESC'].'</option>';
        }
        
        
        $mensaje_respuesta = 'Queremos Enviar a VB desde el Modificar.';
       
         $json['RESULTADO'] = 'OK';			
         $MENSAJES[] = $mensaje_respuesta;
        
         $this->_TEMPLATE->assign('division_OtraUnidadEnviarVB',$listar_division);
         
         
       



         $this->_TEMPLATE->assign('opciones_visaciones',$opciones_visaciones);
         $this->_TEMPLATE->assign('para_enviarvb',$listar_para);
         $this->_TEMPLATE->assign('copia_enviarvb',$listar_copia);
         $this->_TEMPLATE->assign('para_enviarvbtodos',$listar_copia);
         $this->_TEMPLATE->assign('copia_enviarvbtodos',$listar_copia);
         $this->_TEMPLATE->assign('cuerpo_enviarvb',$mensaje_respuesta);
         $this->_TEMPLATE->parse('main.div_enviarvb');
         
         
         $CAMBIA['#div_enviarvb'] = $this->_TEMPLATE->text('main.div_enviarvb');
         $OPEN['#div_enviarvb'] = 'open';
         $json['MENSAJES'] =  $MENSAJES;
         $json['CAMBIA'] = $CAMBIA;
         $json['OPEN'] = $OPEN;
         return json_encode($json);		

    }


    
    //ml: EVB OTRA UNIDAD para el formulario MODIFICAR
    public function fun_agregar_otraunidad_evb(){
        //echo "<pre>";print_r("PASO 1:: LLEGAMOS AL AGREGAR OTRA UNIDAD EVB");echo "</pre>"; 

        try{
             
             $mi_usuario                = $this->_SESION->USUARIO; 
             $wf                        = $_POST['p_wf'];//doc_id 
             $tipo                      = $_POST['p_tipo'];//tipo_certificado
             $p_medio_envio             = 'SEIL';
             $p_doc_datos_sensibles     = $_POST['p_datosensibleSINO'];
             $p_gde_pri_id              = $_POST['p_privacidadTipo'];
             $p_doc_caso_padre          = $_POST['p_padre'];
             $p_tipo_envio              = $_POST['p_tipo_envio'];
             
             //data destinatario
             $arrayDestinatario          = explode("_,", substr($_POST["p_arrayDestinatario"], 0, -1)); 
             $arrayCargoDestinatario     = explode("_,", substr($_POST["p_arrayCargoDestinatario"], 0, -1)); 
             $arrayDireccion             = explode("_,", substr($_POST["p_arrayDireccion"], 0, -1)); 
             $arrayCorreo                = explode("_,", substr($_POST["p_arrayCorreo"], 0, -1));
             $arrayMiTipo                = explode("_,", substr($_POST["p_arrayMiTipo"], 0, -1)); 
             $arrayMiNombreDes           = explode("_,", substr($_POST["p_arrayMiNombreDes"], 0, -1)); 
             
             $arrayMiMedioEnvio          = explode("_,", substr($_POST["p_arrayMiMedioEnvio"], 0, -1)); 
            

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
 
             //data destinatario copia
             $arrayCopia                = explode("_,", substr($_POST["p_arrayCopia"], 0, -1)); 
             $arrayCargoCopia           = explode("_,", substr($_POST["p_arrayCargoCopia"], 0, -1)); 
             $arrayCopiaDireccion       = explode("_,", substr($_POST["p_arrayCopiaDireccion"], 0, -1)); 
             $arrayCopiaCorreo          = explode("_,", substr($_POST["p_arrayCopiaCorreo"], 0, -1)); 
             $arrayMiTipoCopia          = explode("_,", substr($_POST["p_arrayMiTipoCopia"], 0, -1)); 
             $arrayMiNombreDesCopia     = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1)); 
             $arrayMiMedioEnvioCopia    = explode("_,", substr($_POST["p_arrayMiMedioEnvioCopia"], 0, -1));

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
 
             //data destinatario a eliminar
             $arrayDestinatarioE= explode("_,", substr($_POST["p_arrayDestinatarioE"], 0, -1)); 
             $arrayDestinatarioCE= explode("_,", substr($_POST["p_arrayDestinatarioCE"], 0, -1)); 
 
             //obtenemos la ultima version del certificado
             //$certificado_uv =  $this->fun_listar_ultima_version($wf,$tipo);//revisar bbdd
             $certificado_uv =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);//revisar bbdd
             $genera_version = $certificado_uv[0]['DOC_GENERA_VERSION'];//sabemos si se debe generar version  
             $accion_caso = 'MODIFICAR_VERSION';//siempre se trabaja en la version actual      
             
             
             $version = $certificado_uv[0]['DOC_VERSION']; //siempre se trabaja en la version actual


             $dataModificar = array (
                'medio_envio' => $p_medio_envio,
                'datos_sensibles' => $p_doc_datos_sensibles,
                'privacidad' => $p_gde_pri_id,
                'caso_padre' => $p_doc_caso_padre,
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1,
                'privacidad_anterior' => $certificado_uv[0]['GDE_PRIVACIDAD_PRI_ID'],
                'datos_sensibles_anterior' => $certificado_uv[0]['DOC_DATOS_SENSIBLES'],
                'certificado_uv' => $certificado_uv,
                'tipo_envio' => $p_tipo_envio
            );

            /**
            *CONTROLAR CAMBIOS EN DIFERENTTES MODULOS 
            *
            *
            * 
            */

            $this->_SESION->setVariable('ESTADO_DESTINATARIO', 2);
            //$this->_SESION->setVariable('ESTADO_PRIVACIDAD', 2);
            $this->_SESION->setVariable('ESTADO_EXPEDIENTE', 2);

            $estadoCuerpo       = $this->_SESION->getVariable('ESTADO_CUERPO'); 
            $estadoDestinatario = $this->_SESION->getVariable('ESTADO_DESTINATARIO'); 
            $estadoPrivacidad   = $this->_SESION->getVariable('ESTADO_PRIVACIDAD');
            $estadoExpediente   = $this->_SESION->getVariable('ESTADO_EXPEDIENTE');    
  


            $dataCuerpo = array (
                'usar_plantilla' =>  $_POST['p_usarPlantillaSINO'],
                'usar_plantilla_anterior' =>  $certificado_uv[0]['DOC_USA_PLANTILLA'], 
                'file' => $_FILES,
                'pdf_anterior' => $certificado_uv[0]['DOC_PDF'],
                'cuerpo' => $_POST['p_cuerpo'],
                'cuerpo_anterior' => $certificado_uv[0]['DOC_CUERPO'],
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
    
            );
            $dataDestinatarioControl = array(
                    'arrayDestinatario' => $dataDestinatario,
                    'arrayCopia' => $dataCopia,
                    'arrayDestinatarioE' => $arrayDestinatarioE,
                    'arrayDestinatarioCE' => $arrayDestinatarioCE,
                    'tipo_version' => $accion_caso,
                    'version' => $certificado_uv[0]['DOC_VERSION'],
                    'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
            );
            $dataExpediente = array(
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
            );  
    
    
            $p_vis_id = mt_rand();
            $p_paraVB= $_POST['p_otraUnidadParaVB'];//usuario para enviar vb
            $p_comentarioVB= $_POST['p_otraUnidadComentarioVB']; //comentario visacion enviar vb
            //$p_visacionVB='SI';
            $p_visacionVB=$_POST['p_otraUnidadVisacionVB'];    

            //ENVIO A VB
            $dataMV_EVB = array (
                'mi_usuario' => $mi_usuario,
                'version' => $version ,
                'datos_sensibles' => $p_doc_datos_sensibles ,
                'privacidad' => $p_gde_pri_id,
                'wf' => $wf,
                'enviado_a' => $p_paraVB,
                'genera_version' => $genera_version
            );
    
             
            //ACTUALIZAR CERTIFICADO EVB
            $this->fun_actualizar_cert_evb($dataMV_EVB);
            

            //controlamos CERTIFICADO
            $this->fun_chequear_certificado($dataModificar);   
            //CONTROLAMOS MODULO CUERPO    
            $this->fun_chequear_cuerpo($dataCuerpo);   
            //CONTROLAMOS MODULO EXPEDIENTES
            $this->fun_chequear_estado_expediente($dataExpediente);
            //CONTROLAMOS LOS DESTINATARIOS (y DESTINATARIO COPIA)
            $this->fun_chequear_estado_destinatario($dataDestinatarioControl);   
            
            //AGREGAMOS LA VISACION CORRESPONDIENTE A ENVIO A VB
            $this->fun_agregar_visacion_vb($p_vis_id,$wf,$version,$p_paraVB,$p_comentarioVB,$p_visacionVB);    
        
           
            if(isset($_FILES['file3']['tmp_name'])){
                $blob3 = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob3->WriteTemporary(file_get_contents($_FILES['file3']['tmp_name']),OCI_TEMP_BLOB);
                $bindVisacion =  array(":p_vis_id"=> $p_vis_id,":p_vis_adjunto" => $blob3);
                $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_ACTUALIZAR_ADJUNTO",$bindVisacion);
                $this->_ORA->Commit();
            }     



            $usuario_desde  = $this->_SESION->USUARIO;//usuario desde enviar a vb otra unidad 
            $usuarioPara    = $p_paraVB; 
            $comentario     = $p_comentarioVB; 
                

                
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
            //$this->fun_notificarDerivarCaso($correo_para,$copia_correo,$wf,$comentario,$usuario_desde);  
            $this->_CORREOCERTIFICADO->fun_notificarDerivarCaso($correo_para,$copia_correo,$wf,$comentario,$usuario_desde);  


            //derivamos a la bandeja de WF [DESCOMENTAR]
            $this->setAsignar($usuarioPara, $comentario,$wf);
            $this->_ORA->Commit();     
             
             return "OK";
             //CERRAMOS AQUI
             exit();
 
 
       
             
        }catch(Exception $e){
            $this->_LOG->error(print_r($e));
        }


    }


    //ml: funcion para poder actualizar envio a visto bueno desde OTRA UNIDAD ////////////////  NO SE ESTA OCUPANDO :: CHEQUEAR
    public function fun_otraunidad_evb(){

        try{

            $mi_usuario             = $this->_SESION->USUARIO; 
            $wf                     = $_POST['p_wf'];//doc_id 
            $tipo                   = $_POST['p_tipo'];//tipo_certificado
            $p_medio_envio          = 'SEIL';
            $p_doc_datos_sensibles  = $_POST['p_datosensibleSINO'];
            $p_gde_pri_id           = $_POST['p_privacidadTipo'];
            $p_doc_caso_padre       = $_POST['p_padre'];

            $p_vis_id               = mt_rand();
            $p_paraVB               = $_POST['p_otraUnidadParaVB'];//usuario para enviar vb
            $p_comentarioVB         = $_POST['p_otraUnidadComentarioVB']; //comentario visacion enviar vb
            $p_visacionVB           = 'SI';
            $genera_version         = 'SI';
            //$certificado_uv         = $this->fun_listar_ultima_version($wf,$tipo);
            $certificado_uv         = $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);
            $version                = $certificado_uv[0]['DOC_ULTIMA_VERSION'];
            
            //ACTUALIZAR CERTIFICADO
            //$this->fun_actualizar_certificado($p_doc_datos_sensibles,$p_gde_pri_id,$wf,$version);

            $dataMV_EVB = array (
                'mi_usuario' => $mi_usuario,
                'version' => $version ,
                'datos_sensibles' => $p_doc_datos_sensibles ,
                'privacidad' => $p_gde_pri_id,
                'wf' => $wf,
                'enviado_a' =>$p_paraVB,
                'genera_version' => $genera_version
            );
            

            //ACTUALIZAR CERTIFICADO POR ENVIO A VISTO BUENO
            $this->fun_actualizar_cert_evb($dataMV_EVB);

            //CREAMOS LA VISACION ASOCIADA
            $this->fun_agregar_visacion_vb(
                    $p_vis_id,//OK
                    $wf,//ok
                    $version,
                    $p_paraVB,
                    $p_comentarioVB,
                    $p_visacionVB
                );    

            //validamos que venga archivo adjunto en envioVB
            if(isset($_FILES['file3']['tmp_name'])){
                $blob3 = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob3->WriteTemporary(file_get_contents($_FILES['file3']['tmp_name']),OCI_TEMP_BLOB);
                $bindVisacion =  array(":p_vis_id"=> $p_vis_id,":p_vis_adjunto" => $blob3);
                $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_ACTUALIZAR_ADJUNTO",$bindVisacion);
                $this->_ORA->Commit();
            }    



            $usuario_desde  = $this->_SESION->USUARIO;//usuario desde enviar a vb otra unidad 
            $usuarioPara    = $p_paraVB; 
            $comentario     = $p_comentarioVB; 
            

            
            $bindPara = array(':usuario' => $usuarioPara);                            
            $correo_para = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bindPara);  
            $usuarioCopiaEVB = explode(',',$_POST['p_otraUnidadCopiaVB']);
            $correo_copia = array();
            /*if(isset($usuarioCopiaEVB)){
                for ($i=0;$i<count($usuarioCopiaEVB);$i++) { 
                    $bind = array(':usuario' => $usuarioCopiaEVB[$i]);
                    $correo_copia[$i] = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind); 
                }   
                $copia_correo = implode(",", $correo_copia); 
            }else{
                $copia_correo = null;
            }*/

            //enviamos el correo 
            //$this->fun_notificarDerivarCaso($correo_para,$copia_correo,$wf,$comentario,$usuario_desde);     

            //derivamos a la bandeja de WF [DESCOMENTAR]
            $this->setAsignar($usuarioPara, $comentario,$wf);
            $this->_ORA->Commit();

            return 'OK';

        }catch(Exception $e){
            $this->_LOG->error(print_r($e));
        }

    }    


    //ml: solo controla envio a vb  ////////////////  NO SE ESTA OCUPANDO :: CHEQUEAR
    public function fun_enviar_visto_bueno(){
        
        try{

            $mi_usuario     = $this->_SESION->USUARIO; 
            $wf             = $_POST['p_wf'];//doc_id 
            $tipo           = $_POST['p_tipo'];//tipo_certificado
            $p_medio_envio  = 'SEIL';
            $p_doc_datos_sensibles  = $_POST['p_datosensibleSINO'];
            $p_gde_pri_id           = $_POST['p_privacidadTipo'];
            $p_doc_caso_padre       = $_POST['p_padre'];
            //$certificado_uv =  $this->fun_listar_ultima_version($wf,$tipo);
            $certificado_uv =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);
            $version = $certificado_uv[0]['DOC_ULTIMA_VERSION'];
            //$genera_version = $certificado_uv[0]['DOC_GENERA_VERSION'];
            $genera_version = 'SI';

            $dataMV_EVB = array (
                'mi_usuario' => $mi_usuario,
                'version' => $version ,
                'datos_sensibles' => $p_doc_datos_sensibles ,
                'privacidad' => $p_gde_pri_id,
                'wf' => $wf,
                'enviado_a' => $_POST['p_paraVB'],
                'genera_version' => $genera_version
            );

            /*
            echo "<pre>";var_dump("mi usuario :".$dataMV_EVB['mi_usuario']);echo "</pre>"; 
            echo "<pre>";var_dump("version :".$dataMV_EVB['version']);echo "</pre>"; 
            echo "<pre>";var_dump("datos_sensibles :".$dataMV_EVB['datos_sensibles']);echo "</pre>"; 
            echo "<pre>";var_dump("privacidad :".$dataMV_EVB['privacidad']);echo "</pre>"; 
            echo "<pre>";var_dump("wf :".$dataMV_EVB['wf']);echo "</pre>"; 
            echo "<pre>";var_dump("enviado a :".$dataMV_EVB['enviado_a']);echo "</pre>";
            echo "<pre>";var_dump("genera version :".$dataMV_EVB['genera_version']);echo "</pre>";
            */


            //ACTUALIZAR CERTIFICADO EVB
            $this->fun_actualizar_cert_evb($dataMV_EVB);

            $p_vis_id = mt_rand();
            $p_paraVB= $_POST['p_paraVB'];//usuario para enviar vb
            $p_comentarioVB= $_POST['p_comentarioVB']; //comentario visacion enviar vb
            $p_visacionVB='SI';
            $this->fun_agregar_visacion_vb(
                    $p_vis_id,//OK
                    $wf,//ok
                    $version,
                    $p_paraVB,
                    $p_comentarioVB,
                    $p_visacionVB
                );    

            //validamos que venga archivo adjunto en envioVB
            if(isset($_FILES['file2']['tmp_name'])){
                $blob2 = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob2->WriteTemporary(file_get_contents($_FILES['file2']['tmp_name']),OCI_TEMP_BLOB);
                $bindVisacion =  array(":p_vis_id"=> $p_vis_id,":p_vis_adjunto" => $blob2);
                $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_ACTUALIZAR_ADJUNTO",$bindVisacion);
                $this->_ORA->Commit();
            }    


            $usuario_desde  = $this->_SESION->USUARIO;//usuario desde enviar a vb otra unidad 
            $usuarioPara    = $p_paraVB; 
            $comentario     = $p_comentarioVB; 
            

            
            $bindPara = array(':usuario' => $usuarioPara);                            
            $correo_para = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bindPara);  
            $usuarioCopiaEVB = explode(',',$_POST['p_copiaVB']);
            $correo_copia = array();
            /*if(isset($usuarioCopiaEVB)){
                for ($i=0;$i<count($usuarioCopiaEVB);$i++) { 
                    $bind = array(':usuario' => $usuarioCopiaEVB[$i]);
                    $correo_copia[$i] = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind); 
                }   
                $copia_correo = implode(",", $correo_copia); 
            }else{
                $copia_correo = null;
            }*/

            //enviamos el correo 
            //$this->fun_notificarDerivarCaso($correo_para,$copia_correo,$wf,$comentario,$usuario_desde);     

            //derivamos a la bandeja de WF [DESCOMENTAR]
            $this->setAsignar($usuarioPara, $comentario,$wf);
            $this->_ORA->Commit();

            return "OK";
        
        }catch(Exception $e){

            $this->_LOG->error(print_r($e));

        }


    }


    public function fun_enviarvb_todas(){

        try{
            
            $mi_usuario                 = $this->_SESION->USUARIO; 
            $wf                         = $this->_SESION->getVariable('WF');
            $tipo                       = $this->_SESION->getVariable('TIPO_CERTIFICADO');
            //$wf                         = $_POST['p_wf'];//doc_id 
            //$tipo                       = $_POST['p_tipo'];//tipo_certificado
            $p_medio_envio              = 'SEIL';
            $p_doc_datos_sensibles      = $_POST['p_datosensibleSINO'];
            $p_gde_pri_id               = $_POST['p_privacidadTipo'];
            $p_doc_caso_padre           = $_POST['p_padre'];
            $p_tipo_envio               = $_POST['p_tipo_envio'];
           
            //data destinatario
            $arrayDestinatario          = explode("_,", substr($_POST["p_arrayDestinatario"], 0, -1)); 
            $arrayCargoDestinatario     = explode("_,", substr($_POST["p_arrayCargoDestinatario"], 0, -1)); 
            $arrayDireccion             = explode("_,", substr($_POST["p_arrayDireccion"], 0, -1)); 
            $arrayCorreo                = explode("_,", substr($_POST["p_arrayCorreo"], 0, -1));
            $arrayMiTipo                = explode("_,", substr($_POST["p_arrayMiTipo"], 0, -1)); 
            $arrayMiNombreDes           = explode("_,", substr($_POST["p_arrayMiNombreDes"], 0, -1)); 
            

            $arrayMiMedioEnvio          = explode("_,", substr($_POST["p_arrayMiMedioEnvio"], 0, -1)); 
            

            $dataDestinatario = array (
                    'destinatario' => $arrayDestinatario ,
                    'cargo' => $arrayCargoDestinatario ,
                    'direccion' => $arrayDireccion,
                    'correo' => $arrayCorreo,
                    'tipo' => $arrayMiTipo,
                    'con_copia' => 'NO',
                    'nombre' => $arrayMiNombreDes,
                    'metodo_envio' => $arrayMiMedioEnvio
            );

            //data destinatario copia
            $arrayCopia= explode("_,", substr($_POST["p_arrayCopia"], 0, -1)); 
            $arrayCargoCopia= explode("_,", substr($_POST["p_arrayCargoCopia"], 0, -1)); 
            $arrayCopiaDireccion= explode("_,", substr($_POST["p_arrayCopiaDireccion"], 0, -1)); 
            $arrayCopiaCorreo= explode("_,", substr($_POST["p_arrayCopiaCorreo"], 0, -1)); 
            $arrayMiTipoCopia = explode("_,", substr($_POST["p_arrayMiTipoCopia"], 0, -1)); 
            $arrayMiNombreDesCopia = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1)); 
            $arrayMiMedioEnvioCopia     = explode("_,", substr($_POST["p_arrayMiMedioEnvioCopia"], 0, -1));

            $dataCopia = array (
                'destinatario' => $arrayCopia ,
                'cargo' => $arrayCargoCopia ,
                'direccion' => $arrayCopiaDireccion,
                'correo' => $arrayCopiaCorreo,
                'tipo' => $arrayMiTipoCopia,
                'con_copia' => 'SI',
                'nombre' => $arrayMiNombreDesCopia,
                'metodo_envio' => $arrayMiMedioEnvio
            );

            
            
            //data destinatario a eliminar
            $arrayDestinatarioE= explode("_,", substr($_POST["p_arrayDestinatarioE"], 0, -1)); 
            $arrayDestinatarioCE= explode("_,", substr($_POST["p_arrayDestinatarioCE"], 0, -1)); 

            //obtenemos la ultima version del certificado
            //$certificado_uv =  $this->fun_listar_ultima_version($wf,$tipo);//revisar bbdd
            $certificado_uv =  $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);//revisar bbdd
            $genera_version = $certificado_uv[0]['DOC_GENERA_VERSION'];//sabemos si se debe generar version  
            //$accion_caso = $this->fun_modifica_o_versiona($wf, $tipo);
            $accion_caso = 'MODIFICAR_VERSION';//siempre se trabaja en la version actual

            
            //$version = $certificado_uv[0]['DOC_ULTIMA_VERSION'];
            $version = $certificado_uv[0]['DOC_VERSION']; //siempre se trabaja en la version actual

            //hasta aqui igual //////////////////
        
            $dataModificar = array (
                'medio_envio' => $p_medio_envio,
                'datos_sensibles' => $p_doc_datos_sensibles,
                'privacidad' => $p_gde_pri_id,
                'caso_padre' => $p_doc_caso_padre,
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1,
                'privacidad_anterior' => $certificado_uv[0]['GDE_PRIVACIDAD_PRI_ID'],
                'datos_sensibles_anterior' => $certificado_uv[0]['DOC_DATOS_SENSIBLES'],
                'certificado_uv' => $certificado_uv,
                'tipo_envio' => $p_tipo_envio 
            );

          
            
            
            /**
            *CONTROLAR CAMBIOS EN DIFERENTTES MODULOS 
            *
            *
            * 
            */

            $this->_SESION->setVariable('ESTADO_DESTINATARIO', 2);
            //$this->_SESION->setVariable('ESTADO_PRIVACIDAD', 2);
            $this->_SESION->setVariable('ESTADO_EXPEDIENTE', 2);

                
            $estadoCuerpo       = $this->_SESION->getVariable('ESTADO_CUERPO'); 
            $estadoDestinatario = $this->_SESION->getVariable('ESTADO_DESTINATARIO'); 
            $estadoPrivacidad   = $this->_SESION->getVariable('ESTADO_PRIVACIDAD');
            $estadoExpediente   = $this->_SESION->getVariable('ESTADO_EXPEDIENTE');     

            $dataCuerpo = array (
                'usar_plantilla' =>  $_POST['p_usarPlantillaSINO'],
                'usar_plantilla_anterior' =>  $certificado_uv[0]['DOC_USA_PLANTILLA'], 
                'file' => $_FILES,
                'pdf_anterior' => $certificado_uv[0]['DOC_PDF'],
                'cuerpo' => $_POST['p_cuerpo'],
                'cuerpo_anterior' => $certificado_uv[0]['DOC_CUERPO'],
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
    
            );
            $dataDestinatarioControl = array(
                    'arrayDestinatario' => $dataDestinatario,
                    'arrayCopia' => $dataCopia,
                    'arrayDestinatarioE' => $arrayDestinatarioE,
                    'arrayDestinatarioCE' => $arrayDestinatarioCE,
                    'tipo_version' => $accion_caso,
                    'version' => $certificado_uv[0]['DOC_VERSION'],
                    'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
            );
            $dataExpediente = array(
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
            );  


            
            
           
            $p_vis_id = mt_rand();
            $p_paraVB= $_POST['p_paraVBTodos'];//usuario para enviar vb
            $p_comentarioVB= $_POST['p_comentarioVBTodos']; //comentario visacion enviar vb
            //$p_visacionVB='SI';
            $p_visacionVB=$_POST['p_visacionVBTodos'];

            //ENVIO A VB
            $dataMV_EVB = array (
                'mi_usuario' => $mi_usuario,
                'version' => $version ,
                'datos_sensibles' => $p_doc_datos_sensibles ,
                'privacidad' => $p_gde_pri_id,
                'wf' => $wf,
                'enviado_a' => $p_paraVB,
                'genera_version' => $genera_version
            );



            //var_dump($p_vis_id.'//'.$wf.'//'.$version.'//'.$p_paraVB.'//'.$p_comentarioVB.'//'.$p_visacionVB);
            
            
            //ACTUALIZAR CERTIFICADO EVB
            $this->fun_actualizar_cert_evb($dataMV_EVB);
            

            //controlamos CERTIFICADO
            $this->fun_chequear_certificado($dataModificar);   
            //CONTROLAMOS MODULO CUERPO    
            $this->fun_chequear_cuerpo($dataCuerpo);   
            //CONTROLAMOS MODULO EXPEDIENTES
            $this->fun_chequear_estado_expediente($dataExpediente);
            //CONTROLAMOS LOS DESTINATARIOS (y DESTINATARIO COPIA)
            $this->fun_chequear_estado_destinatario($dataDestinatarioControl);   
           

          
            //AGREGAMOS LA VISACION CORRESPONDIENTE A ENVIO A VB
            $this->fun_agregar_visacion_vb($p_vis_id,$wf,$version,$p_paraVB,$p_comentarioVB,$p_visacionVB);    
            
            
            //validamos que venga archivo adjunto en envioVB
            if(isset($_FILES['file4']['tmp_name'])){
                $blob2 = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob2->WriteTemporary(file_get_contents($_FILES['file4']['tmp_name']),OCI_TEMP_BLOB);
                $bindVisacion =  array(":p_vis_id"=> $p_vis_id,":p_vis_adjunto" => $blob2);
                $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_ACTUALIZAR_ADJUNTO",$bindVisacion);
                $this->_ORA->Commit();
            }    
    
    
            $usuario_desde  = $this->_SESION->USUARIO;//usuario desde enviar a vb otra unidad 
            $usuarioPara    = $p_paraVB; 
            $comentario     = $p_comentarioVB; 
                
    
                
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
            //$this->fun_notificarDerivarCaso($correo_para,$copia_correo,$wf,$comentario,$usuario_desde);     
            $this->_CORREOCERTIFICADO->fun_notificarDerivarCaso($correo_para,$copia_correo,$wf,$comentario,$usuario_desde);     

            //derivamos a la bandeja de WF [DESCOMENTAR]
            $this->setAsignar($usuarioPara, $comentario,$wf);
            $this->_ORA->Commit();     

         



            return "OK";    
            exit();//cerramos aqui    

        }catch(Exception $e){

            $this->_LOG->error(print_r($e));

        }


    }


    //ml: CONTROLAMOS LOS CAMBIOS Y HACEMOS LA ACCION DE ENVIAR A VB
    public function fun_agregar_enviar_vb(){

        

        //var_dump("ESTAMOS AQUI :: QUEREMOS ENVIA A VB ");exit();
        

        try{
            
            $mi_usuario                 = $this->_SESION->USUARIO; 
            $wf                         = $_POST['p_wf'];//doc_id 
            $tipo                       = $_POST['p_tipo'];//tipo_certificado
            $p_medio_envio              = 'SEIL';
            $p_doc_datos_sensibles      = $_POST['p_datosensibleSINO'];
            $p_gde_pri_id               = $_POST['p_privacidadTipo'];
            $p_doc_caso_padre           = $_POST['p_padre'];
            $p_tipo_envio               = $_POST['p_tipo_envio'];
           
            //data destinatario
            $arrayDestinatario          = explode("_,", substr($_POST["p_arrayDestinatario"], 0, -1)); 
            $arrayCargoDestinatario     = explode("_,", substr($_POST["p_arrayCargoDestinatario"], 0, -1)); 
            $arrayDireccion             = explode("_,", substr($_POST["p_arrayDireccion"], 0, -1)); 
            $arrayCorreo                = explode("_,", substr($_POST["p_arrayCorreo"], 0, -1));
            $arrayMiTipo                = explode("_,", substr($_POST["p_arrayMiTipo"], 0, -1)); 
            $arrayMiNombreDes           = explode("_,", substr($_POST["p_arrayMiNombreDes"], 0, -1)); 
            $arrayMiMedioEnvio          = explode("_,", substr($_POST["p_arrayMiMedioEnvio"], 0, -1)); 
            

            
            $dataDestinatario = array (
                    'destinatario' => $arrayDestinatario ,
                    'cargo' => $arrayCargoDestinatario ,
                    'direccion' => $arrayDireccion,
                    'correo' => $arrayCorreo,
                    'tipo' => $arrayMiTipo,
                    'con_copia' => 'NO',
                    'nombre' =>  $arrayMiNombreDes,
                    'medio_envio' =>  $arrayMiMedioEnvio  
            );

            //data destinatario copia
            $arrayCopia             = explode("_,", substr($_POST["p_arrayCopia"], 0, -1)); 
            $arrayCargoCopia        = explode("_,", substr($_POST["p_arrayCargoCopia"], 0, -1)); 
            $arrayCopiaDireccion    = explode("_,", substr($_POST["p_arrayCopiaDireccion"], 0, -1)); 
            $arrayCopiaCorreo       = explode("_,", substr($_POST["p_arrayCopiaCorreo"], 0, -1)); 
            $arrayMiTipoCopia       = explode("_,", substr($_POST["p_arrayMiTipoCopia"], 0, -1)); 
            $arrayMiNombreDesCopia  = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1)); 
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

            
            
            //data destinatario a eliminar
            $arrayDestinatarioE= explode("_,", substr($_POST["p_arrayDestinatarioE"], 0, -1)); 
            $arrayDestinatarioCE= explode("_,", substr($_POST["p_arrayDestinatarioCE"], 0, -1)); 

            //obtenemos la ultima version del certificado
            //$certificado_uv =  $this->fun_listar_ultima_version($wf,$tipo);//revisar bbdd
            $certificado_uv = $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo);//revisar bbdd
            $genera_version = $certificado_uv[0]['DOC_GENERA_VERSION'];//sabemos si se debe generar version  
            //$accion_caso = $this->fun_modifica_o_versiona($wf, $tipo);
            $accion_caso = 'MODIFICAR_VERSION';//siempre se trabaja en la version actual

            
            //$version = $certificado_uv[0]['DOC_ULTIMA_VERSION'];
            $version = $certificado_uv[0]['DOC_VERSION']; //siempre se trabaja en la version actual

            //hasta aqui igual //////////////////
        
            $dataModificar = array (
                'medio_envio' => $p_medio_envio,
                'datos_sensibles' => $p_doc_datos_sensibles,
                'privacidad' => $p_gde_pri_id,
                'caso_padre' => $p_doc_caso_padre,
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1,
                'privacidad_anterior' => $certificado_uv[0]['GDE_PRIVACIDAD_PRI_ID'],
                'datos_sensibles_anterior' => $certificado_uv[0]['DOC_DATOS_SENSIBLES'],
                'certificado_uv' => $certificado_uv,
                'tipo_envio' => $p_tipo_envio 
            );

          
            
            
            /**
            *CONTROLAR CAMBIOS EN DIFERENTTES MODULOS 
            *
            *
            * 
            */

            $this->_SESION->setVariable('ESTADO_DESTINATARIO', 2);
            //$this->_SESION->setVariable('ESTADO_PRIVACIDAD', 2);
            $this->_SESION->setVariable('ESTADO_EXPEDIENTE', 2);

                
            $estadoCuerpo       = $this->_SESION->getVariable('ESTADO_CUERPO'); 
            $estadoDestinatario = $this->_SESION->getVariable('ESTADO_DESTINATARIO'); 
            $estadoPrivacidad   = $this->_SESION->getVariable('ESTADO_PRIVACIDAD');
            $estadoExpediente   = $this->_SESION->getVariable('ESTADO_EXPEDIENTE');     

            $dataCuerpo = array (
                'usar_plantilla' =>  $_POST['p_usarPlantillaSINO'],
                'usar_plantilla_anterior' =>  $certificado_uv[0]['DOC_USA_PLANTILLA'], 
                'file' => $_FILES,
                'pdf_anterior' => $certificado_uv[0]['DOC_PDF'],
                'cuerpo' => $_POST['p_cuerpo'],
                'cuerpo_anterior' => $certificado_uv[0]['DOC_CUERPO'],
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
    
            );
            $dataDestinatarioControl = array(
                    'arrayDestinatario' => $dataDestinatario,
                    'arrayCopia' => $dataCopia,
                    'arrayDestinatarioE' => $arrayDestinatarioE,
                    'arrayDestinatarioCE' => $arrayDestinatarioCE,
                    'tipo_version' => $accion_caso,
                    'version' => $certificado_uv[0]['DOC_VERSION'],
                    'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
            );
            $dataExpediente = array(
                'tipo_version' => $accion_caso,
                'version' => $certificado_uv[0]['DOC_VERSION'],
                'nueva_version' => $certificado_uv[0]['DOC_VERSION']+1
            );  


            
            
           
            $p_vis_id = mt_rand();
            $p_paraVB= $_POST['p_paraVB'];//usuario para enviar vb
            $p_comentarioVB= $_POST['p_comentarioVB']; //comentario visacion enviar vb
            //$p_visacionVB='SI';
            $p_visacionVB=$_POST['p_visacionVB'];

            //ENVIO A VB
            $dataMV_EVB = array (
                'mi_usuario' => $mi_usuario,
                'version' => $version ,
                'datos_sensibles' => $p_doc_datos_sensibles ,
                'privacidad' => $p_gde_pri_id,
                'wf' => $wf,
                'enviado_a' => $p_paraVB,
                'genera_version' => $genera_version
            );



            //var_dump($p_vis_id.'//'.$wf.'//'.$version.'//'.$p_paraVB.'//'.$p_comentarioVB.'//'.$p_visacionVB);
            
            
            //ACTUALIZAR CERTIFICADO EVB
            $this->fun_actualizar_cert_evb($dataMV_EVB);
            

            //controlamos CERTIFICADO
            $this->fun_chequear_certificado($dataModificar);   
            //CONTROLAMOS MODULO CUERPO    
            $this->fun_chequear_cuerpo($dataCuerpo);   
            //CONTROLAMOS MODULO EXPEDIENTES
            $this->fun_chequear_estado_expediente($dataExpediente);
            //CONTROLAMOS LOS DESTINATARIOS (y DESTINATARIO COPIA)
            $this->fun_chequear_estado_destinatario($dataDestinatarioControl);   
           

          
            //AGREGAMOS LA VISACION CORRESPONDIENTE A ENVIO A VB
            $this->fun_agregar_visacion_vb($p_vis_id,$wf,$version,$p_paraVB,$p_comentarioVB,$p_visacionVB);    
            
            
            //validamos que venga archivo adjunto en envioVB
            if(isset($_FILES['file2']['tmp_name'])){
                $blob2 = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob2->WriteTemporary(file_get_contents($_FILES['file2']['tmp_name']),OCI_TEMP_BLOB);
                $bindVisacion =  array(":p_vis_id"=> $p_vis_id,":p_vis_adjunto" => $blob2);
                $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_ACTUALIZAR_ADJUNTO",$bindVisacion);
                $this->_ORA->Commit();
            }    
    
    
            $usuario_desde  = $this->_SESION->USUARIO;//usuario desde enviar a vb otra unidad 
            $usuarioPara    = $p_paraVB; 
            $comentario     = $p_comentarioVB; 
                
    
                
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

            //enviamos el correo 
            //$this->fun_notificarDerivarCaso($correo_para,$copia_correo,$wf,$comentario,$usuario_desde); 
            $this->_CORREOCERTIFICADO->fun_notificarDerivarCaso($correo_para,$copia_correo,$wf,$comentario,$usuario_desde); 

            //derivamos a la bandeja de WF [DESCOMENTAR]
            $this->setAsignar($usuarioPara, $comentario,$wf);
            $this->_ORA->Commit();     

         



            return "OK";    
            exit();//cerramos aqui    
            


        }catch(Exception $e){

            $this->_LOG->error(print_r($e));

        }




    }



    //ml: agregamos la visacion del enviar vb
    public function fun_agregar_visacion_vb($p_vis_id,$p_id,$p_doc_version,$p_paraVB,$p_comentarioVB,$p_visacionVB){

        try{
        
            //echo "<pre>";var_dump("comentario oo :".$p_comentarioVB);echo "</pre>";    
            $p_usuario =$this->_SESION->USUARIO; 
            $RES_REFERENCIA = $this->_ORA->NewDescriptor(); 
            $RES_REFERENCIA->WriteTemporary($p_comentarioVB,OCI_TEMP_CLOB);

            
            $bind =  array(":p_vis_id"=> $p_vis_id,
            ":p_doc_id"=> $p_id,
            ":p_doc_version"=> $p_doc_version ,
            ":p_vis_usuario"=> $p_usuario ,
            ":p_usuario_hacia"=> $p_paraVB,
            ":p_vis_vb"=>$p_visacionVB,
            ":p_vis_comentario"=>$RES_REFERENCIA
            );
            $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_AGREGAR_VISACION",$bind);
            $this->_ORA->Commit();
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }
    
    //ml: metodo para derivarlo a otra bandeja de WF
    public function setAsignar($usuario, $comentario,$NUMERO_CASO){

        
        try{
                
                //$bind = array(':caso'=>$NUMERO_CASO, ':usuario' => $usuario, ':desde' => $this->_SESION->USUARIO, ':msg' => $comentario );
                
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
    
    //COMUN
    /*public function fun_notificarDerivarCaso($correo_para,$correo_copia,$NUMERO_CASO,$comentario,$usuario_desde){

        try{

            //$ORA = new Conexion_ora();

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
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }

    } 
    */

     //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||| TIPO DE ENVIO |||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
  
   //ml: listamos los tipos de envios que existen
   public function fun_listar_tipo_envio(){
        
        try{

            $cursor = $this->_ORA->retornaCursor("GDE.GDE_TIPO_DOCUMENTO_PKG.FUN_LISTAR_TIPO_ENVIO",'function');
            if($cursor) {
                while($r = $this->_ORA->FetchArray($cursor)){ 
                    $r['TIPENV_ID']=$r['TIPENV_ID'];
                    $r['TIPENV_NOMBRE']=$r['TIPENV_NOMBRE'];

                    $envios[]=$r;
                }
                $this->_ORA->FreeStatement($cursor);
            }
            return $envios;

        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }

    //ml: html tipo envio para formulario MODIFICAR
    public function fun_tipo_envio_html($miTipoEnvio){
        
        try{

            $tipos_envios = $this->fun_listar_tipo_envio();
            $resultado = '';
            
            if($tipos_envios){
                foreach($tipos_envios as $envios){
                    $resultado .= '<div class="form-check form-check-inline">';
                    if($miTipoEnvio == $envios['TIPENV_ID']){
                        $resultado .= '<label class="form-check-label">
                        <input class="form-check-input" type="radio" id="tipo_envio" name="tipo_envio" value="'.$envios['TIPENV_ID'].'" onclick="fun_cambiar_estado_privacidad(2)" checked>'.$envios['TIPENV_NOMBRE'].'</label>&nbsp;&nbsp;&nbsp;';
                    }else{
                        $resultado .= '<label class="form-check-label">
                        <input class="form-check-input" type="radio" id="tipo_envio" name="tipo_envio" value="'.$envios['TIPENV_ID'].'" onclick="fun_cambiar_estado_privacidad(2)">'.$envios['TIPENV_NOMBRE'].'</label>&nbsp;&nbsp;&nbsp;';
                    }
                    $resultado .='</div>';    
                }
            }

            return $resultado;
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }
    }
    

    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||| FIRMAR CERTIFICADO ||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

        //ml: MOSTRAMOS MODAL PARA FIRMAR CERTIFICADO
        public function fun_mostrar_modal_firmar(){
            $json = array();
            $MENSAJES = array();
            $CAMBIA = array();	
            $OPEN = array();			
            
            $mensaje_respuesta = 'QUEREMOS FIRMAR EL CERTIFICADO.';
           
            $json['RESULTADO'] = 'OK';			
            $MENSAJES[] = $mensaje_respuesta;

            
            //$gde_tipdoc_id = 'certificado';
            $tipo_certificado = $this->_SESION->getVariable('TIPO_CERTIFICADO');
            $tipo_documento = $this->fun_get_tipo_documento($tipo_certificado);
            
            //var_dump($tipo_dicumento);exit();

            $aplfirma = $tipo_documento[0]['TIPDOC_APFIRMA'];
            $usuario =$this->_SESION->USUARIO; 
            //$usuario = 'SOAINT';
            //$aplfirma = 'GENER';
           
        
            if($tipo_documento){
                $aplfirma = $tipo_documento[0]['TIPDOC_APFIRMA'];
                $usuario =$this->_SESION->USUARIO; 
                //$usuario = 'CULLOA';
                //$aplfirma = 'GENER';
               
                $cursorTipoFirmas = $this->_ORA->retornaCursor("fst.FSB_USUARIO_APLIC_PKG.fun_getTiposFirmaAplic","function",array(":usuario" => $usuario,":aplfirma" => $aplfirma ));
                $dataFirmas = $this->_ORA->FetchAll($cursorTipoFirmas);    
   
                
                $listado_firmas = '';
                foreach($dataFirmas as $miFirma){
                   $listado_firmas.= '<option value="'.$miFirma['MEDFIR_ID'].'|'.$miFirma['MEDFIR_TIPO'].'">'.$miFirma['MEDFIR_ID'].' ('.$miFirma['MEDFIR_TIPO'].')</option>';
                }

                $this->_TEMPLATE->assign('listado_firmas',$listado_firmas);
            }

           
             

             
             $this->_TEMPLATE->parse('main.div_firmar_certificado');
             
             
             $CAMBIA['#div_firmar_certificado'] = $this->_TEMPLATE->text('main.div_firmar_certificado');
             $OPEN['#div_firmar_certificado'] = 'open';
             $json['MENSAJES'] =  $MENSAJES;
             $json['CAMBIA'] = $CAMBIA;
             $json['OPEN'] = $OPEN;
             return json_encode($json);		
    
        }


        //ml: validamos que exista rol de firma al usuario
        public function fun_existe_tipo_firma(){
        
            try{

                $tipo_certificado = $this->_SESION->getVariable('TIPO_CERTIFICADO');
                $tipo_documento = $this->fun_get_tipo_documento($tipo_certificado);
                $usuario =$this->_SESION->USUARIO; 
                //$usuario = 'SOAINT';
                $aplfirma = $tipo_documento[0]['TIPDOC_APFIRMA'];
                
                
                $cursor = $this->_ORA->retornaCursor("fst.FSB_USUARIO_APLIC_PKG.fun_getTiposFirmaAplic","function",array(":usuario" => $usuario,":aplfirma" => $aplfirma ));
                $data = $this->_ORA->FetchAll($cursor);    
                
                //var_dump("cantidad : ".count($data));exit();
                if(count($data) > 0){
                    return 'SI';
                }else{
                    return 'NO';
                }
            
            }catch (Exception $e){
                print("ERROR: existe un error a la hora de listar los adjuntos.");
            }
            
        } 
        
        //ml: valida si hay cambios en el certificado
        public function fun_verificar_exiten_cambios(){
            
            try{

                $estadoCuerpo       = $this->_SESION->getVariable('ESTADO_CUERPO'); 
                $estadoDestinatario = $this->_SESION->getVariable('ESTADO_DESTINATARIO'); 
                $estadoPrivacidad   = $this->_SESION->getVariable('ESTADO_PRIVACIDAD');
                $estadoExpediente   = $this->_SESION->getVariable('ESTADO_EXPEDIENTE');   

                //var_dump($estadoCuerpo."///".$estadoDestinatario."///".$estadoPrivacidad."///".$estadoExpediente);
                //exit();

                if($estadoCuerpo > 1 || $estadoDestinatario > 1 || $estadoPrivacidad > 1 || $estadoExpediente > 1){
                    return 'SI';
                }else{
                    return 'NO';
                }
            
            }catch (Exception $e){
                print("ERROR: existe un error a la hora de listar los adjuntos.");
            }

        }

        
        
        //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
        //||||||||||||||||||||||||||||||||||||||||||||||||| ELIMINAR ||||||||||||||||||||||||||||||||||||||||||||||||
        //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

     

         //ml: abrimos el modal para eliminar el certificado
         public function fun_mostrar_modal_eliminar(){

            $json = array();
            $MENSAJES = array();
            $CAMBIA = array();	
            $OPEN = array();			
            
            $mensaje_respuesta = '';
           
            $json['RESULTADO'] = 'OK';			
            $MENSAJES[] = $mensaje_respuesta;

            
            
             $this->_TEMPLATE->assign('mensaje', $mensaje_respuesta);
             $this->_TEMPLATE->parse('main.div_eliminar_certificado');
             
             
             $CAMBIA['#div_eliminar_certificado'] = $this->_TEMPLATE->text('main.div_eliminar_certificado');
             $OPEN['#div_eliminar_certificado'] = 'open';
             $json['MENSAJES'] = $MENSAJES;
             $json['CAMBIA'] = $CAMBIA;
             $json['OPEN'] = $OPEN;
             return json_encode($json);		
         }

         //ml: realizamos la eliminacion del certificado
         public function fun_eliminar_certificado(){

            try{

                $motivo             = $_POST['motivo'];
                $NUMERO_CASO        = $this->_SESION->getVariable("WF");
                $comentario         = $motivo;
                $usuario            = $this->_SESION->USUARIO;
                
                //$version            = $this->_SESION->getVariable("VERSION_CERTIFICADO");
                //$tipo_certificado   = $this->_SESION->getVariable("TIPO_CERTIFICADO");
                $estado             = "anula";
                //var_dump($NUMERO_CASO ."//". $comentario ."//".$usuario);
                //return "OK";
                //exit();
                
                
                //registrar en la bitacora
                $bind = array(':caso'=>$NUMERO_CASO, ':usuario' => $this->_SESION->USUARIO, ':desde' => $this->_SESION->USUARIO, ':msg' => $comentario );
                    $this->_ORA->ejecutaFunc("wfa.wf_rso_pkg.fun_bitacora", $bind);
                    $this->_LOG->log("Bitacora en el WF: ".$NUMERO_CASO.' con bind '.print_r($bind,true));  
                
                //terminar el caso 
                $bind = array(
                    ':ITEMKEY' => $NUMERO_CASO,//numero del wf dentro de las comillas
                    ':ACTIVITY' => 'NT_REVISAR_DOCTO',
                    ':LOOKUPCODE' => 'LC_WF_GEN_SINWF',
                    ':ITEMTYPE' => 'WF_GEN'
        
                );
                $this->_ORA->ejecutaFunc('wfa.wf_siac.avanzar',$bind);

                //cambiamos el estado del certificado
                $this->fun_cambia_estado_certificado($estado);

                $this->_ORA->Commit();   

                return "OK";
            
            }catch (Exception $e){
                print("ERROR: existe un error a la hora de listar los adjuntos.");
            }

         }


            //ml: funcion que abre el modal con la respuesta de la eliminacion
        public function fun_respuesta_eliminar(){
            
            $NUMERO_CASO    = $this->_SESION->getVariable("WF");
            
            $json = array();
            $MENSAJES = array();
            $CAMBIA = array();	
            $OPEN = array();			
            
            $mensaje_respuesta = $NUMERO_CASO;
           
            $json['RESULTADO'] = 'OK';			
            $MENSAJES[] = $mensaje_respuesta;

            
            
             $this->_TEMPLATE->assign('mensaje_respuesta_eliminar', $mensaje_respuesta);
             $this->_TEMPLATE->parse('main.div_respuesta_eliminar');
             
             
             $CAMBIA['#div_respuesta_eliminar'] = $this->_TEMPLATE->text('main.div_respuesta_eliminar');
             $OPEN['#div_respuesta_eliminar'] = 'open';
             $json['MENSAJES'] =  $MENSAJES;
             $json['CAMBIA'] = $CAMBIA;
             $json['OPEN'] = $OPEN;
             return json_encode($json);		
        }

        //ml: metodo para cambiar estado del certificado
        public function fun_cambia_estado_certificado($estado){
            
            try{

                $wf                 = $this->_SESION->getVariable("WF");
                $version            = $this->_SESION->getVariable("VERSION_CERTIFICADO");
                $tipo_certificado   = $this->_SESION->getVariable("TIPO_CERTIFICADO");
                

                $bind =  array(
                    ":p_id"=> $wf,
                    ":p_version" => $version,
                    ":p_estado" => $estado
                );
                $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_CAMBIAR_ESTADO_CERT",$bind); 
                $this->_ORA->Commit();   

            }catch (Exception $e){
                print("ERROR: existe un error a la hora de listar los adjuntos.");
            }
        }



   //|||||||||||||||||||||||||||||||||||||||||||||  medio de envio
   
   //validamos medio de envio electronico
   public function fun_validar_medio_electronico(){

        try{

            //$destinatarios = $this->_SESION->getVariable('DESTINATARIO');
            //var_dump($destinatarios);exit();

            //$p_destinatarioE    = $_POST['p_arrayDestinatarioE']; //destinatario eliminado
            //$p_destinatarioCE   = $_POST['p_arrayDestinatarioCE']; //dest. copia eliminado

            


            $sin_usuario = array();
            $this->_SESION->setVariable('SIN_USUARIO_SEIL',$sin_usuario);
            
            $wf                 = $this->_SESION->getVariable("WF");
            $version            = $this->_SESION->getVariable("VERSION_CERTIFICADO");
            $tipo_certificado   = $this->_SESION->getVariable("TIPO_CERTIFICADO");
        
            //var_dump($wf."///".$version);exit();

            

            //PASO 1 :: obtenemos todos los destinatarios del certificado
            $bind = array(":p_doc_id"=>$wf, ":p_doc_version" =>$version);
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_DISTRIBUCION_PKG.fun_listar_distribucion_xme",'function', $bind);
            $registros =$this->_ORA->FetchAll($cursor);

            
            $errores = 0;

            
            $x=0;
            foreach($registros as $dest){
                
                if($dest['DIS_MEDIO_ENVIO'] == 'SEIL'){
                    $rut = $dest['DIS_RUT']; 
                    $bind = array(':rut' => $rut,':aplic' => 'PUFED');				
                    $cursor = $this->_ORA->retornaCursor('web_usuarios_seil.get_usuarios_busqueda_aplic','function',$bind);			
                    $data = $this->_ORA->FetchAll($cursor);
                    //echo "<pre>";var_dump(count($data));echo "</pre>";
                    if(count($data) == 0){
                        $errores++;
                        $sin_usuario['SIN_USUARIO'][$x]['RUT'] =  $dest['DIS_RUT'];
                        $sin_usuario['SIN_USUARIO'][$x]['DV'] =  $dest['DIS_DV'];
                        $sin_usuario['SIN_USUARIO'][$x]['NOMBRE'] =  $dest['DIS_NOMBRE']; 
                        $x++;
                    }
                }
            }
            
            $this->_SESION->setVariable('SIN_USUARIO_SEIL',$sin_usuario);
            //echo "<pre>"; var_dump($sin_usuario);echo "</pre>";exit();

            if($errores > 0){
                //var_dump("HAY ERRORES");
                return 'NOK';//no paso la validacion , no se puede firmar
            }else{
                //var_dump("NO HAY ERRORES");
                return 'OK';//se puede firmar 
            }
            
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }   
    
   }

  //validamos medio de envio manual
   public function fun_validar_medio_manual(){

        try{

            $sin_direccion = array();
            $this->_SESION->setVariable('SIN_DIRECCION_POSTAL',$sin_direccion);

            $wf                 = $this->_SESION->getVariable("WF");
            $version            = $this->_SESION->getVariable("VERSION_CERTIFICADO");
            $tipo_certificado   = $this->_SESION->getVariable("TIPO_CERTIFICADO");

            $bind = array(":p_doc_id"=>$wf, ":p_doc_version" =>$version);
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_DISTRIBUCION_PKG.fun_listar_distribucion_xme",'function', $bind);
            $registros =$this->_ORA->FetchAll($cursor);

            //var_dump($wf."///".$version."///".$medio_envio);
            //var_dump($registros);exit();
        
            $errores = 0;
            $x=0;
            foreach($registros as $dest){
                if($dest['DIS_DIRECCION'] == 'undefine' or $dest['DIS_DIRECCION'] == null){
                    $errores++;
                    $sin_direccion['SIN_DIRECCION_POSTAL'][$x]['RUT'] =  $dest['DIS_RUT'];
                    $sin_direccion['SIN_DIRECCION_POSTAL'][$x]['DV'] =  $dest['DIS_DV'];
                    $sin_direccion['SIN_DIRECCION_POSTAL'][$x]['NOMBRE'] =  $dest['DIS_NOMBRE']; 
                    $x++;
                }   
            }

            
            $this->_SESION->setVariable('SIN_DIRECCION_POSTAL',$sin_direccion);

            if($errores > 0){
                return 'NOK';//no paso la validacion , no se puede firmar
            }else{
                return 'OK';//se puede firmar 
            }
        
        }catch (Exception $e){
            print("ERROR: existe un error a la hora de listar los adjuntos.");
        }

   }



   //ml: mostramos el modal de error para medio de envio 
   public function fun_respuesta_mde(){

    $json = array();
    $MENSAJES = array();
    $CAMBIA = array();	
    $OPEN = array();			
    
    $sin_usuario_seil = $this->_SESION->getVariable('SIN_USUARIO_SEIL');
    $sin_direccion_postal = $this->_SESION->getVariable('SIN_DIRECCION_POSTAL');

    //echo "<pre>"; var_dump($sin_usuario_seil);echo "</pre>"; exit();
    //echo "<pre>"; var_dump(count($sin_usuario_seil['SIN_USUARIO']));echo "</pre>"; exit();
   
   if($_POST['medio_envio'] == 'manual'){
        $mensaje_respuesta = 'Se ha detectado que tiene seleccionado envio manual, sin embargo, el destinatario '.$sin_direccion_postal['SIN_DIRECCION_POSTAL'][0]['NOMBRE'].' no tiene dirección postal, seleccione algún tipo envio distinto o modifique el destinatario.';
   }else if($_POST['medio_envio'] == 'electronico'){
        if(count($sin_usuario_seil['SIN_USUARIO']) > 0){
            $mensaje_respuesta = 'Se ha detectado que tiene seleccionado envio electrónico, sin embargo, el destinatario '.$sin_usuario_seil['SIN_USUARIO'][0]['NOMBRE'].' no tiene configurado usuarios SEIL que permitan su lectura, seleccione algún envio distinto de electrónico o borre el destinatario e incorpórelo como “Otro” ingresando el correo electrónico.';
        }
    }else{
        $mensaje_respuesta = '';
   }


    $json['RESULTADO'] = 'OK'; //con el OK permite abrir el modal		 	
    $MENSAJES[] = $mensaje_respuesta;

    
    
     $this->_TEMPLATE->assign('mensaje_medio_envio', $mensaje_respuesta);
     $this->_TEMPLATE->parse('main.div_respuesta_mde');
     
     
     $CAMBIA['#div_respuesta_mde'] = $this->_TEMPLATE->text('main.div_respuesta_mde');
     $OPEN['#div_respuesta_mde'] = 'open';
     $json['MENSAJES'] =  $MENSAJES;
     $json['CAMBIA'] = $CAMBIA;
     $json['OPEN'] = $OPEN;

     return json_encode($json);		

   }


}

class FPDI_R extends FPDI{
    function generarPDF($miPDF,$TITULO_DOCUMENTO,$X_DOCUMENTO,$Y_DOCUMENTO){


        $ARCHIVO = tempnam('', 'resol_');
        file_put_contents($ARCHIVO.".pdf",$miPDF);
        $pageCount = $this->setSourceFile($ARCHIVO.".pdf");
        
        for ($n = 1; $n <= $pageCount; $n++) {
            $this->AddPage();
            $this->SetMargins(20, 20);
            
            if($n == 1){
                $tplIdx = $this->importPage($n);
                
                $this->useTemplate($tplIdx, 0.5, 60);
                $this->SetFont('Helvetica', 'B', 20);
                $this->SetXY(75, 65);
                $this->Write(0, $TITULO_DOCUMENTO);
                
                
                
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetXY($X_DOCUMENTO, $Y_DOCUMENTO);
                $this->Write(0, "CER:");
            
            }else{
                $tplIdx = $this->importPage($n);
                $this->useTemplate($tplIdx);
            }
    
            $RUTA_IMG = dirname (__FILE__)."/../img/logocmf.png";
            $this->Image($RUTA_IMG, 20, 20, 50);
        }
        echo $this->Output();
        //$pdf->Output($ARCHIVO2.".pdf", 'F');
    }
}