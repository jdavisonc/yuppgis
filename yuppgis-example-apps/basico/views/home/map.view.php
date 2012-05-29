<?php echo Helpers::css(array('app'=>'basico', 'name' => 'main')) ; ?>

<fieldset>
	<legend>Mapa</legend>
	<?php echo GISHelpers::Map(array(
		MapParams::ID => 1,
		MapParams::CLICK_HANDLERS => array(),
		MapParams::SELECT_HANDLERS => array(),
		MapParams::TYPE => "google",
		MapParams::SPHERICAL_MERCATOR => false,
		MapParams::CENTER => array("-56.19400", "-34.90219"),
		MapParams::ZOOM => 15,
		MapParams::HEIGHT => 600,
		MapParams::WIDTH => 1100
		
	)); ?>
</fieldset>