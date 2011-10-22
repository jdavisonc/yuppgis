<?php

class YuppGISConventions extends YuppConventions {

	public static function gisTableName($tableNameOwner, $attr) {
		return $tableNameOwner . '_' . $attr . '_geo';
	}
	
	public static function getReservedWords(){
		return array("observers", "app", "class", "deleted", "id");
	}
}

?>