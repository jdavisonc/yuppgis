<?php

class SelectGIS extends SelectAttribute {
	
	public function __construct($tableAlias, $attrAlias = null) {
		parent::__construct($tableAlias, null, $attrAlias);
	}
	
}

?>