<?php

YuppLoader::load('casodeestudio.model', 'Paciente');
$m = Model::getInstance();

?>

<html>
	<head>
		<title>Salud Digital</title>
		<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-bootstrap.min'));?>
		<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'main'));?>
		<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'jquery.autocomplete'));?>
		<?php echo Helpers::js(array('name' => 'jquery/jquery-1.6.1.min'));?>
		<?php echo Helpers::js(array('app'=>'casodeestudio', 'name' => 'jquery.autocomplete.min'));?>
		<script type="text/javascript">
			$().ready(function() {
				$("#calle").autocomplete("<?php echo $m->get('url_ws_calles');?>", {
					minChars:1,
				    delay:400,
				    cacheLength:100,
				    matchContains:true,
				    max:10,
					selectFirst: false,
					formatItem: function(item) {
						return item.nombre;
					},
					dataType:'json',
					formatMatch:function(item){
				       return item.nombre;
				    },
				    formatResult:function(item){
				       return item.nombre;
				    },
					parse:function(data) {
	                       return $.map(data, function(item) {
	                               return {
	                                       data: item,
	                                       value: item.nombre,
	                                       result: item.nombre
	                               }
	                       });
	               }});
				});
		</script>
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
						<p>
							<strong>Paciente creado!</strong> Se ha creado el paciente con id
							=
							<?php echo $inserted; ?>
							.
						</p>
					</div>
					<?php } else if (isset($error)) { ?>
					<div class="alert-message block-message error">
						<p>
							<strong>Error al crear Paciente!</strong> Cambia los siguiente
							valores y vuelve a intentarlo.
						</p>
						<br>
						<?php if (is_string($error)){ 
									echo '<ul><li>'.$error.'</li></ul>'; 
							  } else { 
							  		echo DisplayHelper::errors( $error ); 
							  } ?>
					</div>
					<?php } ?>
					<h3>Nuevo Paciente</h3>
				</div>
				<div class="row">
					<div class="span12">
						<form>
							<fieldset>
								<h3>Datos Personales</h3>
								<br />
								<div class="clearfix">
									<label for="nombre">Nombre</label>
									<div class="input">
									<?php echo DisplayHelper::text('nombre', ''); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="apellido">Apellido</label>
									<div class="input">
									<?php echo DisplayHelper::text('apellido', ''); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="sexo">Sexo</label>
									<div class="input">
									<?php echo DisplayHelper::select('sexo', array('F'=>'F', 'M'=>'M'), 'F'); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="fechaNacimiento">F.Nacimiento</label>
									<div class="input">
									<?php echo DisplayHelper::text('fechaNacimiento', ''); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="fechaFallecimiento">F.Fallecmiento</label>
									<div class="input">
									<?php echo DisplayHelper::text('fechaFallecimiento', ''); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="telefono">Telefono</label>
									<div class="input">
									<?php echo DisplayHelper::text('telefono', ''); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="xlInput">Email</label>
									<div class="input">
									<?php echo DisplayHelper::text('email', ''); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="ci">Cedula de Identidad</label>
									<div class="input">
									<?php echo DisplayHelper::text('ci', ''); ?>
									</div>
								</div>
								<h3>Ubicacion</h3>
								<br />
								<div class="clearfix">
									<label for="direccion">Calle</label>
									<div class="input">
									<?php echo DisplayHelper::text('calle', '', array ('id' => 'calle')); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="direccion">Numero de puerta</label>
									<div class="input">
									<?php echo DisplayHelper::text('numero_puerta', ''); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="barrio">Barrio</label>
									<div class="input">
									<?php echo DisplayHelper::text('barrio', ''); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="ciudad">Ciudad</label>
									<div class="input">
									<?php echo DisplayHelper::text('ciudad', ''); ?>
									</div>
								</div>
								<div class="clearfix">
									<label for="departamento">Departamento</label>
									<div class="input">
									<?php echo DisplayHelper::text('departamento', ''); ?>
									</div>
								</div>
								<div class="actions">
									<input type="submit" class="btn primary" value="Crear">&nbsp;
									<button type="reset" class="btn">Cancel</button>
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
