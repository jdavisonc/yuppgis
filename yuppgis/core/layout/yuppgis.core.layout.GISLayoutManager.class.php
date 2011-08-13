<?php
class GISLayoutManager {

	private function __construct() {}
	 
	private static $instance = NULL;
	 
	public static function getInstance()
	{
		if (is_null(self::$instance)) self::$instance = new GISLayoutManager();
		return self::$instance;
	}
	 
	 
	private $referencedJSLibs = array();
	public function addGISJSLibReference( $params )
	{
		global $_base_dir;

		$path = $_base_dir;

		$path .= '/yuppgis/js/'. $params['name'] .'.js';

		if (!array_key_exists($params['name'], $this->referencedJSLibs))
		{
			$this->referencedJSLibs[$params['name']] = $path;
			echo '<script type="text/javascript" src="'. $path .'"></script>';
		}
	}
}
?>