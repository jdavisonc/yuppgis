<?php

$srid = 32721;
$google_maps_key = 'ABQIAAAAPiUEqlE8F8uamnkALXUlpBROxdxrcLD3lYp40t9TM89VIkUnWRTdXwm_Upj0NJZDmalkMVNX0hfonA';

$gisdb = array( YuppConfig::MODE_DEV  => array(
                                     'type'     => YuppConfig::DB_POSTGRES,
                                     'url'      => 'localhost',
                                     'user'     => 'yuppgis',
                                     'pass'     => 'yuppgis',
                                     'database' => 'yupp_dev'
                                   )
);

?>