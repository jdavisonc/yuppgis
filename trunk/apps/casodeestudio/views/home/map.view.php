<?php YuppLoader::load('casodeestudio.model', 'Paciente'); ?>
<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'main')) ; ?>

<h1>Caso de Estudio!</h1>
<br>
	<?php echo GISHelpers::Map(array(
		MapParams::ID => 1,
		MapParams::HEIGHT => 500,
		MapParams::WIDTH => 500,
		MapParams::TYPE => "google"
		
	)); ?>

<h2>aqui la magia...</h2>