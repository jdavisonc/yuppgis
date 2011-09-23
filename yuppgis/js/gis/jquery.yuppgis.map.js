;
(function($) {

	
	
	$.fn.YuppGISMap = function(mapOptions) {

		if (this.length > 1) {
			this.each(function() {
				$(this).YuppGISMap(mapOptions)
			});
			return this;
		}

		var instance = this;
		var selectcontrol, selectedFeature, map, drawControls;	
		/*
		 * var defaultOptions = { test: false };
		 * 
		 * var options = $.extend({}, defaultOptions, mapOptions);
		 */
		

		var _handlers = {
			click : [],
			select : []
		};
		
		var initialize = function(){
			
			
			 var $this = $(this);
             var data = $this.data('map');
             
             if ( !data){
            	 var id = mapOptions.id;
			

					 OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {                
			                defaultHandlerOptions: {
			                    "single": true,
			                    "double": true,
			                    "pixelTolerance": 0,
			                    "stopSingle": true,
			                    "stopDouble": true
			                },

	                initialize: function(options) {
	                    this.handlerOptions = OpenLayers.Util.extend(
	                        {}, this.defaultHandlerOptions
	                    );
	                    OpenLayers.Control.prototype.initialize.apply(
	                        this, arguments
	                    ); 
	                    this.handler = new OpenLayers.Handler.Click(
	                        this, {
	                            "click": function(evt){
	                            	$.each(_handlers.click, function(i, item){	                            		
	                            			
	                            			if (eval("typeof "+ item.handler) == 'function'){
	                            				log("Click: Llamo a " + item.handler);
	                            				window[item.handler](evt);	
	                            			}else{
	                            				log("Click: Llamo a " + item.handler + ' (no existe)');
	                            			}	                            			
	                            		
									});
								} 
	                            
	                        }, this.handlerOptions
	                    );
	                }, 

	               

	        });
	        	
				
			
					
					 $.ajax({
				      url:"/yuppgis/prototipo/Home/getLayersAction?mapId=" + id,				      
				      success: function(data){
				      		
			 				var google = new OpenLayers.Layer.Google( "Google", { type: G_HYBRID_MAP } );
					
							var options = {
								minResolution: "auto",
								minExtent: new OpenLayers.Bounds(-1, -1, 1, 1),
								maxResolution: "auto",
								maxExtent: new OpenLayers.Bounds(-180, -90, 180, 90),
							};
							map = new OpenLayers.Map("map_" + id, options );
			
			 			 	map.addLayer(google);
							map.zoomToMaxExtent();

							var click = new OpenLayers.Control.Click();
	                		map.addControl(click);
	                		click.activate();						
											
			                var wms = new OpenLayers.Layer.WMS( "OpenLayers WMS","http://labs.metacarta.com/wms/vmap0?", 
			                {
			                	layers: "basic"
							});
							
							map.addLayer(wms);
							var vector = [];
						    $.each(data, function(i, item){
			                	var layerurl =  "/yuppgis/prototipo/Home/mapLayer?layerId=" + item.id;
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
	                								rendererOptions: {zIndexing: true}
										        });		
										        
								vector.push(kml);
					            
							});  
							map.addLayers(vector);
							map.addControl(new OpenLayers.Control.EditingToolbar(vector));
							
							 
										
							selectcontrol = new OpenLayers.Control.SelectFeature(vector, {				                
					    	    onSelect: function(feature){
					    	    	onFeatureSelect(feature);
	                            	$.each(_handlers.select, function(i, item){
	                            		
	                            			
		                            		if ( eval("typeof "+ item.handler) == 'function'){
		                            			log("Select: Llamo a " + item.handler);
		                            			window[item.handler](feature);	
	                            			}else{
	                            				log("Select: Llamo a " + item.handler + ' (no existe)');
	                            			}	
		                            	
									});
	                            		
								} , 
					            onUnselect: onFeatureUnselect,			             
					
		                        clickout: false, toggle: false,
		                        multiple: false, hover: false,
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
				
	            selectcontrol.unselect(selectedFeature);
	        }
	        
	        function onFeatureSelect(feature) {      	
	        	
	        	
	        	 $.ajax({
				      url:"/yuppgis/prototipo/home/details",
				      data: {
				      	layerId: feature.attributes.layerId,
				      	className:feature.attributes.className,
				      	elementId:feature.attributes.elementId
				      },			      			      			      
				      success: function(data){
					    var html = data;
					    if (data == ''){
					    	html = feature.attributes.description;
					    };
			            selectedFeature = feature;
			            feature.popup  = new OpenLayers.Popup.FramedCloud("popup_"+id+"_" + feature.attributes.elementId , 
			                                     feature.geometry.getBounds().getCenterLonLat(),
			                                     new OpenLayers.Size(100,100),
			                                     html,
			                                     null, true, onPopupClose);
			            
			            map.addPopup(feature.popup );
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

		var _addHandler = function(handler, type) {
			var h = {				
				handler : handler
			};
			var array = _handlers[type];
			array.push(h);	
			
			return instance;
		};

		var _removeHandler = function(handler, type) {
			var array = _handlers[type];
			$.each(array, function(i, item) {
				if (item.handler == handler) {
					array.splice(i, 1);
				}
			});
			
			return instance;
		};

		/* public methods */

		this.addClickHandler = function(handler) {
			return _addHandler(handler, "click");
		};

		this.addSelectHandler = function(handler) {
			return _addHandler(handler, "select");
		};

		this.removeClickHandler = function(handler) {
			return _removeHandler(handler, "click");
		};

		this.removeSelectHandler = function(handler) {
			return _removeHandler(handler, "select");
		};

		this.getHandlers = function() {
			return _handlers;
		}
		
		

		return initialize();

	};
})(jQuery);














