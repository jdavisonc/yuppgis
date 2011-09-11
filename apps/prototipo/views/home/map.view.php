<?php YuppLoader::load('prototipo.model', 'Paciente'); ?>
<?php echo Helpers::css(array('app'=>'prototipo', 'name' => 'main')) ; ?>

<fieldset>
	<legend>Mapa</legend>
	<?php echo GISHelpers::Map(array(
		MapParams::ID => 1,		
		MapParams::MAP_CLICK_HANDLER => 'customClickHandler'
	)); ?>
</fieldset>
<fieldset>
	<legend>Capas del Mapa</legend>
	<?php echo GISHelpers::MapLayers(array(MapParams::ID => 1)); ?>
</fieldset>
<fieldset>
	<legend>Filtros de Paciente</legend>
	<?php echo  GISHelpers::FiltersMenu('Paciente', 1, 'showFilteredElements'); ?>
</fieldset>
<fieldset>
	<legend>Resultados</legend>
	<div id="resultsDiv"></div>
</fieldset>

<fieldset>
	<legend>Log</legend>
	<?php echo  GISHelpers::Log(1); ?>
</fieldset>

<script type="text/javascript">

function customClickHandler(event){
	trace("User custom click handler", event);
}

function customDoubleClickHandler(event){
	trace("User custom double click handler", event);
}

</script>
