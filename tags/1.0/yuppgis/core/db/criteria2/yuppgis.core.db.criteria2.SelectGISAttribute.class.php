<?php

class SelectGISAttribute extends SelectAttribute {

	public function __construct($tableAlias, $attrName, $selectItemAlias = null) {
		parent::__construct($tableAlias, $attrName, $selectItemAlias);
	}

}

?>