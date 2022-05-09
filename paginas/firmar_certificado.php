<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1); 

  

require_once("class/correo.class.php");
require_once('class/crearDocumento.class.php');
require_once('class/mostrarDocumento.class.php');

require_once("Sistema/class/cryp.class.php");
require_once('fpdf16/fpdf.php');
require_once('fpdi_1.5.4/fpdi.php');
require_once('Sistema/dompdf/autoload.inc.php');
use Dompdf\Dompdf;


class firmar_certificado extends Pagina{

    public function main(){	}

    //ml: realizamos la accion de firmar el certificado
    public function fun_accion_firmar_certificado(){
        
        


        $miFirma            = $_POST['firma'];
        $miPassword         = $_POST['password'];
        $cuerpo             = $_POST['p_cuerpo'];
        $tipo_certificado   = $this->_SESION->getVariable('TIPO_CERTIFICADO');
        $accion             = $this->_SESION->getVariable('ACCION_CERTIFICADO');
        $estado             = $this->_SESION->getVariable("ESTADO_CUERPO");
        $wf                 = $this->_SESION->getVariable("WF");
        $version            = $this->_SESION->getVariable("VERSION_CERTIFICADO"); 
        $mi_certificado     = $this->fun_listar_certificado_xversion($wf,$version);


        $this->_SESION->setVariable('MI_FIRMA',$miFirma);
        $this->_SESION->setVariable('MI_PASSWORD',$miPassword);


        //echo "<pre>";var_dump($mi_certificado);echo "</pre>";exit();
        //var_dump($miFirma."//".$miPassword); exit();   
        //var_dump("ESTAMOS EN EL PHP DE FIRMAR CERTIFICADO");
        //var_dump($accion."//".$estado);
        //var_dump($_FILES);
        //var_dump($_POST);
        //exit();
        
        if($accion == 'M'){//ESTAMOS EN LA ACCION MODIFICAR CERTIFICADO
            //print_r('PASO 1:: estamos en la accion modificar');
            if($estado == 1){ //no hizo ningun cambio en el cuerpo o se cancelo el cambio
               //hay que mostrar lo que hay en la bbdd
               //print_r('PASO 1.1:: estamos en la accion modificar');
               if(!$mi_certificado[0]['DOC_PDF']){//no hay pdf
                    if($mi_certificado[0]['DOC_CUERPO']){
                        //$this->_TEMPLATE->assign('r_cuerpo',$mi_certificado[0]['DOC_CUERPO']->load()); 
                        $this->_SESION->setVariable('CUERPO_CERTIFICADO',$mi_certificado[0]['DOC_CUERPO']->load());
                        return "OK";
                    }else{
                        $this->_SESION->setVariable('CUERPO_CERTIFICADO','ATENCIÓN:: Esta información no existe.'); 
                        return "OK";
                    }
                }else{//hay pdf
                    $mi_archivo = $mi_certificado[0]['DOC_PDF']->load();
                    //$this->_SESION->setVariable('archivo_pdf_adjunto',file_get_contents($mi_archivo));
                    $this->_SESION->setVariable('archivo_pdf_adjunto',$mi_archivo);
                    return "OK1";

                   

                }
            
            
            
            }else if($estado == 2){
                //print_r('PASO 1.2:: estamos en la accion modificar');
                //echo "<pre>"; var_dump($_FILES); echo "</pre>";
                //echo "<pre>"; var_dump($_POST); echo "</pre>";
                
                
                if(isset($_FILES['file']['tmp_name'])){
                    //print_r('PASO 1.2.1:: tiene archivo');exit();
                    if($_FILES['file']['error'] == UPLOAD_ERR_OK){
                        $version =  $this->pdfVersion($_FILES['file']['tmp_name']);
                        $ARCHIVO_NAME = $_FILES['file']['tmp_name']; 
                        if($version != '1.4'){
                            $ARCHIVO = tempnam('', 'resol_');
                            system("ghostscript -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=".$ARCHIVO." ".$ARCHIVO_NAME."");
                            $ARCHIVO_NAME = $ARCHIVO;
                        }
                        $this->_SESION->setVariable('archivo_pdf_adjunto',file_get_contents($ARCHIVO_NAME));
                        //$this->_SESION->setVariable('archivo_pdf_adjunto_md5',md5_file($ARCHIVO_NAME));
                        unlink($ARCHIVO_NAME);
                        return "OK1";
                    }    
                    
                }else{
                    
                    //print_r('PASO 1.2.2:: usamos el cuerpo');exit();

                    $this->CUERPO_CERTIFICADO = $_POST['p_cuerpo'];
                    $this->_SESION->setVariable('CUERPO_CERTIFICADO',$this->CUERPO_CERTIFICADO);
                    return "OK";
                }
            }else{
                return "NOK";//NO SE PUEDE FIRMAR EL DOCUMENTO
            }
        
        
        }
    



    }

  


    //listamos el certificado por la version
    public function fun_listar_certificado_xversion($wf,$version){

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

    }

    //ml: realizar firma con cuerpo 
    public function fun_firmar_con_cuerpo(){

        $medio_envio = $_POST['medio_envio'];
        $numero = $this->fun_obtener_numero();
        $fecha = date('d/m/Y');
        //var_dump("EL NUMERO ES :".$numero);exit();

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
                    
        $html .=  '<div id="contenido">'. $this->limpiarTexto($this->_SESION->getVariable('CUERPO_CERTIFICADO'));
        $html .= '</div></div></body></html>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();



        $PDF_ARC = $dompdf->output();
        $pdf = new FPDI_R();

        $gde_tipdoc_id = 'certificado';
        $res_tipo = $this->fun_get_tipo_documento($gde_tipdoc_id);
        $TITULO_DOCUMENTO = $res_tipo[0]['TIPDOC_TITULO'];
        $X_DOCUMENTO = $res_tipo[0]['TIPDOC_POSICION_X_NUM'];
        $Y_DOCUMENTO = $res_tipo[0]['TIPDOC_POSICION_Y_NUM'];

        $respuesta = $pdf->generarPDF($PDF_ARC,$TITULO_DOCUMENTO,$X_DOCUMENTO,$Y_DOCUMENTO,$fecha,$numero);
        
        //firmamos y obtenemos el numero SGD  
        $numeroSGD = $this->fun_firmar_certificado($respuesta,$medio_envio);
        return $numeroSGD;            
    }

    //ml: realizar firma con adjunto
    public function fun_firmar_con_adjunto(){


        //return "OK";exit();

        $medio_envio = $_POST['medio_envio'];
        $numero = $this->fun_obtener_numero();
        $fecha = date('d/m/Y');
        
        //var_dump("EL NUMERO ES :".$numero);exit();

        $PDF_ARC = $this->_SESION->getVariable('archivo_pdf_adjunto'); //binario
        $pdf = new FPDI_R();

        $gde_tipdoc_id = 'certificado';
        $res_tipo = $this->fun_get_tipo_documento($gde_tipdoc_id);
        $TITULO_DOCUMENTO = $res_tipo[0]['TIPDOC_TITULO'];
        $X_DOCUMENTO = $res_tipo[0]['TIPDOC_POSICION_X_NUM'];
        $Y_DOCUMENTO = $res_tipo[0]['TIPDOC_POSICION_Y_NUM'];
        $respuesta = $pdf->generarPDF($PDF_ARC,$TITULO_DOCUMENTO,$X_DOCUMENTO,$Y_DOCUMENTO,$fecha,$numero);
    
        //firmamos y obtenemos el numero SGD   
        $numeroSGD = $this->fun_firmar_certificado($respuesta,$medio_envio);
        
 
        return $numeroSGD;
    }




   
    



    public function pdfVersion($filename){ 
		$fp = @fopen($filename, 'rb');
		if (!$fp) {
			return 0;
		}
		/* Reset file pointer to the start */
		fseek($fp, 0);
		/* Read 20 bytes from the start of the PDF */
		preg_match('/\d\.\d/',fread($fp,20),$match);
        fclose($fp);
        if (isset($match[0])) {
			return $match[0];
		} else {
			return 0;
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

    //ml: obtenemos el tipo de documento
    public function fun_get_tipo_documento($gde_tipdoc_id){
        
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
    
    }

    //ml: obtenemos el numero de folio segun el el tipo de certificado
    public function fun_obtener_numero(){

        $tipo_certificado = $this->_SESION->getVariable('TIPO_CERTIFICADO');

        $bind =  array(
            ":p_documento" => $tipo_certificado 
        );
        
        $numero = $this->_ORA->ejecutaFunc("GDE.GDE_NROS_PKG.fun_getNroDocumento",$bind);	
        return $numero;
    }

    
    //ml: genero el archivo nuevo con los expedientes adjuntos
    public function fun_generar_documento($documento){

        $adjuntos = $this->_SESION->getVariable("RSO_ADJUNTO");
        
        //var_dump($documento);exit();
        //var_dump($adjuntos);exit();
        //$ARCHIVO = tempnam('', 'resol_');
       
        $files[] = $documento;
        if($adjuntos != ""){
            foreach($adjuntos as $adj){
                //print_r($adj['ID']);print("<br>");
                if($this->fun_obtener_blob($adj['ID']) != 'NOK'){
                    $miAdjunto = $this->fun_obtener_blob($adj['ID']);
                    $ARCHIVO = tempnam('', 'resol_');
                    file_put_contents($ARCHIVO.".pdf",$miAdjunto);
                    $files[] = $ARCHIVO.".pdf";
                }
            }
        }

        //agregamos el adicional
        $files[] = $this->fun_agregar_hoja_adicional();
        
        //echo "<pre>";var_dump($files);echo "</pre>";exit();
        $ARCHIVO_NUEVO = $this->mergePDF($files);

        return $ARCHIVO_NUEVO;
      
    }


    //ml: ontenemos el blob segun el numero de expediente
    public function fun_obtener_blob($numero_expediente){

        $row = $this->getExpedienteDocumento($numero_expediente);	

        $bind_v = array(':id' => $row['ID_SISTEMA']);
        $cursor_variable = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getVariablesFuncion','function',$bind_v);
        $array = array();
        while($row_var = $this->_ORA->FetchArray($cursor_variable)){
            $array[] = $row[$row_var['WFA_VARIABLE']];		
        }
        $xml = $this->ejecutarFuncionXml($row['WFA_PACKAGE'],$row['WFA_FUNCION_XML'],$array );
        if ((string)$xml->Link == ""){
            $obj = $this->ejecutarFuncionArchivo($row['WFA_PACKAGE'],$row['WFA_FUNCION_ARCHIVO'],$array);
            return $obj->load();
        }else{
            return 'NOK';
        }
    }


    //ml: metodo que realiza el proceso de firma del certificado
    public function fun_firmar_certificado($mi_certificado,$medio_envio){

        define("ASINCRONO", "ASIN");
        define("SINCRONO", "SINC");
        
        //var_dump($mi_certificado);exit();
        $documento = $this->fun_generar_documento($mi_certificado);    
        
        $tipo_certificado       = $this->_SESION->getVariable('TIPO_CERTIFICADO');
        $mi_tipo_documento      = $this->fun_get_tipo_documento($tipo_certificado);
        $archivoParaFirmar      = $documento;
        $TIPO_DOCUMENTO_FIRMAR  = $mi_tipo_documento[0]['TIPDOC_APFIRMA'];
        $CLAVE_LDAP             = $this->_SESION->getVariable('MI_PASSWORD');
        $TIPO_FIRMA_POST        = $this->_SESION->getVariable('MI_FIRMA');
    
        //var_dump( $tipo_certificado ."//".$TIPO_DOCUMENTO_FIRMAR."//".$CLAVE_LDAP."//".$TIPO_FIRMA_POST);
        //var_dump($documento);  exit();
        //var_dump(file_get_contents($documento, FILE_USE_INCLUDE_PATH)); 
        //exit();
        //echo "SE FIRMA CORRECTAMENTE EL CERTIFICADO";
        //exit();    

        $TMP_DIR = "/tmp";
        $NUMERO_EXTERNO = $this->_ORA->ejecutaFunc('fst.fst_bus_firma_pkg.fun_getSecuenciaGener');
        $tipo_firma = explode('|',$TIPO_FIRMA_POST);
        $ID_FIRMA = $tipo_firma[0];
        $TIPO_FIRMA = $tipo_firma[1];
        $LLAVE = md5(md5($this->_SESION->USUARIO));
        $LLAVE = substr($LLAVE,0,16);
        $blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
        $PDF_FIRMAR =file_get_contents($archivoParaFirmar, FILE_USE_INCLUDE_PATH);
        $blob->WriteTemporary(Cryp::encrypt($PDF_FIRMAR,$LLAVE),OCI_TEMP_BLOB);
        
        $bind = array(
            ':p_id_eterno' => $NUMERO_EXTERNO,
            ':p_usuario' => $this->_SESION->USUARIO,
            ':p_archivo' => $blob,
            ':p_apl' => $TIPO_DOCUMENTO_FIRMAR,
            ':p_tipo' => 'PDF',
            ':p_tipo_id' => $ID_FIRMA,
            ':p_medio' => $TIPO_FIRMA,
            ':p_cargo' =>  'Generico'
        );

        $this->_ORA->ejecutaFunc('FST.FST_BUS_FIRMA_PKG.fun_setdocumentobus',$bind);
        $this->_LOG->log(__METHOD__.'('.__LINE__.')'.'BUS_BIND '.print_r($bind,true));

        //var_dump("id firma : ". $ID_FIRMA);
        //var_dump("tipo firma post : ".$TIPO_FIRMA_POST);
        //var_dump("tipo firma : ".$TIPO_FIRMA);

        if($TIPO_FIRMA == SINCRONO){
            //print_r("PASO 1 :: ES SINCRONO ");

            
            $AMBIENTE = $this->_ORA->ejecutaFunc('ambiente');
            $HOST = '';
            $HOST = ($AMBIENTE == 'PROD') ? 'http://intranet.svs.local' : $HOST;
            $HOST = ($AMBIENTE == 'TEST') ? 'http://pimiento.svs.local' : $HOST;
            $HOST = ($AMBIENTE == 'DESA') ? 'http://palto.svs.local' : $HOST;
            $JSON = array();
            $JSON['id_externo'] = array();
            $JSON['id_externo'][] = Cryp::encrypt($NUMERO_EXTERNO,$LLAVE);
            $JSON['cargo'] = Cryp::encrypt($TIPO_DOCUMENTO_FIRMAR,$LLAVE);  //se necesita en caso de firma de XML de gobierno
            $JSON['id_firma'] = $ID_FIRMA;                                                 
            $JSON['tipo_firma'] = $TIPO_FIRMA;
            $JSON['apl'] = $TIPO_DOCUMENTO_FIRMAR;
            $JSON['formato'] = 'PDF';
            $JSON = json_encode($JSON);
            $PASSWORD_ENC = Cryp::encrypt($CLAVE_LDAP,$LLAVE);
            $SERVICIO_REST = $HOST.'/intranet/aplic/restfm/firma/';

            $USUARIO = Cryp::encrypt($this->_SESION->USUARIO, $LLAVE);
            $BASIC = $LLAVE.$USUARIO;
            $BASIC = base64_encode($BASIC.":".$PASSWORD_ENC);
            $curl = curl_init($SERVICIO_REST);                        
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Basic '. $BASIC));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $JSON );
            $curl_response = curl_exec($curl);

            curl_close($curl);
            $JSON_RESPUESTA_CURL = json_decode($curl_response);
            
            if((string)$JSON_RESPUESTA_CURL->RESULTADO == 'OK'){

                

                $bind = array(
                            ':p_usuario' => $this->_SESION->USUARIO,
                            ':p_busfir_apl' => $TIPO_DOCUMENTO_FIRMAR,
                            ':p_tipo_documento' => 'PDF',
                            ':p_medfir_id' => $ID_FIRMA,
                            ':p_externo' => $NUMERO_EXTERNO
                            );
                
                $cursor = $this->_ORA->retornaCursor('FST.FST_BUS_FIRMA_PKG.fun_getoficionorevisado','function',$bind);
                while($data = $this->_ORA->FetchArray($cursor)){
                   if($data['BUSFIR_FIRMADO'] == 'SI'){
                        try{
                            $firmado = $data['BUSFIR_ARCHIVO_FIRMADO']->load();
                            $firmado =  Cryp::decrypt($firmado, $LLAVE); //archivo firmado
                            

                            /*
                            $ARCHIVO = tempnam('', 'firmado_');
                            file_put_contents($ARCHIVO.".pdf",$firmado);

                            

                            $tempFile = basename($ARCHIVO.".pdf").PHP_EOL;  //El documento
                            $nameFile = $this->clearNombreArchivo(str_replace("'", "''",  $tempFile)); //El nombre original del fichero en la máquina del cliente.
                            $nameFile = htmlspecialchars($tempFile, ENT_QUOTES, 'UTF-8');
                            
                            var_dump($ARCHIVO.".pdf");
                            var_dump("////////////////");
                            var_dump($nameFile);
                            exit();
                            */

                            //aqui esta el documento


                            /*$numero_archivo = $this->_ORA->ejecutaFunc('wfa.WFA_DOCTOS_DOC2_PKG.getNroArchivo');
                              $blob = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                              $blob->WriteTemporary($firmado,OCI_TEMP_BLOB);
                              $bind = array(
                                        ':p_wfa_id' => $numero_archivo,
                                        ':p_wfa_tipo' => '',
                                        ':p_wfa_descripcion' => '[Firmado]'.(string)$xml->Nombre_Archivo,
                                        ':p_wfa_nombre' => '[Firmado]'.(string)$xml->Nombre_Archivo,
                                        ':p_wfa_mime' => 'application/pdf',
                                        ':p_wfa_hash' => md5($firmado),
                                        ':p_wfa_peso' => strlen($firmado),
                                        ':p_wfa_archivo' => $blob,
                                        );

                                $this->_ORA->ejecutaProc('wfa.WFA_DOCTOS_DOC2_PKG.proc_ins_wfa_archivos',$bind);
                                $id_wfa_documento =  $this->_ORA->ejecutaFunc("wfa.wfa_doctos_doc2_pkg.getNroDocumento",null);
                                
                                $bind = array(':p_secuencia_docto'=>$id_wfa_documento,
                                            ':p_sistema' => 'wfa',
                                            ':p_tipo'=>'blob',
                                            ':p_subtipo'=>'I',
                                            ':p_id_referencial'=>$numero_archivo,
                                            ':p_tiene_anexo'=>'N'
                                            );
                                $this->_ORA->ejecutaProc("wfa.wfa_funciones_wf.prc_inserta_documento", $bind);

                                $bind2= array (':p_secuencia_docto'=>$id_wfa_documento,
                                            ':p_secuencia_docto_anexo'=>$id_wfa_documento,
                                            ':p_orden'=>0,
                                            ':p_tipo'=>'principal'
                                            );
                                $this->_ORA->ejecutaProc("wfa.wfa_funciones_wf.prc_inserta_anexo", $bind2);              

                                $bind3= array (':p_caso_id'=>$_POST['caso_id'],
                                            ':p_secuencia_docto'=>$id_wfa_documento,
                                            ':p_con_anexo'=>'N');

                                                                          
                                $this->_ORA->ejecutaProc("wfa.wfa_funciones_wf.prc_inserta_caso_docto", $bind3);*/

                                //$this->_ORA->Commit();
                                //mensaje de exito
                                
                                //echo "El documento fué firmado con éxito y se adjuntó al expediente.";
                                



                                //||||||||||||||||||||||||||||||||||||||||||||||||||  descometar despues de las prueba
                                $numeroSGD = $this->grabarSGD($firmado);               
                                         
                                unlink($archivoParaFirmar);

                              
                                if($numeroSGD != ""){
            
                                    //print_r("ENTRO A LA BITACORA");
                                    $NUMERO_CASO = $this->_SESION->getVariable("WF");
                                    $comentario = "Se Firmó el Documento";
                                    $bind = array(':caso'=>$NUMERO_CASO, ':usuario' => $this->_SESION->USUARIO, ':desde' => $this->_SESION->USUARIO, ':msg' => $comentario );
                                        $this->_ORA->ejecutaFunc("wfa.wf_rso_pkg.fun_bitacora", $bind);
                                        $this->_LOG->log("Bitacora en el WF: ".$NUMERO_CASO.' con bind '.print_r($bind,true));  
                                        //$this->_ORA->Commit();   
                                    
                                    
                                    //poblamos quien firma el certificado
                                    $this->fun_agregar_quien_firma($numeroSGD);
                                    
                                    //AQUI DEBERIA NOTIFICAR LOS ENVIOS |||||||||||||||||mlatorre
                                    $this->fun_notificar_envios($medio_envio,$documento); 
                                }
                                
                                $this->_ORA->Commit();
                                return $numeroSGD;          
                                exit();
                                            
                        }catch(Exception $e){
                                 //tratar el error
                                 return "NOK";
                                 echo "ERROR:: NO SE PUDO FIRMAR";
                        }

                    }                             

                }

            }else{
                 //Tratar el error
                $error =  (String)$JSON_RESPUESTA_CURL->ERROR;
                
                if(trim($error == "")){
                    //tratar el error
                    echo "Error Inesperado en la Firma";

                }else{
                    //tratar el error
                    echo $error;
                }
                
                return "NOK";
                //exit();
            }
    
        }else{
            return "NOK";
            print_r("PASO 1 :: NO ES SINCRONO");
            print_r( $tipo_certificado ."//".$TIPO_DOCUMENTO_FIRMAR."//".$CLAVE_LDAP."//".$TIPO_FIRMA_POST);
        }        
    }

    //ml: aqui notificamos los envios a los dstinatarios correspondiente
    public function fun_notificar_envios($medio_envio,$ruta_certificado){

        //var_dump($medio_envio);

        $wf         = $this->_SESION->getVariable("WF");
        $version    = $this->_SESION->getVariable("VERSION_CERTIFICADO");
        //$tipo_certificado   = $this->_SESION->getVariable("TIPO_CERTIFICADO");
        $bind       = array(":p_doc_id"=>$wf, ":p_doc_version" =>$version);
        $cursor     = $this->_ORA->retornaCursor("GDE.GDE_DISTRIBUCION_PKG.fun_listar_distribucion_xme",'function', $bind);
        $registros  = $this->_ORA->FetchAll($cursor);

        if($medio_envio != 'noen'){
            if($medio_envio == 'elect'){//medio envio electronico
                foreach($registros as $dest){
                    if($dest['DIS_MEDIO_ENVIO'] == 'SEIL'){

                        //1.- [ok] Hay agregar los destinatarios a la tabla GDE_CONTROL_SEIL 
                        //2.- Enviar correo de notificación a los usuarios indicando del envio del correo.
                        
                        $bind2 =  array(
                            ":p_doc_id"=> $wf,
                            ":p_doc_version"=>$version,
                            ":p_dis_secuencia"=> $dest['DIS_SECUENCIA']
                        );    
                        $this->_ORA->ejecutaProc("GDE.GDE_CONTROL_SEIL_PKG.PRC_AGREGAR_CONTROL_SEIL",$bind2);

                        //FALTA AQUI :: falta el envio del correo de notificacion
                            

                    }else if($dest['DIS_MEDIO_ENVIO'] == 'EMAIL'){
                        
                        //despachar el certificado vía correo electrónico a el/los correos que se encuentran configurados, estos se deben enviar mediante la clase que retorna un ID de correo, este se debe actualizar en la tabla GDE_DISTRIBUCION.DIS_ID_CORREO.
                          
                        
                        //AQUI VOY :: FUNCIONA PERO NO RETORNA ID
                        $id_correo = $this->fun_enviar_correo_notificacion($dest['DIS_CORREO'],$ruta_certificado);
                        

                        //DESCOMENTAR CUANDO SOLUCIONE EL TEMA DEL ID DEL CORREO    
                        //echo "<pre>";var_dump($id_correo);echo "</pre>";    
                        //$this->fun_agregar_correo_envio($id_correo,$dest['DIS_SECUENCIA']);

                        
                    }
                }
            }else{//medio envio manual

                //Se debe derivar el caso a oficina de partes para que pueda despachar el 
                //documento (acá se debe generar un Módulo de despacho en wf)
                $this->fun_derivar_oficina_parte();

                //FALTA AQUI:: falta el envio del correo de notificacion

            }
        }

        $this->_ORA->Commit();
       
    }

    //ml: derivamos a la oficina de partes
    public function fun_derivar_oficina_parte(){
        
        try{  
            $bindOP = array(':p_rol' => 'OFPARTES');
            $cursorOP = $this->_ORA->retornaCursor('DOC2.doc_roles_documento_pkg.fun_usuarios_roles','function',$bindOP);
            $registrosOP  = $this->_ORA->FetchAll($cursorOP);

            $usuarios = array();
            foreach($registrosOP as $rop){
                $usuarios[] = $rop['EP_USUARIO'];
            }
            
            //derivamos a oficina de partes
            $this->fun_asignarVarios($usuarios); 
            
            //falta construir el modulo de wf     

        }catch(Exception $e){
            $this->_LOG->error(print_r($e));
    }
    }

    
    //ml: asignamos todos los usuarios a la oficina de parte
    public function fun_asignarVarios($usuarios){ //usuarios es un arreglo con los usuarios.
        
        $wf = $this->_SESION->getVariable("WF");
        
        try{                                       
            $usuarios_coll = $this->_ORA->NewCollection("VALUE_ARRAY");
            
            foreach ($usuarios as $usr){
                $usuarios_coll->append($usr);
            }

                $bind = array(':caso'=>$wf, ':usuario' => $usuarios_coll);
                $this->_ORA->ejecutaProc("wfa.wf_rso_pkg.fun_asignarVarios", $bind);
                $this->_LOG->log("Se fun_asignarVarios el WF: ".$wf.' con bind '.print_r($bind,true));
        }catch(Exception $e){
                $this->_LOG->error(print_r($e));
        }
    }


    //ml: enviamos correo de notificacion con certificado adjunto ||| REVISAR NO FUNCIONA
    public function fun_enviar_correo_notificacion($email_destino,$ruta_pdf){

        
        $correo = new Correo();

        $correo->ORA = $this->_ORA;
        $correo->DESDE = 'noresponder@svs.cl';
        $correo->DESDE_NOMBRE = 'Comisión para el Mercado Financiero';                                                                    

        $correo->ASUNTO = 'PRUEBA Envio documento XXXXX'; //XXXXX => CER:xxx    

        $correo->TEXTO = 'Estimado (a) xxxxxxx
        Con fecha xxxxxx, esta Comisión hace envío del documento adjunto XXXXXX
        Atentamente, Comisión para el Mercado Financiero.';

        $correo->APLIC = 'PUGDE';
        $correo->ADJUNTO = true;        
        $correo->setPara($email_destino);
        $correo->setCopiaOculta('culloa@svs.cl');
        //$correo->setAdjunto($ruta_pdf,'{SGD}.pdf');
        $ID = $correo->enviar();
        
        return $ID; 

        


    }

    //ml: agregamos el id del correo enviado en la firma a los destinatarios
    public function fun_agregar_correo_envio($id_correo,$secuencia){

        $wf         = $this->_SESION->getVariable("WF");
        $version    = $this->_SESION->getVariable("VERSION_CERTIFICADO");

        $bind =  array(
            ":p_dis_id_correo" => $id_correo,
            ":p_version" => $version,
            ":p_doc_id" => $wf,
            ":p_secuencia" => $secuencia
        );
        
        $this->_ORA->ejecutaProc("GDE.GDE_DISTRIBUCION_PKG.PRC_ACTUALIZAR_CORREO",$bind);
        $this->_ORA->Commit();
    }


    public function fun_respuesta_firma_certificado(){


        $json = array();
        $MENSAJES = array();
        $CAMBIA = array();	
        $OPEN = array();			
        
        $numero_sgd = $_POST['numero_sgd'];
        $mensaje_respuesta_firma = 'El documento fue firmado con éxito y se adjuntó al expediente con el numero SGD : '.$numero_sgd.' .';
        $botonera_respuesta = '<div class="secBotonera">
        <button class="btn btn-warning" type="button" id="btnFormCerrar" name="btnFormCerrar" onclick="accionBtnFormCerrarFirma();">OK</button></div>';               
        
       
        $json['RESULTADO'] = 'OK';			
        $MENSAJES[] = $mensaje_respuesta_firma;

        //$this->_TEMPLATE->assign('mensaje_respuesta',$mensaje_respuesta);
        //$this->_TEMPLATE->parse('main.div_respuesta_firma');
         
        //cambiamos el estado del certificado
        $estado = "firma";
        $this->fun_cambia_estado_certificado($estado);

        $this->_TEMPLATE->assign('mensaje_respuesta_firma',$mensaje_respuesta_firma);
        $this->_TEMPLATE->assign('botonera_respuesta',$botonera_respuesta);      
        $this->_TEMPLATE->parse('div_respuesta_firma');
        

         $CAMBIA['#div_respuesta_firma'] = $this->_TEMPLATE->text('div_respuesta_firma');
         $OPEN['#div_respuesta_firma'] = 'open';
         $json['MENSAJES'] =  $MENSAJES;
         $json['CAMBIA'] = $CAMBIA;
         $json['OPEN'] = $OPEN;
         return json_encode($json);		

    }

    function mergePDF($files){
        try{

            $ARCHIVO = tempnam('','arch_tmp');
            unlink($ARCHIVO);
            $ARCHIVO.=".pdf";
            $LINEA_IMPLODE = implode(' ',$files);
            system('pdftk '.$LINEA_IMPLODE.' cat output '.$ARCHIVO);
            foreach($files as $arc){
                unlink($arc);
            }
            return $ARCHIVO;
        }catch(Exception $e){
            print_r($e);
        }
    }

    /////HALEYM
    public function grabarSGD($contenido) {    
        
        if($contenido){

            $s = $this->_ORA->Select("Select to_char(sysdate,'DD/MM/YYYY HH24:MI:SS') fecha from dual");
            $r = $this->_ORA->FetchArray($s);
            $dateCreate = $r["FECHA"];
            $objGrabar = new crearSgd($this->_ORA, $this->_SESION);
            $datos = array(
                "p_origen" => 'R',
                "p_tipo_docto" => 'DOC_SESION_COM',
                "p_tipo_entidad" => 'PUPUB',
                "p_medio_recepcion" => 'W',
                "p_descripcion" => 'REGISTRO DE SESIONES',
                "p_titulo" => NULL,
                "p_vig_papel" => NULL,
                "p_vig_magnetica" => NULL,
                "p_folio_docto" => NULL,
                "p_publica_web" => NULL,
                "p_clasificacion" => NULL,
                "p_forma_envio_principal" => NULL,
                "p_fecha_despacho" => NULL,
                "p_unidad_recepcion" => NULL,
                "p_forma_envio2" => NULL,
                "p_elimina_papel" => NULL,
                "p_elimina_magnetica" => NULL
            );

            $objGrabar->setDatos($datos);
            $errores = $objGrabar->grabar_documento();

            $remitente = array("p_rem_rut"=>$this->_SESION->RUT, "p_rem_dv"=>$this->_SESION->getDv(), "p_rem_nombre"=>$this->_SESION->NOMBRE);
            $errorRem = $objGrabar->graba_remitente($remitente);
            
            $nro_sgd = $objGrabar->getNrosgd();
            
          
            $error_doc      = $objGrabar->fun_cargar_docto_dir($contenido,0);
            $error_doc_OCR  = $objGrabar->fun_ocr_docto_dir($contenido,0);

            
            return $nro_sgd;
        
        //fin cont    
        }else{
            return "NOK";
        }
    }
    
    //HALEYM
    public function verDoctoSGD($nroSGD){
        $verDocumento = new verDocumento();
        $urlDocto = $verDocumento->getUrl($nroSGD);
        $ver = "<a href='#' onclick='javascript:window.open(\"".$urlDocto."\");'><img src='Sistema/img/doc.png' width='24px' height='24px'></a>";
        return $ver;
    }

    //HALEYM
    public function clearNombreArchivo($nombreArchivo) {
        $nueva_cadena = strtr(utf8_decode($nombreArchivo), array("á" => "a", 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'ñ' => 'n', 'Ñ' => 'N'));

        $nueva_cadena = preg_replace("/[^ A-Za-z0-9_.]/", "_", $nueva_cadena);

        return $nueva_cadena;
    }


   //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
   //|||||||||||||||||||||||||| COMUN UTILIZADO |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
   //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


     //ml: metodo para cambiar estado del certificado
     public function fun_cambia_estado_certificado($estado){
            
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
    }

    //ml: agregamos quien firma el certificado // poblamos GDE_DOCUMENTO con los campos obtenidos de la firma 
    public function fun_agregar_quien_firma($numero_sgd){

        $wf                 = $this->_SESION->getVariable("WF");
        $version            = $this->_SESION->getVariable("VERSION_CERTIFICADO");
        $numero_folio       = $this->fun_obtener_numero();  
        $usuario_firma      = $this->_SESION->USUARIO;
        $doc_ano            = date("Y");
        
        
        //var_dump($wf."///".$version."///".$numero_folio."///".$usuario_firma."///".$numero_sgd);exit();

        
        $bind =  array(
            ":p_id" => $wf,
            ":p_version" => $version,
            ":p_doc_folio" => $numero_folio,
            ":p_doc_sgd" => $numero_sgd,
            ":p_doc_usuario_firma" => $usuario_firma,
            ":p_doc_ano" => $doc_ano
        );    
        $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_AGREGAR_FIRMANTE",$bind); 
        $this->_ORA->Commit();     

    }


    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||  EXPEDIENTE ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    public function getExpedienteDocumento($id){
			
        $bind = array(':id' => $id);
        $cursor = $this->_ORA->retornaCursor('WFA_DOCTOS_PKG.getDocumento','function',$bind);
        return $this->_ORA->FetchArray($cursor);
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

    public function ejecutarFuncionArchivo($package,$funcion, $variable){
        $cant = count($variable);			
        $bindPkg = array();
        foreach($variable as $key => $var){				
            $bindPkg[":var$key"] = $var;
        }
    
        $blob = $this->_ORA->ejecutaFunc($package.".".$funcion,$bindPkg,'BLOB');
        return $blob;
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
    } 		


    //ml: agregamos ultimo file de la firma 
    public function fun_agregar_hoja_adicional_OLD(){
        



        $pdf_fpdi = new FPDI();

		$pageCount = $pdf_fpdi->setSourceFile('Sistema/paginas/plantillas/hojaAdicional.pdf');

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf_fpdi->importPage($pageNo);
            $size = $pdf_fpdi->getTemplateSize($templateId);

            if ($size['w'] > $size['h']) {
                $pdf_fpdi->AddPage('L', array($size['w'], $size['h']));
            } else {
                $pdf_fpdi->AddPage('P', array($size['w'], $size['h']));
            }		
            $pdf_fpdi->useTemplate($templateId);
        }    
        
        $pdf_fpdi->AddFont('Verdana','','verdana.php');
        $pdf_fpdi->SetFont('Verdana','',10);
        $bind = array(':p_usuario' => $this->_SESION->USUARIO);
        $NOMBRE_USUARIO = $this->_ORA->ejecutaFunc('wfa.wfa_usr.getNombreUsuario',$bind);

        $pdf_fpdi->Text(145, 210, utf8_decode($NOMBRE_USUARIO));
        $pdf_fpdi->Text(145, 218, utf8_decode('Comisión para el Mercado Financiero'));
        $pdf_fpdi->Text(145, 226, date("Y.m.d"));


        $ruta_adicional = 'Sistema/paginas/plantillas/hojaAdicional.pdf';
        $adicional_modificado = file_get_contents($ruta_adicional, FILE_USE_INCLUDE_PATH);
        
        $ADICIONAL = tempnam('', 'adicional_');//temporal del adicional
        file_put_contents($ADICIONAL.".pdf",$adicional_modificado);
        
        $miAdicional = $ADICIONAL.".pdf";//ruta del temporal adicional
        
        //var_dump($archivo_adicional); exit();
        //var_dump($miAdicional); exit();
        
        return $miAdicional;





    }


    public function fun_agregar_hoja_adicional(){

        //print_r("AGREGAMOS LA HOJA ADICIONAL");exit();

        $ruta_adicional = 'Sistema/paginas/plantillas/hojaAdicional.pdf';
        $adicional = file_get_contents($ruta_adicional, FILE_USE_INCLUDE_PATH);

        $ARCHIVO = tempnam('', 'adicional_');
        file_put_contents($ARCHIVO.".pdf",$adicional);

        $pdf_fpdi = new FPDI();
        $pageCount = $pdf_fpdi->setSourceFile($ARCHIVO.".pdf");

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf_fpdi->importPage($pageNo);
            $size = $pdf_fpdi->getTemplateSize($templateId);

            if ($size['w'] > $size['h']) {
                $pdf_fpdi->AddPage('L', array($size['w'], $size['h']));
            } else {
                $pdf_fpdi->AddPage('P', array($size['w'], $size['h']));
            }		
            $pdf_fpdi->useTemplate($templateId);
        }    

        $pdf_fpdi->AddFont('Verdana','','verdana.php');
        $pdf_fpdi->SetFont('Verdana','',10);
        $bind = array(':p_usuario' => $this->_SESION->USUARIO);
        $NOMBRE_USUARIO = $this->_ORA->ejecutaFunc('wfa.wfa_usr.getNombreUsuario',$bind);

        $pdf_fpdi->Text(145, 210, utf8_decode($NOMBRE_USUARIO));
        $pdf_fpdi->Text(145, 218, utf8_decode('Comisión para el Mercado Financiero'));
        $pdf_fpdi->Text(145, 226, date("Y.m.d"));


        $pdf_fpdi->Output($ARCHIVO.".pdf" , 'F');
        
        $miArchivo = $ARCHIVO.".pdf";

        return $miArchivo;


    }




}

class FPDI_R extends FPDI{
    function generarPDF($miPDF,$TITULO_DOCUMENTO,$X_DOCUMENTO,$Y_DOCUMENTO,$fecha,$numero){

        
        $ARCHIVO = tempnam('', 'resol_');
        file_put_contents($ARCHIVO.".pdf",$miPDF);
       
      
       
        $pageCount = $this->setSourceFile($ARCHIVO.".pdf");
        $this->AddPage();
        $this->SetMargins(20, 20);



            for ($n = 1; $n <= $pageCount; $n++) {

                if($n == 1){
                    $tplIdx = $this->importPage($n);
                    
                    $this->useTemplate($tplIdx, 0.5, 60);
                    $this->SetFont('Helvetica', 'B', 20);
                    $this->SetXY(75, 65);
                    $this->Write(0, $TITULO_DOCUMENTO);
                    
                    $this->SetFont('Helvetica', 'B', 10);
                    $this->SetXY(125, 55);
                    $this->Write(0, "FECHA :".$fecha);
                   
                    $this->SetFont('Helvetica', 'B', 10);
                    $this->SetXY($X_DOCUMENTO, $Y_DOCUMENTO);
                    $this->Write(0, "CER: ".$numero);
                
                }else{
                    $tplIdx = $this->importPage($n);
                    $this->useTemplate($tplIdx);
                }
        
                $RUTA_IMG = dirname (__FILE__)."/../img/logocmf.png";
                $this->Image($RUTA_IMG, 20, 20, 50);

            }

            //var_dump($ARCHIVO.".pdf");exit(); 
            //var_dump($this->setSourceFile($ARCHIVO.".pdf"));
            
            //$miArchivo = pathinfo($ARCHIVO.".pdf");
            $miArchivo = $ARCHIVO.".pdf";
            //$miArchivo = file($ARCHIVO.".pdf");
            
          

            //$ARCHIVO2 = tempnam('', 'resol_');
            //$miPDF2 = file_put_contents($ARCHIVO2,$ARCHIVO.".pdf");
            //var_dump($miPDF);exit();
            //$miPDF = $this->Output();
            //$miPDF3 = $this->Output('', 'basic.pdf');
            //$miPDF3 = $pdf->Output($ARCHIVO2.".pdf", 'F');


            

            return $miArchivo;


        }



}

?>