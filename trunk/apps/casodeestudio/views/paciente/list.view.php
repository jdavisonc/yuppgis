<?php

YuppLoader::load('casodeestudio.model', 'Paciente');
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
			    <h3>Pacientes</h3>
			  </div>
			  <div class="row">
			    <div class="span16">
			    	<table class="zebra-striped">
			    		<thead>
			    			<tr>
			    				<th>Nombre</th>
			    				<th>Apellido</th>
			    				<th>Sexo</th>
			    				<th><?php echo Enfermedad::getName(Enfermedad::DIABETES); ?></th>
			    				<th><?php echo Enfermedad::getName(Enfermedad::ASMA); ?></th>
			    				<th><?php echo Enfermedad::getName(Enfermedad::OBESIDAD); ?></th>
			    				<th><?php echo Enfermedad::getName(Enfermedad::INSUFICIENCIA_RENAL); ?></th>
			    				<th><?php echo Enfermedad::getName(Enfermedad::HIPERTENCION); ?></th>
			    			</tr>
			    		</thead>
			    		<tbody>
			    			<?php $pacientes = $m->get('list');?>
			    			<?php foreach ($pacientes as $p) {?>
				    			<tr>
				    				<td><?php echo $p->getNombre();?></td>
				    				<td><?php echo $p->getApellido();?></td>
				    				<td><?php echo $p->getSexo();?></td>
				    				<td><?php echo $p->aGet(Enfermedad::DIABETES); ?></td>
				    				<td><?php echo $p->aGet(Enfermedad::ASMA); ?></td>
				    				<td><?php echo $p->aGet(Enfermedad::OBESIDAD); ?></td>
				    				<td><?php echo $p->aGet(Enfermedad::INSUFICIENCIA_RENAL); ?></td>
				    				<td><?php echo $p->aGet(Enfermedad::HIPERTENCION); ?></td>
				    			</tr>
			    			<?php } ?>
			    		</tbody>
			    	</table>
			    	<?php echo Helpers::link( array(
								"app"        => "casodeestudio",
                                "controller" => "paciente",
                                "action"     => "add",
                                "body"       => "Crear",
			    				"attrs"		 => array ("class" => "btn primary")) ); ?>
		        </div>
		     </div>
		  

        <footer>
          <p>&copy; YuppGIS 2011</p>
        </footer>
      </div>
    </div>