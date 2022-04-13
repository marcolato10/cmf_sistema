<?php

class Funcionario{
	
	public static function getNombre($codigoUsuario, $ORA){
		$bind = array(':cod' => $codigoUsuario);
		return $ORA->ejecutaFunc('wfa_usr.getNombreUsuario',$bind);
	}
	
	public static function getUnidadUsuario($codigoUsuario, $ORA){
		$NombreUnidad = '';
		$bind = array(':cod' => $codigoUsuario);
		$unidad =  $ORA->ejecutaFunc('wfa_usr.getUnidad',$bind);
		if(is_numeric($unidad)){
			$bind = array(':cod' => $unidad);
			$NombreUnidad = $ORA->ejecutaFunc('wfa_usr.getNombreUnidad',$bind);
		}
		return $NombreUnidad;
	}
}


?>