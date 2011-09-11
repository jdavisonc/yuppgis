
function trace(msg, s) {
    if (this.console && typeof console.log != "undefined") {
    	console.log(msg + ": ");
        console.log(s);		        
    }
}

function defaultClickHandler(event){trace("default click handler", event);}
function defaultDoubleClickHandler(event){trace("default double click handler", event);}
 
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
	
	var log = '';
	var ids = [];
	$.each(data, function(i, item){
		ids.push(item.id);
		log += item.id + '. ' + item.nombre + '<br/>';
	});
	$('#resultsDiv').html(log);
		
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