<?php YuppLoader::load('prototipo.model', 'Paciente'); ?>
<?php echo Helpers::css(array('app'=>'prototipo', 'name' => 'main')) ; ?>

<fieldset>
	<legend>Mapa</legend>
	<?php echo GISHelpers::Map(array(MapParams::ID => 1, MapParams::KML_URL => '/yuppgis/prototipo/Home/Kml')); ?>
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
function showFilteredElements(data){
	
	var log = '';
	var ids = [];
	$.each(data, function(i, item){
		ids.push(item.id);
		log += item.id + '. ' + item.nombre + '<br/>';
	});
	$('#resultsDiv').html(log);
		
	$.each(map_1.layers, function(i, layer){
		if (i > 1){
			var elements = getElementsInLayer(layer, ids);

			$.each(elements, function(i, feature){						
				//feature.visibility = false;
				feature.attributes['visibility'] = "hidden";				
				layer.drawFeature(feature);
			});
		}
	});
	
	return false;
}

function getElementsInLayer(layer,ids){
	var i,feature,len = layer.features.length,foundFeatures=[];
	for(i=0;i<len;i++){
		feature = layer.features[i];
		if(feature&&feature.attributes){
			if($.inArray(feature.attributes['elementId'], ids) > -1){
				foundFeatures.push(feature);
			}
		}
	}

	return foundFeatures;
}
</script>
