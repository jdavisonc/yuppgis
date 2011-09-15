<?php YuppLoader::load('prototipo.model', 'Paciente'); ?>
<?php echo Helpers::css(array('app'=>'prototipo', 'name' => 'main')) ; ?>

<fieldset>
	<legend>Mapa</legend>
	<?php echo GISHelpers::Map(array(
		MapParams::ID => 1,
		MapParams::CLICK_HANDLERS => array('customClickHandler'),
		MapParams::SELECT_HANDLERS => array('customSelectHandler')
		
	)); ?>
</fieldset>
<fieldset>
	<legend>Capas del Mapa</legend>
	<?php echo GISHelpers::MapLayers(array(MapParams::ID => 1)); ?>
</fieldset>
<fieldset>
	<legend>Filtros de Paciente</legend>
	<?php echo  GISHelpers::FiltersMenu('Paciente', 1, 'listFilteredElements'); ?>
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
	//alert("Custom click handler!");
}

function customSelectHandler(event){
	//alert("Custom select handler!");
}

function listFilteredElements(data){
	
	var log = '';
	var ids = [];
	$.each(data, function(i, item){
		ids.push(item.id);
		log += item.id + '. ' + item.nombre + '<br/>';
	});
	$('#resultsDiv').html(log);
	
	return false;
}

</script>
