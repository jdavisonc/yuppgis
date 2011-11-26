<?php

/**
 * Interfaz que representa un observador.
 * 
 * @package yuppgis.core.basic
 * 
 * @author Jorge Davison
 * @author Martin Taruselli
 * @author Emilia Rosa
 * @author German Schnyder
 */
interface Observer {	
	
	/**
	 * Notifica cuando evento sucede.
	 * @param $sender
	 * @param $params
	 */
	public function notify($sender, $params);
	
}

?>