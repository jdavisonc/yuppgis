<?php

$srid = 900913;

//$google_maps_key = 'ABQIAAAAPiUEqlE8F8uamnkALXUlpBQ9RnTSjqgD0pEXpmEy7NBsQ-MLZBRcZUeaOXZtWgLNdqcDUzhWdmPbfw';
$google_maps_key = 'ABQIAAAA9a4X6TFheB81m4gfqmoVHRTB4vxG9QIfIaQXdPTsELnQKJj6tRQ_bsOGsHKhljG_DftJC5pb06upWA';
//$google_maps_key = 'ABQIAAAA9a4X6TFheB81m4gfqmoVHRQl3YGh390M_4O6wxlWQ-a6-Oz5rBRtOqOhlPbudwJAoZZBg_gql7zLpg';

$gisdb = array( YuppConfig::MODE_DEV  => array(
                                     'type'     => YuppConfig::DB_POSTGRES,
                                     'url'      => 'localhost',
                                     'user'     => 'yuppgis',
                                     'pass'     => 'yuppgis',
                                     'database' => 'casodeestudio'
                                   )
);

$yuppgis_mode = YuppGISConfig::MODE_PREMIUM;
$gis_controller = 'map';

// Configuracion WMS
$wms_url = 'http://localhost/cgi-bin/mapserv';
$wms_map_file = '/home/yuppgis/workspace/YuppGis/apps/casodeestudio/config/casodeestudio.map';
$wms_layers = 'departamento,manzanas,calles,espaciosLibres';
$wms_format = 'aggpng24';

?>