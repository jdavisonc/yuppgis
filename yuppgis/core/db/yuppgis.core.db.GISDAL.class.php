<?php

YuppLoader :: load('yuppgis.core.config', 'YuppGISConfig');

class GISDAL extends DAL {

	private $srid;
	
	public function __construct($appName) {
		Logger::getInstance()->log("GISDAL::construct");

		$cfg = YuppConfig::getInstance();

		$datasource = $cfg->getDatasource($appName);
		$type = $datasource['type'];

		if ( $type != YuppConfig::DB_MYSQL && $type != YuppConfig::DB_POSTGRES ) {
			throw new Exception('datasource type no soportado para operaciones geograficas: '.$datasource['type']);
		}
		
		$this->srid = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::$PROP_SRID);

		parent::__construct($appName);
	}

	/**
	 * Obtiene un registro del tipo geografico
	 * @param $tableName Nombre de tabla
	 * @param $attrs Atributos
	 */
	public function get_geometry( $tableName, $id ) {
		if ( $id === NULL ) throw new Exception("GISDAL.get: id no puede ser null");

		$q = "SELECT id, AsText(geom) as text FROM " . $tableName . " WHERE id=" . $id;

		$this->db->query( $q );

		if ( $row = $this->db->nextRow() ) {
			return $row;
		}

		throw new Exception("GISDAL.get: no se encuentra el objeto con id ". $id . " en la tabla " . $tableName);
	}

	/**
	 * Inserta un registro del tipo geografico
	 * @param $tableName Nombre de tabla
	 * @param $attrs Atributos
	 */
	public function insert_geometry ( $tableName, $attrs ) {
		$query = "INSERT INTO " . $tableName . " ( geom ) VALUES ( GeomFromText ( '". $attrs['geom'] ."', ". $this->srid ."));" ;
		$this->db->execute( $query );
		
		return $this->db->getLastInsertedID($tableName, 'id');
	}
	
	/**
	 * Actualiza un registro del tipo geografico
	 * @param $tableName Nombre de tabla
	 * @param $attrs Atributos
	 */
	public function update_geometry( $tableName, $attrs ) {
		$query = "UPDATE " .  $tableName . " SET geom = GeomFromText( '" . $attrs['geom'] . "', " . $this->srid 
					. ") WHERE id = " . $attrs['id'];
		$this->db->execute( $query );
	}

}

?>