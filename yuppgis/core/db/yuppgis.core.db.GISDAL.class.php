<?php

YuppLoader :: load('yuppgis.core.config', 'YuppGISConfig');

class GISDAL extends DAL {

	private $srid;
	
	private $gisdb;
	private $gisurl;
	private $gisuser;
	private $gispass;
	private $gisdatabase;
	private $gistype;
	
	public function __construct($appName) {
		Logger::getInstance()->log("GISDAL::construct");
		
		$this->init($appName);

		if ($this->gisurl !== $this->url && $this->gistype !== $this->type) {
			
			// Se setea el db
			parent::__construct($appName);
			
			// Se seteea el gisdb
			$this->gisdb = $this->initGISDatasource($this->gistype);
			$this->gisdb->connect( $this->gisurl, $this->gisuser, $this->gispass, $this->gisdatabase );
		} else {
			$this->gisdb = $this->initGISDatasource($this->type);
			$this->gisdb->connect( $this->url, $this->user, $this->pass, $this->database );
			$this->db = $this->gisdb;
		}
	}
	
	protected function init($appName) {
		$gisdatasource = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::$PROP_GISDB);
		if ($gisdatasource != NULL) {
			$this->gisurl      = $gisdatasource['url'];
			$this->gisuser     = $gisdatasource['user'];
			$this->gispass     = $gisdatasource['pass'];
			$this->gisdatabase = $gisdatasource['database'];
			$this->gistype	   = $gisdatasource['type'];
		}
		
		$this->srid = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::$PROP_SRID);

		parent::init($appName);
	}
	
	private function initGISDatasource( $type ) {
		switch( $type ) {
         case YuppConfig::DB_POSTGRES:
            YuppLoader::load( "yuppgis.core.db", "DatabasePostgisSQL" );
            return new DatabasePostgisSQL();
         break;
         default:
            throw new Exception('datasource type no soportado: '.$type);
		}
	}

	/**
	 * Obtiene un registro del tipo geografico
	 * @param $tableName Nombre de tabla
	 * @param $attrs Atributos
	 */
	public function get_geometry( $tableName, $id ) {
		if ( $id === NULL ) throw new Exception("GISDAL.get: id no puede ser null");

		$q = "SELECT id, AsText(geom) as text FROM " . $tableName . " WHERE id=" . $id;

		$this->gisdb->query( $q );

		if ( $row = $this->gisdb->nextRow() ) {
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
		$this->gisdb->execute( $query );
		
		return $this->gisdb->getLastInsertedID($tableName, 'id');
	}
	
	/**
	 * Actualiza un registro del tipo geografico
	 * @param $tableName Nombre de tabla
	 * @param $attrs Atributos
	 */
	public function update_geometry( $tableName, $attrs ) {
		$query = "UPDATE " .  $tableName . " SET geom = GeomFromText( '" . $attrs['geom'] . "', " . $this->srid 
					. ") WHERE id = " . $attrs['id'];
		$this->gisdb->execute( $query );
	}

}

?>