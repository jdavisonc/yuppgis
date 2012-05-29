<?php

/**
 * Clase que representa que un atributo selecionado en una consulta geografica (@link GISQuery), es geografico. 
 *
 * @package yuppgis.core.db.criteria2
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
class SelectGIS extends SelectAttribute {
	
	public function __construct($tableAlias, $attrAlias) {
		parent::__construct($tableAlias, null, $attrAlias);
	}
	
}

?>