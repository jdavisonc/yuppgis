<?php

interface GISWSDAL {
	
	function get($ownerName, $attr, $persistentClass, $id);
	
	function save($ownerName, $attr, PersistentObject $obj);
	
	function delete($ownerName, $attr, $id, $logical);
	
}

?>