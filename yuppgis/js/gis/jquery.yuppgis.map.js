;
(function ($) {


	/*Plugin para representar el mapa y sus elementos */
	$.fn.YuppGISMap = function (mapOptions) {

		/*En principio puedo crear un mapa por cada elemento resultante del selector jquery*/
		if (this.length > 1) {
			this.each(function () {
				$(this).YuppGISMap(mapOptions)
			});
			return this;
		}

		var instance = this;
		var selectcontrol, map, drawControls;
		var selectedFeatures = [];

		/*Acá voy a mantener los handlers asociados a los eventos de click y select, se podrian agregar de otro tipo*/
		var _handlers = {
				click: [],
				select: []
		};

		/*Método de inicialización del plugin*/
		var initialize = function () {

			var $this = $(this);
			var data = $this.data('map');

			/* Si no tengo un mapa asociado al elemento del dom, inicializo todo*/
			/* En caso de que ya exista, voy directo a los métodos aplicables al mapa */
			if (!data) {
				
				/*Proceso los parámetros de entrada*/
				var id = mapOptions.id;
				var appName = mapOptions.appName;
				var controllerName = mapOptions.controllerName;
				var state = mapOptions.state;
				var srid = "EPSG:" + mapOptions.srid;
				var center = mapOptions.center;
				var zoom = mapOptions.zoom;
				var sphericalMercator = mapOptions.sphericalMercator;

				
				OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {
					/*Habilito los eventos sobre el mapa*/
					defaultHandlerOptions: {
						"single": true,
						"double": true,
						"pixelTolerance": 0, /*Este valor indica que tan preciso tengo que ser en el click, 0 es la máxima precisión*/
						"stopSingle": true,
						"stopDouble": true
					},
					/*Registro los handlers para los eventos sobre el mapa*/
					initialize: function (options) {
						this.handlerOptions = OpenLayers.Util.extend({}, this.defaultHandlerOptions);
						OpenLayers.Control.prototype.initialize.apply(
								this, arguments);
						this.handler = new OpenLayers.Handler.Click(
								this, {
									"click": function (evt) {
										$.each(_handlers.click, function (i, item) {

											if (eval("typeof " + item.handler) == 'function') {
												log("Click: Llamo a " + item.handler);
												window[item.handler](evt);
											} else {
												log("Click: Llamo a " + item.handler + ' (no existe)');
											}

										});
									}

								}, this.handlerOptions);
					},



				});
				
				/*Hack para mantener el estado en el querystring*/
				if (state != "") {
					state = "&" + state;
				}
				
				/*Procedo a obtener las capas que quiero mostrar*/
				$.ajax({
					
					url: "/yuppgis/" + appName + "/" + controllerName + "/getLayersAction?mapId=" + id + state,
					
					success: function (data) {

						/*Si obtuve las capas, declaro el elemento mapa con sus parámetros de visualización*/
						map = new OpenLayers.Map("map_" + id, {
					         projection: srid,
					         maxResolution: 156543.0339,
					         units: 'm',
					         maxExtent: new OpenLayers.Bounds(-20037508.34, -20037508.34,
					                                          20037508.34, 20037508.34)

						});
						
						/*De acuerdo al tipo que me indiquen, declaro una capa base de google o personalizada (mapserver)*/
						var layer;
						if(mapOptions.type == 'google'){
							layer = new OpenLayers.Layer.Google("Google", {
								sphericalMercator: sphericalMercator
							});
						}else{
							/*En caso de ser personalizada, la obtengo de una acción predeterminada que permite mantener el control sobre lo que se ve*/
							layer = new OpenLayers.Layer.WMS("WMS",
									"/yuppgis/" + appName + "/" + controllerName + "/mapServer", { });
						}
						
						/*Finalmente agrego la capa base al mapa*/
						map.addLayer(layer);
						
						/*Podría especificar estilos por defecto para el mapa que serían utilizados por los controles*/
						var styleMap = new OpenLayers.StyleMap({
							/*"default":{fillColor: 'blue'}, 
							"select": {fillColor: 'red'}*/
						});

						/*Agrego el control que permite clickear sobre los elementos del mapa*/
						var click = new OpenLayers.Control.Click();
						map.addControl(click);
						click.activate();
						
						/*Procedo a obtener las capas no-base*/
						var vector = [];
						$.each(data, function (i, item) {
							/*Construyo la url de cada capa*/
							var layerurl = "/yuppgis/" + appName + "/" + controllerName + "/mapLayer?layerId=" + item.id + state;
							
							/*y mediante la capa de tipo vector le indico a openlayers que obtenga cada una de las capas en formato kml*/
							var kml = new OpenLayers.Layer.Vector(item.id, {
								strategies: [new OpenLayers.Strategy.Fixed()],
								protocol: new OpenLayers.Protocol.HTTP({
									url: layerurl,
									format: new OpenLayers.Format.KML({
										extractStyles: true,
										extractAttributes: true,
										maxDepth: 2
									})
								}),
								rendererOptions: {
									zIndexing: true
								},
								styleMap: styleMap
							});

							/*Finalmente agrego el kml obtenido al vector de capas no-base*/
							vector.push(kml);

						});

						/*Declaro una capa extra para mantener la edición de figuras*/
						vlayer = new OpenLayers.Layer.Vector("Editing", {
								styleMap: styleMap
						});
						map.addControl(new OpenLayers.Control.EditingToolbar(vlayer));
						
						/*y la agrego al vector de capas no-base*/
						vector.push(vlayer);
						
						/*Agrego todas las capas no-base al mapa*/
						map.addLayers(vector);

						/*Agrego un control que muestra las coordenadas del puntero del mouse*/
						map.addControl(new
						 OpenLayers.Control.MousePosition({displayProjection:
						 map.baseLayer.projection}));
						
						/*Agrego un control que me permite seleccionar elementos del mapa*/
						selectcontrol = new OpenLayers.Control.SelectFeature(vector, {
							/*y le asocio los handlers correspondientes*/
							onSelect: function (feature) {
								onFeatureSelect(feature);
								$.each(_handlers.select, function (i, item) {
									if (eval("typeof " + item.handler) == 'function') {
										log("Select: Llamo a " + item.handler);
										window[item.handler](feature);
									} else {
										log("Select: Llamo a " + item.handler + ' (no existe)');
									}
								});
							},
							onUnselect: onFeatureUnselect,

							clickout: true,
							toggle: true,
							multiple: true,
							hover: false,
							toggleKey: "ctrlKey",

							multipleKey: "shiftKey",

							box: true
						});						

						map.addControl(selectcontrol);
						selectcontrol.activate();

						map.setCenter(new OpenLayers.LonLat(center[0], center[1]), zoom);

						instance.map = map;
					}
				});


				/*Función para atender el evento de cierre de un popup*/				
				function onPopupClose(evt) {                    	

					var selectedFeature = null;

					for (var i=0;i<selectedFeatures.length;i++){                    		                    		
						var elementId = selectedFeatures[i].attributes.elementId;                    		
						var closeName = "popup_" + id +"_" + elementId + "_close";

						if (evt.currentTarget.id == closeName){
							selectedFeature = selectedFeatures[i];
							selectcontrol.unselect(selectedFeature);

							evt.stopPropagation();

							break;
						}
					}


				}

				/*Función para obtener información sobre el elemento seleccionado*/
				/*Por defecto se llama a la acción details del controlador asociado al mapa*/
				function onFeatureSelect(feature) {

					if( feature.attributes.gisType == 'yuppgis_type_point' ){
						log("Select: Llamo a selectHandler");

						$.ajax({
							url: "/yuppgis/" + appName + "/" + controllerName + "/details",
							data: {
								layerId: feature.attributes.layerId,
								className: feature.attributes.className,
								elementId: feature.attributes.elementId
							},
							success: function (data) {
								/*Una vez obtenida la información, la muestro como html en un popup*/
								var html = data;
								if (data == '') {
									html = feature.attributes.description;
								};

								feature.popup = new OpenLayers.Popup.FramedCloud("popup_" + id + "_" + feature.attributes.elementId, 
										feature.geometry.getBounds().getCenterLonLat(), 
										new OpenLayers.Size(100, 100), html, null, true, onPopupClose);

								selectedFeatures.push(feature);

								map.addPopup(feature.popup);
							}
						});
					}
				}

				/*Acción para manejar el evento de de-seleccionar un elemento*/
				function onFeatureUnselect(feature) {
					if( feature.attributes.gisType == 'yuppgis_type_point' ){
						log("Select: Llamo a unselectHandler");
						map.removePopup(feature.popup);

						feature.popup.destroy();
						feature.popup = null;

					}

				}

				instance.map = map;
				/*Guardo el mapa como data-attribute del elemento del dom para poder encadenar funciones*/
				$this.data('map', instance)

				data = instance;
			}

			return data;


		}

		/* Métodos privados del plugin */

		/*Agrega el handler del tipo especificado en type*/
		var _addHandler = function (handler, type) {
			var h = {
					handler: handler
			};
			var array = _handlers[type];
			array.push(h);

			return instance;
		};

		/*Elimina el handler especificado*/
		var _removeHandler = function (handler, type) {
			var array = _handlers[type];
			$.each(array, function (i, item) {
				if (item.handler == handler) {
					array.splice(i, 1);
				}
			});

			return instance;
		};

		/* Métodos públicos */

		/*Agrega un handler de click al mapa*/
		this.addClickHandler = function (handler) {
			return _addHandler(handler, "click");
		};

		/*Agrega un handler de select al mapa*/
		this.addSelectHandler = function (handler) {
			return _addHandler(handler, "select");
		};

		/*Elimina el handler de click del mapa*/
		this.removeClickHandler = function (handler) {
			return _removeHandler(handler, "click");
		};

		/*Elimina el handler de select del mapa*/
		this.removeSelectHandler = function (handler) {
			return _removeHandler(handler, "select");
		};

		/*Obtiene todos los handlers registrados*/
		this.getHandlers = function () {
			return _handlers;
		}

		/*Obtiene las capas visibles en el mapa*/
		this.getVisibleLayers = function(){
			var layers = [];
			for (var i=0;i< map.layers.length;i++){
				if(map.layers[i].visibility){
					layers.push(map.layers[i]);
				}
			}
			return layers;
		}		

		/*Oculta los elementos especificados por id*/
		this.hideFeatures = function(featureIds){
			var fids = [];
			for(var d = 0; d<featureIds.length; d++){
				fids.push(featureIds[d].toString());
			}
			for (var i=0;i< map.layers.length;i++){
				var layer = map.layers[i];
				if (layer.features != undefined){

					for (var j=0; j<layer.features.length;j++){
						if( layer.features[j].attributes.gisType == 'yuppgis_type_point' ){
							var id = layer.features[j].attributes['elementId'];

							if ($.inArray(id,fids) >= 0){	        			   
								layer.features[j].style.display = 'none';												
							}
						}
					}
					layer.redraw();
				}
			}

		}

		/*Vuelve visibles los elementos especificados por id*/
		/*Si se indica, mediante hideNonMatching, oculta aquellos que no fueron especificados*/
		this.showFeatures = function(featureIds, hideNonMatching){
			var fids = [];
			for(var d = 0; d<featureIds.length; d++){
				fids.push(featureIds[d].toString());
			}
			for (var i=0;i< map.layers.length;i++){
				var layer = map.layers[i];
				if (layer.features != undefined){

					for (var j=0; j<layer.features.length;j++){
						if(layer.features[j].attributes.gisType == 'yuppgis_type_point' ){
							var id = layer.features[j].attributes['elementId'];

							if ($.inArray(id,fids) >= 0){	        			   
								layer.features[j].style.display = '';
							}else{
								if (hideNonMatching){
									layer.features[j].style.display = 'none';		
								}
							}
						}
					}

					layer.redraw();
				}
			}

		}

		/*Obtiene un json que contiene toda la información de visualización asociada al mapa*/		
		this.getVisualizationState = function(){

			var mapId = mapOptions.id;

			var model = {
					checkboxes: [],				
					textboxes: [],
					selects: [],
					log: '(no previous log)',
					map: {
						layers: [],
						elements: []
					} 			
			};

			$('input[type=checkbox][data-attr-mapid=' + mapId + ']:checked').each(function(){ 
				model.checkboxes.push(this.id); 
			});
			$('input[type=text][data-attr-mapid=' + mapId + ']').each(function(){ 
				model.textboxes.push({
					id: this.id, 
					text: $(this).val() 
				});
			});
			var logDiv = $('div[data-attr-mapid=' + mapId + '].logarea');
			if(logDiv){
				model.log = logDiv.text()
			}			

			$('select[data-attr-mapid=' + mapId + ']').each(function(){ 
				model.selects.push({
					id: this.id,
					value: this.value
				});				
			});			

			for (var i=0;i< map.layers.length;i++){
				var layer = map.layers[i]; 
				if(layer.visibility){
					model.map.layers.push(layer.name);
				}

			}

			log('State obtained');

			return model;
		}

		/*Restura la información de visualización asociada al mapa especificada en state*/
		this.loadVisualizationState = function(state){

			var mapId = mapOptions.id;

			/* clear */
			$('input[type=checkbox][data-attr-mapid=' + mapId + ']').each(function(){this.checked = false;});			
			$('input[type=text][data-attr-mapid=' + mapId + ']').each(function(){this.text = '';});
			var logDiv = $('div[data-attr-mapid=' + mapId + '].logarea');
			if(logDiv){
				logDiv.text('');
			}
			$('select[data-attr-mapid=' + mapId + ']').each(function(){ $(this).val('')});			

			/* set */
			$.each(state.checkboxes, function(i, item){ $('#'+item).attr('checked', 'checked'); });
			$.each(state.textboxes, function(i, item){ $('#'+item.id).val(item.text); });
			$.each(state.selects, function(i, item){ $('#'+item.id).val(item.value); });

			for (var i=0;i< map.layers.length;i++){
				map.layers[i].setVisibility(false);
			}			

			for(var d = 0; d<state.map.layers.length; d++){
				var layer = map.getLayersByName(state.map.layers[d]);
				if( layer){
					layer[0].setVisibility(true);
				}

			}	
			// map.showFeatures(state.map.features, true);


			log('State Loaded');
		}

		return initialize();

	};
})(jQuery);