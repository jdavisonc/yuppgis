
/*Logging*/
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

function extractIds(data){
	var ids = [];
	$.each(data, function(i, item){
		ids.push(item.id);
	});
	
	return ids;
}
 
/**/


