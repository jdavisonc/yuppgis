<?php 

YuppLoader::load('casodeestudio.model', 'Medico');
$m = Model::getInstance();
$idMedico = $m->get('id');

?>

<html>
	<head>
		<title>Salud Digital</title>
		<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'main')) ; ?>
		<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-bootstrap.min')) ; ?>
		
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
						<li><?php echo Helpers::link( array(
									"app"        => "casodeestudio",
	                                "controller" => "paciente",
	                                "action"     => "list",
	                                "body"       => "Pacientes") ); ?></li>
						<li class="active"><?php echo Helpers::link( array(
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
	
			<div class="content">
				<!-- Main hero unit for a primary marketing message or call to action -->
				<div class="hero-unit">
					<p>
					<?php echo GISHelpers::Map(array(
					MapParams::ID => 2,
					MapParams::HEIGHT => 400,
					MapParams::WIDTH => 850,
					MapParams::TYPE => "google",
					MapParams::STATE => "medicoId=" . $idMedico,
					MapParams::CENTER => array("-6251096.6093197", "-4149355.4159976"),
					MapParams::ZOOM => 13
	
					)); ?>
					</p>
				</div>
	
				<footer>
				<p>&copy; YuppGIS 2011</p>
				</footer>
			</div>
		</div>
	</body>
</html>