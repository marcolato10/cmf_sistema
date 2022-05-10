<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1); 



class imprimir extends Pagina{

    public function onLoad(){
           #PREPRO $_VAR_GLOBAL->usa_plantilla_gral = 'NO'; 
    }

    public function main(){	

        //var_dump($_POST);exit();


        $wf = 459229;
        $tipo = 'certificado';
        $certificado_uv = $this->fun_listar_ultima_version($wf,$tipo);
        $version = $certificado_uv[0]['DOC_VERSION'];

        //echo "<pre>";var_dump($certificado_uv);echo "</pre>";
        //$this->ULTIMA_VERSION = $certificado_uv[0]['DOC_VERSION'];

        $html_certificado = '';
        $html_certificado .= '<tr>
                                    <td>'.$certificado_uv[0]['DOC_FECHA'].'</td>
                                    <td>'.$certificado_uv[0]['GDE_TIPOS_DOCUMENTO_TIPDOC_ID'].'</td>
                                    <td>'.$certificado_uv[0]['DOC_SGD'].'</td>
                                    <td>'.$certificado_uv[0]['DOC_FOLIO'].'</td>
                                    <td>Ver Documento</td>
                                </tr>'; 
     


        $destinatarios = $this->fun_listar_distribucion($wf,$version);
        $html_destinatarios = '';
        foreach($destinatarios as $dest){
            $html_destinatarios .= '<tr>
                                        <td>'.$dest['DIS_NOMBRE'].'</td>
                                        <td>'.$dest['DIS_DIRECCION'].'</td>
                                        <td>'.$dest['DIS_CORREO'].'</td>
                                    </tr>'; 
        }


        $this->_TEMPLATE->assign('listar_certificado', $html_certificado);
        $this->_TEMPLATE->parse('main.table_certificado');
        $this->_TEMPLATE->assign('lista_destinatarios', $html_destinatarios);
        $this->_TEMPLATE->parse('main.table_destinatarios');

        //echo "<pre>";var_dump($destinatarios);echo "</pre>";    

    }

     //ml: obtenemos la ultima version del certificado
     public function fun_listar_ultima_version($wf,$tipo){
        $bind = array(":p_wf"=>$wf, ":p_tipo" =>$tipo);
        $cursor = $this->_ORA->retornaCursor("GDE.GDE_DOCUMENTO_PKG.fun_listar_ultima_version",'function', $bind);
    
    
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
                        $r['DOC_ULTIMA_VERSION']=$r['DOC_ULTIMA_VERSION'];
                        $r['TIPENV_ID']=$r['TIPENV_ID'];
                        //$r['DOC_CUERPO']=$r['DOC_CUERPO']->load(); 
                        $r['DOC_FOLIO']=$r['DOC_FOLIO'];    
                        $r['DOC_SGD']=$r['DOC_SGD'];
                        $r['DOC_USUARIO_FIRMA']=$r['DOC_USUARIO_FIRMA'];
                        $r['DOC_ANO']=$r['DOC_ANO'];

                        $resCertificado[]=$r;    
                    }
                    $this->_ORA->FreeStatement($cursor);
                }    
                
        return $resCertificado;
    }

    //ml: listar distribucion segun la version
    public function fun_listar_distribucion($wf,$version){
        
        $bind = array(":p_doc_id"=>$wf, ":p_doc_version" =>$version);
        $cursor = $this->_ORA->retornaCursor("GDE.GDE_DISTRIBUCION_PKG.fun_listar_distribucion_xme",'function', $bind);
        $registros =$this->_ORA->FetchAll($cursor);
        
        return $registros;
    }


      //ml: lista el certificado por id y tipo
      public function fun_listar_certificado($wf,$tipo){
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
    }


}


?>