<?php

YuppLoader::load("biblioteca.model", "Autor");

class Libro extends PersistentObject {

  function __construct($args = array (), $isSimpleInstance = false)
  {
     $this->setWithTable('biblio_libro');
  
     $this->addAttribute("titulo", Datatypes :: TEXT);
     $this->addAttribute("genero", Datatypes :: TEXT);
     $this->addAttribute("fecha", Datatypes :: DATETIME);
     $this->addAttribute("idioma", Datatypes :: TEXT);
     $this->addAttribute("numeroPaginas", Datatypes :: INT_NUMBER);
	 
	 $this->addHasOne("autor", "Autor");
	 $this->addHasMany("coautores", "Autor");
	 
	 // Restricciones
     $this->addConstraints("numeroPaginas", array(
        Constraint::between(20, 3000)
     ));
	 
     parent :: __construct($args, $isSimpleInstance);
  }

  public static function listAll(ArrayObject $params)
  {
     self :: $thisClass = __CLASS__;
     return PersistentObject :: listAll($params);
  }

  public static function count()
  {
     self :: $thisClass = __CLASS__;
     return PersistentObject :: count();
  }

  public static function get($id)
  {
     self :: $thisClass = __CLASS__;
     return PersistentObject :: get($id);
  }

  public static function findBy(Condition $condition, ArrayObject $params)
  {
     self :: $thisClass = __CLASS__;
     return PersistentObject :: findBy($condition, $params);
  }

  public static function countBy(Condition $condition)
  {
     self :: $thisClass = __CLASS__;
     return PersistentObject :: countBy($condition);
  }
}

?>