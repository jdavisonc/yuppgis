<?php

class YuppGISConventions extends YuppConventions {

	public static function gisTableName($tableNameOwner, $attr) {
		return $tableNameOwner . '_' . $attr . '_geo';
	}
	
}

?>