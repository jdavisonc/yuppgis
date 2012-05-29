<?php

YuppLoader::load('yuppgis.core.mvc', 'GISController');
YuppLoader::load('geolocalizacion.model', 'Padron');

class GeoController extends GISController {
	
	public function geolocalizarAction() {
		$calle = strtoupper($this->params['calle']);
		$numero = $this->params['numero'];
		
		if ($calle && $numero) {
			$padrones = Padron::findBy(
				Condition::_AND()
					->add(
						Condition::_OR()
							->add(Condition::EQ(Padron::getClassName(), 'calle', $calle))
							->add(Condition::EQ(Padron::getClassName(), 'calle', 'AV '.$calle))
							->add(Condition::EQ(Padron::getClassName(), 'calle', 'BV '.$calle)))
					->add(Condition::EQ(Padron::getClassName(), 'numero', $numero)), 
				new ArrayObject());
			
			if ($padrones) {
				$res = $padrones[0];
				return $this->renderString( KMLUtilities::GeometryToKML($res->getId(), $res->getUbicacion()));
			}
		}
		return $this->renderString( "<Error>Resultado no encontrado</Error>" );
	}
	
	public function callesAction() {
		$calle = strtoupper($this->params['calle']);
		
		$q = new GISQuery();
		$q->addAggregation(SelectAggregation::AGTN_DISTINTC, 'p', 'calle', 'nombre');
		$q->addFrom(Padron::getClassName(), 'p');
		$q->setCondition(Condition::LIKE('p', 'calle', '%'.$calle.'%'));

		$pm = PersistentManagerFactory::getManager();
		$result = $pm->findByQuery($q);
		
		if ($result) {
			return $this->renderString( json_encode($result) );
		} else {
			return $this->renderString( "<Error>Resultado no encontrado</Error>" );
		}
	}
	
}

?>