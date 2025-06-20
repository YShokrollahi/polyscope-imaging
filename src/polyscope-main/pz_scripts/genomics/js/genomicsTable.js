/*
*	Description: Genomics Table functions
*   Author: Sebastian Schmittner (stp.schmittner@gmail.com)
*   Date: 2015.10.13 13:46
*   LastAuthor: Sebastian Schmittner (stp.schmittner@gmail.com)
*   LastDate: 2015.10.13 13:46
*   Version: 0.0.1
*/

function isNumeric( value ) {
	return !isNaN(value);
}

function filterTable( content, decimalDigits ) {
	if(content instanceof Array) {
		for(var i = 0; i < content.length; ++i) {
			content[i] = filterTable(content[i], decimalDigits);
		}
	}
	else {
		content = filterContent(content, decimalDigits);
	}
	
	return content;
}

function filterContent( content, decimalDigits ) {
	
	if(	content.charAt(0) == '"' && 
		content.charAt(content.length - 1) == '"') {
		content = content.slice(1).slice(0, -1);
	}
	
	if( isNumeric(content) ) {
		var multiplier = Math.pow(10, decimalDigits);
		content = Math.round(content * multiplier) / multiplier;
	}
	
	return content;
}

function createHeader( arr ) {
	return createTableLine(arr, '<th class="header rotate"><div><span>', '</span></div></th>', '<tr>', '</tr>');
}

function createRow( arr, rowstart ) {
	return createTableLine(arr, '<td>', '</td>', rowstart, '</tr>');
}

function createTableLine( arr, start, stop, rowstart, rowend ) {
	var row = rowstart;
	
	for(var i = 0; i < arr.length; ++i) {
		row = row + start + arr[i] + stop;
	}
	
	row = row + rowend;
	
	return row;
}
