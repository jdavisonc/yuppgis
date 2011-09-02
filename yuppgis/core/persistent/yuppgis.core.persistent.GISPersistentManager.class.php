<?php

interface  GISPersistentManager  {
	
	// TODO_GIS documentar
	public function init_dal( $appName );
	
	/**
	 * Obtiene un objeto geografico desde la base de datos.
	 * @param unknown_type $tableNameOwner
	 * @param unknown_type $attr
	 * @param unknown_type $persistentClass
	 * @param unknown_type $id
	 */
	public function get_gis_object( $tableNameOwner, $attr, $persistentClass, $id );

	/**
	 * Se salva en cascada con el dueño y su nombre de atributo.
	 * @see PersistentManager::save_cascade_owner()
	 */
	public function save_cascade_owner( PersistentObject $owner, $attrNameAssoc, PersistentObject $obj, $sessId );

	/**
	 * Borra elementos en cascada.
	 *
	 * *Precaucion*: Solo se implemento el borrado en cascada para asociasiones hasOne
	 *
	 * @see PersistentManager::delete()
	 */
	public function delete( $persistentInstance, $id, $logical );
	
	// TODO_GIS documentar
	public function findBy( PersistentObject $instance, Condition $condition, ArrayObject $params );
   	
}
?>