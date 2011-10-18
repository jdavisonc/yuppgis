
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

function onlyNumbers(evt) {
	var e = evt
	if(window.event){ // IE
		var charCode = e.keyCode;
	} else if (e.which) { // Safari 4, Firefox 3.0.4
		var charCode = e.which
	}
	if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46 ){
		return false;
	}
	
	return true;
}

/**/


