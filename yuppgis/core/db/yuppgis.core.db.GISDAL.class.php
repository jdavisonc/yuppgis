<?php

class GISDAL extends DAL {


	public function get_geometry( $tableName, $id ) {
		if ( $id === NULL ) throw new Exception("GISDAL.get: id no puede ser null");

		$q = "SELECT id, AsText(geom) as text FROM " . $tableName . " WHERE id=" . $id;

		$this->db->query( $q );

		if ( $row = $this->db->nextRow() )
		{
			return $row;
		}

		throw new Exception("GISDAL.get: no se encuentra el objeto con id ". $id . " en la tabla " . $tableName);
	}
	
	
	
	public function insert_geometry ( $tableName, $attrs ) {
	  //TODO_GIS falta el srid, para agregarlo insertar
	  $srid = 32721;
	  $query = "INSERT INTO " . $tableName . " ( id, geom, class ) values ( ". $attrs['id'] . ", ". " GeomFromText ( '". $attrs['geom'] ."', ". $srid ."), '". $attrs['class'] . "' )" ;
      $this->db->execute( $query );
	}
	

}

?>