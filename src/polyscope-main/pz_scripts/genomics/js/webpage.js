/*
*	Description: Webpage functions
*   Author: Sebastian Schmittner (stp.schmittner@gmail.com)
*   Date: 2015.11.22
*   LastAuthor: Sebastian Schmittner (stp.schmittner@gmail.com)
*   LastDate: 2015.11.22
*   Version: 0.0.1
*/

var fullTableWidth = 0;
var currentC1 = 0.1;

function computeDeltas(vectors) {
	var deltas = [];
	for(i = 0; i < vectors.length - 1; ++i) {
		var delta = _sub(vectors[i], vectors[i + 1]);
		deltas.push(delta);
	}
	
	return deltas;
}

function scaleColor(value, low, delta, nan) {
	if(isNaN(value)) {
		return nan;
	}
	else {
		return _floor(_add(low, _mul(delta, value)));
	}
}

function scaleColorOnMatrix(matrix, low, delta, nan) {
	return applyOnMatrix(matrix, 
		function(line) {
			var l = low, d = delta, n = nan;
			var lo = [];
			for(var i = 0; i < line.length; ++i) {
				lo.push(scaleColor(line[i], l, d, n));
			}
			return lo;
		}
	);
}

function generateHeatMap(matrix) {
	var heatMap = [];
	var columns = transposeLineMatrix(matrix);
	
	var nanColor = [250, 250, 250];
	var lowColor = [0, 76, 138];
	var highColor = [180, 13, 31];
	var colorDelta = _sub(highColor, lowColor);
	
	var colMin = matrixMin(columns);
	var colMax = matrixMax(columns);
	var colDelta = _sub(colMax, colMin);
	
	var columnsMinusMin = _matrixSub(columns, colMin);
	var columnsNormalized = _matrixDiv(columnsMinusMin, colDelta);
	
	var rows = transposeLineMatrix(columnsNormalized);
	heatMap = scaleColorOnMatrix(rows, lowColor, colorDelta, nanColor);
	
	return heatMap;
}

function generateHeatMap2(matrix) {
	var heatMap = [];
	var columns = transposeLineMatrix(matrix);
	var colorList = [[69, 117, 180], [255, 255, 190], [214, 47, 39]];
	
	var f = function(y) {
		return colorList[y + 1];
	};

	heatMap = applyOnMatrix(matrix, function(x) {
		return applyUnaryVectorOperation(x, f);
	});
	
	return heatMap;
}

function rgbToHex(r, g, b) {
    return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}

function createTable( config ) {
	jQuery('<table id="table" class="tablesorter" style="height:100%;width:100%;"></table>').appendTo("#tableBase");
	
	var matrix = config['dataMatrix']
	matrix = transposeLineMatrix(matrix);
	
	var cols = matrix.length;
	var rows = config['header'].length;
	
	var titles = config['header'];
	titles = filterTable(titles, 0);
	
	var header = config['titles'];
	header = filterTable(header, 0);
	
	for(var i = 0; i < header.length; ++i) {
		header[i] = '<a href="' + config['targets'][i]['index'] + '" target="_blank">' + header[i] + '</a>';
	}
	
	header.unshift('Input Sample');
	header = '<thead>' + createHeader(header) + '</thead>';
	jQuery(header).appendTo('#table');
	
	matrix = filterTable(matrix, decimalDigits);
	var colorMap = generateHeatMap2(matrix);
	
	createHtmlTable(config, matrix, rows, titles);
	
	setCellColorBasedOnContent(colorMap);
	
	addPreviewEventHandler(config);
	
	headerFormat = {};
	for(var i = 0; i < config['titles'].length; ++i) {
		headerFormat[i] = {sorter: false};
	}
	
	applyFormat();
	
	setupHandler();
	
	fullTableWidth = adjustHeaderWidths();
	
	jQuery('th').each(function(){
		var $this = jQuery(this);
		$this.addClass('Col' + $this.index());
	});
	
	return matrix;
}

function adjustHeaderWidths() {
	var headers = jQuery('th');
	var firstColWidth = 120.0;
	var colWidth = 25.0;
	
	var colCount = headers.length;
	var tableWidth = firstColWidth + colWidth * colCount;
	jQuery('table').css('width', tableWidth + 'px');
	
	jQuery(headers[0]).css('width', firstColWidth + 'px');

	for(var i = 1; i < headers.length; ++i) {
		jQuery(headers[i]).css('width', colWidth + 'px');
	}
	
	jQuery('table').css('table-layout','fixed');
	
	jQuery('td').css('height', colWidth + 'px');
	
	return tableWidth;
}

function createHtmlTable(config, matrix, rows, titles) {
	var body = '<tbody>';
	
	for(var y = 0; y < rows; ++y) {
			
		var row = matrix[y];
		row.unshift(titles[y]);
		//row.unshift('<a href="' + config['targets'][y]['index'] + '" target="_blank">' + titles[y] + '</a>');
		
		colorClass = '<tr class="Row' + y + '">';
		
		row = createRow(row, colorClass);
		body = body + row;
	}
	
	body = body + '</tbody>';
	
	jQuery(body).appendTo('#table');
}

function setCellColorBasedOnContent(colorMap) {
	var shallBeEmpty = true;
	
	var rowIndex = 0;
	jQuery('#table tr').each(function() {
		var cellIndex = 0;
		if(rowIndex != 0) {
			jQuery.each(this.cells, function() {
				if(cellIndex != 0) {
					var color = colorMap[rowIndex - 1][cellIndex - 1];
					var hexColor = rgbToHex(color[0], color[1], color[2]);
					
					var font = '#FFF';
					if(shallBeEmpty) {
						font = hexColor;
					}
					else {
						if(color[0] > 180 && color[1] > 180 && color[2] > 180) {
							font = '#000';
						}
					}
					
					jQuery(this).css('background-color', hexColor).css('color', font);
				}
				++cellIndex;
			});
		};
		++rowIndex;
	});
}

function addPreviewEventHandler(config) {
	for(var j = 0; j < config['targets'].length; ++j) {
		
		var key = '.Row' + j;
		var xo = 10;
		var yo = 30;
		
		(function(t) {
			jQuery('th:nth-child(' + (j + 2) + ')').hover(
				function(e) {
					removeAllPreviews();
					$("body").append("<p id='preview' style='position:absolute;border:1px solid black'><img src='" + t + "' alt='Image preview' style='width:auto;height:256px;'/></p>");
					$("#preview")
						.css("top", (e.clientY - yo) + "px")
						.css("left", (e.clientX + xo) + "px")
						.css('z-index', 100)
						.fadeIn("fast");
				},
				function(e) {
					removeAllPreviews();
				}
			);
			
			jQuery('td:nth-child(' + (j + 2) + ')').hover(
				function(e) {
					removeAllPreviews();
					$("body").append("<p id='preview' style='position:absolute;border:1px solid black'><img src='" + t + "' alt='Image preview' style='width:auto;height:256px;'/></p>");
					$("#preview")
						.css("top", (e.clientY - yo) + "px")
						.css("left", (e.clientX + xo) + "px")
						.css('z-index', 100)
						.fadeIn("fast");
				},
				function(e) {
					removeAllPreviews();
				}
			);
		}(config['targets'][j]['thumbnail']));
	}
}

function applyFormat() {
	jQuery.tablesorter.addWidget(colorSampleNameColumnWidget);
	
	jQuery('table').tablesorter({
		widgets: ['colorSampleName'],
		widgetColorSampleName: {
			css: ["even", "odd"]
		}
	});
}

function removeAllPreviews() {
	while(jQuery('#preview').remove().length > 0);
}

function scale(delta, value) {
	return delta * (1 - value / delta);
}

var scroll = 25;
var width = window.innerWidth;
var height = window.innerHeight;

width = scale(width, scroll);
height = scale(height, scroll);

jQuery('#tableBase').width(width + 'px').height(height + 'px');

function setupHandler() {
	jQuery('td').click(function(){
		var index = jQuery(this).index();
		if(index !== 0) {
			jQuery('table thead th:eq(' + index + ')').toggleClass('selected');
		}
		
		/*var parent = jQuery(this).parent();
		parent.toggleClass('selected')
		parent.children().first().toggleClass('selected');*/
		createMultiZoomFromSelection();
	});
};

function getClasses(element) {
	return jQuery(element).attr('class').split(' ');
}

function findColClass(items){
	var found = [];
	for(var i = 0; i < items.length;++i) {
		if(items[i].indexOf('Col') > -1){
			found.push(items[i]);
		}
	}
	return found;
};

function getColIndex(rowClassString) {
	var regex = /Col([0-9]*)/;
	var match;
	
	if((match = regex.exec(rowClassString)) !== null){
		return match[1];
	}
	else {
		return -1;
	}
};

function createMultiZoomFromSelection() {

	var selection = jQuery('th.selected');
	var definedWidth = 2;
	var selectedIndexes = [];
	var _titles = [];
	
	for (var i = 0; i < selection.length; ++i){
		var col = selection[i];
		var colClasses = getClasses(col);
		var classes = findColClass(colClasses);
		var rowIndex = getColIndex(classes[0]);
		selectedIndexes.push(parseInt(rowIndex) - 1);
	}
	
	var layout = {};
	layout.zooms = [];
	layout.zooms[0] = [];
	layout.zooms[1] = [];
	
	var x = 0;
	var y = 0;
	for(var i = 0; i < selection.length; ++i, ++x) {
		if(x >= definedWidth) {
			x = 0;
			y = y + 1;
		}
		
		layout.zooms[x][y] = tableData.targets[selectedIndexes[i]].dzi;
		_titles.push(tableData.titles[selectedIndexes[i]]);
	}
	layout.cols = definedWidth;
	layout.rows = y + 1;
	
	var multizoomHtml = createInBrowserMultiZoom(layout, _titles);
	
	var frame = document.getElementById('iframeZoom');
	var frameDoc = frame.contentDocument || frame.contentWindow.document;
	frameDoc.open();
	frameDoc.write(multizoomHtml);
	frameDoc.close();
};

function doResize(cell1, cell2) {

	var headerHeight = jQuery('#pageHeader').height();
	var scroll = 25;
	var splitterWidth = 12;
	var width = window.innerWidth;
	var height = window.innerHeight;

	width = scale(width, scroll);
	height = scale(height, scroll);

	var c1 = width * cell1;
	var c2 = width * cell2;
	
	c1 = Math.floor(c1);
	c2 = window.innerWidth - c1;
	
	var $container = jQuery('.container');
	var $multiZoom = jQuery('.multiZoom');
	var $iframeZoom = jQuery('.iframeZoom');
	
	$container
		.width(c1 + 'px');

	$multiZoom
		.css('margin-left', c1 + 'px')
		.css('width', c2 + 'px');
		
	var restWidth = $multiZoom.innerWidth() - splitterWidth;
	
	$iframeZoom
		.width(restWidth + 'px');

	var restOfPage = height - headerHeight;

	jQuery('.splitter')
		.css('width', splitterWidth + 'px')
		.css('height', restOfPage)
		.css('float', 'left');
	
	$container.height( restOfPage + 'px')
	$multiZoom.height( restOfPage + 'px')
	$iframeZoom.height( restOfPage + 'px')
};

function handleRequest(request) {
	switch (request.readyState) {
		case 4:
			if(request.status != 200) {
			}
			else
			{	
				tableData = JSON.parse(request.responseText);
				
				for(var i = 0; i < tableData['targets'].length; ++i) {
					tableData['targets'][i]['index'] = serverString + tableData['targets'][i]['index'];
					tableData['targets'][i]['thumbnail'] = serverString + tableData['targets'][i]['thumbnail'];
					tableData['targets'][i]['dzi'] = tableData['targets'][i]['dzi'].replace('/var/www', serverString);
				}
				
				realMatrix = createTable(tableData);
				
				addSplitterHover();
			}
			request = null;
	}	
}

var colorSampleNameColumnWidget = {
	id: "colorSampleName",
	format: function(table) {
		var $tr, $tc, row = -1, odd;
		
		$("tr:visible", table.tBodies[0]).each(function (i) {
			$tr = $(this);
			
			if(!$tr.hasClass(table.config.cssChildRow)) row++;
			odd = (row % 2 == 0);
			$tc = $($tr[0].cells[0]);
			$tc.removeClass(
			table.config.widgetColorSampleName.css[odd ? 0 : 1]).addClass(
			table.config.widgetColorSampleName.css[odd ? 1 : 0]);
		});
	}
};

function addSplitterHover() {
	jQuery('#splitter').mouseenter(function(){
		jQuery(this).addClass('over');
	});
	jQuery('#splitter').mouseleave(function(){
		jQuery(this).removeClass('over');
	});
	jQuery('#splitter').draggable({ 
		helper: 'clone',
		containment: '#box',
		cursor: 'grab',
		iframeFix: true,
		axis: 'x',
		start: function(event, ui) {
			jQuery(this).addClass('force');
		},
		drag: function(event, ui) {
			var offset = ui.offset;
			var innerWidth = jQuery('#box').innerWidth();
			var left = Math.min(Math.max(offset.left / innerWidth, 0.0), 1.0);
			left = Math.floor(left * innerWidth) / innerWidth;
			var right = 1.0 - left;
			doResize(left, right);
			currentC1 = left;
		},
		stop: function(event, ui) {
			jQuery(this).removeClass('force');
		}
		});
}
