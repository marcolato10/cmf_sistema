<?php

	class Paso1 extends ClaseSistema{
		
		//Esto es como si reemplazara el main
		static $DEFAULT_PASO1_TIPO_RESOLUCION = 'exenta';
		
		
		
		
		public function generarHTML(){
			
			//Se elimina para que no quede a disposicion del usuario, 
			/*$data = $this->_RESOLUCION->fun_getTipoResolucion();
			foreach($data as $registro){
				if($this->_RESOLUCION->TIPRES_ID == $registro['TIPRES_ID']){
					$registro['SELECTED'] = 'selected';
				}else{
					$registro['SELECTED'] = '';
				}
				$this->_TEMPLATE->assign('TIPO_RESOLUCION',$registro);
				$this->_TEMPLATE->parse('main.paso1.tipo_resolucion');
			}*/
			//Primero se deja el archol para la situacin Inicial
			
			$this->_RESOLUCION->CLASIFICACION_OBJ->fun_dibujaClasificacion($this->_RESOLUCION->CLA_ID);
			$this->_TEMPLATE->assign('CANTIDAD_CLASIFICACION',count($this->_RESOLUCION->CLA_ID));
			
			
			#Acá se deben dibujar todas las clasificaciones
			
			$this->_RESOLUCION->CLASIFICACION_OBJ->dibujaPropiedades();

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
			
			$this->_RESOLUCION->DESTINATARIO_OBJ->seteaHtml(1);
			$this->_RESOLUCION->DESTINATARIO_OBJ->seteaHtml(2);

			$this->_TEMPLATE->parse('main.paso1.div_buscarFiscalizado');
			$this->_TEMPLATE->parse('main.paso1');
		}
	}

?>