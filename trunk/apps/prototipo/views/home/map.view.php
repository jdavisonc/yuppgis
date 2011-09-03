
<?php YuppLoader::load('prototipo.model', 'Paciente'); ?>

<table>
<tr><td  colspan="3"><?php echo 'Mapa 1' ?></td><tr>
	<tr>
		<td><?php echo GISHelpers::Map(array(MapParams::ID => 1, MapParams::KML_URL => '/yuppgis/prototipo/Home/Kml')); ?></td>
		<td><?php echo GISHelpers::MapLayers(array(MapParams::ID => 1)); ?></td>
		<td></td>
	</tr>
	
</table>
