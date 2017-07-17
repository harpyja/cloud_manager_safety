function moveItem_l2r(origin, destination, is_move_all) {
	moveItem(origin, destination, is_move_all);
}

function moveItem_r2l(origin, destination, is_move_all) {
	moveItem(origin, destination, is_move_all);
}

function is_target_item_exists(destination, origin_val) {
	var exsits = false;
	if (destination.length > 0) {
		for ( var j = 0; j < destination.length; j++) {
			if (destination.options[j].value == origin_val) {
				exsits = true;
				break;
			}
		}
	}
	return exsits;
}

function moveItem(origin, destination, is_move_all) {
	if (!check_select_elements(origin, destination)) {
		return;
	}
	
	for ( var i = 0; i < origin.options.length; i++) {
		if (is_move_all == false) {
			if (origin.options[i].selected) {
				var exsits = is_target_item_exists(destination,
						origin.options[i].value);
				if (exsits == false) {
					destination.options[destination.length] = new Option(
							origin.options[i].text, origin.options[i].value);
					origin.options[i] = null;
					i = i - 1;
				}
			}
		} else {
			var exsits = is_target_item_exists(destination,
					origin.options[i].value);
			
			if (exsits == false) {
				destination.options[destination.length] = new Option(
						origin.options[i].text, origin.options[i].value);
				origin.options[i] = null;
				i = i - 1;
			}
		}
	}

	destination.selectedIndex = -1;
	sortOptions(destination.options);

}

function sortOptions(options) {
	newOptions = new Array();
	for (i = 0; i < options.length; i++)
		newOptions[i] = options[i];

	// newOptions = newOptions.sort(mysort);
	options.length = 0;
	for (i = 0; i < newOptions.length; i++)
		options[i] = newOptions[i];
}

function clearOptions(obj) {
	obj.options.length = 0;
}

/**
 * 检查对象
 * 
 * @return boolean
 */
check_select_elements = function(sourceSel, targetSel) {

	if (!sourceSel) {
		alert('source select undefined');
		return false;
	} else {
		if (sourceSel.nodeName != 'SELECT') {
			alert('source select is not SELECT');
			return false;
		}
	}

	/* target select */
	if (!targetSel) {
		alert('target select undefined');
		return false;
	} else {
		if (targetSel.nodeName != 'SELECT') {
			alert('target select is not SELECT');
			return false;
		}
	}

	return true;
}

/**
 * 选择,提交时用
 */
select_items=function(targetId){
	if(document.getElementById(targetId)){
		var options = document.getElementById(targetId).options; //alert(options.length);
		for (i = 0 ; i<options.length ; i++)
			options[i].selected = true;
	}
}

