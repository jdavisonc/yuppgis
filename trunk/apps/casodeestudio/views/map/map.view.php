<?php YuppLoader::load('casodeestudio.model', 'Paciente'); ?>
<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'main')) ; ?>

<link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.3.0/bootstrap.min.css">

<h1>Salud Digital</h1>

  <div class="container-fluid">
    <div class="sidebar">
      Test
    </div>
    <div class="content">
		<?php echo GISHelpers::Map(array(
			MapParams::ID => 1,
			MapParams::HEIGHT => 500,
			MapParams::WIDTH => 500,
			MapParams::TYPE => "google"
			
		)); ?>
    </div>
  </div>