<?php

$srid = 32721;

$yuppgis_mode = YuppGISConfig::MODE_PREMIUM;
$gis_controller = 'geo';

$gisdb = array( YuppConfig::MODE_DEV  => array(
                                     'type'     => YuppConfig::DB_POSTGRES,
                                     'url'      => 'localhost',
                                     'user'     => 'yuppgis',
                                     'pass'     => 'yuppgis',
                                     'database' => 'gis'
                                   )
);

?>