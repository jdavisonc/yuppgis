<?php

class GISQuery extends Query {
	
	public function addFunction(GISFunction $function) {
		$this->select->add( $function );
		return $this;
	}
	
}