<?php

class Estado {
	
	const CONTROLADO = "controlado";
	const ADVERTENCIA = "advertencia";
	const NO_CONTROLADO = "no_controlado";
	
	public static function getEstados() {
		return array(self::CONTROLADO, self::NO_CONTROLADO, self::ADVERTENCIA, '');
	}
	
	public static function getEstadosView() {
		return array(self::CONTROLADO => self::CONTROLADO, self::NO_CONTROLADO => self::NO_CONTROLADO, 
				self::ADVERTENCIA => self::ADVERTENCIA, '' => '');
	}
	
}

?>