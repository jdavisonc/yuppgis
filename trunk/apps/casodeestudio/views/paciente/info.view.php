<?php

YuppLoader::load('casodeestudio.model', 'Paciente');
$m = Model::getInstance();
$paciente = $m->get('paciente');

?>
<html>
	<head>
		<title>Salud Digital</title>
		<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-bootstrap.min'));?>
		<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-docs.min'));?>
		<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-prettify.min'));?>
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
				    <h1><?php echo $paciente->getNombre() . ' ' . $paciente->getApellido(); ?></h1>
				  </div>
				  <div class="row">
				    <div class="span16">
				    	<div class="row grid">
				    		<div class="span3 title ">Cedula de Identidad</div>
							<div class="span3  value"><?php echo $paciente->getCi(); ?></div>
							<div class="span1"></div>
						    <div class="span3 title ">Sexo</div>
							<div class="span3  value"><?php echo $paciente->getSexo(); ?></div>
						</div>
						<div class="row grid">
						    <div class="span3 title ">F.Nacimiento</div>
							<div class="span3  value"><?php echo $paciente->getFechaNacimiento(); ?></div>
							<div class="span1"></div>
							<div class="span3 title ">F.Fallecimiento</div>
							<div class="span3  value"><?php echo $paciente->getFechaFallecimiento(); ?></div>
						</div>
						<div class="row grid">
						    <div class="span3 title ">Telefono</div>
							<div class="span3  value"><?php echo $paciente->getTelefono(); ?></div>
							<div class="span1"></div>
							<div class="span3 title ">Email</div>
							<div class="span3  value"><?php echo $paciente->getEmail(); ?></div>
						</div><br/>
						
						<div class="page-header">
						    <h4>Ubicacion</h4>
						  </div>
						<div class="row grid">
						    <div class="span3 title ">Direccion</div>
							<div class="span3  value"><?php echo $paciente->getDireccion(); ?></div>
							<div class="span1"></div>
							<div class="span3 title ">Barrio</div>
							<div class="span3  value"><?php echo $paciente->getBarrio(); ?></div>
						</div>
						<div class="row grid">
						    <div class="span3 title">Ciudad</div>
							<div class="span3 value"><?php echo $paciente->getCiudad(); ?></div>
							<div class="span1" ></div>
							<div class="span3 title">Departamento</div>
							<div class="span3 value"><?php echo $paciente->getDepartamento(); ?></div>
						</div><br/>
						<div class="page-header">
						   <h4>Enfermedades</h4>
						</div>
						<div class="row grid">
						    <div class="span3 title "><?php echo Enfermedad::getName(Enfermedad::ASMA); ?></div>
							<div class="span3  value"><?php echo $paciente->aGet(Enfermedad::ASMA); ?></div>
							<div class="span1" ></div>
							<div class="span3 title "><?php echo Enfermedad::getName(Enfermedad::DIABETES); ?></div>
							<div class="span3  value"><?php echo $paciente->aGet(Enfermedad::DIABETES); ?></div>
						</div>
						<div class="row grid">
						    <div class="span3 title "><?php echo Enfermedad::getName(Enfermedad::HIPERTENSION); ?></div>
							<div class="span3  value"><?php echo $paciente->aGet(Enfermedad::HIPERTENSION); ?></div>
							<div class="span1" ></div>
							<div class="span3 title "><?php echo Enfermedad::getName(Enfermedad::INSUFICIENCIA_RENAL); ?></div>
							<div class="span3  value"><?php echo $paciente->aGet(Enfermedad::INSUFICIENCIA_RENAL); ?></div>
						</div>
						<div class="row grid">
						    <div class="span3 title "><?php echo Enfermedad::getName(Enfermedad::OBESIDAD); ?></div>
							<div class="span3  value"><?php echo $paciente->aGet(Enfermedad::OBESIDAD); ?></div>
						</div><br/>
						<?php echo Helpers::link( array(
								"app"        => "casodeestudio",
	                            "controller" => "paciente",
	                            "action"     => "editEnf",
	                            "body"       => "Editar Enfermedades",
								"id"         => $paciente->getId(),  
				    			"attrs"		 => array ("class" => "btn primary")) ); ?>
				    	<br>
				    	<br>
				    	<form><input type="button" value="Volver" class="btn" onclick="history.go(-1)"></form>
			        </div>
			     </div>
			  
	
	        <footer>
	          <p>&copy; YuppGIS 2011</p>
	        </footer>
	      </div>
	    </div>
    </body>
</html>