<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1); 

require_once("class/correo.class.php");


class correoCertificado extends ClaseSistema{

    // Método constructor de los CORREOS CERTIFICADO
    public function __construct($obj){
        
        $this->setControl($obj);
            
        /*$this->DESTINATARIO_OBJ = new Destinatario();
        $this->DESTINATARIO_OBJ->setControl($obj);
        */
        
        //$this->cargarSesion();
        
    }


    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //||||||||||||||||||||||||||||||||||||||||| ENVIAR VB |||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    //ml :: CORREO NOTIFICACION :: DERIVACION DE CASO :: ENVIAR A VB
    public function fun_notificarDerivarCaso($correo_para,$correo_copia,$NUMERO_CASO,$comentario,$usuario_desde){


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

     
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||| FIRMA CERTIFICADO ||||||||||||||||||||||||||||||||||||||||||||||||||
    //|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    //ml :: FIRMA CERTIFICADO :: NOTIFICAMOS A TODOS LOS PARTICIPANTES  (VISACIONES)
    public function fun_notificarAllParticipanes($email_destino,$label_certificado,$numero_cer){

        $correo = new Correo();
        $correo->ORA = $this->_ORA;
        $correo->DESDE = 'noresponder@svs.cl';
        $correo->DESDE_NOMBRE = 'Comisión para el Mercado Financiero'; 

        $correo->ASUNTO = 'Notificación firma documento '.$label_certificado.' '.$numero_cer;   
        $correo->TEXTO = 'Estimado (a)
        Con fecha '.$fecha_cer.', infomamos que el documento '.$label_certificado.' '.$numero_cer.'
        se encuentra firmado, Atentamente, Comisión para el Mercado Financiero.';

        $correo->APLIC = 'PUGDE';
        $correo->setPara($email_destino);
        $correo->setCopiaOculta('culloa@svs.cl');
        $correo->enviar();    


    }



    //ml :: NOTIFICAMOS X CORREO A LOS DESTINATARIOS EMAIL 
    public function fun_notificarDestinatarioEmail($email_destino,$ruta_pdf,$destinatario,$numeroSGD,$numero_cer,$fecha_cer,$label_certificado){

        try{

            
            $correo = new Correo();
            
            $correo->ORA = $this->_ORA;
            $correo->DESDE = 'noresponder@svs.cl';
            $correo->DESDE_NOMBRE = 'Comisión para el Mercado Financiero';                                                                    

            $correo->ASUNTO = 'Envio documento '.$numero_cer;   
            $correo->TEXTO = 'Estimado (a) '.$destinatario.'
            Con fecha '.$fecha_cer.', esta Comisión hace envío del documento adjunto '.$label_certificado.' '.$numero_cer.'
            Atentamente, Comisión para el Mercado Financiero.';

            $correo->APLIC = 'PUGDE';
            $correo->ADJUNTO = true;        
            $correo->setPara($email_destino);
            $correo->setCopiaOculta('culloa@svs.cl');
            $correo->setAdjunto($ruta_pdf,$numeroSGD.'.pdf');
            $correo->enviar();
            
            //var_dump();exit();

            return $correo->ID_CORREO; 
        
        
        }catch(Exception $e){
            $this->_LOG->error(print_r($e));
        }
    }


}