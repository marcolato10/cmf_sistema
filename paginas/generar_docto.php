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
            //$this->modificar_certificado($wf,$tipo);
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






//ml_ mejora: se puede reutilizar la funcion "fun_listar_certificado"
public function modificar_certificado($wf,$tipo){

   
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
            
            
            //var_dump($resCertificado[0]['DOC_DATOS_SENSIBLES']);exit();    

            //$this->fun_chequea_datos_sensibles($resCertificado[0]['DOC_DATOS_SENSIBLES'],$resCertificado[0]['DOC_DATOS_SENSIBLES'],$wf,$tipo);
            $this->fun_chequea_usa_plantilla($resCertificado[0]['DOC_USA_PLANTILLA']);
            $this->fun_chequea_cuerpo($resCertificado[0]['DOC_CUERPO']);
            $this->fun_chequea_visaciones($wf,$tipo);
            //$this->fun_chequear_versiones_wf($wf,$tipo);
            //$this->fun_chequear_privacidad_wf($wf,$tipo);

            //>>> duda     
            $this->_TEMPLATE->assign('padre',$wf);
            $this->_TEMPLATE->parse('main.padre');
            $this->cargarExpedientes($wf); //se puede mejorar
} 

   


    //||||||||||||||||||||||||||||||||||||  CERTIFICADOS NUEVOS ||||||||||||||||||||||||||||||||||
    //ml: funcion que nos lleva al nuevo certificado para ser creado
    public function fun_nuevo_certificado($padre,$tipo){

       
        
        $idCCertificado = $this->_ORA->ejecutaFunc("utl_frm_html.seq_get",array(':caso_seq'=>'CASO_SEQ'));
        $this->_SESION->setVariable('MI_DOC_ID',$idCCertificado);

        $this->_TEMPLATE->assign('miCCertificado',$idCCertificado);
        $this->_TEMPLATE->assign('padre',$padre);
        $this->_TEMPLATE->parse('main.padre');
        $this->cargarExpedientes($padre); //se puede mejorar
        //$this->listar_visaciones($padre,$tipo); //no va en los certificados nuevos
        //$this->listar_adjuntos($padre,$tipo); //no va en los certificados nuevos
        //$this->fun_listar_versiones($padre,$tipo); //no va en los certificados nuevos
       
        /* respuesta de los modulos que se deben mostrar */
        $destinatario=$this->tiene_destinatario($padre,$tipo);
        $privacidad=$this->tiene_privacidad($padre,$tipo);
        $cuerpo_usa_plantilla = $this->cuerpo_usa_plantilla($padre,$tipo);    
        $cuerpo_selecciona_pdf = $this->cuerpo_selecciona_pdf($padre,$tipo); 
        $adjuntar_expediente = $this->adjuntar_expediente($padre,$tipo);

        
        $resultado_tipo_envios = $this->fun_tipo_envio_html(); 
        $this->_TEMPLATE->assign('resultado_tipo_envio',$resultado_tipo_envios);
        
        /* validamos si debe mostrarse el modulo PRIVACIDAD este*/ 
        //if($privacidad > 0 ){ //si es 0 no tiene  privacidad si es > 0 tiene  privacidad
            //print_r("tiene privacidad");
            
            
            
            $disponible_dato_sensible_si = $this->fun_chequea_datos_sensibles('SI');
            $disponible_dato_sensible_no = $this->fun_chequea_datos_sensibles('NO');
            

            
            $resultado_privacidad = $this->html_privacidad($tipo);
            
            
            $this->_TEMPLATE->assign('disponible_dato_sensible_si',$disponible_dato_sensible_si);
            $this->_TEMPLATE->assign('disponible_dato_sensible_no',$disponible_dato_sensible_no);
            $this->_TEMPLATE->assign('resultado_privacidad',$resultado_privacidad);
            $cadena = $this->privacidad_cadena($tipo);
            //var_dump($cadena);
            $this->_TEMPLATE->assign('cadena',$cadena);
            $this->_TEMPLATE->parse('main.existe_privacidad');
            
        //}
        /* validamos si debe mostrarse en el modulo CUERPO la seccion usa plantilla este*/
        if($cuerpo_usa_plantilla == 0){
            $resultado_usa_plantilla = $this->html_usa_plantilla();
            
            $listado = "";
            $plantillas = $this->fun_listar_plantillas('certificado');
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
            $resultado_selecciona_pdf = $this->html_selecciona_pdf();
            $this->_TEMPLATE->assign('selecciona_pdf',$resultado_selecciona_pdf);
            $this->_TEMPLATE->parse('main.selecciona_pdf');
        }
        
        
        if($adjuntar_expediente == 0){ //este
            $resultado_adjuntar_expediente = $this->html_adjuntar_expediente();
            $this->_TEMPLATE->assign('btn_ver_expediente',$resultado_adjuntar_expediente);
            $this->_TEMPLATE->parse('main.btn_ver_expediente');
        }
       

        

        try{

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

    //ml: chequeamos los datos sensibles si existen para habilitar opciones DATOS SENSIBLES
    public function fun_chequea_datos_sensibles($sensible){

        $tipo_certificado = $this->_SESION->getVariable('TIPO_CERTIFICADO');

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
        
    }


    public function listar_adjuntos($padre,$tipo){
        $bind = array(":p1"=>$tipo, ":p2" =>$padre);
        try{
		   $cursor = $this->_ORA->retornaCursor("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_lista_archivos_adjuntos",'function', $bind);
			if ($cursor) {
			 	 while($r = $this->_ORA->FetchArray($cursor)){ 
					$r['ADJ_ID']=$r['ADJ_ID'];
                    $r['ADJ_USUARIO']=$r['ADJ_USUARIO'];
                    $r['ADJ_FECHA']=$r['ADJ_FECHA'];
                    $r['ADJ_ARCHIVO_EXP']=$r['ADJ_ARCHIVO_EXP'];
                    $r['GDE_DOCUMENTO_DOC_VERSION']=$r['GDE_DOCUMENTO_DOC_VERSION'];
                   


                    $resultado2[]=$r;

					 //$this->_TEMPLATE->assign('listado_visaciones',$r);
					 //$this->_TEMPLATE->parse('main.listado_visaciones');						
			 	 }			
			 	 $this->_ORA->FreeStatement($cursor);
			}
            
            //var_dump(count($resultado2));
            $listado = "";
            if(isset($resultado2) && !empty($resultado2) && is_array($resultado2)){
                foreach($resultado2 as $key => $datos){
                    
                    $listado .= "<li>".$resultado2[$key]['ADJ_ARCHIVO_EXP'].' '.$resultado2[$key]['VIS_FECHA']."<a href='javascript:void(0)' id='btnEliminar' class='btnEliminar' onclick='eliminar(".$resultado2[$key]['ADJ_ID'].");'>Eliminarr</a></li>";    
                 }
            }
            

		}catch (Exception $e){
            
            print("ERROR: existe un error a la hora de listar los adjuntos.");
            print_r($e);
        }

        $this->_TEMPLATE->assign('listado_adjuntos',$listado);
        $this->_TEMPLATE->parse('main.listado_adjuntos');

    }


     //ml Me lista los tipos de DOCUMENTOS asociados por TIPODOC_ID para mi privacidad 
    public function listar_privacidad_asociada($p_tipo){
        $bind = array(":p1"=>$p_tipo);
        try{
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
            
          

		}catch (Exception $e){
            print("ERROR: existe un error a la hora de listar privacidad asociada");
            exit();
        }

        return  $resultado2;

    }



    /* validamos si corresponde agregar el modulo DESTINATARIO*/
    public function tiene_destinatario($padre,$tipo){
        /* aqui obtenemos la respuesta de la bbdd */
        return 1;   
    }

    /* validamos si corresponde agregar el modulo PRIVACIDAD*/
    public function tiene_privacidad($padre,$tipo){
        $bind = array(":p1"=>$tipo, ":p2" =>$padre);
        $result = $this->_ORA->ejecutaFunc("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_existe_privacidad", $bind);
        
        //var_dump($result);exit();    
        return $result;   
    }

    /* validamos si el cuepro usa plantilla*/
    public function cuerpo_usa_plantilla($padre,$tipo){
        $bind = array(":p1"=>$tipo, ":p2" =>$padre);
        $result = $this->_ORA->ejecutaFunc("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_cuerpo_usa_plantilla", $bind);
        //echo $result;
        return $result;   
    }

    /* validamos si el cuepro selecciona pdf*/
    public function cuerpo_selecciona_pdf($padre,$tipo){
        $bind = array(":p1"=>$tipo, ":p2" =>$padre);
        $result = $this->_ORA->ejecutaFunc("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_cuerpo_seleccionar_pdf", $bind);
        //echo $result;
        return $result;   
    }

    /* validamos si se debe adjuntar expediente*/
    public function adjuntar_expediente($padre,$tipo){
        $bind = array(":p1"=>$tipo, ":p2" =>$padre);
        $result = $this->_ORA->ejecutaFunc("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_expediente_tiene_adj", $bind);
        //echo $result;
        return $result;   
    }


    public function privacidad_cadena($tipo){
        $resultado2 = $this->listar_privacidad_asociada($tipo);
        $cantidad_datos = count($resultado2);
        //var_dump($resultado2);exit();
        foreach($resultado2 as $key => $datos){
            $array_identificados[$key] = $resultado2[$key]['PRI_ID']; 
        }
        $cadena =  implode("_",$array_identificados);
        
        return $cadena;
    }





    /*ml: html modulo privacidad */
    public function html_privacidad($tipo){

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
    }


    /* html modulo cuerpo seccion usa plantilla */
    public function html_usa_plantilla(){
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
    }


    //html modulo CUERPO sección selecciona pdf
    public function html_selecciona_pdf(){
        $html_selecciona_pdf = "<diV class='sec1Destinatario'><label class='seleccionaPdf' for='destinatario'>Seleccionar PDF</label><input class='inputSeleccionaPdf' type='file' id='agregarPdf' name='agregarPdf'></diV>";
        return $html_selecciona_pdf;
    }
    //html modulo EXPEDIENTE
    public function html_adjuntar_expediente(){
        $html_adjuntar_expediente = "<diV class='sec1Destinatario'><a class='' href='javascript:void(0)' id='btnVerExpediente' onclick='verExpedienteModal();'>Ver Expediente</a></diV>";
        return $html_adjuntar_expediente;
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

    //mmmmm
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
			
        //Acá me falta estebecer seguridad para saber que los archivos del expediente padre tengo acceso
        //$bind = array(':padre' => $this->CASO_PADRE);
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
        
        
    }


    protected function ejecutarFuncionXml($package,$funcion, $variable){
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


    /* obs: funcion que muestra la respuesta al guardar certificado */
    public function fun_respuesta($param){

        
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
    }




    public function fun_crear_proceso($NUMERO_CASO, $NUMERO_CASO_PADRE,$NUMERO_PROCESO, $REDACTOR, $TIPO_DOCUMENTO){
        
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
}
 



//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
// >>>>>>>>>>>>>>>>>>>>>>> Funciones usadas para MODIFICAR certificado <<<<<<<<<<<<<<<<<<<<<<<<<
//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

  
    public function fun_chequear_privacidad_wf($wf,$tipo){
    
        $privacidades = $this->listar_privacidad_asociada($tipo);
        $mi_certificado = $this->fun_listar_certificado($wf,$tipo);
        $mi_privacidad = $mi_certificado[0]['GDE_PRIVACIDAD_PRI_ID'];
        
        $html_privacidad ='
        <div class="sec1Destinatario">
            <label class="textoPregunta" for="DocContieneDatosSenciblesPersonales">Privacidad :</label>
        </div>
        <div class="sec1Destinatario">';
        foreach($privacidades as $key => $datos){
            $html_privacidad .="<label class='labelTipo'>".$privacidades[$key]['PRI_NOMBRE']." (Tipo ".$privacidades[$key]['PRI_TRAD_GDOC'].")</label>";
            
            if($privacidades[$key]['PRI_ID'] == $mi_privacidad){
                $html_privacidad .="<input class='inputTipo' type='radio' id='privacidadTipo_".$privacidades[$key]['PRI_ID']."' name='privacidadTipo' value='".$privacidades[$key]['PRI_ID']."' checked onclick='validarSeleccion();'>";
            }else{
                $html_privacidad .="<input class='inputTipo' type='radio' id='privacidadTipo_".$privacidades[$key]['PRI_ID']."' name='privacidadTipo' value='".$privacidades[$key]['PRI_ID']."' onclick='validarSeleccion();'>";
            }    
        }
        $html_privacidad .="</div>";
       
        return $html_privacidad;
    }    


    //ml_: chequeamos datos sensibles para poder cargar respuesta en la vista
    /*public function  fun_chequea_datos_sensibles($res_sensibles,$res_privacidad,$wf,$tipo){
        if(strtoupper($res_sensibles) == 'SI'){
            $this->_TEMPLATE->assign('r_dato_sensible_si','checked');
            $this->_TEMPLATE->assign('r_dato_sensible_no','');
            $resp_chequeo_privacidad = $this->fun_chequear_privacidad_wf($wf,$tipo);
            $this->_TEMPLATE->assign('resultado_privacidad',$resp_chequeo_privacidad);
            $this->_TEMPLATE->parse('main.existe_privacidad');
        }else{
            $this->_TEMPLATE->assign('r_dato_sensible_no','checked');
            $this->_TEMPLATE->assign('r_dato_sensible_si','');
            $resp_chequeo_privacidad = $this->fun_chequear_privacidad_wf($wf,$tipo);
            $this->_TEMPLATE->assign('resultado_privacidad',$resp_chequeo_privacidad);
            $this->_TEMPLATE->parse('main.existe_privacidad');
        }
    }*/

    //ml: chequeamos el cuerpo para poder cargar respuesta en la vista
    public function  fun_chequea_cuerpo($res){
        $this->_TEMPLATE->assign('r_cuerpo',$res);
        $this->_TEMPLATE->parse('main.cuerpo_certificado');
    }

    //ml: chequeamos el usa plantilla para poder cargar respuesta en la vista
    public function  fun_chequea_usa_plantilla($res){
        if(strtoupper($res) == 'SI'){
            $this->_TEMPLATE->assign('r_usa_plantilla_si','checked');
            $this->_TEMPLATE->assign('r_usa_plantilla_no','');
            $this->_TEMPLATE->parse('main.usa_plantilla');
        }else{
            $this->_TEMPLATE->assign('r_usa_plantilla_no','checked');
            $this->_TEMPLATE->assign('r_usa_plantilla_si','');
            $this->_TEMPLATE->parse('main.usa_plantilla');
        }
    }

    //ml: chequeamos las visaciones para poder cargar en la vista
    public function fun_chequea_visaciones($wf,$tipo){

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
                    $r['VIS_COMENTARIO']=$r['VIS_COMENTARIO'];
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


                    $resultado2[]=$r;

					 //$this->_TEMPLATE->assign('listado_visaciones',$r);
					 //$this->_TEMPLATE->parse('main.listado_visaciones');						
			 	 }			
			 	 $this->_ORA->FreeStatement($cursor);
			}
            
            //echo "<pre>";var_dump($resultado2);echo "</pre>";exit();
            $listado = "";
            if(isset($resultado2) && !empty($resultado2) && is_array($resultado2)){
                foreach($resultado2 as $key => $datos){
                    $bindNombre = array(':usr' => $resultado2[$key]['VIS_USUARIO']);
                    $nombreUsuario = $this->_ORA->ejecutaFunc('wfa.wfa_usr.getNombreUsuario',$bindNombre);
                    $listado .= "<li>".$nombreUsuario.' ('.$resultado2[$key]['VIS_VB'].') '.$resultado2[$key]['VIS_FECHA']."</li>";    
                 }
            }
            

		}catch (Exception $e){
            print("hay un error");
        }

        $this->_TEMPLATE->assign('listado_visaciones',$listado);
        $this->_TEMPLATE->parse('main.listado_visaciones');

    }

    //ml: chequeamos las versiones se listen para poder cargar en la vista
    /*public function fun_chequear_versiones_wf($wf,$tipo){

        $bind = array(":p_wf"=>$wf, ":p_tipo" =>$tipo);
        try{
		   $cursor = $this->_ORA->retornaCursor("GDE.GDE_TIPO_DOCUMENTO_PKG.fun_listar_versiones_wf",'function', $bind);
           if ($cursor) {
			 	 while($r = $this->_ORA->FetchArray($cursor)){ 
					$r['DOC_ID']=$r['DOC_ID'];
                    $r['DOC_VERSION']=$r['DOC_VERSION'];
                    $r['DOC_FECHA']=$r['DOC_FECHA'];
                    $res_versiones[]=$r;

			 	 }			
			 	 $this->_ORA->FreeStatement($cursor);
			}   
           
            //var_dump($res_versiones);exit();
            $listado = "";
            if(isset($res_versiones) && !empty($res_versiones) && is_array($res_versiones)){
                foreach($res_versiones as $key => $datos){
                    $listado .= "<li>Versión ".$res_versiones[$key]['DOC_VERSION'].' ('.$res_versiones[$key]['DOC_FECHA'].")<a href='javascript:void(0)' id='btnVerVersion' name='btnVerVersion' class='btnVerVersion' onclick='verVersion(".$res_versiones[$key]['DOC_ID'].",".$res_versiones[$key]['DOC_VERSION'].");'>Ver versión</a></li>";    
                 }
            }
		}catch (Exception $e){
            print("hay un error");
        }
        
        $this->_TEMPLATE->assign('listado_versiones',$listado);
        $this->_TEMPLATE->parse('main.listado_versiones');

    }*/

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

//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
// >>>>>>>>>>>>>>>>>>>>>>> Funciones usadas para crear nuevo certificado <<<<<<<<<<<<<<<<<<<<<<<<<
//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    
    //ml: agrega certificado desde el formulario CREAR
    public function fun_agregar_certificado(){

            
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
           

            //echo "<pre>"; var_dump($arrayMiNombreDes); echo "</pre>";exit();


            $dataDestinatario = array (
                'destinatario' => $arrayDestinatario ,
                'cargo' => $arrayCargoDestinatario ,
                'direccion' => $arrayDireccion,
                'correo' => $arrayCorreo,
                'tipo' => $arrayMiTipo,
                'con_copia' => 'NO',
                'nombre' =>  $arrayMiNombreDes
            );


            //DESTINATARIOS COPIA
            $arrayMiTipoCopia = explode("_,", substr($_POST["p_arrayMiTipoCopia"], 0, -1)); 
            $arrayCopia= explode("_,", substr($_POST["p_arrayCopia"], 0, -1)); 
            $arrayCargoCopia= explode("_,", substr($_POST["p_arrayCargoCopia"], 0, -1)); 
            $arrayCopiaDireccion= explode("_,", substr($_POST["p_arrayCopiaDireccion"], 0, -1)); 
            $arrayCopiaCorreo= explode("_,", substr($_POST["p_arrayCopiaCorreo"], 0, -1)); 
            $arrayMiNombreDesCopia = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1)); 

            $dataCopia = array (
                'destinatario' => $arrayCopia ,
                'cargo' => $arrayCargoCopia ,
                'direccion' => $arrayCopiaDireccion,
                'correo' => $arrayCopiaCorreo,
                'tipo' => $arrayMiTipoCopia,
                'con_copia' => 'SI',
                'nombre' => $arrayMiNombreDesCopia
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

           
            //yyyyyyyyyyyyyyy    


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

            $this->fun_agregar_nuevo_certificado($dataCertificado);

       

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
                $this->fun_agregar_destinatario($arrayDestinatario,$arrayCargoDestinatario,$p_id,$p_doc_version,$arrayDireccion,$arrayCorreo,$p_medio_envio,$arrayMiTipo,$arrayMiNombreDes);
                
                if($_POST["p_arrayCopia"] != ""){
                //llamar la funcion agregar copia
                $this->fun_agregar_copia($arrayCopia,$arrayCargoCopia,$p_id,$p_doc_version,$arrayCopiaDireccion,$arrayCopiaCorreo,$p_medio_envio,$arrayMiTipoCopia,$arrayMiNombreDesCopia);
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
            $this->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);

         
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
                    
                    $this->_ORA->ejecutaProc("GDE.GDE_TIPO_DOCUMENTO_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
                    $this->_ORA->Commit();
                }
            }



            return "OK";
            
    }

    //origen:  validamos formato del archivo adjunto en el cuerpo
    public function fun_validar_archivo(){

        $file = $_POST['p_file'];
        //$nombre_archivo = $_FILES[$_POST['p_file']]['name'];
        //var_dump("el nombre es: ".$file);     

        // exit();
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

          
    }

    //ml: cargamos el cuerpo de la plantilla seleccionada
    public function fun_cargar_cuerpo_plantilla(){
            
        $plantilla = $this->fun_obtener_plantilla_get($_POST['p_plantilla']);
        //$this->fun_chequea_cuerpo($plantilla[0]['PLA_CUERPO']);     
        //$this->_TEMPLATE->assign('r_cuerpo',$plantilla[0]['PLA_CUERPO']);
        //$this->_TEMPLATE->parse('main.cuerpo_certificado');

        //var_dump($plantilla[0]['PLA_CUERPO']);    
        return  $plantilla[0]['PLA_CUERPO'];
    }

        //ml obtenemos la plantilla por el id
        public function fun_obtener_plantilla_get($id){
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

        }

        //ml: funcion para poder listar las plantillas disponibles por tipo de documento
        public function fun_listar_plantillas($tipo){
            //$tipo='certificado';    
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
        }     





        //agregar distribucion (destinatario)
        public function fun_agregar_destinatario($arrayDestinatario,$arrayCargoDestinatario,$p_id,$p_doc_version,$arrayDireccion,$arrayCorreo,$p_medio_envio,$arrayMiTipo,$arrayMiNombreDes){

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
                        ":p_dis_medio_envio"=>$p_medio_envio,
                        ":p_dis_dv"=>$this->fun_digito_verificador($arrayDestinatario[$x]),
                        ":p_dis_tipo_entidad"=>$arrayMiTipo[$x],
                        ":p_dis_nombre"=>$arrayMiNombreDes[$x]
                    );    
                    $this->_ORA->ejecutaProc("GDE.GDE_DISTRIBUCION_PKG.PRC_AGREGAR_DISTRIBUCION",$bindDistribucion);
                    $this->_ORA->Commit();
                }    
            }
        }
    
        //agregar distribucion (copia destinatario)
        public function fun_agregar_copia($arrayCopia,$arrayCargoCopia,$p_id,$p_doc_version,$arrayCopiaDireccion,$arrayCopiaCorreo,$p_medio_envio,$arrayMiTipoCopia,$arrayMiNombreDesCopia){
    
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
                    ":p_dis_medio_envio"=>$p_medio_envio,
                    ":p_dis_dv"=>$this->fun_digito_verificador($arrayCopia[$y]),
                    ":p_dis_tipo_entidad"=>$arrayMiTipoCopia[$y],
                    ":p_dis_nombre"=>$arrayMiNombreDesCopia[$y]
                    );    
                    $this->_ORA->ejecutaProc("GDE.GDE_DISTRIBUCION_PKG.PRC_AGREGAR_DISTRIBUCION",$bindDistribucionCopia);
                    $this->_ORA->Commit();
                }    
            }
        
        }
    


    //ml: funcion para detectar el DV del RUT
    function fun_digito_verificador($r){
        $s=1;
        for($m=0;$r!=0;$r/=10)
            $s=($s+$r%10*(9-$m++%6))%11;
        //echo 'El digito verificador es: ',chr($s?$s+47:75);
        return chr($s?$s+47:75);
    }


    public function listar_visaciones($padre,$tipo){ //para los certificados nuevos no existen visaciones 
        $bind = array(":p1"=>$tipo, ":p2" =>$padre);
        try{
		   $cursor = $this->_ORA->retornaCursor("GDE.GDE_VISACIONES_PKG.fun_lista_VISACIONES",'function', $bind);
			if ($cursor) {
			 	 while($r = $this->_ORA->FetchArray($cursor)){ 
					$r['VIS_ID']=$r['VIS_ID'];
                    $r['VIS_USUARIO']=$r['VIS_USUARIO'];
                    $r['VIS_USUARIO_HACIA']=$r['VIS_USUARIO_HACIA'];
                    $r['VIS_VB']=$r['VIS_VB'];
                    $r['VIS_FECHA']=$r['VIS_FECHA'];
                    $r['VIS_COMENTARIO']=$r['VIS_COMENTARIO'];
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


                    $resultado2[]=$r;

					 //$this->_TEMPLATE->assign('listado_visaciones',$r);
					 //$this->_TEMPLATE->parse('main.listado_visaciones');						
			 	 }			
			 	 $this->_ORA->FreeStatement($cursor);
			}
            
            //echo "<pre>";var_dump($resultado2);echo "</pre>";exit();
            $listado = "";
            if(isset($resultado2) && !empty($resultado2) && is_array($resultado2)){
                foreach($resultado2 as $key => $datos){
                    $bindNombre = array(':usr' => $resultado2[$key]['VIS_USUARIO']);
                    $nombreUsuario = $this->_ORA->ejecutaFunc('wfa.wfa_usr.getNombreUsuario',$bindNombre);
                    $listado .= "<li>".$nombreUsuario.' ('.$resultado2[$key]['VIS_VB'].') '.$resultado2[$key]['VIS_FECHA']."</li>";    
                 }
            }
            

		}catch (Exception $e){
            print("hay un error");
        }

        $this->_TEMPLATE->assign('listado_visaciones',$listado);
        $this->_TEMPLATE->parse('main.listado_visaciones');

    }


    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||| CERTIFICADO |||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    //ml: agregamos nueva version de certificado/documento
    public function fun_agregar_nuevo_certificado($dataCertificado){

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

    }


    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||| EBVIAR VB |||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    
    //mostramos el modal de enviar a vb
    public function fun_enviar_vb(){
        $json = array();
        $MENSAJES = array();
        $CAMBIA = array();	
        $OPEN = array();			
        
        $unidad = ($unidad == null) ? $this->_SESION->UNIDAD : $unidad;
        $cursorPara = $this->_ORA->retornaCursor("WFA.WFA_USR.getNombresUsrsUnidad","function",array(":unidad" => $unidad));
        $dataPara = $this->_ORA->FetchAll($cursorPara);        
        

        //echo "<pre>";var_dump($dataPara); echo "</pre>";

      
        $cursorAllUsuarios = $this->_ORA->retornaCursor("WFA.WFA_USR.getAllUsuarios","function",array(":p_caso_id" => null,":p_origen" => null ));
        $dataCopia = $this->_ORA->FetchAll($cursorAllUsuarios);    
        //$dataTodos = $this->_ORA->FetchAll($cursorAllUsuarios);        


        //echo "<pre>";var_dump($dataCopia); echo "</pre>";
        //exit();
        



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

    public function fun_enviar_correo($correo_para,$correo_copia,$NUMERO_CASO,$comentario,$usuario_desde){

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

    } 

    //ml: enviamos a VB desde pestaña MI UNIDAD
    public function fun_agregar_enviar_vb(){
    
        //xxxxxxxxxxxxxxx OK
        
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




                $arrayMiTipo = explode("_,", substr($_POST["p_arrayMiTipo"], 0, -1));
                $arrayMiTipoCopia = explode("_,", substr($_POST["p_arrayMiTipoCopia"], 0, -1)); 
                $arrayCopia= explode("_,", substr($_POST["p_arrayCopia"], 0, -1)); 
                $arrayDestinatario= explode("_,", substr($_POST["p_arrayDestinatario"], 0, -1)); 
                $arrayCargoCopia= explode("_,", substr($_POST["p_arrayCargoCopia"], 0, -1)); 
                $arrayCargoDestinatario= explode("_,", substr($_POST["p_arrayCargoDestinatario"], 0, -1)); 
                $arrayCopiaDireccion= explode("_,", substr($_POST["p_arrayCopiaDireccion"], 0, -1)); 
                $arrayCopiaCorreo= explode("_,", substr($_POST["p_arrayCopiaCorreo"], 0, -1)); 
                $arrayDireccion= explode("_,", substr($_POST["p_arrayDireccion"], 0, -1)); 
                $arrayCorreo= explode("_,", substr($_POST["p_arrayCorreo"], 0, -1)); 
                $arrayMiNombreDes = explode("_,", substr($_POST["p_arrayMiNombreDes"], 0, -1)); 
                $arrayMiNombreDesCopia = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1));

                $p_medio_envio = 'SEIL';
                //$p_id =$this->_ORA->ejecutaFunc("utl_frm_html.seq_get",array(':caso_seq'=>'CASO_SEQ'));
                $p_id =$_POST['p_miccertificado'];
                //parametros adjuntos
                $gde_documento_doc_version = 0; //sera 0 en caso que es 1era vez 
                $adj_usuario =$this->_SESION->USUARIO; 
                $adjuntos = $this->_SESION->getVariable("RSO_ADJUNTO");
                
                
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
    
                $this->fun_agregar_nuevo_certificado($dataCertificado);

               

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


                
                $p_comentario= $_POST['p_comentarioVB']; //comentario visacion enviar vb
                $p_paraVB= $_POST['p_paraVB'];//usuario para enviar vb
                //$p_visacionVB= $_POST['p_visacionVB'];
                $p_visacionVB='SI';
                
                $p_vis_id = mt_rand();//validar como agregar este campo (id tabla visaciones)
                $this->fun_agregar_visacion_vb($p_vis_id,$p_id,$p_doc_version,$p_paraVB,$p_comentario,$p_visacionVB);        
            
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
                    $this->fun_agregar_destinatario($arrayDestinatario,$arrayCargoDestinatario,$p_id,$p_doc_version,$arrayDireccion,$arrayCorreo,$p_medio_envio,$arrayMiTipo,$arrayMiNombreDes);
                    if($_POST["p_arrayCopia"] != ""){
                    //llamar la funcion agregar copia
                    $this->fun_agregar_copia($arrayCopia,$arrayCargoCopia,$p_id,$p_doc_version,$arrayCopiaDireccion,$arrayCopiaCorreo,$p_medio_envio,$arrayMiTipoCopia,$arrayMiNombreDesCopia);
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
                $this->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);

            
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
                        
                        $this->_ORA->ejecutaProc("GDE.GDE_TIPO_DOCUMENTO_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
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
                if(isset($usuarioCopiaEVB)){
                    for ($i=0;$i<count($usuarioCopiaEVB);$i++) { 
                        $bind = array(':usuario' => $usuarioCopiaEVB[$i]);
                        //$correo_copia[$i] = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind); //No existe correo para null
                    }   
                    $copia_correo = implode(",", $correo_copia); 
                }else{
                    $copia_correo = null;
                }

                //enviamos el correo //esta tirando error email null 
                //$this->fun_enviar_correo($correo_para,$copia_correo,$p_id,$comentario,$usuario_desde);     

                //derivamos a la bandeja de WF [DESCOMENTAR]
                $this->setAsignar($usuarioPara, $_POST['p_comentarioVB'],$p_id);
                $this->_ORA->Commit();

                return "OK";
                exit();

            }catch(Exception $e){

                $this->_LOG->error(print_r($e));

            }

    }

    public function fun_enviarvb_todas_PRUEBAS(){

        $arrayMiNombreDes       = explode("_,", substr($_POST["p_arrayMiNombreDes"], 0, -1)); 
        $arrayMiNombreDesCopia  = explode("_,", substr($_POST["p_arrayMiNombreDesCopia"], 0, -1));
        
        echo "<pre>";var_dump($arrayMiNombreDes);echo "</pre>";exit();
        echo "<pre>";var_dump($arrayMiNombreDesCopia);echo "</pre>";

    }


    //ml: enviamos a VB desde pestaña TODAS
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

            $this->fun_agregar_nuevo_certificado($dataCertificado);




       

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
            //$p_visacionVB= $_POST['p_visacionVB'];
            $p_visacionVB='SI';
            
            $p_vis_id = mt_rand();//validar como agregar este campo (id tabla visaciones)
            $this->fun_agregar_visacion_vb($p_vis_id,$p_id,$p_doc_version,$p_paraVB,$p_comentario,$p_visacionVB);        
        
            //validamos que venga archivo adjunto en envioVB
            if(isset($_FILES['file4']['tmp_name'])){
                $blob2 = $this->_ORA->NewDescriptor(OCI_DTYPE_BLOB);
                $blob2->WriteTemporary(file_get_contents($_FILES['file4']['tmp_name']),OCI_TEMP_BLOB);
                $bindVisacion =  array(":p_vis_id"=> $p_vis_id,":p_vis_adjunto" => $blob2);
                $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_ACTUALIZAR_ADJUNTO",$bindVisacion);
                $this->_ORA->Commit();
            }



            ////////////////////////////////////////mlatorre


              //||||||||||||||||||||||||||||||||AGREGAR DESTINATARIO y COPIA si existe|||||||||||||||||||||||||||||||||||||||||||||||||||
                //se agrega destinatario siempre y cuando exista destinatario por agregar 
                if($_POST["p_arrayDestinatario"] != ""){
                    //llamar la funcion agregar destinatario
                    $this->fun_agregar_destinatario(
                        $arrayDestinatario,
                        $arrayCargoDestinatario,
                        $p_id,
                        $p_doc_version,
                        $arrayDireccion,
                        $arrayCorreo,
                        $p_medio_envio,
                        $arrayMiTipo,
                        $arrayMiNombreDes);
                    
                    if($_POST["p_arrayCopia"] != ""){
                    //llamar la funcion agregar copia
                    $this->fun_agregar_copia(
                        $arrayCopia,
                        $arrayCargoCopia,
                        $p_id,
                        $p_doc_version,
                        $arrayCopiaDireccion,
                        $arrayCopiaCorreo,
                        $p_medio_envio,
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
                $this->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);

            
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
                        
                        $this->_ORA->ejecutaProc("GDE.GDE_TIPO_DOCUMENTO_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
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
                if(isset($usuarioCopiaEVB)){
                    for ($i=0;$i<count($usuarioCopiaEVB);$i++) { 
                        $bind = array(':usuario' => $usuarioCopiaEVB[$i]);
                        //$correo_copia[$i] = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind); 
                    }   
                    $copia_correo = implode(",", $correo_copia); 
                }else{
                    $copia_correo = null;
                }

                //enviamos el correo 
                //$this->fun_enviar_correo($correo_para,$copia_correo,$p_id,$comentario,$usuario_desde);     

                //derivamos a la bandeja de WF [DESCOMENTAR]
                $this->setAsignar($usuarioPara, $_POST['p_comentarioVBTodos'],$p_id);
                $this->_ORA->Commit();

                return "OK";




        }catch(Exception $e){

            $this->_LOG->error(print_r($e));

        }


    }



    //ml: metodo para derivarlo a otra bandeja de WF
    public function setAsignar($usuario, $comentario,$NUMERO_CASO){
            
        try{
              $bind = array(':caso'=>$NUMERO_CASO, ':usuario' => $usuario);
              $this->_ORA->ejecutaProc("wfa.wf_rso_pkg.fun_asignar", $bind);
              $this->_LOG->log("Se asigna el WF: ".$NUMERO_CASO.' con bind '.print_r($bind,true));

        }catch(Exception $e){
              $this->_LOG->error(print_r($e));
        }
    }


    //ml: agregamos la visacion del enviar vb
    public function fun_agregar_visacion_vb($p_vis_id,$p_id,$p_doc_version,$p_paraVB,$p_comentarioVB,$p_visacionVB){

        //$p_vis_id = mt_rand();//validar como agregar este campo (id tabla visaciones)
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
        $this->_ORA->ejecutaProc("GDE.GDE_VISACIONES_PKG.PRC_AGREGAR_VISACION",$bind);
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
    }

    //cargamos el combo de los PARA en el formulario de OTRA UNIDAD    
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

 
    //ml: enviar a VB para la pestaña OTRA UNIDAD
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

            $this->fun_agregar_nuevo_certificado($dataCertificado);

            
        
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
            
            $p_visacionVB='SI';
            $p_comentario= $_POST['p_otraUnidadComentarioVB']; //comentario visacion enviar vb
            $p_paraVB= $_POST['p_otraUnidadParaVB'];//usuario para enviar vb
            //$p_visacionVB= $_POST['p_otraUnidadVisacionVB'];
            //$p_copiaVB =  $_POST['p_otraUnidadCopiaVB'];
           

            //var_dump($p_visacionVB."//".$p_comentario."//". $p_paraVB."//".$p_visacionVB."//".$p_doc_version."//".$p_id);
            //echo "<pre>";var_dump($blob3);echo "</pre>";
            //echo "<pre>";var_dump($agregarPDF3);echo "</pre>";
            
        
            $p_vis_id = mt_rand();//validar como agregar este campo (id tabla visaciones)
            $respVisacion = $this->fun_agregar_visacion_vb($p_vis_id,$p_id,$p_doc_version,$p_paraVB,$p_comentario,$p_visacionVB);
            
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
                $this->fun_agregar_destinatario(
                    $arrayDestinatario,
                    $arrayCargoDestinatario,
                    $p_id,
                    $p_doc_version,
                    $arrayDireccion,
                    $arrayCorreo,
                    $p_medio_envio,
                    $arrayMiTipo,
                    $arrayMiNombreDes);
                if($_POST["p_arrayCopia"] != ""){
                //llamar la funcion agregar copia
                $this->fun_agregar_copia($arrayCopia,$arrayCargoCopia,$p_id,$p_doc_version,$arrayCopiaDireccion,$arrayCopiaCorreo,$p_medio_envio,$arrayMiTipoCopia,$arrayMiNombreDesCopia);
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
            $this->fun_crear_proceso($p_id, $p_doc_caso_padre,$NUMERO_PROCESO, $p_doc_redactor, $TIPO_DOCUMENTO);

     
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
                    
                    $this->_ORA->ejecutaProc("GDE.GDE_TIPO_DOCUMENTO_PKG.PRC_AGREGAR_ADJUNTO",$bindAdjunto);
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
            if(isset($usuarioCopiaEVB)){
                for ($i=0;$i<count($usuarioCopiaEVB);$i++) { 
                    $bind = array(':usuario' => $usuarioCopiaEVB[$i]);
                    //$correo_copia[$i] = $this->_ORA->ejecutaFunc("wfa_usr.getEmailUsuario",$bind); 
                }   
                $copia_correo = implode(",", $correo_copia); 
            }else{
                $copia_correo = null;
            }

            //enviamos el correo 
            //$this->fun_enviar_correo($correo_para,$copia_correo,$p_id,$comentario,$usuario_desde);      
            
            //derivamos a la bandeja de WF [DESCOMENTAR]
            $this->setAsignar($usuarioPara, $_POST['p_otraUnidadComentarioVB'],$p_id);
            $this->_ORA->Commit();
            
            return "OK";      

        }catch(Exception $e){

            $this->_LOG->error(print_r($e));

        }


    } 


    //realizamos la busqueda de enviar a vb
    public function fun_buscar_evb(){
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
    }


    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||| TIPO DE ENVIO |||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
  
   //ml: listamos los tipos de envios que existen
    public function fun_listar_tipo_envio(){
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
    }

    public function fun_tipo_envio_html(){

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
    }



   


   
}    







