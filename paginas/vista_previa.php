<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1); 



   

    
    require_once('fpdf16/fpdf.php');
    require_once('fpdi_1.5.4/fpdi.php');
    require_once('Sistema/dompdf/autoload.inc.php');
    use Dompdf\Dompdf;
	
	
class vista_previa extends Pagina{

    public 	$CUERPO_CERTIFICADO;
    public 	$ADJUNTO_CERTIFICADO;

    
	public function main(){	}
    
    public function fun_inicio_vp(){

        $accion =  $this->_SESION->getVariable('ACCION_CERTIFICADO');
        $estado = $this->_SESION->getVariable("ESTADO_CUERPO");
        $wf = $this->_SESION->getVariable("WF");
        $version = $this->_SESION->getVariable("VERSION_CERTIFICADO"); 
        $mi_certificado = $this->fun_listar_certificado_xversion($wf,$version);
       

        //echo "<pre>";var_dump($accion.'//'.$estado.'//'.$wf.'//'.$version);echo "</pre>";
        //echo "<pre>";var_dump($mi_certificado);echo "</pre>";exit();

    

        if($accion == 'M'){//ESTAMOS EN LA ACCION MODIFICAR CERTIFICADO
            //print_r('PASO 1:: estamos en la accion modificar');
            if($estado == 1){ //no hizo ningun cambio en el cuerpo o se cancelo el cambio
               //hay que mostrar lo que hay en la bbdd
               //print_r('PASO 1.1:: estamos en la accion modificar');
               if(!$mi_certificado[0]['DOC_PDF']){//no hay pdf
                    //print_r('PASO :: NO HAY PDF');
                    if($mi_certificado[0]['DOC_CUERPO']){
                        //$this->_TEMPLATE->assign('r_cuerpo',$mi_certificado[0]['DOC_CUERPO']->load()); 
                        $this->_SESION->setVariable('CUERPO_CERTIFICADO',$mi_certificado[0]['DOC_CUERPO']->load());
                        return "ok";
                    }else{
                        $this->_SESION->setVariable('CUERPO_CERTIFICADO','ATENCIÓN:: Esta información no existe.'); 
                        return "ok";
                    }
                }else{//hay pdf
                    //print_r('PASO :: HAY PDF');
                    $mi_archivo = $mi_certificado[0]['DOC_PDF']->load();
                    //$this->_SESION->setVariable('archivo_pdf_adjunto',file_get_contents($mi_archivo));
                    $this->_SESION->setVariable('archivo_pdf_adjunto',$mi_archivo);
                    return "ok1";
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
                        return "ok1";
                    }    
                    
                }else{
                    
                    //print_r('PASO 1.2.2:: usamos el cuerpo');exit();

                    $this->CUERPO_CERTIFICADO = $_POST['p_cuerpo'];
                    $this->_SESION->setVariable('CUERPO_CERTIFICADO',$this->CUERPO_CERTIFICADO);
                    return "ok";
                }
            }else{
                //no existe esta opcion
            }
        
        
        }else if($accion == 'N'){ //estamos en la accion NUEVO CERTIFICADO

            if(isset($_FILES['file']['tmp_name'])){
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
                    return "ok1";
                }    
                
            }else{
    
                $this->CUERPO_CERTIFICADO = $_POST['p_cuerpo'];
                $this->_SESION->setVariable('CUERPO_CERTIFICADO',$this->CUERPO_CERTIFICADO);
                return "ok";
            }
    

        }else{ //ESTA ACCION NO EXISTE
            print_r('NO EXISTE ESTA ACCION');
        }

        
       
    }


      //listamos el certificado por la version [ya existe en modificar_docto.php]
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

    public function pdfVersion($filename)
	{ 
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

    public function fun_pdf_adjunto(){

        $estado = $this->_SESION->getVariable("ESTADO_CUERPO");
        
        /*if($estado == 1){
            //mlatorre
            //aqui hay que mostrar lo que sta en la bbdd
        }
        if($estado == 2){
         */   
            $PDF_ARC = $this->_SESION->getVariable('archivo_pdf_adjunto');
            
            //var_dump($PDF_ARC);exit();
            
            $pdf = new FPDI_R();

            $gde_tipdoc_id = 'certificado';
            $res_tipo = $this->fun_get_tipo_documento($gde_tipdoc_id);
            $TITULO_DOCUMENTO = $res_tipo[0]['TIPDOC_TITULO'];
            $X_DOCUMENTO = $res_tipo[0]['TIPDOC_POSICION_X_NUM'];
            $Y_DOCUMENTO = $res_tipo[0]['TIPDOC_POSICION_Y_NUM'];
            $pdf->generarPDF($PDF_ARC,$TITULO_DOCUMENTO,$X_DOCUMENTO,$Y_DOCUMENTO);
        //}



    }

  

    //arma PDF con el contenido del cuerpo
    public function fun_pdf_con_cuerpo(){
        
        //var_dump($this->_SESION->getVariable("ESTADO_CUERPO"));exit();
        $estado = $this->_SESION->getVariable("ESTADO_CUERPO");
        /*if($estado == 1){
            //aqui hay que mostrar lo que sta en la bbdd
        }
        if($estado == 2){
        */    
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

            $pdf->generarPDF($PDF_ARC,$TITULO_DOCUMENTO,$X_DOCUMENTO,$Y_DOCUMENTO);

        //}
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
    
    
              $res_tipo[]=$tipo;
    
            }			
            $this->_ORA->FreeStatement($cursor);
        }  
        
        return $res_tipo;
    
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
?>