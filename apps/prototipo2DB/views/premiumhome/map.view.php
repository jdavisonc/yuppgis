<?php echo Helpers::css(array('app'=>'prototipo2DB', 'name' => 'main')) ; ?>

<fieldset>
	<legend>Mapa</legend>
	<?php echo GISHelpers::Map(array(
		MapParams::ID => 1,
		MapParams::CLICK_HANDLERS => array(),
		MapParams::SELECT_HANDLERS => array(),
		MapParams::TYPE => "google"
		
	)); ?>
</fieldset>