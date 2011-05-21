<?php
 // Modelo pasado desde el controlador
 $m = Model::getInstance();
?>
<html>
  <head>
    <style>
	  table {
	    border: 1px solid;
	  }
	  td {
	    border: 1px solid;
		padding: 5px;
	  }
	</style>
	<?php echo h('js', array('name'=>'jquery/jquery-1.5.min')); ?>
      
    <script type="text/javascript">
      // Handlers para JQuery
      var before_function = function(req, json) {
           
        $('#estado').html( "Cargando..." );
      }
      var after_function = function(json) {

        var libro = json;
		alert(libro.titulo +' ('+ libro.genero +')');
		$('#estado').html( "" );
      }
    </script>
  </head>
 <body>
   <h1>Libros</h1>
   <div id="estado"></div>
   <table>
     <!-- El controlador puso la lista de libros en la clave 'libros' -->
     <?php foreach( $m->get('libros') as $libro) : ?>
       <tr>
         <td><?php echo $libro->getTitulo(); ?></td>
         <td><?php echo $libro->getGenero(); ?></td>
         <td><?php echo $libro->getIdioma(); ?></td>
		 <td><?php echo Helpers::ajax_link( array( "component"  => "biblioteca",
                                      "controller" => "libro",
                                      "action"     => "jsonShow",
                                      "id"         => $libro->getId(),
                                      "body"       => "Obtener datos por Ajax",
                                      "after"      => "after_function",
                                      "before"     => "before_function" )  ); ?>
			
		</td>
       </tr>
     <?php endforeach; ?>
   </table>
   
   <?php /*
   <?php foreach( $m->get('libros') as $libro) : ?>
   <?php Helpers::template( array("controller" => "libro",  
                                  "name"       => "details",  
                                  "args"       => array("libro" => $libro)  
                ) ); ?>
   <?php endforeach; ?>
   */ ?>
   
 </body>
</html>
