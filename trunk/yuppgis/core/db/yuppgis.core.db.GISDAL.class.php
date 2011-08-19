<?php

class GISDAL extends DAL {


	public function get_geometry( $tableName, $id ) {
		if ( $id === NULL ) throw new Exception("GISDAL.get: id no puede ser null");

		$q = "SELECT id, AsGML(geom) as gml FROM " . $tableName . " WHERE id=" . $id;

		$this->db->query( $q );

		if ( $row = $this->db->nextRow() )
		{
			return $row;
		}

		throw new Exception("GISDAL.get: no se encuentra el objeto con id ". $id . " en la tabla " . $tableName);
	}

}

?>