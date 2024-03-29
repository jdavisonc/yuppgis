<?php
/**
 * Este archivo contiene la definicion de la clase que maneja toda la logica de persistencia en alto nivel.
 * La cual se encarga de comunicarse con las capas de persistencia inferiores (DAL) y generar objetos persistentes con los datos cargados.
 * 
 * Created on 15/12/2007
 * Modified on 13/06/2010
 * 
 * @name core.persistent.PersistentManager.class.php
 * @author Pablo Pazos Gutierrez <pablo.swp@gmail.com>
 * @version v0.9.0
 * @package core.persistent
 * 
 */

YuppLoader::load( "core.db.criteria2", "Condition" );
YuppLoader::load( "core.db.criteria2", "ComplexCondition" );
YuppLoader::load( "core.db.criteria2", "CompareCondition" );
YuppLoader::load( "core.db.criteria2", "BinaryInfixCondition" );
YuppLoader::load( "core.db.criteria2", "UnaryPrefixCondition" );

YuppLoader::load( "core.utils",        "Callback" );
YuppLoader::load( "core.persistent",   "ArtifactHolder" );

YuppLoader::load( "core.persistent",   "MultipleTableInheritanceSupport" );

/*
TODOs GRANDEs

1. Mantener las asociaciones:
   Si salvo un objeto que ya esta guardado deberia:
     - verificar que los objetos asociados, tanto por hasOne o hasMany, siguien ahi o no, si no:
       es hasOne: el id del objeto deberia ponerse en null.
       es hasMany: deberia eliminar las asociaciones en las tablas intermedias. (en lugar de preguntar/actualizar , podria eliminar todo y actualizar todo, hay que ver que es mas costoso en tiempo).

2. PARA SOPORTE DE HERENCIA
   - ES NECESARIO poner nullables los atributos de las clases que no son hijas de PO,
     asi clases hermanas pueden agregarse a la tabla y no saltan restricciones de la
     tabla porque tiene atributos en null.
   - Solucion: todos los atributos menos los inyectados, como id, deleted y class, son nullables,
     ya que si mando un null a un atributo no nulo va a saltar en la validacion de las constraints
     en lugar de dejarlo pasar hasta la validacion de la db.
*/

/**
 * Esta clase implementa toda la logica necesaria para persistir objetos persistentes y 
 * para obtener datos de la base y crear objetos persistentes.
 * @package core.persistent
 * @subpackage classes
 */
class PersistentManager {

   private $po_loader; // POLoader Interface instance
   protected $dal;
   
   const CASCADE_LOAD_ESTRATEGY = 1;
   const LAZY_LOAD_ESTRATEGY    = 2;
   
   
   public function __construct( $load_estragegy, $appName = null)
   {
   	  switch ($load_estragegy)
      {
         case self::LAZY_LOAD_ESTRATEGY:
            YuppLoader::load( "core.persistent", "LazyLoadStrategy" );
            $this->po_loader = new LazyLoadStrategy();
         break;
         case self::CASCADE_LOAD_ESTRATEGY:
            YuppLoader::load( "core.persistent", "CascadeLoadStrategy" );
            $this->po_loader = new CascadeLoadStrategy();
         break;
         default:
            YuppLoader::load( "core.persistent", "LazyLoadStrategy" );
            $this->po_loader = new LazyLoadStrategy();
         break;
      }
      $this->po_loader->setManager( $this ); // Inversion Of Control
      
      if ($appName == null) {
			$ctx = YuppContext::getInstance();
	      	$appName = $ctx->getApp();
	      	if ($ctx->isAnotherApp()) $appName = $ctx->getRealApp();
      }
      
      Logger::getInstance()->pm_log("PM::__construct appName: " . $appName);
      
      $this->init_dal( $appName );
   }
   
   public function init_dal( $appName ) {
   		$this->dal = new DAL($appName); // FIXME: de donde saco el nombre de la app actual???
   }

   /**
    * Se llama para los elementos asociados por hasMany. (independientemente que la relacion sea * o 1 del otro lado)
    * ownerAttr es el atributo de owner que apunta a child.
    * 
    * @param PersistenObject $owner objeto donde se declara la relacion con child, es el lado fuerte de la relacion.
    * @param PersistenObject $child objeto relacionado a owner, es el lado debil de la relacion.
    * @param string $ownerAttr nombre del atributo de owner que mantiene la relacion con child.
    * @param integer ord es el orden de child en el atributo hasMany ownerAttr de owner.
    */
   public function save_assoc( PersistentObject $owner, PersistentObject $child, $ownerAttr, $ord )
   {
      Logger::getInstance()->pm_log("PM::save_assoc " . get_class($owner) . " -> " . get_class($child));

      // Considera la direccion de la relacion del owner con el child.
      // VERIFICAR: el owner de la relacion, como esta ahora, es la parte fuerte declarada o asumida,
      //            pero la relacion podria ser bidireccional y sin restricciones estructurales,
      //            instancias de child pueden tener varios owners sin que estos tengas asociados
      //            a esos childs, o sea, las relaciones instanciadas son l2r.
      //            Como esta ahora al pedir relaciones l2r, como ahora no tiene info en la base
      //            q diga q son asi, se instancia la relacion como bidir, por lo que no queda
      //            el mismo snapshot que fue el que se salvo.

      // En una relacion n-n bidireccional, es necesario verificar si la instancia de esa relacion
      // es tambien bidireccional (si tengo visibilidad para ambos lados desde cada elemento de la relacion).
      
      // Todavia no se si la relacion es bidireccional.
      $relType = ObjectReference::TYPE_ONEDIR;

      // Se que el owner hasMany child, pero no se como es la relacion desde child,
      // puede no haber    => owner ->(*)child y la relacion es de tipo 1
      // puede ser hasOne  => owner (1)<->(*)child tengo que ver si tengo linkeado owner en child, si lo tengo, es de tipo 2.
      // puede ser hasMany => owner (*)<->(*)child, con owner la parte fuerte, tengo que fijarme si child contains al owner, si es asi, es de tipo 2.
      $hoBidirChildAttr = $child->getHasOneAttributeNameByAssocAttribute( get_class($owner), $ownerAttr );
      if ( $hoBidirChildAttr ) // hasOne
      {
         $assocObj = $child->aGet($hoBidirChildAttr);

         // Si hay objeto, si esta cargado, y si coincide el id.
         if ($assocObj && $assocObj !== PersistentObject::NOT_LOADED_ASSOC && $assocObj->getId() === $owner->getId() )
         {
            $relType = ObjectReference::TYPE_BIDIR;
         }
      }
      else // si el atributo no era de hasOne, es hasMany
      {
         $hmBidirChildAttr = $child->getHasManyAttributeNameByAssocAttribute( get_class($owner), $ownerAttr );
         if ( $hmBidirChildAttr && $child->aContains( $hmBidirChildAttr, $owner->getId() ) ) // FIXME: No se como se llama el atributo como para preguntar si child tiene a owner...
         {
            $relType = ObjectReference::TYPE_BIDIR;
         }
      }
      
      // FIXME: si es hasOne, ¿esta bien que ejecute el codigo de abajo checkeando hasMany? Capaz es porque es bidireccional 1-* y lo esta mirando desde el otro lado de la relacion.

      // FIXME: (owner_id, ref_id) debe ser clave, o sea, unique porque primary key es "id". 
      // (en varios lugares como aca abajo y en remove_assoc considero que la relacion entre 2 objetos es unica en la misma tabla.)
      
      // No importa si el id es de la clase declarada en la relacion hasMany
      // o el id de la clase concreta, ahora son todos iguales.
      $ref_id = $child->getId();
      
      // El owner id debe ser el de la clase donde se declara la relacion hasmany
      // De todas formas, los ids de todas las instancias parciales de la clase declarada
      // van a ser los mismos
      $owner_id = $owner->getId();
      
      // ========================================================================
      // VERIFICA DE QUE LA RELACION NO EXISTE YA.
      // FIXME: ojo ahora tendria que tener en cuenta la direccion tambien!

      //Logger::getInstance()->pm_log("PM: owner_id=$owner_id, ref_id=$ref_id " . __LINE__);
      // se pasan instancias... para poder pedir el withtable q se setea en tiempo de ejecucion!!!!
      $tableName = YuppConventions::relTableName( $owner, $ownerAttr, $child );
      $params['where'] = Condition::_AND()
                           ->add( Condition::EQ($tableName, "owner_id", $owner_id ) )
                           ->add( Condition::EQ($tableName, "ref_id",   $ref_id) );

      // FIXME: llamar a exists de DAL
      if ( $this->dal->count($tableName, $params) == 0 )
      {
         //Logger::getInstance()->pm_log("PM::save_assoc No existe la relacion en la tabla intermedia, hago insert en ella. " . __LINE__);
         
         // La asociacion se guarda con insert xq chekea q la relacion no exista para meterlo en la base.
         // TODO: deberia fijarme si los objetos con estos ids ya estan.
         // TODO2: Ademas deberia mantener las relaciones, si se eliminan objetos deberia borrar las relaciones!!!

         $refObj = NULL;
         if ( $owner->getHasManyType($ownerAttr) === PersistentObject::HASMANY_LIST )
         {
            $refObj = new ObjectReference(array("owner_id"=>$owner_id, "ref_id"=>$ref_id, "type"=>$relType, "ord"=>$ord));
         }
         else
         {
            $refObj = new ObjectReference(array("owner_id"=>$owner_id, "ref_id"=>$ref_id, "type"=>$relType));
         }

         $this->dal->insert( $tableName, $refObj );
      }
   } // save_assoc

   /**
    * Salva solo un objeto (sin las asociaciones)
    */
   public function save_object( PersistentObject $obj, $sessId )
   {
      Logger::getInstance()->pm_log("PersistentManager::save_object " . get_class($obj) );
      
      $obj->executeBeforeSave();

      // ===========================================================================================
      // FIX: faltaba validar clases relacionadas
      // http://code.google.com/p/yupp/issues/detail?id=50
      // FIXME: Para la instancia ppal, si pasa el validate de PO.save, viene y
      //        lo ejecuta de nuevo aca. Subir alguna bandera para que no lo haga.
      //if (!$obj->validate()) return false;
      // ===========================================================================================

      $tableName = YuppConventions::tableName( $obj );

      if ( !$obj->getId() ) // || !$dal->exists( $tableName, $obj->getId() ) ) // Si no tiene id, hago insert, si no update.
      {
         // FIXME: PO no se le deberia pasar a DAL, deberia transformarse a datos aqui.
         $this->dal->insert( $tableName, $obj ); // Salva los objetos, con sus datos simples.
      }
      else
      {
         // Nuevo: si se modificaron campos simples o asociaciones hasone hago udate, si no, no.
         if ($obj->isDirty() || $obj->isDirtyOne())
         {
            // El primero es siempre el que corresponde con la superclase de nivel 1
            $pinss = MultipleTableInheritanceSupport::getPartialInstancesToSave( $obj ); 
            foreach ( $pinss as $partialInstance )
            {
               $tableName = YuppConventions::tableName( $partialInstance );
       
               // ========================================================================================
               // Con el nuevo esquema de identificacion, el id del objeto es el mismo que el de todos
               // los objetos parciales de MTI, por lo que no es necesario hacer esto de pedir los ids.
               // Igualmente, como es update, tampoco lo veo necesario, porque la instancia parcial ya
               // tendria el identificador del padre, para que setearselo de nuevo? y tambienm, para que
               // setearle la class de nuevo si ya la tiene?

               // El id de todas las instancias parciales es el mismo.
               $id = $obj->getId();
                
               $partialInstance->setId( $id );
               $partialInstance->setClass( $obj->getClass() ); // En ambos casos tengo que colocar la clase correcta porque getPartialInstancesToSave me devuelve solo las clases que generan tabla... y si tengo C1 me va a devolver C, y la clase se la tengo que setear en C1 aunque se mapee en la misma tabla.
                
               //Logger::struct( $partialInstance, "PARTIAL INSTANCE" );
               //Logger::struct( $this->getDataFromObject($partialInstance), "PARTIAL INSTANCE" );
          
               // 2: Si existe, hace update
               if ( $this->dal->exists( $tableName, $id ) ) // VERIFY: este chekeo se hace en save del PM...
               {
                  $this->dal->update( $tableName, $this->getDataFromObject($partialInstance) );
               }
               else
               {
                  Logger::getInstance()->dal_log("DAL::update NO EXISTE " . $tableName . " " . $id . " " . __LINE__);
               }
            } // foreach ( $pinss as $partialInstance )
         } // si esta dirty
      }

      $obj->setSessId( $sessId );
      
      $obj->executeAfterSave();
      
   } // save_object


   /**
    * Si no esta salvado:
    *   Para cada hasOne:
    *     ...
    *   save_object()
    *   Para cada hasMany:
    *     ...
    * 
    * @return boolean true si no hubo error, false en caso contrario
    */
   public function save_cascade( PersistentObject $obj, $sessId )
   {
      Logger::getInstance()->pm_log("PersistentManager::save_cascade " . get_class($obj) . " SESSIONID: " . $sessId );

      // Para detectar loops en el salvado del modelo
      $obj->setLoopDetectorSessId( $sessId );

      // Si el objeto no fue salvado en la operacion actual...
      if (!$obj->isSaved( $sessId ))
      {
         // Nuevo: solo salva si se ha cambiado un atributo o una relacion hasOne (dirty)
         if ($obj->isDirtyOne())
         {
         	
             //asOne no necesita tablas intermedias (salvar la referencia)
             // Retorna los valores no nulos de hasOne
             $sassoc = $obj->getSimpleAssocValues(); // TODO?: Podria chekear si debe o no salvarse en cascada...
             foreach ( $sassoc as $attrName => $assocObj )
             {
                // ojo el objeto debe estar cargado (se verifica eso)
                if ( $assocObj !== PersistentObject::NOT_LOADED_ASSOC )
                {
                   //echo "=== PO loaded: $attrName<br/>";
                   
                   // Si se detecta un loop en el salvado del modelo,
                   if ( $assocObj->isLoopMarked( $sessId ) )
                   {
                      //Logger::getInstance()->pm_log("LOOP DETECTADO " . get_class($obj) . " " . get_class($assocObj));
    
                      // Agrega al objeto un callback cuando para que se llame cuando termine de llamarse, para salvar el objeto hasOne asociado.
                      // Se salva el objeto actual sin el asociado (assocObj viene a ser instancia de A del modelo A -> B -> C -> A, donde obj viene a ser instancia de C).
                      // Esto deja a obj inconsistente, pero se arregla con el callback cuando termina de salvar a A, se actualiza la referencia de C a A.
    
                      // =============================================================================
                      // Se empezo a salvar desde A, se quiere salvar C que a su vez necesita A.
                      // $assocObj es A.
                      // $obj es C.
    
                      // 1. Actualizar ids de hasOne. // update_simple_assocs
                      $callb_update = new Callback();
                      $callb_update->set( $obj, 'update_simple_assocs', array() );
    
                      // FIXME (posible bug TICKET #4.1): OJO!, este save deberia ser un save simple (no salvar nada en cascada) y hacerce obligatoriamente, sin considerar el id de session...
                      // 2. Salvar el objeto. Llama a save del PO que es el wrapper del PM...
                      $callb_save = new Callback();
                      $callb_save->set( $obj, 'single_save', array() ); // Intento solucion TICKET #4.1
    
                      // Registro los callbacks en A, para que cuando se salve, se actualice C con su id.
                      $assocObj->registerAfterSaveCallback( $callb_update );
                      $assocObj->registerAfterSaveCallback( $callb_save );
    
                      // No se sigue salvando en cascada el objeto asociado xq ya se quiso salvar y se llego
                      // a un loop, se corta el loop y se salvan los objetos con los datos que tienen, y los
                      // datos que no se tienen se salvan en callbacks.
                      // =====================================================================================
                   }
                   else // Si no es un loop en el modelo, salva en cascada como siempre...
                   {
                      if (!$assocObj->isSaved( $sessId ) && $obj->isOwnerOf( $attrName )) // VERIFY:  si el objeto asociado esta salvado, la asociacion tambien ????
                      {                                                              // VERIFY: Salva en cascada solo si soy el duenio de la relacion.. esto esta bien para 1..* ??
                         Logger::getInstance()->pm_log("PM::save_assoc save_cascade de ". $assocObj->getClass() .__LINE__);
                         
                         // hasOne no necesita tablas intermedias (salvar la referencia)
                         // salva objeto y sus asociaciones.
                         $this->save_cascade_owner( $obj, $attrName, $assocObj, $sessId );
                      }
                   }
                } // si esta cargado
                else
                {
                   //echo "=== PO not loaded: $attrName<br/>";
                }
             } // Para cada objeto asociado

             // ------------------------------------------------------------------------------------------------------------------
             // VERIFY: Como y donde se setean los atributos de id de las referencias!!
             // (tendria que hacerse en DAL verificando que el atributo corresponde a una asociacion hasOne)
             //
             // Aca tengo los ids de los hasOne y puedo salvar las referencias desde obj a ellos.
             // FIXME!!!!!: TENGO QUE SALVAR ANTES LOS hasOne para tener sus ids y setear los atributos generados "email_id" ...!!!
             $obj->update_simple_assocs(); // Actualiza los atributos de referencia a objetos de hasOne (como "email_id")
         
         } // si la instancia esta dirty
         
         //Logger::struct( $obj , "PRE PM.save_object en PM.save_cascade");
         
         Logger::getInstance()->pm_log("PM::save_assoc save_object ". $obj->getClass() ." @".__LINE__);
         
         // salva el objeto simple, verificando restricciones en la instancia $obj
         $this->save_object( $obj, $sessId );

         // Si se han modificado los hasMany
         if ($obj->isDirtyMany())
         {
             $massoc = $obj->getManyAssocValues(); // Es una lista de listas de objetos.
             foreach ($massoc as $attrName => $objList)
             {
                $ord = 0;
                
                Logger::getInstance()->pm_log("save_cascade foreach hasManyAssoc: ". $attrName ." ". __FILE__ ." ". __LINE__ );
                
                foreach ( $objList as $assocObj )
                {
                   // Problema con cascada hasMany: a1 -> b1 -> c1 -> a1
                   // cuando c1 quiere salvar a a1 no entra aca, eso esta bien, pero deberia salvarse la relacion c1 -> a1...
                   // No se cual es la condicion para salvar la relacion solo, voy a intentar solo decir que c1 es owner de a1 a ver que pasa...
                   if ( $obj->isOwnerOf( $attrName ) )
                   {
                      Logger::getInstance()->pm_log("PM::save_assoc ". $obj->getClass()." isOwnerOf $attrName. " .__LINE__);
                      
                      // FIXME ?: por que aca no es igual que en las relaciones hasOne?
                      
                      // VERIFY: si el objeto asociado esta salvado, la asociacion tambien ????
                      // VERIFY: Salva en cascada solo si soy el duenio de la relacion.. esto esta bien para 1..* ??
                      if (!$assocObj->isSaved( $sessId )) 
                      {
                         // salva objeto y sus asociaciones.
                         $this->save_cascade( $assocObj, $sessId );
                         Logger::getInstance()->pm_log("PM::save_cascade objeto guardado: ". $assocObj->getClass(). " ". $assocObj->getId(). " " .__LINE__);
                      }
    
                      Logger::getInstance()->pm_log("PM::save_assoc save_assoc de ". $obj->getClass(). " ". $assocObj->getClass(). " " .__LINE__);
                      
                      // Actualiza tabla intermedia.
                      // Necesito tener, si la relacion es bidireccional, el nombre del atributo de assocObj que tiene Many obj, podria haber varios!
                      $this->save_assoc( $obj, $assocObj, $attrName, $ord ); // Se debe salvar aunque a1 este salvado (problema loop hasmany)
                   }
                   else
                   {
                      Logger::getInstance()->pm_log("PM::save_assoc ". $obj->getClass()." !isOwnerOf $attrName. " .__LINE__);
                   }
                    
                   $ord++;
                } // para cada objeto dentro de una relacion hasMany
             } // para cada relacion hasMany
         } // si tiene dirtyMany
      } // if is_saved obj
      
      // Termina de guardar el objeto, limpia los bits de dirty.
      $obj->resetDirty();
      
   } // save_cascade
   
   /**
    * 
    * Redirecciona a save_cascade se puede legar a utilizar cuando el objeto a salvar depende de atributos del que lo tiene.
    * Ejemplo: El nombre ela talbla del objeto $obj es la concatenacion de su nombre de objeto mas el nombre de la tabla de quien
    * 		   lo contiene.
    * @param PersistentObject $owner
    * @param PersistentObject $obj
    * @param unknown_type $sessId
    */
   public function save_cascade_owner( PersistentObject $owner, $attrNameObj, PersistentObject $obj, $sessId ) {
   		$this->save_cascade( $obj, $sessId );
   }

  /**
   * save solo sirve para arrancar la session, la que hace el trabajo de salvar realmente es save_cascade, que salva todo el modelo.
   */
   public function save( PersistentObject $obj )
   {
      Logger::getInstance()->pm_log("PersistentManager::save " . get_class($obj));
      $sessId = time()."_". rand()."_". rand(); // se genera una vez y se mantiene por todo el save. Se agregaron rands porque para saves consecutivos se hacia muy rapido y la sessId quedaba exactamente igual.
      $this->save_cascade( $obj, $sessId );
   }

/*
   // Hace el insert, si no existe, o updatea si existe.
   public static function _save( PersistentObject &$obj )
   {
      Logger::log("PersistentManager::save");

     // FIXME 1: Si tengo asociado 1 objeto persistente, y tengo la instancia cargada (no solo el id),
     // tengo q ver si tengo que persistirla o no, o sea si es en cascada. Si tengo que persistir,
     // hago una cola de objetos a persistir, y cada vez que encuentro uno nuevo lo meto en la cola
     // con su instancia y cuando termino con el objeto actual, vuelvo a ese y repito el procedimiento
     // hasta tener la cola vacia.

     // Salva en cascada los objetos simples relacionados...
     // FIXME: El problema de hacerlo asi es que cuando salvo el objeto asociado se le asigna un id,
     // y ese id no lo puedo salvar en el objeto que lo tiene asociado porque ya lo salve antes.
     // Par resolver est ose deberia hacer lo siguiente:
     // 1. Manejar la estrcutura como stack. Y Salvar el ultimo primero, asi el id generado queda disponible para el padre,
     //    el problema es como darse cuenta quien es el padre en el stack...
     // A -> B -> C,D
     // 1: Salvo D, quiero ponerle el id a B
     // 2: Salvo C, quiero ponerle el id a B
     // 3: Salvo B, quiero ponerle el id a A
     // 4: Salvo A.

     // 1: identifico la relacion mediante la clase padre B, el nombre del atributo "unD_id", y el nomnbre de la clase D. "b_un_d_id_d",
     // esto lo guardo aparte (asociado a esa instancia de D) y antes de salvar D.
     // Cuando salvo D, con su id y la key de la relacion, busco la clase en el stack que
     // tenga esa relacion (el tema es que puede haber otra B con la misma relacion, pero es otra instancia!),
     // lo mejor es pasarle tambien la B o crear un back-ref temporal para poder saber a quien setearle el id.
     //
     // SOL!!!!!!!!
     // O si capaz, hago la recorrida BFS, salvo primero todas las clases directamente asociadas con la actual,
     // pero la hago recursiva y en la vuelta de la recurcion seteo los ids!!!!


     // Este id identifica el momento de la operacion de salvado y se usa para marcar todos los elementos que se salvaron en la misma operacion.
     // Sirve tembien para saber cuales objetos fueron salvados en la operacion actual, de modo de cortar posibles loops de salvado
     // por haber loops en las asociaciones del modelo.
     $sessId = time();


     $dal = DAL::getInstance();
     $objTableName = PersistentManager::tableName( get_class($obj) );

     // ======================
     // TODO: Deberia generar FK para cada elemento asociado!!!!!
     // ======================

      $assocObjectsQueue = array(); // Cola de objetos simples asociados.
      $assocObjectsQueue[] = $obj; // Inicializo con el objeto que quiero guardar.

      // Estructura para saber cuales son los "padres" (origen de la relacion) de cada objeto.
      $assocOwners = array(); // VERIFY: ESTO ALCANZA???

      // Salva cada objeto y los que se tienen asociados
      while ( sizeof($assocObjectsQueue) > 0 )
      {
         $objToSave = array_pop( $assocObjectsQueue );

         //
         //echo "QUEUE: <br>";
         //print_r( $assocObjectsQueue );
         //echo "OBJ TO SAVE: <br>";
         //print_r( $objToSave );
         //

         // Si el objeto no fue salvado en la operacion actual...
         if (!$objToSave->isSaved( $sessId ))
         {
            $tableName = PersistentManager::tableName( get_class($objToSave) );

            if ( !$dal->exists( $tableName, $obj->getId() ) ) // Si no tiene id, hago insert, si no update.
            {
               // Solo se deberian mandar atributos simples!!!!!!!!!!!!!!!!
               $dal->insert( $tableName, $objToSave ); // Salva los objetos, con sus datos simples.
            }
            else
            {
               $dal->update( $tableName, $objToSave );
            }

            // Marco como salvado
            $objToSave->setSessId( $sessId );


            // Encolo demas objetos relacionados...
            $assocObjects = $objToSave->getSimpleAssocValues(); // Podria chekear si debe o no salvarse en cascada...
            // TODO: si es null algun objeto asociado, tengo que poner el atributo en NULL en la base!!!!!

            //echo "XXXXXXXXXXXXX<br>";
            //print_r( $assocObjects );
            //echo "YYYYYYYYYYYYY<br>";

            // TODO: La solucion seria salvar el objeto padre y los hijos en la misma vuelta,
           //       asi poder salvar las referencias, con lo que hay que tener cuidado es
           //       cuando se salvan los hijos tambien se debe hacer en 2 niveles pero no
           //       se deben salvar xq ya se salvaron. Podria hacerse recursivo!
           //
            // Si quiero salvar los ObjectReference aca me hacen falta los ids de los objetos asociados... los cuales deberia salvarlos antes...
            //foreach ( $assocObjects as $aobj )
            //{
            //}

            $assocObjectsQueue = array_merge($assocObjectsQueue, $assocObjects);

            // =======================================================================================================================
            // ESTO ES NECESARIO PARA HACER EL LOAD !!!!!!!!!!!!!!!!!!!
            //
            // TODO: para los objetos asociados por has many tengo que generar tablas intermedias a mano para mantener las relaciones.
            // Deberia poder crear e insertar usando DAL.
            // La idea que tengo es hacer una operacion para crear tablas (ya esta) con el nombre de
            // los 2 objetos concatenados (el padre primero y luego el hijo).
            // Luego, quiero poder hacer insert en esa tabla, de objetos dinamicos, en su representacion de
            // array asociativo, nombreCampo=>valor, y los campos serian los ids del padre y del hijo.
            //
            //$refTableName = NO TENGO EL NOMBRE DE LA CLASE PADRE! CON LA COLA PIERDO LA REFERENCIA AL PADRE!!! ME FALTA ALGUNA ESTRUCTURA...
            //$dal->createTable( $refTableName, new ObjectReference() ); // Esto deberia hacerse en el generate, no aca...

            // =======================================================================================================================
            // TODO: para las relaciones 1..* deberia borrar los objetos asociados actualmente y agregar los objetos con los que viene.
            // 1. Esto se podira hacer borrando todos los asociados actualmente en la base y guardando los que trae.
            // 2. La solucion mas sofisticada es ver que objetos fueron modificados, cuando detecto una modificacion guardo ese objeto.
            //    (se podria usar un atributo "version" o simplemente una bandera de modificado)
            //
            // =======================================================================================================================
            // TODO: Agregarle los objetos del hasMany. Puedo tener varios declarados en hasMany, cada valor es una lista de objetos.
            //
            $manyAssocObjects = $objToSave->getManyAssocValues(); // Es una lista de listas de objetos.

            foreach ($manyAssocObjects as $objList)
            {
               // TODO: La solucion seria salvar el objeto padre y los hijos en la misma vuelta,
               //       asi poder salvar las referencias, con lo que hay que tener cuidado es
               //       cuando se salvan los hijos tambien se debe hacer en 2 niveles pero no
               //       se deben salvar xq ya se salvaron. Podria hacerse recursivo!
               //
               // Si quiero salvar los ObjectReference aca me hacen falta los ids de los objetos asociados... los cuales deberia salvarlos antes...
               //foreach ( $objList as $aobj )
               //{
               //}
               $assocObjectsQueue = array_merge($assocObjectsQueue, $objList);
            }

            // (FIXED) uso el sessId para saber si salve o no, marcando los salvados.
            // FIXME: No puedo caer en problemas de loops, o sea si tengo A->B->C->A que A se salve de nuevo porque
            // lo tiene aosciado C y C se salve porque lo tiene A.
            // Tengo que introducir algun algoritmo que me permita saber que objetos ya fueron salvados y cuales no.
            // (por lo menos un atributo de marca para cada objeto asi voy marcando los salvados)
         }
      }
   } // save
*/


   /**
    * Se utiliza en get_object y en listAll.
    * @param Class $classLoaded subclase de PersistentObject por la que se quiere cargar, por ejemplo se puede cargar por A pero la instancia real es una subclase de A, p.e. G. 
    * @param array $attrValues array asociativo resultante de cargar una fila de una tabla por su id, es exactamente lo que devuelve $dal->get( $tableName, $id ).
    * @return PersistentObject objeto referenciado por los datos, si es MTI devuelve el objeto completo de la clase correcta.
    */
   protected function get_mti_object_byData( $classLoaded, $attrValues )
   {
      Logger::getInstance()->pm_log("PM.get_mti_object_byData: CLASS LOADED: ". $classLoaded ." ". print_r($attrValues, true));

      // $attrValues['id'] es el identificador de todas las instancias parciales de MTI.

      // Nueva instancia de la clase real.
      $cins = new $attrValues["class"](array(), true); // Intancia para hallar nombre de tabla (solo para eso, no se usa luego).
      
      // Si no esta mapeado en la misma (pruebo con cins porque con obj puede no funcionar si es una clase de nivel 1).
      // O sea, si $persistentClass es A o A1 me dice que MTI es false aunque sea una instancia real de C, C1, G o G1.
      if ( MultipleTableInheritanceSupport::isMTISubclassInstance( $cins ) )
      {
         //Logger::getInstance()->pm_log("ES MTI: " . __FILE__ . " " . __LINE__);
         
         // 2.1: Cargar la ultima instancia parcial en la estructura de herencia.
         //$superclases = ModelUtils::getAllAncestorsOf( $attrValues["class"] ); // $attrValues["class"] es la ultima en la estructura del carga de multiple tabla, puede tener subclases pero se guardan en la misma tabla que ella. Por eso necesito solo los padres xq son los que se pueden guardar en otras tablas.
         //$superclases[] = $attrValues["class"];

         // SOLO DEBE HACERSE SI LA CLASE $persistentClass no es la misma que la que dice su atributo "class"...
         // En ese caso, $sc_partial_instance es igual a los attrValues cargados al principio.
         $sc_partial_row = NULL; // Matriz de datos simples
         if ( self::isMappedOnSameTable($attrValues['class'], $classLoaded) )
         {
            $sc_partial_row = $attrValues; // Ya es la ultima instancia, no cargo nada mas.
         }
         else
         {
            // Necesito cargar porque el ultimo registro esta en otra tabla.
            
            // FIXME: esto se puede simplificar sabiendo que todas las instancias parciales de MTI tienen el mismo id.
            $tableName = YuppConventions::tableName( $cins );
            
            // Ahora el id en la clase de nivel 1, y el de la instancia final, es siempre el mismo.
            // Por eso, pido directo por id
            $sc_partial_row = $this->dal->get($tableName, $attrValues['id']);
         }
         
         // MERGE DE LA INSTANCIA CARGADA CON $sc_partial_instance
         //
         // AHORA DEBERIA VER, con esta instancia cargada, si falta cargar otra instancia (aparte de la primera que cargue y esta).
         // Si hay, hago un bucle cargando y mergeando.
         // 
         // PARA MERGE, USAR: MTI::mergePartialInstances( $po_ins1, $po_ins2 )
          
         // VERIFY: capaz hacer merge en cada cargada es poco performante, hay que tomar tiempos y considerar otras alternativas.
         $attrValues = array_merge( $attrValues, $sc_partial_row ); // AttrValues va recolectando los atributos, en este caso el id de $sc_partial_instance esta bien que sobreescriba el id de la otra instancia parcial xq importa que quede el id de la ultima clase de la estructura de herencia.
          

         // Obtiene las instancias parciales para todas las superclases
         $superclasses = ModelUtils::getAllAncestorsOf($attrValues['class']);
         foreach ($superclasses as $mtiClass)
         {
            // Solo quiero las superclases que no se hayan cargado, $persistentClass es la primera que se carga.
            if ($mtiClass !== $classLoaded && $mtiClass !== 'Observable')
            {
               $tableName = YuppConventions::tableName( $mtiClass );
               $scAttrValues = $this->dal->get( $tableName, $attrValues['id'] ); // Se usa el mismo id para todas las instancias parciales
               $attrValues = array_merge( $attrValues, $scAttrValues );
            }
         }
   
         // $attrValues deberia tener todos los atributos simples de las instancias parciales cargadas.
          
      } // if instancia parcial

      // Soporte para herencia. (TODO: necesito mas que esto para multiples tablas)
      $realClass = $attrValues['class'];
      
      return $this->createObjectFromData( $realClass, $attrValues );
   
   } // get_mti_object_byData

   // Trae un objeto simple sin asociaciones hasMany y solo los ids de hasOne.
   public function get_object( $persistentClass, $id )
   {
      Logger::getInstance()->pm_log("PM.get_object " . $persistentClass . " " . $id);

      // Si llega aqui es porque ya se verifico que no estaba en ArtifactHolder.

      // 1: Cargar la instancia que me piden.

      //$dal = DAL::getInstance();
      $obj = new $persistentClass(array(), true); // Intancia para hallar nombre de tabla (solo para eso, no se usa luego).
      $tableName = YuppConventions::tableName( $obj );

      // HERENCIA EN MULTIPLE TABLA
      // Cargo el registro de la clase que me mandan por su id, esto es para verificar si la clase que me mandan 
      // es realmente la clase de la instancia que me piden. Si $persistentClass no esta mapeada en la misma 
      // tabla que el atributo "class" del registro, cargo el registro
      // de la clase que diga la columna "class", ya que ese registro es el que tiene todos los ids inyectados por
      // MTI y es la que me deja cargar todos los registros de instancias parciales para luego unirlos y generar
      // una unica instanca, que es la que me piden.
      $attrValues = $this->dal->get( $tableName, $id );
      
/*
 * VER: Otra posible solucion para mti, es que cargue solo los atributos que tengo en esa tabla, 
 * y luego cargue lo demas lazy, o sea: 
 * si a PO le pido un getXX y me doy cuenta que XX no lo tengo (porque pude no haberlo cargado) 
 * verifico si esta en otra tabla de una instancia parcial y ahi cargo la instancia parcial. 
 * (o sea, lazy load para atributos simples)
 */
 
      // 2: Verificar si es una instancia parcial y cargar las demas instancias parciales, mergear, y generar la instancia final.
      
      return $this->get_mti_object_byData( $persistentClass, $attrValues );

   } // get_object
   
   /**
    * FIXME: $class viene en data['class'].
    * Crea una instancia del objeto a partir de informacion dada por DAL.
    */
   protected function createObjectFromData( $class, $data )
   {
      Logger::getInstance()->pm_log("PersistentManager.createObjectFromData " . $class );
      
      // $data son $attrValues.
      
      $obj = new $class(); // Instancia a devolver, instanciado en la clase correcta.

      // Carga atributos simples
      foreach ($data as $colname => $value)
      {
         // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         // ACA ESTA EL PROBLEMA AL CARGAR QUE DICE QUE NO hasAttribute para normalizedName...
         
// FIX rapido porque hasAttribute no busca en los atributos ocn nombre normalizado como columna.
// En todos los lugares donde pregunte por hasAttribute puede haber el mismo problema.
// Tengo un problema cuando la clase no tiene el atributo declarado en ella pero si esta declarado en la 
// superclase... me tira que no existe el atributo.
//       if ($obj->hasAttribute($attr)) // Setea solo si es un atributo de el.
//       {
         // Obtiene el nombre del atributo para setearlo, si es NULL la clase no tiene ese atributo.
         
         $attr = $obj->getAttributeByColumn( $colname );
         if ( !is_null($attr) )
         {
            // TODO: Ver como se cargan los NULLs, por ahora se setean... como debe ser?
            
            // Deshace el addslashes del inser_query y update_query de DAL.
            // FIXME: esto deberia ser tambien responsabilidad de DAL.
            if ( is_string( $value ) ) $value = stripslashes($value);
            
            $obj->aSet( $attr, $value );
         }
      }
      
      // Apaga las banderas que se prendieron en la carga
      $obj->resetDirty();

      return $obj;
   }
   
   /**
    * Operacion inversa a createObjectFromData, sirve para extraer los datos para de mandarselos a DAL.
    */
   private function getDataFromObject( $obj )
   {
      Logger::getInstance()->pm_log("PersistentManager.getDataFromObject");
      
      $data = array();
      $attrs = $obj->getAttributeTypes();
      foreach ( $attrs as $attr => $type )
      {
         $data[$attr] = $obj->aGet( $attr );
      }
      
      return $data;
   }

   /**
    * Obtiene solo una asociacion.
    */
   public function get_many_assoc_lazy( PersistentObject $obj, $hmattr )
   {
      Logger::getInstance()->pm_log("PersistentManager.get_many_assoc_lazy " . get_class( $obj ) . " " . $hmattr);

      // TODO: tengo que cargar solo si tiene deleted en false en la tabla de join.

      // FIXME: esta clase podria ser superclase de la subclase que quiero cargar.
      //        tengo que ver en la tabla de que tipo es realmente y cargar una instancia de eso. 
      $hmattrClazz = $obj->getType( $hmattr );
            
      // (***)
      $_obj = new $hmattrClazz(); // Intancia para hallar nombre de tabla.
      $relObjTableName = YuppConventions::tableName( $_obj );

      // FIXME: el problema de hacer el fetch con una consulta es que no puedo saver
      // si el/los objetos ya estan cargados en el ArtifactHolder, no se si esto
      // sea un problema... tal vez si lo cargo aunque ya este cargado lo unico que
      // hago es agregarlo de nuevo en el ArtifactHolder y lsito... hay que ver.

      // Por cada atributo tengo una lista de objetos de ese tipo para traer.
      // TODO: ver quien es el duenio de la relacion!
      // VERIFY!!!: Como la relacion existe, si uno no es el duenio, DEBE ser el otro.
      //
      $relTableName = "";
      $obj_is_owner = false;
      if( $obj->isOwnerOf( $hmattr ) )
      {
         $relTableName = YuppConventions::relTableName( $obj, $hmattr, new $hmattrClazz() );
         $obj_is_owner = true;
      }
      else
      {
         // Si no soy owner tengo que pedir el atributo...
         $ownerInstance = new $hmattrClazz();
         $ownerAttrNameOfSameAssoc = $ownerInstance->getHasManyAttributeNameByAssocAttribute( get_class($obj), $hmattr );
         $relTableName = YuppConventions::relTableName( $ownerInstance, $ownerAttrNameOfSameAssoc, $obj );
      }

      // =================================================================================
      // (***)
      // FIXME: deberia hacer un join con la tabla de referencia y la 
      //        tabla destino para traer todos los atributos y no tener 
      //        que hacer consultas individuales para cargar cada objeto.
      //
      // =================================================================================

      // =================================================================================
      // QIERO PEDIR SOLO LOS ELEMENTOS DE ObjectReference, para poder recorrerlo y ver si ya tengo objetos cargados,
      // y cargo solo los que no estan cargados. Seteo todos los objetos al atributo hasMany del objeto.

      YuppLoader::load( "core.db.criteria2", "Query" );
      $q = new Query();
      $q->addFrom( $relTableName, 'ref' );  // person_phone ref // FIXME: ESTO ES addFrom.
      $q->addFrom( $relObjTableName, 'obj' );
      
      
      // FIXME: quiero todos los atributos...
      // Se agregan los atributos de la clase como proyeccion de la query.
      // Solo quiero los atributos de OBJ, agrego sus atributos como proyecciones de la consulta.
      /* esto seleccionaba solo los atributos declarados en la clase.
      foreach( $_obj->getAttributeTypes() as $attr => $type )
      {
         //$q->addProjection("obj", $attr);
         // TODO: normalizar en la query mismo
         $q->addProjection( "obj", DatabaseNormalization::col($attr) );
      }
      */
      $q->addProjection( 'obj', '*' ); // Todos los atributos de la tabla con alias "obj".
      
      // Necesito saber el nombre del atributo de los ids asociados.
      $hm_assoc_attr = "owner_id"; // FIXME: poner el string en una clase de convensiones de yupp

      // Los ids de todas las instnacias parciales de la clase declarada en el atributo
      // hasMany, van a ser todos iguales, por eso uso el id del objeto que viene.
      $obj_id = $obj->getId();

      // Tengo que ver el objeto en la tabla de referehcia si es el owner_id o el ref_id
      if ( $obj_is_owner )
      {
          // FIXME: poner el string en una clase de convensiones de yupp
         $hm_assoc_attr = 'ref_id'; // yo soy el owner entonces el asociado es ref.
         $q->setCondition(
           Condition::_AND()
             ->add( Condition::EQ('ref', 'owner_id', $obj_id) ) // ref.owner_id = el id del duenio (person_phone.owner_id = obj->getId)
             ->add( Condition::EQA('obj', 'id', 'ref', 'ref_id') ) // JOIN
         );
      }
      else // Aca obj es ref_id y class es owner_id !!! (soy el lado debil)
      {
         $q->setCondition(
            Condition::_AND()
              ->add( Condition::EQ('ref', 'ref_id', $obj_id) ) // ref.owner_id = el id del duenio (person_phone.ref_id = obj->getId)
              ->add( Condition::EQ('ref', 'type', ObjectReference::TYPE_BIDIR) ) // type = bidir
              ->add( Condition::EQA('obj', 'id', 'ref', 'owner_id') ) // JOIN
         );
      }
      
      // ==========================================================================
      // Desde v0.1.6: soporte para tipos de hasMany
      // Si es de tipo lista, debe donsiderar el orden.
      
      if ( $obj->getHasManyType($hmattr) === PersistentObject::HASMANY_LIST )
      {
         $q->addOrder("ref", "ord", "ASC"); // Orden ascendente por atributo ORD de la tabla intermedia.
      }
      
      Logger::getInstance()->pm_log("PersistentManager.get_many_assoc_lazy query ". __FILE__ ." ". __LINE__);
      
      // Trae todos los objetos linkeados... (solo sus atributos simples)
      $data = $this->dal->query( $q );

      // FIN QUERY...
      
      $wasDirty = $obj->isDirtyMany();

      // Ojo, se prenden bits de dirty (es necesario detectar si no estaba dirty antes, para saber si puede limpiar).
      $obj->aSet( $hmattr, array() ); // Inicalizo lista xq seguramente estaba en NOT_LOADED.

      foreach ( $data as $many_attrValues ) // $many_attrValues es un array asociativo de atributo/valor (que son los atributos simples de una instancia de la clase)
      {
         /* Esta cargado?
          * $rel_obj_id = $many_attrValues[ $hm_assoc_attr ]; // El codigo que usa esta linea esta comentado...
          * if ( ArtifactHolder::getInstance()->existsModel( $hmattrClazz, $rel_obj_id ) )
          * {
          *    $rel_obj = ArtifactHolder::getInstance()->getModel( $hmattrClazz, $rel_obj_id );
          * }
          * else
          * {
          *    $rel_obj = $this->get_object( $hmattrClazz, $rel_obj_id ); // Carga solo el objeto, sin asociaciones.
          *    ArtifactHolder::getInstance()->addModel( $rel_obj ); // FIXME: ArtHolder deberia referenciarse solo del PM!!!!!
          * }
          */
         
         // Esto soluciona la carga de autorrelacion desde una subclase.
         // B(heredaDe)A y A(hasMany)A, y quiero cargar B que a su vez tiene asociados varios Bs.
         
         // FIXME: esta clase podria ser superclase de la subclase que quiero cargar.
         //        tengo que ver en la tabla de que tipo es realmente y cargar una instancia de eso. 
         // (***)
         //$rel_obj = $this->createObjectFromData( $hmattrClazz, $many_attrValues );
         
         if ( $many_attrValues['class']===$hmattrClazz )
         {
            // FIXME: si el rel_obj tiene hasOne, y hereda de otra clase, no se cargan los hasOne.
            //Logger::getInstance()->pm_log("Caso1: $hmattrClazz ". __FILE__ ." ". __LINE__);
            //Logger::struct($many_attrValues, "many_attrValues");
            
            //echo "   la clase es la misma que la declarada<br/>";
            $rel_obj = $this->createObjectFromData( $hmattrClazz, $many_attrValues );
         }
         else
         {
            //Logger::getInstance()->pm_log("Caso2: [$hmattrClazz / ". $many_attrValues['class'] ."] " . __FILE__ ." ". __LINE__);
            //Logger::struct($many_attrValues, "many_attrValues");
            
            //echo "   la clase NO es la misma que la declarada<br/>";
            // TODO: deberia cargar los atributos declarados en la clase $many_attrValues['class'], que estan en otra tabla que la que acabo de cargar.
            //       por ejemplo el id cargado es el de una superclase no el de la clase que deberia ser la instancia.
            $rel_obj = $this->get_mti_object_byData( $hmattrClazz, $many_attrValues );
         }

         $obj->aAddTo( $hmattr, $rel_obj );
      }
      
      if (!$wasDirty) $obj->resetDirtyMany();

   } // get_many_assocs_lazy

// =========================

   /*
    * Get con soporte para herencia:
    * A <- B <- C1, C2 estructura de herencia.
    * - pido atributos de una fila por id
    * - creo instancia de la clase y seteo atributos
    *   - si pido por ejemplo, por la clase B, deberia pedir tambien atributos de C1 y C2, y
    *     deberia instanciar cada clase con su clase real para que no me haga problemas al
    *     setear los atributos, ahi deberia funcionar bien y si hay errores es porque estoy
    *     seteando un atributo en una clase que no lo tiene...
    *
    * tons:
    * - pido fila
    * - veo clase de la fila
    *   - si es la misma clase por la que estoy pidiendo
    *     - hago instancia de esa clase (de la fila) y cargo como siempre
    *   - si no (puede ser herencia)
    *     - Verifico que la fila es de una subclase de la clase por la que pido (si no es asi es un error enorme!!! xq hizo save de algo mal)
    *     - Hago instancia de la clase de la fila y cargo.
    *
    */
   // Hace select por el id y devuelve null si no encuentra.
   public function get( $persistentClass, $id )
   {
      Logger::getInstance()->pm_log("PM.get " . $persistentClass . " " . $id);

      //////////////////////////////////////////////////////////
      //
      // 1. eager: traigo todo el modelo.
      //           cargo el objeto
      //           cargo sus clases asociadas hasOne
      //           cargo sus clases asociadas hasMany
      //
      //              para cada objeto asociado hasOne
      //                 cargo sus clases asociadas hasOne
      //                 cargo sus clases asociadas hasMany
      //
      //                     ...
      //
      //              para cada objeto asociado hasMany
      //                 ...
      //                 ...
      //
      //////////////////////////////////////////////////////////

      $obj = NULL;

      if ( ArtifactHolder::getInstance()->existsModel( $persistentClass, $id ) ) // Si ya esta cargado...
      {
         $obj = ArtifactHolder::getInstance()->getModel( $persistentClass, $id );
      }
      else
      {
         // Define la estrategia con la que se cargan los objetos...
         $obj = $this->po_loader->get($persistentClass, $id); // Llama primero a get_objects.
         
         // Ya se hace reset en createObjectFromData, el metodo que se usa para crear el objeto en la carga.
         // FIXME: solo si la clase estaba limpia antes de la operacion
         //$obj->resetDirty(); // Apaga las banderas que se prendieron en la carga

         ArtifactHolder::getInstance()->addModel( $obj ); // Lo pongo aca para que no se guarde luego de la recursion de las assocs...
      }
      
      return $obj;
   }

   
   /**
    * Fixme, deberia recibir solo la clase, no una instancia.
    * @return Devuelve todos los elementos de la clase dada, que no estan aliminados,
    *         segun los criterios de paginacion y ordenamiento dados.
    */
   // Si max == -1 traigo todos los items.
   public function listAll( $ins, ArrayObject $params )
   {
      Logger::getInstance()->pm_log("PM::listAll ". $ins->getClass() ." : " . __FILE__."@". __LINE__);

      $objTableName = YuppConventions::tableName( $ins ); // ins se usa solo para sacar el nombre de la tabla y para sacar los nombres de las subclases.

      // Quiero solo los registros de las subclases y ella misma.
      $class = get_class( $ins );
      $scs = ModelUtils::getAllSubclassesOf( $class );
      $scs[] = $class;

      // Definicion de la condicion.
      $cond = Condition::_AND(); // Para hacer and con la condicion de deleted false y la de herencia

      // Condicion para soportar carga de herencia.
      if ( count($scs) == 1 )
      {
         $cond->add( Condition::EQ($objTableName, "class", $scs[0]) );
      }
      else
      {
          $cond_or = Condition::_OR();
          foreach ($scs as $subclass)
          {
              $cond_or->add( Condition::EQ($objTableName, "class", $subclass) );
          }
          $cond->add( $cond_or );
      }

      // OBS: No devuelve elementos eliminados (deleted=false)
      // FIXME: deberia tener un parametro "loadDeletedTo" que indique si quiere
      // cargar las instancias eliminadas de forma logica, pueden haber aplicaciones
      // que les interese acceder a las instancias eliminadas de forma logica.
      //
      // FIXME: Si le pongo false a la RV no aparece nada y me tira consulta erronea.
      // Tendria que ponerle un convertidor de true/false a 1/0...
      $cond->add( Condition::EQ($objTableName, "deleted", 0) ); // FIXME: en postgres boolean se verifica contra '0' no contra 0.

      $params['where'] = $cond;

      $allAttrValues = $this->dal->listAll( $objTableName, $params );
      // FIXME: Ahora me devuelve todos las columnas, necesito solo ID y CLASS, luego con eso pido todo lo demas
      // usando otras funciones que ya tengo, sobre todo para respetar la estrategia de carga.
      // Pero tambien es cierto que de esta forma ya se carga todos los atributos simples de una y luego podria ver
      // segun la estrategia si cargo las asociaciones con otras clases, para esto el proceso de carga deberia estar
      // mas estandarizado, con funciones que me acepten por ejemplo un mapa con los atributos simples y tenga el loop
      // que aca aparece seteandolos a determinada clase (eso esta en get_object tambien), hay que ver, por ahora lo dejo
      // sin esa posible optimizacion, que se que funciona. (PERO AHORA TRAR LOS ATRIBUTOS 2 VECES xq el dal->listAll los trae tambien).


      // =============================== ==========================================
      // Ahora esta cargando en cascada... deberia cargar segun LoadEstrategy... ??? ESTO SIGUE SIENDO ASI???

      // FIXME: esto no soporta MTI, deberia hacerse como se hace en get_object, preguntar 
      //        si es MTI, si es, ver si es la ultima instancia, si no es, cargar la ultima 
      //        intancia, y luego cargar todas las otras intancias parciales, para por 
      //        ultimo armar el objeto completo. (*)

      $res = array(); // Lista de objetos
      foreach ($allAttrValues as $row)
      {
         $persistentClass = $row['class']; // soporte de herencia!!!!

         // Carga considerando estrategia... y se fija en el holder si ese objeto no esta ya cargado.

         //  (*) Soporte para MTI como en get_object (incluye a createObjectFromData).
         $obj = $this->get_mti_object_byData( $class, $row );
         
         /* Ya cargo toda la informacion, no necesito consultar de nuevo, uso createObjectFromData.
         if ( ArtifactHolder::getInstance()->existsModel( $persistentClass, $row['id'] ) ) // Si ya esta cargado...
         {
            $obj = ArtifactHolder::getInstance()->getModel( $persistentClass, $row['id'] );
         }
         else
         {
            $obj = $this->po_loader->get($persistentClass, $row['id']); // Define la estrategia con la que se cargan los objetos...
            ArtifactHolder::getInstance()->addModel( $obj ); // Lo pongo aca para que no se guarde luego de la recursion de las assocs...
         }
         */
         
         // Nuevo, proceso la informacion que ya traje en lugar de hacer otra consulta.
         //$obj = $this->createObjectFromData( $persistentClass, $row );

         $res[] = $obj;

         //$this->get_simple_assocs( $obj ); // OK
         //$this->get_many_assocs( $obj ); // TODO: falta crear los objetos y linkearlos
      }

      return $res;

   } // listAll


   /**
    * Hace una consulta y devuelve las filas correspondientes a los registros que matchean el criterio.
    * Es una lista de esos registros, por eso es una matriz.
    */
   private function findByAttributeMatrix( PersistentObject $instance, Condition $condition, ArrayObject $params )
   {
      Logger::getInstance()->pm_log("PM::findByAttributeMatrix ". $instance->getClass() ." : " . __FILE__."@". __LINE__);

      // FIXME: misma logica que en listAll, reutilizar codigo.      
      $tableName = YuppConventions::tableName( $instance );

      // Quiero solo los registros de las subclases y ella misma.
      $class = get_class( $instance );
      $scs = ModelUtils::getAllSubclassesOf( $class );
      $scs[] = $class;

      // La condicion total es la que me pasan AND CONDICION_DE_NOMBRES_DE_SUBCLASES AND NO_ELIMINADO

      // Definicion de la condicion.
      $cond_total = Condition::_AND();

      // CONDICION_DE_NOMBRES_DE_SUBCLASES
      if ( count($scs) == 1 )
      {
         $cond_total->add( Condition::EQ($tableName, "class", $scs[0]) );
      }
      else
      {
         $cond_or = Condition::_OR();
         foreach ($scs as $subclass)
         {
            $cond_or->add( Condition::EQ($tableName, "class", $subclass) );
         }
         $cond_total->add( $cond_or );
      }

      // =====================================================================================
      // NO_ELIMINADO
      // FIXED: Si viene una condicion deleted, no agregarla. p.e. puedo pedir deleted = true
      //if ($condition->hasCondForAttr('deleted')) echo 'tiene para deleted';
      //else echo 'no tiene para deleted';
      //
      if (!$condition->hasCondForAttr('deleted'))
         $cond_total->add( Condition::EQ($tableName, 'deleted', 0) );

      // CRITERIO DE BUSQUEDA
      $cond_total->add( $condition );

      $params['where'] = $cond_total;

      $allAttrValues = $this->dal->listAll( $tableName, $params ); // FIXME: AHORA TIRA TODOS LOS ATRIBUTOS Y NECESITO SOLO CLASS e ID.
      
      return $allAttrValues;
      
   } // findByAttributeMatrix


   /**
    * Devuelve una lista de PO correspondientes a la consulta realizada.
    */
   public function findBy( PersistentObject $instance, Condition $condition, ArrayObject $params )
   {
      Logger::getInstance()->pm_log("PM::findBy ". $instance->getClass() ." : " . __FILE__."@". __LINE__);
      
      // Consulta para saber la clase real (subclase concreta) del objeto que se está pidiendo
      $allAttrValues = $this->findByAttributeMatrix( $instance, $condition, $params ); //$dal->listAll( $tableName, $params ); // FIXME: AHORA TIRA TODOS LOS ATRIBUTOS Y NECESITO SOLO CLASS e ID.

      $res = array(); // Lista de objetos
      foreach ($allAttrValues as $row)
      {
         // FIXED: http://code.google.com/p/yupp/issues/detail?id=110
         // Si la clase real (row[class]) es distinta a la clase por la que busco ($instance->getClass()),
         // la clase por la que busco será una superclase de la real.
         
         // Carga considerando estrategia... y se fija en el holder si ese objeto no esta ya cargado.
         $obj = NULL;
         if ( ArtifactHolder::getInstance()->existsModel( $row['class'], $row['id'] ) ) // Si ya esta cargado...
         {
            $obj = ArtifactHolder::getInstance()->getModel( $row['class'], $row['id'] );
         }
         else
         {
            $obj = $this->po_loader->get($row['class'], $row['id']); // Define la estrategia con la que se cargan los objetos...
            ArtifactHolder::getInstance()->addModel( $obj ); // Lo pongo aca para que no se guarde luego de la recursion de las assocs...
         }

         $res[] = $obj;
      }

      return $res;
      
   } // findBy
   

   /**
    * 
    */
   public function findByQuery( Query $q )
   {
      return $this->dal->query( $q );
   }


   // FIXME: El mundo seria mas sencillo si en lugar de pasarle la clase le paso la instancia...
   // ya que tengo que hacer un get_class para pasarle la clase y luego aca hago un new para crear una instancia...
   // para eso le paso la instancia que ya tengo y listo...
   public function exists( $persistentClass, $id )
   {
      return $this->dal->exists( YuppConventions::tableName( new $persistentClass() ), $id );
   }

   /**
    * Cuenta las instancias de una clase, sin contar las instancias eliminadas.
    */
   public function count( $ins )
   {
      /*
      $objTableName = YuppConventions::tableName( $ins );
      $params = array();

      // Quiero solo los registros de las subclases y ella misma.
      $class = get_class( $ins );
      $scs = ModelUtils::getAllSubclassesOf( $class );
      $scs[] = $class;

      // Definicion de la condicion.
      $cond = Condition::_AND();
      if ( count($scs) == 1 )
      {
         $cond->add( Condition::EQ($objTableName, "class", $scs[0]) );
      }
      else
      {
          $cond_a = Condition::_OR();
          foreach ($scs as $subclass)
          {
              $cond_a->add( Condition::EQ($objTableName, "class", $subclass) );
          }
          $cond->add( $cond_a );
      }
      
      $cond->add( Condition::EQ($objTableName, "deleted", 0) );
      $params['where'] = $cond;

      return $this->dal->count( $objTableName, $params );
      */
      return $this->countBy($ins, NULL);
   }


   /**
    * FIXME: pasarle la clase, no una instancia.
    */
   public function countBy( $ins, $condition )
   {
      $objTableName = YuppConventions::tableName( $ins );
      $params = array();

      // Quiero solo los registros de las subclases y ella misma.
      $class = get_class( $ins );
      $scs = ModelUtils::getAllSubclassesOf( $class );
      $scs[] = $class;

      // Definicion de la condicion.
      $cond_total = Condition::_AND();
      if ( count($scs) == 1 )
      {
         $cond_total->add( Condition::EQ($objTableName, "class", $scs[0]) );
      }
      else
      {
         $cond = Condition::_OR();
         foreach ($scs as $subclass)
         {
            $cond->add( Condition::EQ($objTableName, "class", $subclass) );
         }
         $cond_total->add( $cond );
      }
      
      // FIXED: igual que en findByAttributeMatrix usada por findBy
      // Si no tiene condicion deleted, le pone deleted false.
      if ( $condition == NULL || !$condition->hasCondForAttr('deleted'))
         $cond_total->add( Condition::EQ($objTableName, "deleted", 0) );

      // CRITERIO DE BUSQUEDA
      if ($condition != NULL) $cond_total->add( $condition );

      $params['where'] = $cond_total;

      return $this->dal->count( $objTableName, $params );
      
   } // countBy



   // Elimina un objeto de la base de datos.
   // Logical indica si la eliminacion es solo logica o es fisica.
   // FIXME: no es necesario pasar el id, lo tiene la instancia adentro.
   //public function delete( &$persistentInstance, $id, $logical )
   public function delete( $persistentInstance, $id, $logical )
   {
      Logger::add( Logger::LEVEL_PM, "PM::delete ". __FILE__."@". __LINE__ );
      
      // TODO: setear deleted a la instancia si se pudo hacer el delete en la tabla!
      /*
      TODO: Que pasa si una instancia tiene belongsTo esta instancia, pero tambien tiene belongsTo
            otra instancia de otra cosa? Lo mas logico seria no eliminarla. ???
      */
      // TODO: Esto borra solo un objeto, falta ver el tema de los objetos asociados y el borrado en cascada...
      /*
         TODO: Si es MTI se que se va a llamar varias veces seguidas a DAL.delete, porque 
               no dejar que las consultas se acumulen en un buffer (string) en DAL y luego
               se ejecuten todas juntas, es mas, podria rodear con BEGIN y COMMIT para 
               hacerla transaccional.
      */
      
      // Se asume que la instancia ya es la ultima porque esta cargada con "get" 
      // o con "listAll" que garantiza que carga la instancia completa.
      
      // Borra el registro de la clase actual (no estaba incluida en los ancestors si es MTI)
      // Si no es MTI, este es el lunico delete que se hace
      $this->dal->delete( $persistentInstance->getClass(), $id, $logical );
      
      // Soporte MTI
      if (MultipleTableInheritanceSupport::isMTISubclassInstance( $persistentInstance ))
      {
         // Ahora tengo que pedir las superclases y para cada una, borrar la instancia parcial
         $superclasses = ModelUtils::getAllAncestorsOf($persistentInstance->getClass());
         foreach ($superclasses as $mtiClass)
         {
            $this->dal->delete( $mtiClass, $id, $logical ); // Todas las instancias parciales tienen el mismo id 
         }
      }
   } // delete

   // Nombre de la tabla que modela una relacion.
   //public function relTableName( $ownerClassName, $childClassName )
   /**
    * Nombre de la tabla de relaciones entre 2 clases, considerando el nombre del atributo de un lado de la relacion.
    * @param PersistentObject $ins1 Lado fuerte de la relacion entre $ins1 e $ins2
    * @param string $inst1Attr atributo de $ins1 que apunta a $ins2
    * @param PersistentObject $ins2 Lado debil de la relacion.
    *
    */
   // FIXME: T#32 esta funcion deberia ir en la clase que implementa las convenciones.
   /*
   public function relTableName( $ins1, $inst1Attr, $ins2 )
   {
      if ( $ins1->getWithTable() != NULL && strcmp($ins1->getWithTable(), "") != 0 ) // Me aseguro que haya algo.
      {
         $tableName1 = $ins1->getWithTable();
      }

      if ( $ins2->getWithTable() != NULL && strcmp($ins2->getWithTable(), "") != 0 ) // Me aseguro que haya algo.
      {
         $tableName2 = $ins2->getWithTable();
      }

      $tableName1 = DatabaseNormalization::table( $tableName1 );
      $tableName2 = DatabaseNormalization::table( $tableName2 );

      // TODO: Normalizar $inst1Attr ?

      return $tableName1 . "_" . $inst1Attr . "_" . $tableName2; // owner_child
   }
   */

   /**
    * generate
    * Genera la tabla para una clase y todas las tablas intermedias 
    * para sus relaciones hasMany de la que son suyas.
    * 
    * Si dalForApp es NULL se usa this->dal, de lo contrario se usa esa DAL.
    */
   protected function generate( $ins, $dalForApp = NULL )
   {
      Logger::getInstance()->pm_log("PersistentManager::generate");
      
      // La DAL que se va a usar
      $dal = $this->dal;
      if ($dalForApp !== NULL) $dal = $dalForApp;
      
      // TODO: Si la tabla existe deberia hacer un respaldo y borrarla y generarla de nuevo.
      //DROP TABLE IF EXISTS `acceso`;

      // Si la clase tiene un nombre de tabla, uso ese, si no el nombre de la clase.
      $tableName = YuppConventions::tableName( $ins );
      
      // Ya se sabe que id es el identificador de la tabla, es un atributo inyectado por PO.
      $pks = array (
               array (
                 'name'    => 'id',
                 'type'    => Datatypes :: INT_NUMBER,
                 'default' => 1
               )
             );
      
      /* EJEMPLO de la estructura que se debe crear.
      $cols = array(
                     array('name'     => 'name',
                           'type'     => Datatypes :: TEXT,
                           'nullable' => false),
                     // FK
                     array('name'     => 'ent_id',
                           'type'     => Datatypes :: INT_NUMBER,
                           'nullable' => true)
                   );
      */
      
      // =====================================================================================================
//      $nullable = NULL; // Hay que determinar si el atributo es nullable.
      
      // Si es una clase de nivel 2 o superior y esta mapeado en la misma tabla que su superclase, 
      // todos sus atributos (declarados en ella) deben ser nullables.
      // TODO: ahora no tengo una funcionalidad que me diga que atributos estan declarados en que
      // clase, por ahora le pongo que todos sus atributos sean nullables.
      
      // =====================================================================================================
      // FIXME: no sirve chekear por la clase porque la instancia que me pasan es un merge de todas las 
      // subclases que se mapean en la misma tabla, asi que puede ser que parent_class sea POe igual 
      // tenga que declarar nullables.
      
      // >> Solucion rapida <<, para los atributos de las subclases, en generateAll inyectarles
      //                         contraints nullables true.
      
      // Son iguales, no se sobreescribe el valor de "class" por el de la instancia real porque no interesa, 
      // solo son instancias de merges de POs para una tabla.
      //Logger::getInstance()->log( "getClass: " . $ins->getClass() );
      //Logger::getInstance()->log( "GET_CLASS: " . get_class($ins) );
      
//      if ( get_parent_class($ins) != PersistentObject && 
//           self::isMappedOnSameTable($ins->getClass(), get_parent_class($ins)) )
//      {
//         $nullable = true;
//      }
      // =====================================================================================================
      
      $cols  = array();
      $attrs = $ins->getAttributeTypes(); // Ya tiene los MTI attrs!
      foreach ( $attrs as $attr => $type )
      {
         if ( $attr !== 'id' )
         {
            $cols[] = array(
                        'name' => $attr,
                        'type' => $type,
                        'nullable' => (DatabaseNormalization::isSimpleAssocName( $attr )) ? true : $ins->nullable( $attr ) // FIXME: si es un atributo de una subclase (nivel 2 o mas, deberia ser nullable independientemente de la restriccion nullable).
                      );
         }
      }
      
      // ====================================================================================================
      // Sigue fallando, genera esto: (el vacio en nullable es el false)
      //  [5] => Array
      //  (
      //      [name] => entrada_id
      //      [type] => type_int32
      //      [nullable] => 
      //  )
      
      // Mientras que tengo esto en el objeto: (o sea la constraint nullable esta en true)
      //          [entrada_id] => Array
      //          (
      //              [0] => Nullable Object
      //                  (
      //                      [nullable:private] => 1
      //                  )
      //          )
      
      // El problema es que PO.nullable cuando es un atributo de referencia hasOne, 
      // se va a fijar si el atributo hasOne es nullable, y en este caso el atributo 
      // NO es nullable, lo que hace a la referencia no nullable.
      // SOLUCION!: Lo resuelvo fijandome si es un atributo de referencia, lo hago 
      // nullable, si no me fijo en si es nullable en el PO.
      
      // =========================================================
      //Logger::struct( $cols, "=== COLS ===" );

      $dal->createTable2($tableName, $pks, $cols, $ins->getConstraints());      

      // Crea tablas intermedias para las relaciones hasMany.
      // Estas tablas deberan ser creadas por las partes que no tienen el belongsTo, o sea la clase duenia de la relacion.
      // FIXME: si la relacion hasMany esta declarada en una superClase, la clase actual tiene la 
      //        relacion pero no deberia generar la tabla de JOIN a partir de ella, si no de la 
      //        tabla en la que se declara la relacion.
      $hasMany = $ins->getHasMany();
      foreach ( $hasMany as $attr => $assocClassName )
      {
         Logger::getInstance()->pm_log("AssocClassName: $assocClassName, attr: $attr");
         
         //if ($ins->isOwnerOf( $attr )) Logger::show("isOwner: $attr", "h3");
         //if ($ins->attributeDeclaredOnThisClass( $attr )) Logger::show("attributeDeclaredOnThisClass: $attr", "h3");
         
         // VERIFY, FIXME, TODO: Toma la asuncion de que el belongsTo es por clase.
         // Podria generar un problema si tengo dos atributos de la misma clase pero
         // pertenezco a uno y no al otro porque el modelo es asi.
         
         // Para casos donde no es n-n el hasMany, lo que importa es donde se declara la relacion,
         // no que lado es el owner. Para la n-n si es importante el owner.
         
         // Verifico si la relacion es hasMany n-n
         if ( $ins->getClass() !== $assocClassName ) // Verifico que no tenga un hasMany hacia mi mismo. Si tengo una relacion hasMany con migo, al verificar si es n-n siempre da true (porque verifica un bucle).
         {
            $hmRelObj = new $assocClassName(NULL, true);
            if ( $hmRelObj->hasManyOfThis($ins->getClass()) )
            {
               if ( $ins->isOwnerOf( $attr ) )
               {
                  $this->generateHasManyJoinTable($ins, $attr, $assocClassName, $dal);
               }
            }
            else if ( $ins->attributeDeclaredOnThisClass( $attr ) ) // Para generar la tabla de JOIN debo tener al atributo declarado en mi.
            {
               $this->generateHasManyJoinTable($ins, $attr, $assocClassName, $dal);
            }
         } // si el hasMany no es con migo mismo.
         else if ( $ins->attributeDeclaredOnThisClass( $attr ) ) // Para generar la tabla de JOIN debo tener al atributo declarado en mi.
         {
            $this->generateHasManyJoinTable($ins, $attr, $assocClassName, $dal);          
         }
      }

      // hasOne no necesita tabla intermedia...
      
   } // generate
   
   private function generateHasManyJoinTable($ins, $attr, $assocClassName, $dal)
   {
      $tableName = YuppConventions::relTableName( $ins, $attr, new $assocClassName() );

      //Logger::struct($this->getDataFromObject( new ObjectReference() ), "ObjRef ===");
      
      // "owner_id", "ref_id" son FKs.
      // Aqui se generan las columnas, luego se insertan las FKs
      // =========================================================

      $pks = array(
               array(
                 'name'    => 'id',
                 'type'    => Datatypes :: INT_NUMBER,
                 'default' => 1
               )
             );

      $cols = array();

      // FIXME: todo lo declarado aqui esta declarado en la clase ObjectReference, 
      //        deberia hacerse referencia a eso en lugar de redeclarar todo 
      //        (como los atributos y restricciones).
      
      $cols[] = array(
                 'name' => "owner_id",
                 'type' => Datatypes::INT_NUMBER, // Se de que tipo, esta definido asien ObjectReference.
                 'nullable' => false );
      $cols[] = array(
                 'name' => "ref_id",
                 'type' => Datatypes :: INT_NUMBER, // Se de que tipo, esta definido asien ObjectReference.
                 'nullable' => false );
      $cols[] = array(
                 'name' => "type",
                 'type' => Datatypes :: INT_NUMBER, // Se de que tipo, esta definido asien ObjectReference.
                 'nullable' => false );
       $cols[] = array(
                 'name' => "deleted",
                 'type' => Datatypes :: BOOLEAN, // Se de que tipo, esta definido asien PO.
                 'nullable' => false );
       $cols[] = array(
                 'name' => "class",
                 'type' => Datatypes :: TEXT, // Se de que tipo, esta definido asien PO.
                 'nullable' => false );
                      
       // El tema con la columna ord es que igual esta declarada en la clase ObjectReference,
       // entonces las consultas que se basen en los atributos que tenga la clase van a hacer
       // referencia a "ord" aunque la coleccion hasMany no sea una lista. 
       // Entonces lo que hago es generar igual la columna ord aunque la coleccion no sea lista,
       // y queda nullable, asi si es SET o COLLECTION no se da bola a ord.
       $cols[] = array(
                 'name' => "ord",
                 'type' => Datatypes :: INT_NUMBER, // Se de que tipo, esta definido asien PO.
                 'nullable' => true );
         
      // Si es una lista se genera la columna "ord".
      /*
      $hmattrType = $ins->getHasManyType( $attr );
      if ( $hmattrType === PersistentObject::HASMANY_LIST )
      {
         $cols[] = array(
                 'name' => "ord",
                 'type' => Datatypes :: INT_NUMBER, // Se de que tipo, esta definido asien PO.
                 'nullable' => true
                );
      }
      */
  
      $dal->createTable2( $tableName, $pks, $cols, array() );

   } // generateHasManyJoinTable

   
   
   /**
    * generateAll
    * Genera todas las tablas correspondientes al modelo previamente cargado.
    * 
    * @pre Deberia haber cargado, antes de llamar, todas las clases persistentes.
    */
   public function generateAll( $appName ) {
   		
   		Logger::getInstance()->pm_log("PersistentManager::generateAll ======");
        $dalForApp = $this->dal; //
        
		// Todas las clases del primer nivel del modelo.
		$A = ModelUtils::getSubclassesOf( 'PersistentObject', $appName ); // FIXME> no es recursiva!
		
		// Se utiliza luego para generar FKs.
        $generatedPOs = array();
        $dalForApp = $this->dal;
        
        foreach( $A as $clazz )
          {
             $struct = MultipleTableInheritanceSupport::getMultipleTableInheritanceStructureToGenerateModel( $clazz );
    
             // struct es un mapeo por clave las clases que generan una tabla y valor las clases que se mapean a esa tabla.
             foreach ($struct as $class => $subclassesOnSameTable)
             {
                // Instancia que genera tabla
                $c_ins = new $class(); // FIXME: supongo que ya tiene withTable, luego veo el caso que no se le ponga WT a la superclase...
                // FIXME: como tambien tiene los atributos de las superclases y como van en otra tabla, hay que sacarlos.
                
                // Para cara subclase que se mapea en la misma tabla
                foreach ( $subclassesOnSameTable as $subclass )
                {
                   $sc_ins = new $subclass(); // Para setear los atributos.
                   
                   $props = $sc_ins->getAttributeTypes();
                   $hone  = $sc_ins->getHasOne();
                   $hmany = $sc_ins->getHasMany();
                   
                   // FIXME: si el artibuto no es de una subclase parece que tambien pone nullable true...
                   
                   // Agrega constraint nullable true, para que los atributos de las subclases
                   // puedan ser nulos en la tabla, para que funcione bien el mapeo de herencia de una tabla.
                   //Logger::getInstance()->pm_log( "Para cada attr de: $subclass " . __FILE__ . " " . __LINE__);
                   foreach ($props as $attr => $type)
                   {
                      // FIXME: esta parte seria mas facil si simplemente cuando la clase tiene la constraint 
                      // y le seteo otra del mismo tipo para el mismo atributo, sobreescriba la anterior.
    
                      $constraint = $sc_ins->getConstraintOfClass( $attr, 'Nullable' );
                      if ($constraint !== NULL)
                      {
                         //Logger::getInstance()->log( "CONTRAINT NULLABLE EXISTE!");
                         // Si hay, setea en true
                         $constraint->setValue(true);
                      }
                      else
                      {
                         // Si no hay, agrega nueva
                         //Logger::getInstance()->log( "CONTRAINT NULLABLE NO EXISTE!, LA AGREGA");
                         $sc_ins->addConstraints($attr, array(Constraint::nullable(true)));
                      }
                   }
                   
                   //Logger::getInstance()->pm_log( "Termina con las constraints ======= " . __FILE__ . " " . __LINE__);
                   
                   // Se toma luego de modificar las restricciones
                   $constraints = $sc_ins->getConstraints();
                   
                   foreach( $props as $name => $type ) $c_ins->addAttribute($name, $type);
                   foreach( $hone  as $name => $type ) $c_ins->addHasOne($name, $type);
                   foreach( $hmany as $name => $type ) $c_ins->addHasMany($name, $type);
                   
                   // Agrego las constraints al final porque puedo referenciar atributos que todavia no fueron agregados.
                   foreach( $constraints as $attr => $constraintList ) $c_ins->addConstraints($attr, $constraintList);
                }
                
                $parent_class = get_parent_class($c_ins);
                if ( $parent_class !== 'PersistentObject' ) // Si la instancia no es de primer nivel
                {
                   // La superclase de c_ins se mapea en otra tabla, saco esos atributos...
                   $suc_ins = new $parent_class();
                   $c_ins = PersistentObject::less($c_ins, $suc_ins); // Saco los atributos de la superclase
                }
                
                $tableName = YuppConventions::tableName( $c_ins );

                // FIXME: esta operacion necesita instanciar una DAL por cada aplicacion.
                // La implementacion esta orientada a la clase, no a la aplicacion, hay que modificarla.
                
                // Si la tabla ya existe, no la crea.
                if ( !$dalForApp->tableExists( $tableName ) )
                {
                   // FIXME: c_ins no tiene las restricciones sobre los atributos inyectados.
                   $this->generate( $c_ins, $dalForApp );
                
                   // Para luego generar FKs.
                   $generatedPOs[] = $c_ins;
                }
             } // foreach ($struct as $class => $subclassesOnSameTable)
          } // foreach( $A as $clazz )
          
          
          // ======================================================================
          // Crear FKs en la base.
          
          //Logger::struct( $generatedPOs, "GENERATED OBJS" );
          
          foreach ($generatedPOs as $ins)
          {
             $tableName = YuppConventions::tableName( $ins );
             $fks = array();
             
             // FKs hasOne
             $ho_attrs = $ins->getHasOne();
             foreach ( $ho_attrs as $attr => $refClass )
             {
                // Problema: pasa lo mismo que pasaba en YuppConventions.relTableName, esta tratando
                // de inyectar la FK en la tabla incorrecta porque la instancia es de una superclase
                // de la clase donde se declara la relacion HasOne, entonces hay que verificar si una
                // subclase no tiene ya el atributo hasOne declarado, para asegurarse que es de la
                // instancia actual y no intentar generar la FK si no lo es.
                
                $instConElAtributoHasOne = NULL;
                $subclasses = ModelUtils::getAllAncestorsOf( $ins->getClass() );
                
                foreach ( $subclasses as $aclass )
                {
                   $ains = new $aclass();
                   if ( $ains->hasOneOfThis( $refClass ) )
                   {
                      //Logger::getInstance()->log( $ains->getClass() . " TIENE UNO DE: $refClass" );
                      $instConElAtributoHasOne = $ains; // EL ATRIBUTO ES DE OTRA INSTANCIA!
                      break;
                   }
                }
                
                // Si el atributo de FK hasOne es de la instancia actual, se genera:
                if ( $instConElAtributoHasOne === NULL )
                {
                   // Para ChasOne esta generando "chasOne", y el nombre de la tabla que aparece en la tabla es "chasone".
                  $refTableName = YuppConventions::tableName( $refClass );
                  $fks[] = array(
                             'name'    => DatabaseNormalization::simpleAssoc($attr), // nom_id, $attr = nom
                             'table'   => $refTableName,
                             'refName' => 'id' // Se que esta referencia es al atributo "id".
                            );
                }
             }
             
             // FKs tablas intermedias HasMany
             $hasMany = $ins->getHasMany();
             
             foreach ( $hasMany as $attr => $assocClassName )
             {
                //Logger::getInstance()->pm_log("AssocClassName: $assocClassName, attr: $attr");
                
                if ( $ins->isOwnerOf( $attr ) ) // VERIFY, FIXME, TODO: Toma la asuncion de que el belongsTo es por clase. Podria generar un problema si tengo dos atributos de la misma clase pero pertenezco a uno y no al otro porque el modelo es asi.
                {
                   $hm_fks = array();
                   $hasManyTableName = YuppConventions::relTableName( $ins, $attr, new $assocClassName() );
       
                   // "owner_id", "ref_id" son FKs.
       
                   // ===============================================================================
                   // El nombre de la tabla owner para la FK debe ser el de la clase 
                   // donde se declara el attr hasMany,
                   // no para el ultimo de la estructura de MTI (como pasaba antes).
                   $classes = ModelUtils::getAllAncestorsOf( $ins->getClass() );
          
                   //Logger::struct( $classes, "Superclases de " . $ins1->getClass() );
                   
                   $instConElAtributoHasMany = $ins; // En ppio pienso que la instancia es la que tiene el atributo masMany.
                   foreach ( $classes as $aclass )
                   {
                      $_ins = new $aclass();
                      if ( $_ins->hasManyOfThis( $assocClassName ) )
                      {
                         //Logger::getInstance()->log("TIENE MANY DE " . $ins2->getClass());
                         $instConElAtributoHasMany = $_ins;
                         break;
                      }
                      
                      //Logger::struct( $ins, "Instancia de $aclass" );
                   }
                   // ===============================================================================
                   
                   $hm_fks[] = array(
                             'name'    => "owner_id",
                             'table'   => YuppConventions::tableName( $instConElAtributoHasMany->getClass() ), // FIXME: Genera link a gs (tabla de G1) aunque el atributo sea declarado en cs (tabla de C1). Esto puede generar problemas al cargar (NO PASA NADA AL CARGAR, ANDA FENOMENO!), aunque la instancia es la misma, deberia hacer la referencia a la tabla correspondiente a la instancia que declara el atributo, solo por consistencia y correctitud.
                             'refName' => 'id' // Se que esta referencia es al atributo "id".
                            );
       
                   $hm_fks[] = array(
                             'name'    => "ref_id",
                             'table'   => YuppConventions::tableName( $assocClassName ),
                             'refName' => 'id' // Se que esta referencia es al atributo "id".
                            );
                            
                   // Genera FKs
                   $dalForApp->addForeignKeys($hasManyTableName, $hm_fks);
                }
             } // foreach hasMany
             
             // Genera FKs
             $dalForApp->addForeignKeys($tableName, $fks);
             
          } // foreach PO
		
          
   } // generateAll
   

   
   // para getMultipleTableInheritance que filtre la solucion.
//   function filter_not_null( $array )
//   {
//      return $array !== NULL;
//   }

   /** ES COMO LO CONTRARIO DE SAVE_ASSOC, pero para solo un registro. save_assoc( PersistentObject &$owner, PersistentObject &$child, $ownerAttr )
    * Elimina la asociacion hasMany entre los objetos. (marca como eliminada o borra fisicamente el registro en la tabla de join correspondiente a la relacion entre los objetos).
    * attr1 es un atributo de obj1
    * attr2 es un atributo de obj2
    * attr1 y attr2 corresponden a los roles de la misma asociacion entre obj1 y obj2
    * attr1 y/o attr2 debe(n) ser hasMany
    * logical indica si la baja es fisica o logica.
    */
   public function remove_assoc( $obj1, $obj2, $attr1, $attr2, $logical = false )
   {
      // TODO: Si la relacion es A(1)<->(*)B (bidireccional) deberia setear en NULL el atributo A y A_id de B.

      // Veo cual es el owner:
      $owner     = &$obj1;
      $ownerAttr = &$attr1;
      $child     = &$obj2;
      if ( $obj2->isOwnerOf( $attr1 ) ) // Si la asoc al obj1 es duenia de obj2
      {
         $owner     = &$obj2;
         $ownerAttr = &$attr2;
         $child     = &$obj1;
      }
      
      Logger::getInstance()->log( 'PM::remove_assoc owner '.$owner->getClass().', child '. $child->getClass() );

      // Para eliminar no me interesa el tipo de relacion (si esta instanciada bidireccional o unidireccional).
      // Quiero eliminar el que tenga ownerid y childid de los objetos que me pasaron.
      // (obs: entonces no permito mas de una relacion entre 2 instancias!)                               );

      // El id de la superclase, es igual que el id de la clase declarada en el hasMany, y el mismo que la instancia final
      // Por eso uso el id del objeto directamente
      $ref_id = $child->getId();

      Logger::getInstance()->log( 'PM::remove_assoc owner_id '.$owner->getId().', ref_id '. $ref_id );

      // se pasan instancias... para poder pedir el withtable q se setea en tiempo de ejecucion!!!!
      //
      $tableName =  YuppConventions::relTableName( $owner, $ownerAttr, $child );

      // Necesito el id del registro para poder eliminarlo...
      // esto es porque no tengo un deleteWhere y solo tengo un delete por id... (TODO)
      YuppLoader::load( "core.db.criteria2", "Query" );
      $q = new Query();
      $q->addFrom( $tableName, "ref" )
        ->addProjection( "ref", "id" )
        ->setCondition( Condition::_AND()
          ->add( Condition::EQ("ref", "owner_id", $owner->getId()) )
          ->add( Condition::EQ("ref", "ref_id", $ref_id) ) );

      $data = $this->dal->query( $q );
      $id = $data[0]['id']; // Se que hay solo un registro...
                            // TODO: podria no haber ninguno, OJO! hay que tener en cuenta ese caso.

      $this->dal->deleteFromTable( $tableName, $id, $logical );

   } // remove_assoc
   
   
   // Metodos utilitarios para manejar mapeo de herencia multi-tabla
   
   /** FIXME: no deberia ser de PO? o de MTISup? no deberia estar en PM deberia ser algo del model utils o mti support.
    * Devuelve true si ambas clases se mapean en la misma tabla, las clases podrian ser 
    * superclase y subclase, ser clases primas, hermanas o no tener relacion alguna.
    * Este metodo es mas general que isMappedOnSameTableSubclass.
    */
   public static function isMappedOnSameTable( $class1, $class2 )
   {
      // TODO
      // el caso superclase subclase lo handlea isMappedOnSameTableSubclass.

      $table1 = YuppConventions::tableName( $class1 );
      $table2 = YuppConventions::tableName( $class2 );
      
      //Logger::getInstance()->log( "isMappedOnSameTable: table1 $table1" );
      //Logger::getInstance()->log( "isMappedOnSameTable: table2 $table2" );
      
      return ($table1 === $table2);
      
      /*
      // Chekeo ambos casos de subclass primero...
      if ( is_subclass_of($class1, $class2) )
      {
         return self::isMappedOnSameTableSubclass( $class1, $class2 );
      }
      else if ( is_subclass_of($class2, $class1) )
      {
         return self::isMappedOnSameTableSubclass( $class2, $class1 );
      }
      else
      {
         $c1_ins = new $class1();
         $c2_ins = new $class1();
      
         // SOLUCION COMPLICADA PERO CORRECTA.
         // Me tengo que fijar si pertenecen a la misma estructura de herencia (si son primas o hermanas).
         // Luego me fijo en alguna superclase comun y desde ahi busco en que tabla se mapean.
         // ...
         
         // No lo podria hacer simplemente comparando withTable? se que si tiene y son distintos se mapean en distintas tablas,
         // y si una no tiene ya se que la que tiene va en otra tabla aunque pertenezca a la misma estructura de herencia.
         // Pero si ninguna tiene withTable, tengo que encontrar quien define la tabla para cada clase y ver si son la misma...
         // Para este caso (que incluye a los otros tengo) la funcion tableName que deberia dar el nombre de la tabla para 
         // cualquier instancia, tenga o no withTable declarado en la instancia.
         $table1 = YuppConventions::tableName( $c1_ins );
         $table2 = YuppConventions::tableName( $c2_ins );
         
         return ($table1 === $table2);
      }
      */
   }
   
   public function tableExists($className) {
   		$res = array();
   		$tableName = YuppConventions::tableName( $className );
   		if ($this->dal->tableExists( $tableName )) {
   			$res[$className] = array('tableName'=>$tableName, 'created'=>"CREADA");
   		} else {
   			$res[$className] = array('tableName'=>$tableName, 'created'=>"NO CREADA");
   		}
   		return $res;
   }
   
} // PersistentManager

?>