<?php YuppLoader::load('prototipo.model', 'Paciente'); ?>
<?php echo Helpers::css(array('app'=>'prototipo', 'name' => 'main')) ; ?>

<fieldset>
	<legend>Mapa</legend>
	<?php echo GISHelpers::Map(array(
		MapParams::ID => 1, 
		MapParams::KML_URL => '/yuppgis/prototipo/Home/Kml',
		MapParams::CLICK_HANDLER => 'customClickHandler',
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

<script type="text/javascript">

function customClickHandler(event){
	trace("custom click handler", event);
}

function customDoubleClickHandler(event){
	trace("custom double click handler", event);
}

</script>
