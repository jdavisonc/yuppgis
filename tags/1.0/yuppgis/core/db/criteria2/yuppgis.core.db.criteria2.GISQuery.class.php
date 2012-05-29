<?php

/**
 * 
 * Clase que representa un consulta geografica
 * 
 * @package yuppgis.core.db.criteria2
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class GISQuery extends Query {
	
	public static function getClassName() {
        return get_called_class();
    }
	
	public function addFunction(GISFunction $function) {
		$this->select->add( $function );
		return $this;
	}
	
	public function addFrom($instance_or_class, $alias) {
		$from = new stdClass();
		$from->instance_or_class = $instance_or_class;
		$from->alias = $alias;
		$this->from[] = $from;
		return $this;
	}
	
}

?>