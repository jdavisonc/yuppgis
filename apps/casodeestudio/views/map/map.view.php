<?php YuppLoader::load('casodeestudio.model', 'Paciente'); ?>
<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'main')) ; ?>
<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-bootstrap.min')) ; ?>

    <style type="text/css">
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
          <p class="pull-right">Logged in as <a href="#">username</a></p>
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
          <h5>Sidebar</h5>
          <ul>
            <li><a href="#">Link</a></li>
            <li><a href="#">Link</a></li>
            <li><a href="#">Link</a></li>
            <li><a href="#">Link</a></li>
            <li><a href="#">Link</a></li>
            <li><a href="#">Link</a></li>
          </ul>
          <h5>Sidebar</h5>
          <ul>
            <li><a href="#">Link</a></li>
            <li><a href="#">Link</a></li>
          </ul>
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
				MapParams::TYPE => "google"
				
			)); ?>
          </p>
        </div>

        <footer>
          <p>&copy; YuppGIS 2011</p>
        </footer>
      </div>
    </div>