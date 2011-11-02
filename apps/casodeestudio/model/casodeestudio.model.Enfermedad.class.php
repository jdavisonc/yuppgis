<?php

class Enfermedad {
	
	const ASMA = 'asma';
	const DIABETES = 'diabetes';
	const HIPERTENCION = 'hipertencion';
	const INSUFICIENCIA_RENAL = 'insuficiencia_renal';
	const OBESIDAD = 'obesidad';
	
	public static function getName($enfermedad) {
		switch ($enfermedad) {
			case self::DIABETES:
				return "Diabetes";
			case self::ASMA:
				return "Asma";
			case self::OBESIDAD:
				return "Obesidad";
			case self::HIPERTENCION:
				return "Hipertencion Arterial";
			case self::INSUFICIENCIA_RENAL:
				return "Insuficiencia Renal";
		}
	}
	
	public static function fromName($name) {
		switch ($name) {
			case "Diabetes":
				return self::DIABETES;
			case "Asma":
				return self::ASMA;
			case "Obesidad":
				return self::OBESIDAD;
			case "Hipertencion Arterial":
				return self::HIPERTENCION;
			case "Insuficiencia Renal":
				return self::INSUFICIENCIA_RENAL;
		}
	}
	
	public static function getEnfermedades() {
		return array(self::ASMA, self::DIABETES, self::HIPERTENCION, self::INSUFICIENCIA_RENAL, self::OBESIDAD);
	}
	
	public static function getLayerIdForEnfermedad($enfermedad) {
		$finded = array_keys(self::getEnfermedades(), $enfermedad);
		if ($finded) {
			return $finded[0] + 1; // Mas uno porque el indice comienza en 0
		} else {
			throw new Exception("No se encontro enfermedad");
		}
	}
}

?>