<?php

YuppLoader::load('casodeestudio.model', 'Paciente');
$m = Model::getInstance();
$paciente = $m->get('paciente');

?>

<html>
	<head>
		<title>Salud Digital</title>
		<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-bootstrap.min'));?>
		<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'main'));?>
		<style>
		body {
			padding-top: 60px;
		}
		</style>
	</head>
	<body>
	
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
				  	<!-- Notificacion -->
		        	<?php 
		        		$inserted = $m->get('inserted');
		        		$error = $m->get('error');
		        		if (isset($inserted)) {?>
				        <div class="alert-message success">
						  <a class="close" href="#">×</a>
						  <p><strong>Paciente creado!</strong> Se ha creado el paciente con id = <?php echo $inserted; ?>.</p>
						</div>
					<?php } else if (isset($error)) { ?>
						<div class="alert-message block-message error">
					        <a class="close" href="#">×</a>
					        <p><strong>Error al crear Paciente!</strong> Cambia los siguiente valores y vuelve a intentarlo.</p><br>
					        <?php echo DisplayHelper::errors( $error ); ?>
					      </div>
					<?php } ?>
				    <h3>Enfermedades de <?php echo $paciente->getNombre() . ' '. $paciente->getApellido(); ?></h3>
				  </div>
				  <div class="row">
				    <div class="span12">
			          	<form>
			          		<fieldset>
								<div class="clearfix">
						           <label for="nombre"><?php echo Enfermedad::getName(Enfermedad::ASMA); ?></label>
						           <div class="input">
						             <?php echo DisplayHelper::select(Enfermedad::ASMA, Estado::getEstadosView(), $paciente->aGet(Enfermedad::ASMA)); ?>
						           </div>
						        </div>
								<div class="clearfix">
						           <label for="nombre"><?php echo Enfermedad::getName(Enfermedad::DIABETES); ?></label>
						           <div class="input">
						             <?php echo DisplayHelper::select(Enfermedad::DIABETES, Estado::getEstadosView(), $paciente->aGet(Enfermedad::DIABETES)); ?>
						           </div>
						        </div>
								<div class="clearfix">
						           <label for="nombre"><?php echo Enfermedad::getName(Enfermedad::HIPERTENCION); ?></label>
						           <div class="input">
						             <?php echo DisplayHelper::select(Enfermedad::HIPERTENCION, Estado::getEstadosView(), $paciente->aGet(Enfermedad::HIPERTENCION)); ?>
						           </div>
						        </div>
								<div class="clearfix">
						           <label for="nombre"><?php echo Enfermedad::getName(Enfermedad::INSUFICIENCIA_RENAL); ?></label>
						           <div class="input">
						             <?php echo DisplayHelper::select(Enfermedad::INSUFICIENCIA_RENAL, Estado::getEstadosView(), $paciente->aGet(Enfermedad::INSUFICIENCIA_RENAL)); ?>
						           </div>
						        </div>
								<div class="clearfix">
						           <label for="nombre"><?php echo Enfermedad::getName(Enfermedad::OBESIDAD); ?></label>
						           <div class="input">
						             <?php echo DisplayHelper::select(Enfermedad::OBESIDAD, Estado::getEstadosView(), $paciente->aGet(Enfermedad::OBESIDAD)); ?>
						           </div>
						        </div>
						        <?php echo DisplayHelper::hidden('edited', true); ?>
						        <?php echo DisplayHelper::hidden('id', $paciente->getId()); ?>
								<div class="actions">
									<input type="submit" class="btn primary" value="Modificar">&nbsp;<button type="reset" class="btn">Cancel</button>
								</div>
			          		</fieldset>
			          	</form>
			        </div>
			     </div>
			  
	
	        <footer>
	          <p>&copy; YuppGIS 2011</p>
	        </footer>
	      </div>
	    </div>
    </body>
</html>