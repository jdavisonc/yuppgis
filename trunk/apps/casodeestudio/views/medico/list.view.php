<?php

YuppLoader::load('casodeestudio.model', 'Medico');
$m = Model::getInstance();

echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-bootstrap.min'));
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
                    <li><?php echo Helpers::link( array(
								"app"        => "casodeestudio",
                                "controller" => "medico",
                                "action"     => "list",
                                "body"       => "Medicos") ); ?></li>
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
			    <h3>Medicos</h3>
			  </div>
			  <div class="row">
			    <div class="span16">
			    	<table class="zebra-striped">
			    		<thead>
			    			<tr>
			    				<th>Nombre</th>
			    				<th>Apellido</th>
			    				<th>Accion</th>
			    			</tr>
			    		</thead>
			    		<tbody>
			    			<?php $medicos = $m->get('list');?>
			    			<?php foreach ($medicos as $medico) {?>
				    			<tr>
				    				<td><?php echo $medico->getNombre();?></td>
				    				<td><?php echo $medico->getApellido();?></td>
				    				<td>
				    					<?php echo Helpers::link( array(
                                                        "app"        => "casodeestudio",
                           								"controller" => "medico",
                           								"action"     => "mapaMedico",
                           								"body"       => "Ver Mapa",
				    									"id"		 => $medico->getId(),
                                                   		"attrs"		 => array ("class" => "btn primary")) ); ?>
                                    </td>
								</tr>
			    			<?php } ?>
			    		</tbody>
			    	</table>
			    	
		        </div>
		     </div>
		  

        <footer>
          <p>&copy; YuppGIS 2011</p>
        </footer>
      </div>
    </div>
    