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
	
}

?>