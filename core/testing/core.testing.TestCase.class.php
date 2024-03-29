<?php

abstract class TestCase {

   // TestSuite
   private $suite;

   function __construct($suite)
   {
      $this->suite = $suite;
   }
   
   public function assert($cond, $msg = 'Error')
   {
      // TODO: obtener un mensaje que diga mas, linea, clase y
      //       metodo donde se intenta verificar la condicion
      //if (!$cond) $this->suite->report('error');
      
      if (!$cond)
      {
         // http://php.net/manual/en/function.debug-backtrace.php
         
         ob_start(); 
         debug_print_backtrace(); // Stack de llamadas que resultaron en un test que falla
         $trace = ob_get_contents();
         $moreInfo = ob_get_contents(); // Todos los echos y prints que se pudieron hacer
         ob_end_clean(); 

         // Se quita la llamada a este metodo de el stack (assert)
         $pos = strpos($trace, "\n");
         if ($pos !== false)
         {
            $trace = substr($trace, $pos);
         }
         
         // TODO: hay que remover las ultimas lineas que son llamadas del framework
         /*
          * #4  CoreController->testAppAction(Array ()) called at [C:\wamp\www\YuppPHPFramework\core\mvc\core.mvc.YuppController.class.php:59]
#5  YuppController->__call(testApp, Array ())
#6  CoreController->testApp() called at [C:\wamp\www\YuppPHPFramework\core\routing\core.routing.Executer.class.php:163]
#7  Executer->execute() called at [C:\wamp\www\YuppPHPFramework\core\web\core.web.RequestManager.class.php:158]
#8  RequestManager::doRequest() called at [C:\wamp\www\YuppPHPFramework\index.php:94]

          */
         
         $this->suite->report('ERROR', $msg, $trace, $moreInfo);
      }
      else
      {
         // tengo que mostrar los tests correctos
         $this->suite->report('OK', $msg);
      }
   }
   
   // A implementar por las subclases
   public abstract function run();
}
?>