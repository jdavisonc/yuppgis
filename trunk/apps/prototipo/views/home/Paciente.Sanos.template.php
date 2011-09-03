<div>
  Estoy en template de capa: <?php echo $layer->getName(); ?>
</div>

 <?php  $element = Paciente::get($elementId); ?>
 Estoy viendo a <?php echo $element->getNombre(); ?> !!