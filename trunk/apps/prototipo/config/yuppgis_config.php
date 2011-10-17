<?php

$srid = 32721;

$google_maps_key = 'ABQIAAAAPiUEqlE8F8uamnkALXUlpBQ9RnTSjqgD0pEXpmEy7NBsQ-MLZBRcZUeaOXZtWgLNdqcDUzhWdmPbfw';
//$google_maps_key = 'ABQIAAAA9a4X6TFheB81m4gfqmoVHRTB4vxG9QIfIaQXdPTsELnQKJj6tRQ_bsOGsHKhljG_DftJC5pb06upWA';

$gisdb = array( YuppConfig::MODE_DEV  => array(
                                     'type'     => YuppConfig::DB_POSTGRES,
                                     'url'      => 'localhost',
                                     'user'     => 'yuppgis',
                                     'pass'     => 'yuppgis',
                                     'database' => 'yupp_dev'
                                   )
);

$yuppgis_mode = YuppGISConfig::MODE_PREMIUM;

?>