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




				$.ajax({
					url: "/yuppgis/prototipo/Home/getLayersAction?mapId=" + id,
					success: function (data) {

						var google = new OpenLayers.Layer.Google("Google", {
							type: G_HYBRID_MAP
						});

						var options = {
								minResolution: "auto",
								minExtent: new OpenLayers.Bounds(-1, -1, 1, 1),
								maxResolution: "auto",
								maxExtent: new OpenLayers.Bounds(-180, -90, 180, 90),
						};
						map = new OpenLayers.Map("map_" + id, options);
						var wms = new OpenLayers.Layer.WMS("WMS",
				                "http://localhost/cgi-bin/mapserv?",
				                {
				                    map: '/home/yuppgis/workspace/YuppGis/yuppgis/yuppgis.map',
				                    layers: 'departamento,manzanas',
				                    format: 'aggpng24',
				                    transparent: 'on'
				                },
				                {
				                	minResolution: "auto",
									minExtent: new OpenLayers.Bounds(-1, -1, 1, 1),
									maxResolution: "auto",
									
									 maxExtent: new OpenLayers.Bounds(324000, 6100000, 663000, 6614430),
					                    scales: [5000, 10000, 25000, 50000, 100000, 250000, 500000,
					                         1000000, 2500000, 5000000, 10000000, 25000000, 50000000, 100000000],
				                    units: 'm',
				                    projection: "EPSG:32721",
				                    gutter: 0,
				                    ratio: 1/*,
				                    wrapDateLine: true,
				                    isBaselayer: true,
				                    singleTile: true,
				                    transitionEffect: 'resize',
				                    queryVisible: true
									*/
				                });

						if(mapOptions.type == 'google'){
							map.addLayer(google);
						}else{
							map.addLayer(wms);
						}						
						
						map.zoomToMaxExtent();

						var click = new OpenLayers.Control.Click();
						map.addControl(click);
						click.activate();

						
						var vector = [];
						$.each(data, function (i, item) {
							var layerurl = "/yuppgis/prototipo/Home/mapLayer?layerId=" + item.id;
							var kml = new OpenLayers.Layer.Vector(item.id, {
								strategies: [new OpenLayers.Strategy.Fixed()],
								protocol: new OpenLayers.Protocol.HTTP({
									url: layerurl,
									format: new OpenLayers.Format.KML({
										extractStyles: true,
										extractAttributes: true,
										maxDepth: 5
									})
								}),
								rendererOptions: {
									zIndexing: true
								}
							});

							vector.push(kml);

						});
						map.addLayers(vector);
						map.addControl(new OpenLayers.Control.EditingToolbar(vector));



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

							clickout: false,
							toggle: false,
							multiple: true,
							hover: false,
							toggleKey: "ctrlKey",

							multipleKey: "shiftKey",

							box: true

						});


						map.addControl(selectcontrol);
						selectcontrol.activate();

						map.setCenter(new OpenLayers.LonLat(-56.181944, -34.883611), 15);

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


					$.ajax({
						url: "/yuppgis/prototipo/home/details",
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

				function onFeatureUnselect(feature) {

					map.removePopup(feature.popup);

					feature.popup.destroy();
					feature.popup = null;

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

		this.hideFeatures = function(featureIds){
			var fids = [];
			for(var d = 0; d<featureIds.length; d++){
				fids.push(featureIds[d].toString());
			}
			for (var i=0;i< map.layers.length;i++){
				var layer = map.layers[i];
				if (layer.features != undefined){

					for (var j=0; j<layer.features.length;j++){
						var id = layer.features[j].attributes['elementId'];

						if ($.inArray(id,fids) >= 0){	        			   
							layer.features[j].style.display = 'none';												
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
						var id = layer.features[j].attributes['elementId'];

						if ($.inArray(id,fids) >= 0){	        			   
							layer.features[j].style.display = '';
						}else{
							if (hideNonMatching){
								layer.features[j].style.display = 'none';		
							}
						}
					}

					layer.redraw();
				}
			}

		}

		return initialize();

	};
})(jQuery);