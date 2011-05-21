<?php

YuppLoader::load("biblioteca.model", "Libro");

class Autor extends PersistentObject
{
  function __construct($args = array (), $isSimpleInstance = false)
  {
     $this->setWithTable("biblio_autor");
	 
	 $this->belongsTo = array( 'Libro' );
	 
     $this->addAttribute("nombre", Datatypes :: TEXT);
     $this->addAttribute("fechaNacimiento", Datatypes :: DATE);

	 // Restricciones
     $this->addConstraints("nombre", array(
        Constraint::nullable(false),
        Constraint::blank(false)
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