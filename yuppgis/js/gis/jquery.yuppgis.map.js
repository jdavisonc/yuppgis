;
(function ($) {



	$.fn.YuppGISMap = function (mapOptions) {

		if (this.length > 1) {
			this.each(function () {
				$(this).YuppGISMap(mapOptions)
			});
			return this;
		}

		var instance = this;
		var selectcontrol, map, drawControls;
		var selectedFeatures = [];
		/*
		 * var defaultOptions = { test: false };
		 * 
		 * var options = $.extend({}, defaultOptions, mapOptions);
		 */


		var _handlers = {
				click: [],
				select: []
		};

		var initialize = function () {


			var $this = $(this);
			var data = $this.data('map');

			if (!data) {
				var id = mapOptions.id;
				var appName = mapOptions.appName;
				var controllerName = mapOptions.controllerName;
				var state = mapOptions.state;
				var srid = "EPSG:" + mapOptions.srid;
				var center = mapOptions.center;
				var zoom = mapOptions.zoom;

				OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {
					defaultHandlerOptions: {
						"single": true,
						"double": true,
						"pixelTolerance": 0,
						"stopSingle": true,
						"stopDouble": true
					},

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
				
				if (state != "") {
					state = "&" + state;
				}
				
				$.ajax({
					
					url: "/yuppgis/" + appName + "/" + controllerName + "/getLayersAction?mapId=" + id + state,
					
					success: function (data) {

						map = new OpenLayers.Map("map_" + id, {
					         projection: srid,
					         maxResolution: 156543.0339,
					         units: 'm',
					         maxExtent: new OpenLayers.Bounds(-20037508.34, -20037508.34,
					                                          20037508.34, 20037508.34)

						});
						
						var layer;
						if(mapOptions.type == 'google'){
							layer = new OpenLayers.Layer.Google("Google", {
								sphericalMercator: true
							});
						}else{
							layer = new OpenLayers.Layer.WMS("WMS",
									"/yuppgis/" + appName + "/" + controllerName + "/mapServer", { });
						}
						map.addLayer(layer);
						

						var styleMap = new OpenLayers.StyleMap({
							/*"default":{fillColor: 'blue'}, 
							"select": {fillColor: 'red'}*/
						});

						var click = new OpenLayers.Control.Click();
						map.addControl(click);
						click.activate();
						

						var vector = [];
						$.each(data, function (i, item) {
							var layerurl = "/yuppgis/" + appName + "/" + controllerName + "/mapLayer?layerId=" + item.id + state;
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

							vector.push(kml);

						});

						
						vlayer = new OpenLayers.Layer.Vector("Editing", {
								styleMap: styleMap
						});
						
						vector.push(vlayer);
						
						map.addControl(new OpenLayers.Control.EditingToolbar(vlayer));

						map.addLayers(vector);

						map.addControl(new
						 OpenLayers.Control.MousePosition({displayProjection:
						 map.baseLayer.projection}));
						
						
						selectcontrol = new OpenLayers.Control.SelectFeature(vector, {
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

				function onFeatureUnselect(feature) {
					if( feature.attributes.gisType == 'yuppgis_type_point' ){
						log("Select: Llamo a unselectHandler");
						map.removePopup(feature.popup);

						feature.popup.destroy();
						feature.popup = null;

					}

				}

				instance.map = map;
				$this.data('map', instance)

				data = instance;
			}

			return data;


		}

		/* private methods */

		var _addHandler = function (handler, type) {
			var h = {
					handler: handler
			};
			var array = _handlers[type];
			array.push(h);

			return instance;
		};

		var _removeHandler = function (handler, type) {
			var array = _handlers[type];
			$.each(array, function (i, item) {
				if (item.handler == handler) {
					array.splice(i, 1);
				}
			});

			return instance;
		};

		/* public methods */

		this.addClickHandler = function (handler) {
			return _addHandler(handler, "click");
		};

		this.addSelectHandler = function (handler) {
			return _addHandler(handler, "select");
		};

		this.removeClickHandler = function (handler) {
			return _removeHandler(handler, "click");
		};

		this.removeSelectHandler = function (handler) {
			return _removeHandler(handler, "select");
		};

		this.getHandlers = function () {
			return _handlers;
		}

		this.getVisibleLayers = function(){
			var layers = [];
			for (var i=0;i< map.layers.length;i++){
				if(map.layers[i].visibility){
					layers.push(map.layers[i]);
				}
			}
			return layers;
		}		

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