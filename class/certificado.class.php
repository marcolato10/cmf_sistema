<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1); 


//require_once(dirname(__FILE__).'/clasificacion.class.php');
//require_once(dirname(__FILE__).'/destinatario.class.php');
//require_once(dirname(__FILE__).'/adjunto.class.php');
//require_once(dirname(__FILE__).'/notificacion.class.php');
//require_once(dirname(__FILE__).'/funcionario.class.php');

	class Certificado extends ClaseSistema{

  	
		/**  ATRIBUTOS DE LA CLASE *******************************************/
        //ATRIBUTOS 
        public 	$RES_REFERENCIA;
		
		//OBJETOS DE LA CLASE 
		//public $DESTINATARIO_OBJ;	
		
	
		/** FIN ATRIBUTOS *******************************************************/
		
		
		
		
		// Método constructor del certificado
		public function __construct($obj){
			
			$this->setControl($obj);
				
            /*$this->DESTINATARIO_OBJ = new Destinatario();
			$this->DESTINATARIO_OBJ->setControl($obj);
			*/
            
            //$this->cargarSesion();
            
		}
		
		
		
        //ml: chequeamos los datos sensibles si existen para habilitar opciones DATOS SENSIBLES
        public function fun_chequea_datos_sensibles($sensible,$tipo_certificado){

            try{  
                
                //$tipo_certificado = $this->_SESION->getVariable('TIPO_CERTIFICADO');

                if($sensible == 'NO'){
                    $identifica = 'NO';    
                    $bind = array(":p_tipodoc_id"=>$tipo_certificado, ":p_identifica" =>$identifica);
                    $resultado = $this->_ORA->ejecutaFunc("GDE.GDE_PRIVACIDAD_PKG.FUN_EXISTE_DATOS_SENSIBLES", $bind);
                }else{
                    $identifica = 'SI';    
                    $bind = array(":p_tipodoc_id"=>$tipo_certificado, ":p_identifica" =>$identifica);
                    $resultado = $this->_ORA->ejecutaFunc("GDE.GDE_PRIVACIDAD_PKG.FUN_EXISTE_DATOS_SENSIBLES", $bind);
                }
                
                if($resultado > 0){
                    $resultado = '';
                }else{
                    $resultado = 'disabled';
                }
                
                return $resultado; 
    
    
            }catch (Exception $e){
                $this->util->mailError($e);
            }
            
        }

        //ml: validamos si el cuepro selecciona pdf
        public function cuerpo_selecciona_pdf($padre,$tipo){
            try{  

                $bind = array(":p1"=>$tipo, ":p2" =>$padre);
                $result = $this->_ORA->ejecutaFunc("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_cuerpo_seleccionar_pdf", $bind);
                //echo $result;
                return $result;   
            
            }catch (Exception $e){
                $this->util->mailError($e);
            }    
        }

        //ml: validamos si el cuepro usa plantilla
        public function cuerpo_usa_plantilla($padre,$tipo){
            try{  

                $bind = array(":p1"=>$tipo, ":p2" =>$padre);
                $result = $this->_ORA->ejecutaFunc("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_cuerpo_usa_plantilla", $bind);
                //echo $result;
                return $result;   
            
            }catch (Exception $e){
                $this->util->mailError($e);
            }    
        }

        //ml: validamos si se debe adjuntar expediente
        public function adjuntar_expediente($padre,$tipo){
            
            try{  
            
                $bind = array(":p1"=>$tipo, ":p2" =>$padre);
                $result = $this->_ORA->ejecutaFunc("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_expediente_tiene_adj", $bind);
                //echo $result;
                return $result;   
    
            }catch (Exception $e){
                $this->util->mailError($e);
            }    
        
        }

        //ml: html modulo cuerpo seccion usa plantilla 
        public function html_usa_plantilla(){
        
            try{ 

                $html_usa_plantilla = "<div class='sec1Destinatario'>
                <label class='textoPregunta' for='DocContieneDatosSenciblesPersonales'>Usa plantilla? :</label>
                </div>
                <div class='sec1Destinatario'>
                    <div class='form-check form-check-inline'>
                        <label class='form-check-label'>Si</label>&nbsp;
                        <input class='form-check-input' type='radio' id='usarPlantillaSINO' name='usarPlantillaSINO' value='si'>
                    </div>
                    <div class='form-check form-check-inline'>
                        <label class='form-check-label'>No</label>&nbsp;
                        <input class='inputTipo' type='radio' id='usarPlantillaSINO' name='usarPlantillaSINO' value='no'>
                    </div>
                </div>";
                return $html_usa_plantilla;

            }catch (Exception $e){
                $this->util->mailError($e);
            }   
                
        }

        //ml: funcion para poder listar las plantillas disponibles por tipo de documento
        public function fun_listar_plantillas($tipo){
    
            try{  
                $bind = array(":p_tipo_doc"=>$tipo);
                $cursor = $this->_ORA->retornaCursor("GDE.GDE_PLANTILLA_PKG.FUN_LISTAR_PLANTILLA_DOC",'function', $bind);
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
        
     


        
        //ml: html modulo CUERPO sección selecciona pdf
        public function html_selecciona_pdf(){
            try{ 

                $html_selecciona_pdf = "<diV class='sec1Destinatario'><label class='seleccionaPdf' for='destinatario'>Seleccionar PDF</label><input class='inputSeleccionaPdf' type='file' id='agregarPdf' name='agregarPdf'></diV>";
                return $html_selecciona_pdf;
            
            }catch (Exception $e){
                $this->util->mailError($e);
            }   
                
        }

        //ml: html modulo EXPEDIENTE
        public function html_adjuntar_expediente(){
            try{ 
    
                $html_adjuntar_expediente = "<diV class='sec1Destinatario'><a class='' href='javascript:void(0)' id='btnVerExpediente' onclick='verExpedienteModal();'>Ver Expediente</a></diV>";
                return $html_adjuntar_expediente;
            
            }catch (Exception $e){
                $this->util->mailError($e);
            }   
        }


        //ml Me lista los tipos de DOCUMENTOS asociados por TIPODOC_ID para mi privacidad 
        public function listar_privacidad_asociada($p_tipo){
            
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
                $this->util->mailError($e);
            }
        }


        public function privacidad_cadena($tipo){
        
            try{ 
    
                $resultado2 = $this->listar_privacidad_asociada($tipo);
                $cantidad_datos = count($resultado2);
                //var_dump($resultado2);exit();
                foreach($resultado2 as $key => $datos){
                    $array_identificados[$key] = $resultado2[$key]['PRI_ID']; 
                }
                $cadena =  implode("_",$array_identificados);
                
                return $cadena;
             
            }catch (Exception $e){
                $this->util->mailError($e);
            }        
        
        }

        //ml: html modulo privacidad 
        public function html_privacidad($tipo){

            try{ 

                $resultado2 = $this->listar_privacidad_asociada($tipo);
                $html_privacidad ='
                <div class="sec1Destinatario">
                    <label class="textoPregunta" for="DocContieneDatosSenciblesPersonales">Privacidad :</label>
                </div>
                <div class="sec1Destinatario">
                <div class="errorModificar" id="errorPrivacidad">(*) Usted debe seleccionar una opción</div>';
                
                foreach($resultado2 as $key => $datos){
                    if($resultado2[$key]['PRI_ID'] == 'publi'){
                        $html_privacidad .='<div class="form-check form-check-inline">';
                        $html_privacidad .="<label class='form-check-label'>".$resultado2[$key]['PRI_NOMBRE']." (Tipo ".$resultado2[$key]['PRI_TRAD_GDOC'].")</label>&nbsp;&nbsp;";
                        $html_privacidad .="<input class='form-check-input' type='radio' id='privacidadTipo_".$resultado2[$key]['PRI_ID']."' name='privacidadTipo' value='".$resultado2[$key]['PRI_ID']."' disabled>";
                        $html_privacidad .='</div>';
                    }else{
                        $html_privacidad .='<div class="form-check form-check-inline">';
                        $html_privacidad .="<label class='form-check-label'>".$resultado2[$key]['PRI_NOMBRE']." (Tipo ".$resultado2[$key]['PRI_TRAD_GDOC'].")</label>&nbsp;&nbsp;";
                        $html_privacidad .="<input class='form-check-input' type='radio' id='privacidadTipo_".$resultado2[$key]['PRI_ID']."' name='privacidadTipo' value='".$resultado2[$key]['PRI_ID']."' disabled>";
                        $html_privacidad .='</div>';
                    }
                }
                $html_privacidad .="</div>";
                return $html_privacidad;

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


        //ml: funcion para detectar el DV del RUT
        function fun_digito_verificador($r){
            try{  
                $s=1;
                for($m=0;$r!=0;$r/=10)
                    $s=($s+$r%10*(9-$m++%6))%11;
                //echo 'El digito verificador es: ',chr($s?$s+47:75);
                return chr($s?$s+47:75);
            
            }catch (Exception $e){
                $this->util->mailError($e);
        }
        }

    
        //ml: CREAMOS EL PROCESO     
        public function fun_crear_proceso($NUMERO_CASO, $NUMERO_CASO_PADRE,$NUMERO_PROCESO, $REDACTOR, $TIPO_DOCUMENTO){
        
            try{
    
                $hoy = $this->_ORA->retornaValor('SYSDATE', 'DUAL','1=1');
                $bind = array(':p_caso_id'=> $NUMERO_CASO,
                            ':p_caso_fecha_recepcion_doc'=> NULL,
                            ':p_caso_nro_dto_inicial'=>   NULL,
                            ':p_caso_user_inicio'=>   NULL,
                            ':p_caso_fecha__inicio_proceso'=> $hoy ,
                            ':p_caso_observaciones'=>  NULL,
                            ':p_caso_origen'=>  NULL,
                            ':p_caso_nombre_caso'=>  "Documento ".$TIPO_DOCUMENTO,
                            ':p_caso_fecha_termino'=>  NULL,
                            ':p_caso_tiempo_congelado'=>  NULL,
                            ':p_caso_plazo'=>   $this->PLAZO,
                            ':p_espr_id'=>     NULL,
                            ':p_prcs_id'=>  $NUMERO_PROCESO,
                            ':p_caso_caso_id'=>  NULL,
                            ':p_prrd_valor'=>   NULL,
                            ':p_caso_tipo_doc'=>  NULL
                            );
    
                $this->_ORA->ejecutaProc("casos_tab.ins_multiple",$bind);
                
                $bind = array(':p_caso_id' => $NUMERO_CASO,
                            ':p_usuario' => $REDACTOR,
                            ':p_prcs_id' => $NUMERO_PROCESO
                            );
                $resultado = $this->_ORA->ejecutaFunc("wfa.wf_rso_pkg.crea_wf_proceso",$bind);    
                $bind = array(':p_padre' => $NUMERO_CASO_PADRE,
                            ':p_hijo' => $NUMERO_CASO
                            );            
                $this->_ORA->ejecutaProc("wfa_padre_hijo.actualizaPadreHijo",$bind);
                $this->_ORA->Commit();
            
            }catch (Exception $e){
                $this->util->mailError($e);
            }                
        }    

    
    
        
    //ml: agregar distribucion (destinatario)
    public function fun_agregar_destinatario($arrayDestinatario,$arrayCargoDestinatario,$p_id,$p_doc_version,$arrayDireccion,$arrayCorreo,$arrayMedioEnvio,$arrayMiTipo,$arrayMiNombreDes){

        try{  
            //||||||||||||| AGREGAMOS LA DISTRIBUCION |||||||||||||||||||||||
            if((count($arrayDestinatario)===count($arrayCargoDestinatario)) and count($arrayCargoDestinatario)>0){
                for($x=0;$x<count($arrayDestinatario);$x++){
                    $dis_secuencia = mt_rand(); //validar que sea asi
                    

                    if($arrayCorreo[$x] == 'undefined'){
                        
                        $correoDestinatario = null;
                    }else{
                        $correoDestinatario = $arrayCorreo[$x];
                        
                    }

                    $bindDistribucion =  array(
                        ":p_dis_secuencia"=> $dis_secuencia,
                        ":p_doc_id"=>$p_id,
                        ":p_doc_version"=>$p_doc_version,
                        ":p_dis_cargo"=>$arrayCargoDestinatario[$x],
                        ":p_dis_rut"=>$arrayDestinatario[$x],
                        ":p_dis_con_copia"=>'NO',
                        ":p_dis_direccion"=>$arrayDireccion[$x],
                        ":p_dis_correo"=>$correoDestinatario,
                        //":p_dis_medio_envio"=>$p_medio_envio,
                        ":p_dis_medio_envio"=>$arrayMedioEnvio[$x],
                        ":p_dis_dv"=>$this->fun_digito_verificador($arrayDestinatario[$x]),
                        ":p_dis_tipo_entidad"=>$arrayMiTipo[$x],
                        ":p_dis_nombre"=>$arrayMiNombreDes[$x]
                    );    
                    $this->_ORA->ejecutaProc("GDE.GDE_DISTRIBUCION_PKG.PRC_AGREGAR_DISTRIBUCION",$bindDistribucion);
                    $this->_ORA->Commit();
                }    
            }

        }catch (Exception $e){
            $this->util->mailError($e);
       }
    }    


    //agregar distribucion (copia destinatario)
    public function fun_agregar_copia($arrayCopia,$arrayCargoCopia,$p_id,$p_doc_version,$arrayCopiaDireccion,$arrayCopiaCorreo,$arrayMiMedioEnvioCopia,$arrayMiTipoCopia,$arrayMiNombreDesCopia){
            
        try{ 
            //||||||||||||| AGREGAMOS LA DISTRIBUCION con COPIA |||||||||||||||||||||||
            if((count($arrayCopia)===count($arrayCargoCopia)) and count($arrayCargoCopia) > 0){
                for($y=0;$y<count($arrayCopia);$y++){
                    $dis_secuenciaCopia = mt_rand(); //validar que sea asi
                    if($arrayCopiaCorreo[$y] == 'undefined'){
                        $correoCopia = null;
                    }else{
                        $correoCopia = $arrayCopiaCorreo[$y];
                    }
                    
                    $bindDistribucionCopia =  array(":p_dis_secuencia"=> $dis_secuenciaCopia,
                    ":p_doc_id"=>$p_id,
                    ":p_doc_version"=>$p_doc_version,
                    ":p_dis_cargo"=>$arrayCargoCopia[$y],
                    ":p_dis_rut"=>$arrayCopia[$y],
                    ":p_dis_con_copia"=>"SI",
                    ":p_dis_direccion"=>$arrayCopiaDireccion[$y],
                    ":p_dis_correo"=>$correoCopia,
                    //":p_dis_medio_envio"=>$p_medio_envio,
                    ":p_dis_medio_envio"=>$arrayMiMedioEnvioCopia[$y],
                    ":p_dis_dv"=>$this->fun_digito_verificador($arrayCopia[$y]),
                    ":p_dis_tipo_entidad"=>$arrayMiTipoCopia[$y],
                    ":p_dis_nombre"=>$arrayMiNombreDesCopia[$y]
                    );    
                    $this->_ORA->ejecutaProc("GDE.GDE_DISTRIBUCION_PKG.PRC_AGREGAR_DISTRIBUCION",$bindDistribucionCopia);
                    $this->_ORA->Commit();
                }    
            }
        
        }catch (Exception $e){
            $this->util->mailError($e);
       }
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
                        $r['ESTDOC_DESCRIPCION']=$r['ESTDOC_DESCRIPCION'];
                        $r['TIPENV_NOMBRE']=$r['TIPENV_NOMBRE'];
    
                        $resCertificado[]=$r;    
                    }
                    $this->_ORA->FreeStatement($cursor);
                }    
                
        return $resCertificado;
    }

    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||| FIRMA CERTIFICADO |||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


        //ml: cerramos el caso una vez firmado 
        public function fun_cerrar_caso($wf){
            
            try{
                
                $bind = array(':ITEMKEY' => $wf,
                ':ACTIVITY' => 'NT_REVISAR_DOCTO',
                ':LOOKUPCODE' => ' LC_WF_GEN_SINWF ',
                ':ITEMTYPE' => 'WF_GEN'
                );
        
                $this->_ORA->ejecutaFunc('wfa.wf_siac.avanzar',$bind);
            
            }catch (Exception $e){
                $this->util->mailError($e);
            }   
        }   


    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||| CERTIFICADO |||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    //ml: agregamos nueva version de certificado/documento
    public function fun_agregar_nuevo_certificado($dataCertificado){

        try{  

            $p_id                   = $dataCertificado['id'];
            $p_doc_version          = $dataCertificado['version'];
            $p_doc_datos_sensibles  = $dataCertificado['datos_sensibles'];
            $p_doc_usa_plantilla    = $dataCertificado['usa_plantilla'];
            $gde_tipdoc_id          = $dataCertificado['tipo_doc']; 
            $p_gde_dis_secuencia    = $dataCertificado['secuencia'];
            $p_gde_estdoc_id        = $dataCertificado['estado_doc'];
            $p_gde_pri_id           = $dataCertificado['privacidad'];
            $p_doc_genera_version   = $dataCertificado['genera_version'];
            $p_doc_caso_padre       = $dataCertificado['caso_padre'];
            $p_doc_redactor         = $dataCertificado['redactor'];    
            $p_doc_ultima_version   = $dataCertificado['ultima_version'];
            $RES_REFERENCIA         = $dataCertificado['cuerpo'];
            $p_doc_enviado_a        = $dataCertificado['enviado_a'];
            $p_tipo_envio           = $dataCertificado['tipo_envio'];



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
            $this->_ORA->ejecutaProc("GDE.GDE_DOCUMENTO_PKG.PRC_AGREGAR_CERTIFICADO",$bind);
            $this->_ORA->Commit();
        
        }catch (Exception $e){
            $this->util->mailError($e);
       }
    }    



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
            $this->util->mailError($e);
       }
    }


    public function fun_tipo_envio_html(){

        try{

            $tipos_envios = $this->fun_listar_tipo_envio();
            $resultado = '';
            
            if($tipos_envios){
                foreach($tipos_envios as $envios){
                    $resultado .='<div class="form-check form-check-inline">';
                    
                    $resultado .= '<label class="form-check-input"><input class="form-check-label" type="radio" id="tipo_envio" name="tipo_envio" value="'.$envios['TIPENV_ID'].'">&nbsp;'.$envios['TIPENV_NOMBRE'].'</label>&nbsp;&nbsp;&nbsp;&nbsp;';
                    $resultado .= '</div>';
                }
            }
            return $resultado;

        }catch (Exception $e){
            $this->util->mailError($e);
       }
    }
        



}