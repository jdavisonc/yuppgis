<h4>Integrantes</h4>
<div style="width: 160px; font-size: 9px">
<table class="zebra-striped">
  <?php 
  	foreach ($attrs as $p) {
  		echo '<tr><td>';
  		echo '<a href="'.Helpers::url( array("app" => "casodeestudio",
                               "controller" => "paciente",
                               "action"     => "info",
                               "id"         => $p['id']) ).'">';
  		echo h('img', array('app'=>'casodeestudio', 'src'=>'paciente-info.png'));
  		echo '</a>';
  		echo '</td><td>'.$p['nombre']." ".$p['apellido']."</td></tr>";
  	}
  	 ?>
</table> 
</div>