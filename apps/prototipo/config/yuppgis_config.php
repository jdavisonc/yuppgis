<?php

$srid = 32721;
$google_maps_key = 'ABQIAAAA9a4X6TFheB81m4gfqmoVHRQl3YGh390M_4O6wxlWQ-a6-Oz5rBRtOqOhlPbudwJAoZZBg_gql7zLpg';

$gisdb = array( YuppConfig::MODE_DEV  => array(
                                     'type'     => YuppConfig::DB_POSTGRES,
                                     'url'      => 'localhost',
                                     'user'     => 'yuppgis',
                                     'pass'     => 'yuppgis',
                                     'database' => 'yupp_dev'
                                   )
);

?>