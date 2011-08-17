<?php

class DataLayer extends GISPersistentObject {

	private  $id;
	private  $name;
	private  $elements;
	private  $tags;
	private  $indexAttribute;

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $id
	 * @param unknown_type $name
	 * @param unknown_type $indexAttribute
	 */
	function __construct($id,$name, $indexAttribute='id'){
		$this->id=isset($id) ? uniqid() :$id;
		$this->name = isset($name) ? "".$this->id :$name;
		$this->elements = array();
		$this->tags = array();		
		$this->indexAttribute = $indexAttribute;		
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $element
	 */
	function addElement($element){
		$index = $element-> aGet($this->indexAttribute);
		$this->elements[$index] = $element;
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $key
	 */
	function removeElement($key){
		if ( array_key_exists($key, $this->elements) ){
			unset($this->elements[$key]);
		}
	}

	/**
	 * 
	 * Enter description here ...
	 */
	function getElements(){
		return $this->elements;
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	function getId(){
		return $this->id;
	}
}

?>