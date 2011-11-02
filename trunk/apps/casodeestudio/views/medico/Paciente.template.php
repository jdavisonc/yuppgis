<?php echo Helpers::css(array('app'=>'casodeestudio', 'name' => 'twitter-bootstrap.min')) ; ?>

<h4>Integrantes</h4>
<div style="width: 160px; font-size: 9px">
<table class="zebra-striped">
  <?php 
  	foreach ($attrs as $p) {
  		echo '<tr><td></td><td>'.$p['nombre']." ".$p['apellido']."</td></tr>";
  	}
  	 ?>
</table> 
</div>