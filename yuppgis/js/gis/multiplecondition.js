function removeNewCondition(control){
	$(control).parent('.newcondition').remove();
}

function addNewCondition(control, paramAND, paramOR){

	var labelAND ='AND';
	var labelOR ='OR';
	
	if (paramAND != null) {
		labelAND = paramAND;
	}
	
	if (paramOR != null) {
		labelOR = paramOR;
	}
	var condselect = '<select class="booleanselect"><option value="and">' + labelAND + '</option><option value="or">' + labelOR + '</option></select>';

	var orig_select = $(control).parents('.conditionfilter:first').find('.conditionselect').first();	
	var select = '<select class="conditionselect" data-attr-mapid="'+ orig_select.attr('data-attr-mapid') +'">' + orig_select.html() + '</select>';

	var orig_input = $(control).parents('.conditionfilter:first').find('.conditiontext').first().html();	
	var input = '<input class="conditiontext" data-attr-mapid="' + $(orig_input).attr('data-attr-mapid') + '" type="text"  />';

	var plus = '<button class="btn addcondition" onclick="javascript:return addNewCondition(this);">+</button>';
	var minus = '<button class="btn addcondition" onclick="javascript:return removeNewCondition(this);">-</button>';

	var newCondition = '<span class="newcondition"><br />' + condselect + select + input + plus + minus + '</span>';

	$(control).parent('.newcondition').after(newCondition);

	return false;
}

function getMultipleConditionJson(control){
	var json = {
			conditions: []
	};
	$(control).parent('.conditionfilter').find('.newcondition').each( function(i, item){

		json.conditions.push({
			condition: $(item).find('.booleanselect').val(),
			field: $(item).find('.conditionselect').val(),
			text: $(item).find('.conditiontext').val()

		})


	});

	return json;
}

