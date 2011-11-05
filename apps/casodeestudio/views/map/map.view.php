<?php YuppLoader::load('casodeestudio.model', 'Paciente'); ?>
<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'main')) ; ?>
<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-bootstrap.min')) ; ?>

<script type="text/javascript">

function customClickHandler(event){
	var position = event.object.getLonLatFromPixel(event.xy);
	alert("Custom click handler! " + position);
	
}

</script>

<style>
body {
	padding-top: 60px;
}
</style>

	<div class="topbar">
		<div class="topbar-inner">
			<div class="container-fluid">
				<?php echo Helpers::link( array(
								"app"        => "casodeestudio",
                                "controller" => "map",
                                "action"     => "map",
                                "body"       => "Salud Digital",
								"attrs"		 => array ("class"=>"brand")) ); ?>
								<!-- logo -->
				<?php echo h('img', array(
                  'app'=>'casodeestudio', 
                  'src'=>'app_64.png', 
                  'w'=>'32', 
                  'h'=>'32', 
                  'text'=>'logo' )); ?>
				<ul class="nav">
					<li class="active"><?php echo Helpers::link( array(
								"app"        => "casodeestudio",
                                "controller" => "map",
                                "action"     => "map",
                                "body"       => "Home") ); ?></li>
					<li><?php echo Helpers::link( array(
								"app"        => "casodeestudio",
                                "controller" => "paciente",
                                "action"     => "list",
                                "body"       => "Pacientes") ); ?></li>
                    <li><?php echo Helpers::link( array(
								"app"        => "casodeestudio",
                                "controller" => "medico",
                                "action"     => "list",
                                "body"       => "Medicos") ); ?></li>
				</ul>
				<p class="pull-right">
					Logged in as <a href="#">username</a>
				</p>
			</div>
		</div>
	</div>

    <div class="container-fluid">
      <div class="sidebar">
        <div class="well">
          <h5>Enfermedades</h5>
          <div class="maplayers">
          	<?php echo GISHelpers::MapLayers(array(MapParams::ID => 1)); ?>
          </div>
          <h5>Filtrar por atributos</h5>
          <div>
          	<?php echo GISHelpers::FiltersMenu('Paciente', 1); ?>
          </div>
        </div>
      </div>
      <div class="content">
        <!-- Main hero unit for a primary marketing message or call to action -->
        <div class="hero-unit">
          <p>
          	<?php echo GISHelpers::Map(array(
				MapParams::ID => 1,
				MapParams::HEIGHT => 400,
				MapParams::WIDTH => 850,
				MapParams::TYPE => "google",
				MapParams::CLICK_HANDLERS => array('customClickHandler'),
				MapParams::CENTER => array("-6251096.6093197", "-4149355.4159976"),
				MapParams::ZOOM => 14
				
			)); ?>
          </p>
        </div>

        <footer>
          <p>&copy; YuppGIS 2011</p>
        </footer>
      </div>
    </div>