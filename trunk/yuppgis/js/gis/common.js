
function trace(msg, s) {
    if (this.console && typeof console.log != "undefined") {
    	console.log(msg + ": ");
        console.log(s);		        
    }
    
}


function log(msg){

    $('.logarea').append(msg + '<br/>');
    $('.logarea').scrollTop($('.logarea').height());
}
 
function getElementsInLayer(layer,ids){
	var i,feature,len = layer.features.length,foundFeatures=[];
	for(i=0;i<len;i++){
		feature = layer.features[i];
		if(feature&&feature.attributes){
			if($.inArray(feature.attributes['elementId'], ids) > -1){
				foundFeatures.push(feature);
			}
		}
	}

	return foundFeatures;
}

function showFilteredElements(data){
	
		
	$.each(map_1.layers, function(i, layer){
		if (i > 1){
			var elements = getElementsInLayer(layer, ids);

			$.each(elements, function(i, feature){						
				//feature.visibility = false;
				feature.attributes['visibility'] = "hidden";				
				layer.drawFeature(feature);
			});
		}
	});
	
	return false;
}

var handlers = {
		click: [],
		select:[]
};

function addClickHandler(mapId, handler){		
	addHandler(mapId, handler, "click");
}

function addSelectHandler($mapId, $handler){	
	addHandler(mapId, handler, "select");
}

function addHandler(mapId, handler, type){
	var h = {
			mapId: mapId,
			handler: handler
	};	
	var array = handlers[type];
	array.push(h);
}

function removeClickHandler(mapId, handler){
	removeHandler(mapId, handler, "click");
}

function removeSelectHandler(mapId, handler){
	removeHandler(mapId, handler, "select");
}

function removeHandler(mapId, handler, type){
   var array = handlers[type];
   $.each(array, function(i,item) {
      if(item.mapId == mapId && item.handler == handler) {          
          array.splice(i, 1);
      }    
   });
}

