<?php YuppLoader::load('casodeestudio.model', 'Paciente'); ?>
<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'main')) ; ?>
<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-bootstrap.min')) ; ?>

<style>
body {
	padding-top: 60px;
}
</style>

	<div class="topbar">
		<div class="topbar-inner">
			<div class="container-fluid">
				<a class="brand" href="#">Salud Digital</a>
				<ul class="nav">
					<li class="active"><a href="#">Home</a></li>
					<li><a href="#about">About</a></li>
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
        <div class="well">
          <h5>Sidebar</h5>
          <ul>
            <li><a href="#">Link</a></li>
            <li><a href="#">Link</a></li>
            <li><a href="#">Link</a></li>
            <li><a href="#">Link</a></li>
          </ul>
        </div>
      </div>
      <div class="content">
        <!-- Main hero unit for a primary marketing message or call to action -->
        <div class="hero-unit">
			<section id="forms">
			  <div class="page-header">
			    <h3>Nuevo Paciente</h3>
			  </div>
			  <div class="row">
			    <div class="span12">
		          	<form>
		          		<fieldset>
							<div class="clearfix">
					           <label for="xlInput">Nombre</label>
					           <div class="input">
					             <?php echo DisplayHelper::text('nombre', ''); ?>
					           </div>
					        </div>
							<div class="clearfix">
					           <label for="xlInput">Apellido</label>
					           <div class="input">
					             <?php echo DisplayHelper::text('appelido', ''); ?>
					           </div>
					        </div>
							<div class="clearfix">
					           <label for="xlInput">Sexo</label>
					           <div class="input">
					             <?php echo DisplayHelper::select('sexo', array('F'=>'F', 'M'=>'M'), 'F'); ?>
					           </div>
					        </div>
							<div class="clearfix">
					           <label for="xlInput">F.Nacimiento</label>
					           <div class="input">
					             <?php echo DisplayHelper::text('fechaNacimiento', ''); ?>
					           </div>
					        </div>
							<div class="clearfix">
					           <label for="xlInput">F.Fallecmiento</label>
					           <div class="input">
					             <?php echo DisplayHelper::text('fechaFallecimiento', ''); ?>
					           </div>
					        </div>
							<div class="clearfix">
					           <label for="xlInput">Telefono</label>
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
							<div class="actions">
								<input type="submit" class="btn primary" value="Crear">&nbsp;<button type="reset" class="btn">Cancel</button>
							</div>
		          		</fieldset>
		          	</form>
		        </div>
		     </div>
		  </section>
		  
        </div>

        <footer>
          <p>&copy; YuppGIS 2011</p>
        </footer>
      </div>
    </div>