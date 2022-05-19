<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1); 


include('Sistema/class/paginapurso.class.php');
include('Sistema/class/claseSistema.class.php');
require 'class/mostrarDocumento.class.php';
include('Sistema/class/certificado.class.php');

class imprimir extends Pagina{

    public function onLoad(){

        #PREPRO $_VAR_GLOBAL->usa_plantilla_gral = 'NO'; 
        $this->_CERTIFICADO = new Certificado($this);     
    }

    public function main(){	


   

        try{

                //var_dump($_GET['caso']);exit();

                $verDocumento = new verDocumento();
                //echo $verDocumento->getUrl('2022051463510');exit();

                $wf = $_GET['caso'];
                $tipo_certificado = 'certificado';
                $url_certificado = $verDocumento->getUrl($wf);
                //$certificado_uv = $this->fun_listar_ultima_version($wf,$tipo_certificado);
                $certificado_uv = $this->_CERTIFICADO->fun_listar_ultima_version($wf,$tipo_certificado);
                $version = $certificado_uv[0]['DOC_VERSION'];
                $estado_certificado = $certificado_uv[0]['GDE_ESTADO_DOCUMENTO_ESTDOC_ID'];
                
                $resultado_tipo = $this->fun_get_tipo_documento($tipo_certificado);
                $label_certificado = $resultado_tipo[0]['TIPDOC_LABEL_NUMERO'];

                //echo $estado_certificado; exit();
                if($estado_certificado == 'firma'){
                    
                    //echo "<pre>";var_dump($certificado_uv);echo "</pre>";
                    //$this->ULTIMA_VERSION = $certificado_uv[0]['DOC_VERSION'];

                    $html_certificado = '';
                    $html_certificado .= '<tr>
                                                <td>'.$certificado_uv[0]['DOC_FECHA'].'</td>
                                                <td>'.$label_certificado.' '.$certificado_uv[0]['DOC_FOLIO'].'</td>
                                                <td>'.$certificado_uv[0]['DOC_SGD'].'</td>
                                                <td>'.$certificado_uv[0]['DOC_FOLIO'].'</td>
                                                <td><a href="'.$url_certificado.'" target="_blank">Ver Documento</a></td>
                                            </tr>'; 
                


                    $destinatarios = $this->fun_listar_distribucion($wf,$version);
                    $html_destinatarios = '';
                    $html_lista_oculta = '';
                    foreach($destinatarios as $dest){
                        if($dest['DIS_CORREO'] == null or $dest['DIS_CORREO'] == 'undefined'){
                            $correo = ''; 
                        }else{
                            $correo = $dest['DIS_CORREO'];
                        }
                        $html_destinatarios .= '<tr>
                                                    <td>'.$dest['DIS_NOMBRE'].'</td>
                                                    <td>'.$dest['DIS_DIRECCION'].'</td>
                                                    <td>'.$correo.'</td>
                                                </tr>'; 
                        
                        $html_lista_oculta .= '<input type="hidden" class="numero" value="'.$certificado_uv[0]['DOC_FOLIO'].'">
                        <input type="hidden" class="sgd" value="'.$certificado_uv[0]['DOC_SGD'].'">
                        <input type="hidden" class="cargo" value="'.$dest['DIS_CARGO'].'">
                        <input type="hidden" class="nombre" value="'.$dest['DIS_NOMBRE'].'">
                        <input type="hidden" class="direccion" value="'.$dest['DIS_DIRECCION'].'">
                        <input type="hidden" class="fecha" value="'.$certificado_uv[0]['DOC_FECHA'].'">';                        
                    }

                    $forma_despacho = $certificado_uv[0]['TIPENV_NOMBRE'];

                    $this->_TEMPLATE->assign('forma_despacho', $forma_despacho);
                    $this->_TEMPLATE->assign('listar_certificado', $html_certificado);
                    $this->_TEMPLATE->assign('lista_oculta', $html_lista_oculta);
                    $this->_TEMPLATE->parse('main.table_certificado');
                    $this->_TEMPLATE->assign('lista_destinatarios', $html_destinatarios);
                    $this->_TEMPLATE->parse('main.table_destinatarios');

                    //echo "<pre>";var_dump($destinatarios);echo "</pre>";    
                
                }else{
                    echo "NO SE PUEDE IMPRIMIR , CERTIFICADO AUN NO ESTA FIRMADO";
                }

            }catch (Exception $e){
                $this->util->mailError($e);
            }   


    }



    //ml: listar distribucion segun la version
    public function fun_listar_distribucion($wf,$version){
        
        try{
        
            $bind = array(":p_doc_id"=>$wf, ":p_doc_version" =>$version);
            $cursor = $this->_ORA->retornaCursor("GDE.GDE_DISTRIBUCION_PKG.fun_listar_distribucion_xme",'function', $bind);
            $registros =$this->_ORA->FetchAll($cursor);
        
            return $registros;
        
        }catch (Exception $e){
            $this->util->mailError($e);
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
                        $r['DOC_CUERPO']=$r['DOC_CUERPO']->load(); 
                        
    
                        $resCertificado[]=$r;    
                    }
                    $this->_ORA->FreeStatement($cursor);
                }    
                
            return $resCertificado;
        
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


}


?>