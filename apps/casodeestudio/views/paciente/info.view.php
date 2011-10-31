<?php

YuppLoader::load('casodeestudio.model', 'Paciente');
$m = Model::getInstance();
$paciente = $m->get('paciente');

echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-bootstrap.min'));
echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-docs'));
echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-prettify'));
echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'main'));

?>

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
					<li><?php echo Helpers::link( array(
								"app"        => "casodeestudio",
                                "controller" => "map",
                                "action"     => "map",
                                "body"       => "Home") ); ?></li>
					<li class="active"><?php echo Helpers::link( array(
								"app"        => "casodeestudio",
                                "controller" => "paciente",
                                "action"     => "list",
                                "body"       => "Pacientes") ); ?></li>
					<li><a href="#contact">Contact</a></li>
				</ul>
				<p class="pull-right">
					Logged in as <a href="#">username</a>
				</p>
			</div>
		</div>
	</div>

    <div class="container-fluid">
      <div class="sidebar">
         <div align="right">
          <?php echo h('img', array(
                  'app'=>'casodeestudio', 
                  'src'=>'app_64.png', 
                  'w'=>'64', 
                  'h'=>'64', 
                  'text'=>'logo' )); ?>
         </div>
      </div>
      <div class="content">
			  <div class="page-header">
			    <h3>Paciente</h3>
			  </div>
			  <div class="row">
			    <div class="span16">
			    	<div class="row show-grid">
					    <div class="span-one-third">Nombre</div>
						<div class="span-two-thirds"><?php echo $paciente->getNombre(); ?></div>
					</div>
		        </div>
		     </div>
		  

        <footer>
          <p>&copy; YuppGIS 2011</p>
        </footer>
      </div>
    </div>