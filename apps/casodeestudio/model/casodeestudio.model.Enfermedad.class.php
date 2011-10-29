<?php

class Enfermedad {
	
	const DIABETES = 'diabetes';
	const HIPERTENCION = 'hipertencion';
	const OBESIDAD = 'obesidad';
	const ASMA = 'asma';
	const INSUFICIENCIA_RENAL = 'insuficiencia_renal';
	
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
	
}

?>