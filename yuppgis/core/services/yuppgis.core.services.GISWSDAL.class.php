<?php

interface GISWSDAL {
	
	function get($ownerTableName, $attr, $persistentClass, $id);
	
	function save($ownerTableName, $attrNameAssoc, $kml);
	
	function delete($ownerTableName, $attrNameAssoc, $id, $logical);

	function findBy();
	
}

?>