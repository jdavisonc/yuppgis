
<?php YuppLoader::load('prototipo.model', 'Paciente'); ?>

<table>
	<tr>
		<td><?php echo GISHelpers::Map(); ?></td>
		<td>Acciones sobre Pacientes:<br /> <?php echo GISHelpers::ActionsMenu("Paciente", "actionsmenu") ?>
		</td>
	</tr>
</table>
