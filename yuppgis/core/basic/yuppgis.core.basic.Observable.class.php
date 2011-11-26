<?php

YuppLoader::load( "yuppgis.core.basic", "Observer" );

/**
 * Clase que representa una clase observable que genera eventos.
 * 
 * @package yuppgis.core.basic
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
abstract class Observable extends PersistentObject {
	
	public function __construct($args = array(), $isSimpleInstance = false ) {
		$this->addAttribute("observers", Datatypes::TEXT);		
		parent :: __construct($args, $isSimpleInstance);
	}
	
	public function registerObserver(Observer $observer) {
		if($observer->getId() == null){
			throw new Exception("El observer tiene que haber sido persistido.", 0);
		}
		$observers = $this->getObservers();
		
		if(!(strstr($observers, get_class($observer)))) {
			$observers .= ";".get_class($observer)."_".$observer->getId();
			$this->setObservers($observers);
		}
	}
	
	public function unregisterObserver(Observer $observer) {
		if($observer->getId() == null){
			throw new Exception("El observer tiene que haber sido persistido.", 0);
		}
		
		$observers = $this->getObservers();		
		str_replace(";".get_class($observer)."_".$observer->getId(), "", $observers);
		$this->setObservers($observers);
	}
	
	abstract public function notifyObservers($params);
	
}

?>