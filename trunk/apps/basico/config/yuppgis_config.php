<?php

$srid = 32721;
$google_maps_key = 'ABQIAAAA9a4X6TFheB81m4gfqmoVHRQl3YGh390M_4O6wxlWQ-a6-Oz5rBRtOqOhlPbudwJAoZZBg_gql7zLpg';
$yuppgis_mode = YuppGISConfig::MODE_BASIC;

$basic_url = 'http://localhost/yuppgis/prototipo/Home/mapLayer?layerId=1&id={id}&owner={ownerName}&attr={attr}&op={op}';
$basic_get_url = 'http://maps.google.com/maps/ms?vpsrc=0&ctz=120&vps=3&jsv=373i&ie=UTF8&authuser=0&msa=0&output=kml&msid=206095476364368189916.0004af9bb2e37ef577a5e';
//$basic_get_url = 'http://localhost/yuppgis/prototipo/service/getElement?id={id}&owner={ownerName}&attr={attr}&class={class}&op={op}';
$basic_save_url = 'http://localhost/yuppgis/prototipo/service/saveElement?owner={ownerName}&attr={attr}&op={op}';
$basic_delete_url = 'http://localhost/yuppgis/prototipo/Home/mapLayer?layerId=1&id={id}&owner={ownerName}&attr={attr}&op={op}';

?>