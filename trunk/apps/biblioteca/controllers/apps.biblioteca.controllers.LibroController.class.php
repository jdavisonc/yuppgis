<?php

YuppLoader::load('biblioteca.model', 'Libro');

class LibroController extends YuppController {

   public function indexAction()
   {
      //return $this->renderString("Bienvenido a su nueva aplicacion!");
      return $this->redirect( array('action'=>'list') );
   }
	
   public function jsonShowAction()
   {
      YuppLoader::load('core.persistent.serialize', 'JSONPO');
	  
	  sleep( 1 );
	  
      $id = $this->params['id'];        // Obtiene el par�metro id
      $libro = Libro::get( $id );       // Carga el libro con ese id
      $json = JSONPO::toJSON( $libro ); // Genera la serializaci�n a json

      // Lo que se devolver� en el response HTTP ser� de tipo json
      header('Content-type: application/json');

      // Escribe el string json en la respuesta al usuario
      return $this->renderString( $json );
   }
   
   public function xmlShowAction()
   {
	  YuppLoader::load('core.persistent.serialize', 'XMLPO');
	   
	  $id = $this->params['id'];
      $libro = Libro::get( $id );
      $xml = XMLPO::toXML( $libro );
		
      header('Content-type: text/xml');
      return $this->renderString( $xml );
   }
   
   	public function listAction()
	{
		$libros = Libro::listAll($this->params);
		
		return array('libros' => $libros);
	}
}

?>