truncate padron;
truncate padron_ubicacion_geo;
insert into padron (id, calle, numero, padron, ubicacion_id, "class", deleted) select gid, nom_calle, num_puerta, padron, gid, 'Padron', false from v_mdg_accesos;
insert into padron_ubicacion_geo (id, geom) select gid, ST_Transform(ST_SetSRID(the_geom, 32721), 900913) from v_mdg_accesos;