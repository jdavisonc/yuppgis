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

		if ( $this->gisurl !== $this->url && $this->gistype !== $this->type && $this->gisdatabase !== $this->database ) {
			
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
		$gisdatasource = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_GISDB);
		if ($gisdatasource != NULL) {
			$currentMode = YuppConfig::getInstance()->getCurrentMode();
			$this->gisurl      = $gisdatasource[$currentMode]['url'];
			$this->gisuser     = $gisdatasource[$currentMode]['user'];
			$this->gispass     = $gisdatasource[$currentMode]['pass'];
			$this->gisdatabase = $gisdatasource[$currentMode]['database'];
			$this->gistype	   = $gisdatasource[$currentMode]['type'];
		}
		
		$this->srid = YuppGISConfig::getInstance()->getGISPropertyValue($appName, YuppGISConfig::PROP_SRID);

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
	
	public function isTheSameConnectorThatGISDB() {
		return ($this->db == $this->gisdb);
	}

	/**
	 * Inserta un registro del tipo geografico
	 * @param $tableName Nombre de tabla
	 * @param $attrs Atributos
	 */
	public function insert_geometry ( $tableName, $attrs ) {
		$uiprop = 'null';
		if ($attrs['uiproperty'] != null){
			$uiprop = "'".$attrs['uiproperty']."'";
		}
		
		$query = "INSERT INTO " . $tableName . " ( geom, uiproperty ) ".
					"VALUES ( ".$this->gisdb->geomFromText($attrs['geom'], $this->srid).", ".$uiprop." );" ;
		$this->gisdb->execute( $query );
		
		return $this->gisdb->getLastInsertedID($tableName, 'id');
	}
	
	/**
	 * Actualiza un registro del tipo geografico
	 * @param $tableName Nombre de tabla
	 * @param $attrs Atributos
	 */
	public function update_geometry( $tableName, $attrs ) {
		$uiprop = 'null';
		if ($attrs['uiproperty'] != null){
			$uiprop = "'".$attrs['uiproperty']."'";
		}
		
		$query = "UPDATE " .  $tableName . " SET geom = ".$this->gisdb->geomFromText($attrs['geom'], $this->srid).
					", uiproperty = ".$uiprop." WHERE id = " . $attrs['id'];
		$this->gisdb->execute( $query );
	}
	
	/**
	 * Ejecuta consulta sobre gis datasource
	 * @param Query $query
	 * @throws Exception
	 */
	public function gis_query( Query $query ) {
		$res = array();
	    try {
	    	
	    	$gisSelects = $this->extractGISSelectAndConvertGISValues($query->getSelect());
	    	
	    	$q = $this->gisdb->evaluateGISQuery($query, $this->srid);
			
	    	if ( !$this->gisdb->query($q) ) { 
				throw new Exception("ERROR");
			}
			$i = 1;
			while ( $row = $this->gisdb->nextRow() ) {
				foreach ($gisSelects as $gs) {
					$geom = WKTGEO::fromText($row[$gs]);
					$idKey = 'id' . $i;
					if (array_key_exists($idKey, $row)) {
						$geom->setId($row[$idKey]);
						$geom->aSet('uiproperty', $row['uiproperty' . $i]);
					}
					
					$row[$gs] = $geom;
					$i++;
				} 
				$res[] = $row;
			}
			
		} catch (Exception $e) {
			Logger::getInstance()->log("GISDAL::gis_query error: " . $e->getMessage() . " \n " . $this->gisdb->getLastError());
			throw $e;
		}
		return $res;
	}
	
	/**
	 * Funcion que retorna un array con los select geograficos que se van a hacer en la consulta.
	 * Tambien remplaza los objetos geograficos por su representacion en WKT.
	 * @param $select Select a ser inspeccionado
	 * @return array con alias de elementos geograficos en el select
	 */
	private function extractGISSelectAndConvertGISValues($select) {
		$gisSelects = array();
		foreach ($select->getAll() as $proj) {
			if ($proj instanceof SelectValue && $proj->getValue() instanceof Geometry) {
				$wkt = WKTGEO::toText($proj->getValue());
				$proj->setValue($wkt);
				$gisSelects[] = $proj->getSelectItemAlias();
			} else if ($proj instanceof GISFunction) {
				// Si existe un selectValue en la funcion geo, se convierte su valor a texto
				foreach ($proj->getParams() as $param) {
					if ($param instanceof SelectValue) {
						$wkt = WKTGEO::toText($param->getValue());
						$param->setValue($wkt);
					}
				}
				if ($proj->returnGeometry()) {
					$gisSelects[] = $proj->getSelectItemAlias();
				}
			} else if ($proj instanceof SelectGIS) {
				$gisSelects[] = $proj->getSelectItemAlias();
			}
		}
		return $gisSelects;
	}

}

?>