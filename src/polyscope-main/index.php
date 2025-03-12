<?php
session_start();
include 'auth/session_timeout.php';
// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // If not logged in, redirect to the login page
    header('Location: /auth/login.php');
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title>Polyscope Mainpage</title>

<link type="text/css" rel="stylesheet" href="reset.css"/>
<link type="text/css" rel="stylesheet" href="main.css"/>
<link type="text/css" rel="stylesheet" href="style.css"/>
<link rel="icon" type="image/png" href="favicon.png" />

<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="jquery-ui.min.js"></script>
<script type="text/javascript" src="raphael.js"></script>
<script type="text/javascript" src="serveraccess.js"></script>

</head>
<body>

<div id='pageHeader'>

	<div id="topMenu">
	

		<ul>
			<li><img src="auth/images/logo.png" alt="Logo" style="height: 40px;"></li>
			<li><a href="index.php">Polyzoomer</a></li>
			<li><a href="/customers/<?php echo $username; ?>-mdanderson-org/" target="_blank">Customers</a></li>
			<li><a href="/docs/index.html" target="_blank" >Documentation/Help</a></li>
			<li>
    <?php if ($username === 'Guest'): ?>
        <a href="auth/login.php">Login</a>
    <?php else: ?>
        Hello, <?php echo htmlspecialchars($username); ?>
    <?php endif; ?>
</li>
<?php if ($username !== 'Guest'): ?>
    <?php if ($username === 'yshokrollahi'): ?>
        <li><a href="auth/admin_dashboard.php">Dashboard</a></li>
    <?php endif; ?>
    <li><a href="auth/logout.php">Sign Out</a></li>
	<li><button id="cleanupButton">Clean Up Stuck Jobs</button></li>
<?php endif; ?>

		</ul>
	</div>
	<div class='header'><h1>Polyscope</h1></div>
	<div class='header'><h2>Yuan lab @ MD Anderson</h2></div>
</div>

<!-- main page -->
<div id="main" class="content">
<div id="row" class="content-row">

<div class="container">
<div id="calendar" class="element">
<div id="timebox" class="timebox"></div>
<div id="datebox" class="datebox"></div>
</div>
</div>

<div class="container">
<div id="cpumemory" class="element loadLow" title="CPU load">
</div>
</div>

<div class="container">
<div id="diskusage" class="element"></div>
</div>

</div>
</div>
<!-- -->

<!-- new media -->
<div id="newdevice" class="content" style="display:none;">
<div id="ndrow" class="content-row">

<div class="container">
<div id="selectvolume" class="dev-list"></div>

<div id="polyzoombutton" class="polyzoombutton">
<a href="#" style="width:100%; text-align:center; position:absolute;"><img src="/images/pz3.gif"></a>
</div>

</div>

<div class="container">
<div id="selectlist" class="dir-list"></div>
</div>

</div>
</div>
<!-- -->

<!-- polyzooming -->
<div id="polyzooming" class="content" style="display:none;">
<div id="pzrow" class="content-row">

<div class="container">
	<div id="emailbox" class="emailbox">
		<input type="text" id="write" class="realInput" notab="notab" value="" />
		<input type="text" id="proposalWrite" class="proposalInput" value="" />
	</div>

	<div class="polyzoomcontent">
		<div id="polyzoomall" class="polyzoomelement" width="100%"></div>
		<!-- blocker -->
		<div id="blocker"></div>
	</div>
</div>

</div>
</div>
<!-- --> 

<div id="footer" class="footer">
<div id="left" class="navbuttonleft"><img height="40" width="40" src="images/arrow_left.png"/></div>
<div id="right" class="navbuttonright"><img height="40" width="40" src="images/arrow_right.png"/></div>
</div>

<div id="keyboardcontainer" class="keyboard">
	<ul id="keyboard">
		<li class="unused"><span class="off"></span></li>
		<li class="symbol"><span class="off">1</span></li>
		<li class="symbol"><span class="off">2</span></li>
		<li class="symbol"><span class="off">3</span></li>
		<li class="symbol"><span class="off">4</span></li>
		<li class="symbol"><span class="off">5</span></li>
		<li class="symbol"><span class="off">6</span></li>
		<li class="symbol"><span class="off">7</span></li>
		<li class="symbol"><span class="off">8</span></li>
		<li class="symbol"><span class="off">9</span></li>
		<li class="symbol"><span class="off">0</span></li>
		<li class="symbol"><span class="off">-</span></li>
		<li class="delete lastitem">delete</li>
		<li class="atsign">@</li>
		<li class="letter">q</li>
		<li class="letter">w</li>
		<li class="letter">e</li>
		<li class="letter">r</li>
		<li class="letter">t</li>
		<li class="letter">y</li>
		<li class="letter">u</li>
		<li class="letter">i</li>
		<li class="letter">o</li>
		<li class="letter">p</li>
		<li class="unused"><span class="off"></span></li>
		<li class="unused lastitem"><span class="off"></span></li>
		<li class="icr">icr.ac.uk</li>
		<li class="letter">a</li>
		<li class="letter">s</li>
		<li class="letter">d</li>
		<li class="letter">f</li>
		<li class="letter">g</li>
		<li class="letter">h</li>
		<li class="letter">j</li>
		<li class="letter">k</li>
		<li class="letter">l</li>
		<li class="symbol"><span class="off">_</span></li>
		<li class="return lastitem">return</li>
		<li class="unused left-shift"></li>
		<li class="letter">z</li>
		<li class="letter">x</li>
		<li class="letter">c</li>
		<li class="letter">v</li>
		<li class="letter">b</li>
		<li class="letter">n</li>
		<li class="letter">m</li>
		<li class="symbol"><span class="off">.</span></li>
		<li class="symbol"><span class="off">/</span></li>
		<li class="unused right-shift lastitem"></li>
		<li class="space lastitem">&nbsp;</li>
	</ul>
</div>

<script>
// http://stackoverflow.com/questions/920236/how-can-i-detect-if-a-selector-returns-null
$.fn.exists = function () {
    return this.length !== 0;
}

var rad = Math.PI / 180;

function sector(x, y, r, a1, a2) {
    var flag = (a2 - a1) > 180;
    a1 = (a1 % 360) * Math.PI / 180;
    a2 = (a2 % 360) * Math.PI / 180;
    return [["M", x, y], ["l", r * Math.cos(a1), r * Math.sin(a1)], ["A", r, r, 0, +flag, 1, x + r * Math.cos(a2), y + r * Math.sin(a2)], ["z"]];
}

// autocomplete emailSent
var autoCompleteEmails = [];

// states
var serverMemoryUsage = [];
var serverCpuUsage = [];
var serverDiskUsage = 0;

var PAGESTATES = {
	TRANSFERSTYLE:	'slow',
	IDLE        : {value: 0, name: 'Idle', code: 'I', element: 'main'},
	NEWDEVICE   : {value: 1, name: 'Device-List', code: 'D', element: 'newdevice'},
	POLYZOOMING : {value: 2, name: 'Polyzooming', code: 'P', element: 'polyzooming'}
};

function transferState(stateTo, kind) {
	if($('#emailbox').is(':visible')) {
		return;
	}

	if(currentState != stateTo) {
		if(kind === undefined) {
			manualTransfer = false;
		}
		else {
			manualTransfer = true;
		}
		
		var dateTime = new Date();

		var time = dateTime.getTime();
		var isManual = (manualTransfer == true);
		var isValidAutomatic = (manualTransfer == false) && (time - lastTransferTime >= automaticTimeoutInMs);
		
		if(isManual || isValidAutomatic) {
			$('#' + PAGESTATES.IDLE.element).hide();
			$('#' + PAGESTATES.NEWDEVICE.element).hide();
			$('#' + PAGESTATES.POLYZOOMING.element).hide();
			$('#keyboardcontainer').hide();
			
			$('#' + stateTo.element).show(PAGESTATES.TRANSFERSTYLE);
			currentState = stateTo;
		}
		
		lastTransferTime = dateTime.getTime();
	}
}

var currentState = PAGESTATES.IDLE;
var directories = [];

$('input[notab=notab]').on('keydown', function(e){ if (e.keyCode == '\t'.charCodeAt())  e.preventDefault() });

$('body').on('click', '.navbuttonleft',function() {
	var prevStateId = currentState.value - 1;
	setStateById(prevStateId);
});

$('body').on('click', '.navbuttonright', function(){
	var nextStateId = currentState.value + 1;
	setStateById(nextStateId);
});

function setStateById(id) {
	
	$.each(PAGESTATES, function(key, element) {
		
		if(PAGESTATES.hasOwnProperty(key)) {
			if(element !== null && typeof element === 'object') {
				if(element.value == id) {
					transferState(element, true);
				}
			}
		}
	});
}

var dateTimer = new Date();
var manualTransfer = false;
var automaticTimeoutInMs = 4000;
var lastTransferTime = dateTimer.getTime();

// x
var diskUsageHeight = 200;//$("#diskusage").height();
var diskUsageWidth = 200;//$("#diskusage").width();

var diskUsageElement = document.getElementById("diskusage");
diskUsageElement.paper = window.Raphael(diskUsageElement, diskUsageWidth, diskUsageHeight);

// x
var dateTimeHeight = 200;//$("#calendar").height();
var dateTimeWidth = 200;//$("#calendar").width();

var dateTimeElement = document.getElementById("calendar");
dateTimeElement.paper = window.Raphael(dateTimeElement, dateTimeWidth, dateTimeHeight);

// server data refresh
window.setInterval("ServerRefresh()", 1000);

function ServerRefresh () {
	
	var request = serverRequest("retrieveServerData.php", null, function() { updateText( request ) }, null); 
}

function updateText(request) {

	switch (request.readyState) {
		case 4:
			if(request.status != 200) {
			}
			else
			{	
				var serverVariables = JSON.parse(request.responseText);
				
				updateServerStats(serverVariables);
			}
			request = null;
	}	
}

function timePad(num) {
    var s = "00" + num;
    return s.substr(s.length - 2);
}

// REFACTOR!! :p
function updateServerStats( serverStats ) {

  var cpuMemUsage = serverStats.cpuMemUsage;
  var dateTime = serverStats.dateTime;
  var diskUsage = parseInt(serverStats.diskUsage) / 100.0;

  //handleSystemLoadInfo(cpuMemUsage.cpu);

  diskUsageElement.paper.clear();
  
  diskUsageElement.circleBase = diskUsageElement.paper.circle(100, 100, 80);
  diskUsageElement.circleBase.attr("stroke", "#000");
  diskUsageElement.circleBase.attr("stroke-width", "2");
  
  var sectorDiskUse = sector(100, 100, 80, -90, -90 + diskUsage * 360);  
  diskUsageElement.pieDiskUse = diskUsageElement.paper.path(sectorDiskUse);
  diskUsageElement.pieDiskUse.attr("fill", "#ff0");
  diskUsageElement.pieDiskUse.attr("stroke", "#000");
  diskUsageElement.pieDiskUse.attr("stroke-width", "2");

  var sectorDiskFree = sector(100, 100, 80, -90 + diskUsage * 360, 270);  
  diskUsageElement.pieDiskFree = diskUsageElement.paper.path(sectorDiskFree);
  diskUsageElement.pieDiskFree.attr("fill", "#f80");
  diskUsageElement.pieDiskFree.attr("stroke", "#000");
  diskUsageElement.pieDiskFree.attr("stroke-width", "2"); 
  
  dateText = dateTime.year + '-' + dateTime.month + '-' + dateTime.day;
  timeText = timePad(dateTime.hour) + ':' + timePad(dateTime.minute) + ':' + timePad(dateTime.second);
  
  dateTimeElement.paper.clear();
  
  dateTimeElement.time = dateTimeElement.paper.text(100,75,timeText).attr({font: "50px Fontin-Sans, Arial", fill: "#000", "text-align": "center"});
  dateTimeElement.date = dateTimeElement.paper.text(100,150,dateText).attr({font: "25px Fontin-Sans, Arial", fill: "#000", "text-align": "center"});
 
}

function handleSystemLoadInfo( load ) {
	
	var memCpuUsageElement = document.getElementById("cpumemory");
	var averageLoad = load.weightedAverage;
	var tooltipText = '';
	
	if( averageLoad < 2.0 ) {
		$('#cpumemory').addClass('loadLow');

		$('#cpumemory').removeClass('loadMedium');
		$('#cpumemory').removeClass('loadHigh');
		
		tootipText = 'Load low\nWait time short';
	}
	else if( averageLoad >= 2.0 && averageLoad < 6.0 ) {
		$('#cpumemory').addClass('loadMedium');

		$('#cpumemory').removeClass('loadLow');
		$('#cpumemory').removeClass('loadHigh');

		tootipText = 'Load medium\nWait time approx. one hour';
	}
	else { // averageLoad >= 6.0 
		$('#cpumemory').addClass('loadHigh');

		$('#cpumemory').removeClass('loadLow');
		$('#cpumemory').removeClass('loadMedium');

		tootipText = 'Load high\nWait time can be several hours';
	}

	$('#cpumemory').tooltip({
		content: function() {
			return tootipText;
		},
		open: function(event,ui) {
			jQuery("div.ui-helper-hidden-accessible").remove();
		}
	});
}

// server media refresh

// mounts list
var mounts = [];

window.setInterval("ServerMediaRefresh()", 4000);

function ServerMediaRefresh() {
  var request = serverRequest("getPolyzoomerMounts.php", null, function() { updateMedia(request) }, null); 
}

function updateMedia(request) {

	switch (request.readyState) {
		case 4:
			if(request.status != 200) {
			}
			else
			{	
				var serverMedia =  JSON.parse( request.responseText );
				
				var list = [];
				
				for ( var i = 0; i < serverMedia.length; ++i ) {
					list.push(serverMedia[i]);
				}
					
				var newMedia = $(list).not(mounts).get();
				var oldMedia = $(mounts).not(list).get();
				
				if (newMedia.length != 0) {
					// signal that new media is attached (or was removed)
					for ( var i = 0; i < newMedia.length; ++i ) {
						mediaAdd(newMedia[i]);
						mounts.push(newMedia[i]);
						transferState(PAGESTATES.NEWDEVICE);
					}
					
				};

				if (oldMedia.length != 0) {
					for ( var i = 0; i < oldMedia.length; ++i ) {
						mediaRemove(oldMedia[i]);
					}
				}

				if ( serverMedia.length <= 0 && projectsToCommit.length == 0 ) {
					transferState(PAGESTATES.IDLE);
				}
			
			}
			request = null;
	}	
}

function mediaAdd( media ) {

	ServerDirectoryScan(media, true, false);
	$("#write").val('');
	
}

function mediaRemove( media ) {
	var index = mounts.indexOf(media);
	
	var done = false;
	var highlightKey = "";
	
	if ( index - 1 >= 0 && index - 1 < mounts.length ) {
		updateDirectoryContentView( mounts[ index - 1 ] );
		highlightKey = mounts[ index - 1];
		done = true;
	}
	
	if ( index + 1 >= 0 && index + 1 < mounts.length ) {
		updateDirectoryContentView( mounts[ index + 1 ] );
		highlightKey = mounts[ index + 1];
		done = true;
	}
	
	if ( !done ) {
		document.getElementById("selectlist").innerHTML = "";
	}
	
	if ( index != -1 ) {
		mounts.splice(index, 1);
	}

	delete directories[media];
	updateDirectoryListing();

	highlightDirectory( highlightKey );
}

function updateDirectoryListing() {
	var volumeSelect = document.getElementById("selectvolume");
	volumeSelect.innerHTML = createDirectoryListing();
}

function createDirectoryListing() {

	var listingHtml = "";
	
	listingHtml = listingHtml + "<ul id=\"directoryListing\">";
	for(var i = 0; i < mounts.length; ++i) {
			listingHtml = listingHtml + "<li class=\"devListEntry\" id=\"dirName_" + mounts[i] + "\" realDirName=\"" + mounts[i] + "\"><a href=\"#\">" + mounts[i] + "</a></li>";
	}

	listingHtml = listingHtml + "</ul>";
	
	return listingHtml;
}

function updateDirectoryContentView(newDir, firstCall) {
	if ( typeof(firstCall) === 'undefined' ) firstCall = false;
	
	if ( $("#directoryListing").exists() ) {

		var selectedItem = $("#directoryListing").find('.highlight');

		if ( selectedItem.length != 0 && selectedItem[0].attributes.realDirName.value != newDir ) {
			// safe the old listing
			var oldInnerHTML = document.getElementById("selectlist").innerHTML;
			directories[selectedItem[0].attributes.realDirName.value] = oldInnerHTML;
		}

		// set the new listing
		document.getElementById("selectlist").innerHTML = directories[newDir];
		
		// set all checkboxes checked which contain the checked class
		$(".checked").prop('checked', true);
		
		if ( firstCall ) {
			$(".fileList").find("UL").hide();
			directories[newDir] = document.getElementById("selectlist").innerHTML;
		}
		
		$('body').on('click', '.extClass', function(e) {
			if( e.isDone === undefined || e.isDone === false ) {
				var checkbox = $(this).children('input');
				$(checkbox).prop('checked', !$(checkbox).prop('checked'));
				e.stopPropagation();
				e.isDone = true;
			}
		});
		
		$('body').on('click', 'input.selectedDir', function(e) {
			var parent = $(this).parent();
			if(parent.hasClass('toload')) {
				loadDirectory(parent, e, true);
				$(this).prop('checked', true);
			}
			else {
				var isChecked = $(this).prop('checked');
				$(this).siblings('.subDirectory').find('input.selectedFile').each( function(i) {
					$(this).prop('checked', isChecked);
				});
			}
		});
		
		// deactivate all inputs, must be overridden by larger click areas
		$('body').on('click', 'input', function(e) {
			e.stopPropagation();
		});
		
		$('body').off('click', '.directoryClass').on('click', '.directoryClass', function(e) {
			loadDirectory($(this), e, false);
		});
		
		$('body').on('click', '.selectedRoot', function(e) {
			var isChecked = $(this).prop('checked');
			$(this).parent().parent().find('input.selectedFile').each( function(i) {
				$(this).prop('checked', isChecked);
			});			
		});
	}
}

function loadDirectory( element, e, shallBeSelected ) {
	if(element.hasClass('toload')) {
		var dir = element.children('a').attr('id');
		loadDirectoryOnDemand(dir, shallBeSelected);
	}

	element.children("UL:first").slideToggle("medium");
			
	e.stopPropagation();
}

function highlightDirectory(dirName) {
	if ( $("#directoryListing").exists() ) {
		
		// unset all elements
		$("#directoryListing").find('li').each( function() {
			$(this).removeClass("highlight");
		});

		// set only the selected one
		var element = $("#directoryListing").find("li#dirName_" + dirName);
		element[0].className += " highlight";
	}
}

// UPDATE DIRECTORIES
function ServerDirectoryScan(pathToScan, isRoot, shallBeSelected) {
  var bool = isRoot ? "true" : "false";
  var request = serverRequest("getDirectoryContents.php", "path=" + JSON.stringify(pathToScan) + "&link=" + JSON.stringify("[link]") + "&first=" + bool, function() { updateDirectories(request, isRoot, pathToScan, shallBeSelected) }, null); 
}

function updateDirectories(request, isRoot, pathToScan, shallBeSelected) {

	switch (request.readyState) {
		case 4:
			if(request.status != 200) {
			}
			else
			{	
				var directoryContent =  JSON.parse( request.responseText );

				if ( isRoot ) {
					directories[pathToScan] = directoryContent;
				
					document.getElementById("selectvolume").innerHTML = "";
					var volumeSelect = document.getElementById("selectvolume");
					volumeSelect.innerHTML = createDirectoryListing();
					highlightDirectory(pathToScan);

					updateDirectoryContentView( pathToScan, true );

					$(".devListEntry").click( function( event ) {
						ServerDirectoryScan( event.target.parentNode.attributes.realDirName.value, true, false );
					});
				}
				else {
					pathToScan = packPath(pathToScan);
					
					var path = document.getElementById(pathToScan);			
					
					var directoryElement = $('#' + pathToScan).parent('.directoryClass');
					if( directoryElement.hasClass('toload') ) {
						directoryElement.removeClass('toload').addClass('loaded');
						$('#' + pathToScan).after(directoryContent);
					}
					
					if( shallBeSelected ) {
						$('#' + pathToScan).siblings('.subDirectory').find('input.selectedFile').each( function(i) {
							$(this).prop('checked', true);
						});
					}
				}
			}
			request = null;
			
			makeDraggables();
	}	
}

function makeDraggables() {

	/*jQuery('.directoryClass').draggable({
		revert: 'invalid',
		containment: 'document',
		cursor: 'move',
		helper: 'clone',
	});*/
	
	jQuery('.extClass').draggable({
		revert: 'invalid',
		containment: 'document',
		cursor: 'move',
		helper: 'clone',
	});
}

function makeDroppable() {
	
	jQuery('.polyzoombutton').droppable({
		tolerance: "touch",
		accept: ".directoryClass, .extClass",
		drop: function(event, ui) {
			
			if( ui === undefined || event === undefined ) {
				return;
			}
			
			var container = jQuery(ui.helper[0]);
			
			var files = new Array();
			var dirs = new Array();
			
			if( container.hasClass('extClass') ) {
				
				var path = container.find('.selectedFile').attr('value');
				
				files.push(path);
			}
			else if( container.hasClass('directoryClass') ) {
			}
			else {
			}
			
			startProcessing(files, dirs);
		}
	});
}

function loadDirectoryOnDemand(path, shallBeSelected) {
	var path = unpackPath(path);
	
	ServerDirectoryScan(path, false, shallBeSelected);
}

function packPath(path) {
	return path.replace(/\//g, '___SLASH___');
}

function unpackPath(pack) {
	return pack.replace(/___SLASH___/g, '/');
}

function getSelections(id) {
	var inputs = document.getElementsByClassName(id);
	var names  = [].map.call(inputs, function( input ) {
        if ( input.checked ) {
			return input.value;
		} else {
			return "";
		};
    });

	names = names.filter(function(n){ return n != "" });

	return names;
}

// Polyzooming
$("#polyzoombutton").click( function() {
	
	transferState(PAGESTATES.POLYZOOMING);
	
	var selectedDirs = getSelections('selectedDir');
	var selectedFiles = getSelections('selectedFile');
	
	clearAllSelections();
	
	startProcessing( selectedFiles, selectedDirs );
});

function clearAllSelections() {
	
	$('.selectedDir').prop('checked', false);
	$('.selectedFile').prop('checked', false);
}

var startedUploads = 0;
var finishedUploads = 0;
var startedZooms = 0;
var finishedZooms = 0;
var projectsToCommit = new Array();

function isEmailEmpty(project) {
	return projectsToCommit[cleanString(project)].email == "";
}

function updateEmailMissingWarning(project) {
	var name = cleanString(project);
	
	if(isEmailEmpty(project)) {
		$(".ext-polyzoom-" + name).find("span#email").removeClass("emailok").addClass("emailmissing").text("Please enter your email!");
	}
	else {
		$(".ext-polyzoom-" + name).find("span#email").removeClass("emailmissing").addClass("emailok").text("Email Ok!");
	}
}

function startProcessing( files, dirs ) {

	addProjects(files, 0);
	addProjects(dirs, 1);
}

function addProjects(items, isDir) {
	
	var itemsToSubmit = new Array();
	
	for ( var i = 0; i < items.length; ++i ) {
		++startedUploads;
		
		var realPath = unpackPath(items[i]);
		var id = cleanString(realPath);
		
		var projectEntity = new Object();
		projectEntity.name = realPath;
		projectEntity.email = "";
		projectEntity.timedout = false;
		projectEntity.timer = undefined;
		projectEntity.zoomDone = false;
		projectEntity.isDir = isDir;
		projectEntity.valid = false;
		projectEntity.creationTimeOut = 5;
		projectsToCommit[id] = projectEntity;
		
		itemsToSubmit.push(realPath);
	}
	
	uploadProjects(itemsToSubmit, isDir);
}

function uploadProjects( filesToCopy, isDir ) {
	var request = serverRequest("issueUploadProject.php", "path=" + JSON.stringify(filesToCopy) + "&isDir=" + JSON.stringify(isDir), function() { handleUploadProject(request, filesToCopy) }, null); 
}

function uploadProject( fileToCopy, isDir ) {
	var request = serverRequest("issueUploadProject.php", "path=" + JSON.stringify(fileToCopy) + "&isDir=" + JSON.stringify(isDir), function() { handleUploadProject(request, fileToCopy) }, null); 
}

function handleUploadProject( request, fileId ) {
	switch (request.readyState) {
		case 4:
			if(request.status != 200) {
			}
			else
			{	
				var fileInfo = JSON.parse( request.responseText );
				
				if(fileInfo.typ == 'multiple') {
					var count = fileInfo.jobs.length;
					
					var jobs = fileInfo.jobs;
					
					for(var i = 0; i < count; ++i) {
						var id = cleanString(jobs[i].fileId);

						$('.ext-polyzoom-' + id).addClass('polyzoom').removeClass('upload');
						$('.ext-polyzoom-' + id).data('guid', jobs[i].guid);
						
						projectsToCommit[id].guid = jobs[i].guid;
						projectsToCommit[id].valid = true;
						projectsToCommit[id].creationTimeOut = 5;
						
						updateEmailMissingWarning(jobs[i].fileId);
					}
				}
				else if(fileInfo.typ == 'single'){
					var id = cleanString(fileId);
					
					$('.ext-polyzoom-' + id).addClass('polyzoom').removeClass('upload');
					$('.ext-polyzoom-' + id).data('guid', fileInfo.guid);
					
					projectsToCommit[id].guid = fileInfo.guid;
					projectsToCommit[id].valid = true;
					
					updateEmailMissingWarning(fileId);
				}
			}
			
			request = null;
	}
}

window.setInterval("ProjectStatus()", 3000);
function ProjectStatus() {
	var request = serverRequest("getProjectStatus.php", null, function() { getProjectStatus( request ) }, null);
}

function getProjectStatus( request ) {
	switch (request.readyState) {
		case 4:
			if(request.status != 200) {
			}
			else
			{	
				var serverVariables = JSON.parse(request.responseText);
				
				if(serverVariables['valid'] == true) {
					updateProjectStatus(serverVariables);
				}
			}
			request = null;
	}	
}

function updateProjectStatus( projectStatus ) {
	
	var checkBoxes = new Array();
	$("input:checkbox.selectedEmail:checked").each(function() {
		checkBoxes.push($(this).data('guid'));
	});
	
	var jobs = projectStatus['jobs'];
	
	var jobList = new Array();
	var jobKeys = new Array();
	for(var i = 0; i < jobs.length; ++i) {
		var guid = jobs[i]['data']['guid'];
		jobList[guid] = i;
		jobKeys.push(guid);
	}

	var projectList = new Array();
	var projectKeys = new Array();
	for(var key in projectsToCommit) {
		var guid = projectsToCommit[key].guid;
		projectList[guid] = key;
		projectKeys.push(guid);
	}
	
	var newItems = new Array();
	for(var i = 0; i < jobKeys.length; ++i) {
		if($('[data-guid="' + jobKeys[i] + '"]').length === 0) {
			newItems.push(jobKeys[i]);
		}
	}
	
	// REMOVE OLD ITEMS
	var oldItems = $(projectKeys).not(jobKeys).get();
	
	for(var i = 0; i < oldItems.length; ++i) {
		
		var guid = oldItems[i];
		var key = projectList[guid];
		var project = projectsToCommit[key];
		var id = cleanString(project.name);
		
		if(project.valid) {
			if(project.creationTimeOut <= 0) {
				$(".ext-polyzoom-" + id).parent('.projectEntry').remove();
				delete projectsToCommit[key];
			}
			else {
				--project.creationTimeOut;
			}
		}
		
		if(projectsToCommit.length <= 0) {
			transferState(PAGESTATES.NEWDEVICE);
		}
	}
	
	// ADD NEW ITEMS
	for(var i = 0; i < newItems.length; ++i) {
		
		var guid = newItems[i];
		var project = projectsToCommit[projectList[guid]];
		var job = jobs[jobList[guid]].data;
		
		var id = cleanString(job['origFilename']);
		var realPath = job['origFilename'];
		
		var pzListing = document.getElementById('polyzoomall');
		pzListing.innerHTML += 
		"<div class='projectEntry'>" +
		"<div class='checkSquare'>" +
		"<input type='checkbox' name='selectedEmails[]' class='selectedEmail' data-guid='" + guid + "' value='" + id + "'/>" + 
		"</div>" +
		"<div class='ext-polyzoom-" + id + " clickme " + job['status'] + "'>" + 
		"<img src='./images/pz_load_2.gif' style='vertical-align:middle;'></img>" + 
		"<span id='status'>" + realPath + " - " + job['status'] + " - </span>" + 
		"<span id='email'></span></div>" +
		"</div>";
		
		if(project === undefined) {
			++startedUploads;
	
			var projectEntity = new Object();
			projectEntity.name = realPath;
			projectEntity.email = "";
			projectEntity.timedout = false;
			projectEntity.timer = undefined;
			projectEntity.zoomDone = false;
			projectEntity.isDir = false;
			projectEntity.guid = guid;
			projectEntity.valid = true;
			projectEntity.creationTimeOut = 5;
			projectsToCommit[id] = projectEntity;
		}
		
		addKeyboardHandler(".ext-polyzoom-", id);
		updateEmailMissingWarning(realPath);
		
		checkBoxes.push(guid);
	}

	// UPDATE ITEMS
	var currentItems = $(jobKeys).not(newItems).not(oldItems).get();
	
	var stati = [
			'pending',
			'upload',
			'uploading',
			'uploaded',
			'putToOwnFolder',
			'estimateSize',
			'readyForQueue',
			'inQueue',
			'processing',
			'finished',
			'emailSent',
			'readyToBeRemoved'
			];

	for(var i = 0; i < currentItems.length; ++i) {
		
		var guid = currentItems[i];
		var job = jobs[jobList[guid]].data;
		var project = projectsToCommit[projectList[guid]];
		
		if(project === undefined) {
		
			var id = cleanString(job['origFilename']);
			var realPath = job['origFilename'];
		
			var query = $(".ext-polyzoom-" + id);
			
			for(var j = 0; j < stati.length; ++j) {
				query.removeClass(stati[j])
			}

			query.addClass(job['status']);
			
			query.find("span#status").text(realPath + " - " + job['status'] + " - ");
			query.find("span#email").text();
		}
		else {
			var query = $(".ext-polyzoom-" + projectList[guid]);
			
			for(var j = 0; j < stati.length; ++j) {
				query.removeClass(stati[j])
			}

			query.addClass(job['status']);
			
			query.find("span#status").text(project.name + " - " + job['status'] + " - ");
			query.find("span#email").text();
			
			if(project.email != '') {
				if(job['email'] == 'EMAIL_PLACE_HOLDER') {
					updateEmail(project.email, project.guid);
				}
			}
		}
	}
	
	for(var i = 0; i < checkBoxes.length; ++i) {
		$('input:checkbox.selectedEmail[data-guid="' + checkBoxes[i] + '"]').prop('checked', true);
	}
}

function addKeyboardHandler(classKey, classExtension) {
	
	$('.checkSquare').find(':input').click(function(e) {
		e.stopPropagation();
	});
	
	$('.checkSquare').click(function(e) {
		var cb = $(this).find(':input');
		cb.prop("checked", !cb.prop("checked"));
		e.stopPropagation();
	});
	
	$('#polyzoomall').on('click', classKey + classExtension, function(){
		
		if(projectsToCommit[classExtension].timedout == false) {
		
			if(typeof projectsToCommit[classExtension].timer !== 'undefined') {
				clearTimeout(projectsToCommit[classExtension].timer); 
			}
			
			$("#write").val(projectsToCommit[classExtension].email);
			$("#emailbox").show("slow", function() {
				adjustKeyboardAndBlocker();
			});
			
			$(".return").click(function() {
				handleClickedEmails();
			});
		}
	});
}

function handleClickedEmails() {

	var proposal = $("#proposalWrite").val();
	var realEmail = $("#write").val();
	
	var emailAdr = "";
	
	if(proposal.length > realEmail.length) {
		emailAdr = proposal;
		$("#write").val(proposal);
	}
	else {
		emailAdr = realEmail;
	}

	if(emailAdr.indexOf("@") == -1) {
		return;
	}
	
	$("#keyboardcontainer").hide("slow");
	$("#emailbox").hide("slow");
	$("#blocker").hide("slow");
	
	if(emailAdr != '') {
		var projectsForEmails = getSelections('selectedEmail');
		var email = emailAdr.toLowerCase().split(/\r\n|\r|\n/g)[0];
		
		for(var i = 0; i < projectsForEmails.length; ++i) {

			var projectName = projectsForEmails[i];
			
			projectsToCommit[projectName].email = email;
			updateEmailMissingWarning(projectName);				

			projectsToCommit[projectName].timer = window.setTimeout((
				function(id) {
					return function() {
					if(projectsToCommit[id] !== 'undefined') {
						projectsToCommit[id].timedout = true;
						
						$('.ext-polyzoom-' + id).parent().find(':checkbox').hide();
						
						updateEmail(projectsToCommit[projectName].email, projectsToCommit[projectName].guid);
					}}})(projectName)
				, 30000
			);
		}
	}
}
		
function updateEmail(emailAdress, projectGuid) {
	addEmailToAutoList(emailAdress);
	var request = serverRequest("issueUpdateEmail.php", "email=" + JSON.stringify(emailAdress) + "&guid=" + JSON.stringify(projectGuid), function() { doIssueEmailUpdate( request, emailAdress, projectGuid ) }, null);
}

function doIssueEmailUpdate( request, emailAdress, projectGuid ) {
	switch (request.readyState) {
		case 4:
			if(request.status != 200) {
			}
			else
			{	
				var serverVariables = JSON.parse(request.responseText);
				
				loadAutoEmails();
				
				// DONE
			}
			request = null;
	}	
}
	
function loadAutoEmails() {
	issueAutoCompleteMessage(0, "");
}

function addEmailToAutoList( email ) {
	if(jQuery.inArray(email, autoCompleteEmails) == -1) {
		issueAutoCompleteMessage(1, email);
	}
}

function issueAutoCompleteMessage( intent, email ) {
	var param = "";
	
	if( intent == 0 ) {
		param = "intent=0";
	}
	else if( intent == 1 ) {
		param = "intent=1&email=" + JSON.stringify(email);
	}
	else {
	}
	
	var request = serverRequest('autoCompleteEmails.php', param, function() { handleAutoCompleteMessage( request, intent ) }, null);
}

function handleAutoCompleteMessage( request, intent ) {
	switch (request.readyState) {
		case 4:
			if(request.status != 200) {
			}
			else
			{	
				var dataFromServer = JSON.parse(request.responseText);

				if( intent == 0 ) {
					autoCompleteEmails = dataFromServer;
				}
				else if( intent == 1 ) {
				}
				else {
				}
				
				// DONE
			}
			request = null;
	}	
}

function adjustKeyboardAndBlocker() {

	var width = $("#polyzoomall").width();
	var height = $("#polyzoomall").height();

	var pos = $("#polyzoomall").offset();
	
	$("#blocker")
		.show()
		.width(width)
		.height(height)
		.css({"position": "absolute", 
			  "top": pos.top, 
			  "left": pos.left});
	
	$("#keyboardcontainer")
		.show("slow")
		.css({"position": "absolute", 
		      "top": (window.innerHeight * 0.5 - 100) + 'px',  
			  "left": (window.innerWidth * 0.5 - 325) + 'px', 
			  "z-index": 3});
			  
	setEmailboxSize();
}

function cleanString( string ) {
	return string.replace(/[`~!@#$%^&*()|+\=?;:'",.<>\{\}\[\]\\\/ ]/gi, '-');
}

$("#write").on("keyup paste change", function(event) {
	onEmailChange(event);
});

function onEmailChange(event) {
    var THIS = $("#write");

    if (event && (event.which == '\t'.charCodeAt() || event.which == '\n'.charCodeAt() || event.which == '\r'.charCodeAt())) {
        if ($("#proposalWrite").val() == '' || $("#proposalWrite").val()==THIS.val()) {
            if (event.which == '\n'.charCodeAt() || event.which == '\r'.charCodeAt()) {
                handleClickedEmails();
            }
        } else {
            THIS.val($("#proposalWrite").val());
        }
    } else {
        var text = THIS.val();
	
        if(text == "") {
            $("#proposalWrite").val("");
            return;
        }

        var bestFit = matchInArray(text, autoCompleteEmails);
        $("#proposalWrite").val(bestFit);
    }
}

// from: http://stackoverflow.com/questions/10152650/javascript-match-regular-expression-against-the-array-of-items
function matchInArray(string, expressions) {
	var len = expressions.length,
		i = 0,
		re = new RegExp("^" + string, "");
		
	for(; i < len; ++i) {
		if(expressions[i].match(re)) {
			return expressions[i];
		}
	}
	
	return "";
}

function setEmailboxSize() {
	var keyboard = $('#keyboardcontainer');
	var width = 680;//keyboard.width();
	var height = 250;//keyboard.height();
	var position = keyboard.position();
	
	var email = $('#write');
	var proposal = $('#proposalWrite');
	
	proposal.width( width ).css({left: position.left, top: position.top - email.height() - 20});
	email.width( width ).css({left: position.left, top: position.top - email.height() - 20});
}

loadAutoEmails();
setEmailboxSize();
makeDroppable();

</script>
</script>

<script type="text/javascript" src="keyboard.js"></script>

<!-- Cleanup Button Script -->
<script>
$(document).ready(function() {
    $('#cleanupButton').click(function() {
        console.log("Cleanup button clicked");
        var ageThreshold = prompt("Enter the age threshold for file deletion (in hours):", "24");
        if (ageThreshold === null) {
            return; // User cancelled the prompt
        }
        $.ajax({
            url: 'cleanup_jobs_api.php',
            type: 'POST',
            data: { ageThreshold: ageThreshold },
            dataType: 'json',
            success: function(response) {
                console.log("Received response:", response);
                if (response.status === 'success' || response.status === 'partial') {
                    let message = `${response.message}\n\n`;
                    message += `Directory: ${response.directoryPath}\n`;
                    message += `Total files: ${response.totalFiles}\n`;
                    message += `Age threshold: ${response.ageThreshold} hours\n`;
                    message += `Deleted files: ${response.deletedFiles.length}\n`;
                    message += `Kept files: ${response.keptFiles.length}\n`;

                    if (response.deletedFiles.length > 0) {
                        message += "\nDeleted files:\n";
                        response.deletedFiles.forEach(file => {
                            message += `${file.path} (Age: ${file.age} hours)\n`;
                        });
                    }

                    if (response.keptFiles.length > 0) {
                        message += "\nKept files:\n";
                        response.keptFiles.forEach(file => {
                            message += `${file.path} (Age: ${file.age} hours)\n`;
                        });
                    }

                    if (response.errors && response.errors.length > 0) {
                        message += "\nErrors:\n" + response.errors.join("\n");
                    }

                    alert(message);
                    console.log(message);
                } else {
                    alert('Error: ' + response.message);
                    console.error('Error details:', response);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX request failed:");
                console.error("Status:", textStatus);
                console.error("Error:", errorThrown);
                alert('An error occurred while cleaning up jobs. Check the console for details.');
            }
        });
    });
});
</script>

<script>
// $(document).ready(function() {
//     // Add upload button function
//     function addUploadButton($dirElement) {
//         // Don't add duplicate buttons
//         if ($dirElement.find('> .upload-button').length) return;

//         const uploadButton = $('<button>')
//             .addClass('upload-button')
//             .text('Upload')
//             .css({
//                 'margin-left': '10px',
//                 'padding': '2px 8px',
//                 'background': '#4CAF50',
//                 'color': 'white',
//                 'border': 'none',
//                 'border-radius': '3px',
//                 'cursor': 'pointer',
//                 'display': 'inline-block',
//                 'vertical-align': 'middle',
//                 'font-size': '12px',
//                 'line-height': '1.5'
//             });

//         // Add button after the directory name
//         const $dirLink = $dirElement.find('> a');
//         $dirLink.after(uploadButton);

//         // Handle click
//         uploadButton.on('click', function(e) {
//             e.preventDefault();
//             e.stopPropagation();
            
//             const currentPath = $dirLink.attr('id')?.replace(/___SLASH___/g, '/');
//             if (!currentPath) return;

//             const fileInput = $('<input>')
//                 .attr({
//                     type: 'file',
//                     multiple: true,
//                     accept: '.jpg,.jpeg,.png,.gif,.tiff,.tif,.bmp,.webp,.pdf'
//                 })
//                 .css('display', 'none');
            
//             $('body').append(fileInput);
//             fileInput.click();

//             fileInput.on('change', function() {
//                 const files = Array.from(this.files);
//                 if (files.length) {
//                     handleFileUpload(files, currentPath, uploadButton);
//                 }
//                 fileInput.remove();
//             });
//         });
//     }

//     // File upload handler that works with your existing API
//     function handleFileUpload(files, directory, uploadButton) {
//         const formData = new FormData();
//         formData.append('directory', directory);
        
//         // Add each file to form data
//         files.forEach(file => {
//             formData.append('files[]', file);
//         });

//         uploadButton.text('Uploading...').prop('disabled', true);

//         $.ajax({
//             url: '/api/upload.php',
//             type: 'POST',
//             data: formData,
//             processData: false,
//             contentType: false,
//             success: function(response) {
//                 if (response.success) {
//                     let message = `Upload complete! ${response.uploaded.length} files uploaded successfully.`;
//                     if (response.failed && response.failed.length > 0) {
//                         message += `\n${response.failed.length} files failed.`;
//                     }
//                     alert(message);
                    
//                     // Refresh directory listing
//                     if (typeof ServerDirectoryScan === 'function') {
//                         ServerDirectoryScan(directory, true, false);
//                     }
//                 } else {
//                     alert('Upload failed: ' + (response.message || 'Unknown error'));
//                 }
//             },
//             error: function(xhr) {
//                 alert('Upload failed: ' + (xhr.responseJSON?.message || 'Server error'));
//             },
//             complete: function() {
//                 uploadButton.text('Upload').prop('disabled', false);
//             }
//         });
//     }

//     // Add upload buttons to directories
//     function processDirectories() {
//         $('.directoryClass').each(function() {
//             addUploadButton($(this));
//         });
//     }

//     // Check for directories periodically
//     let checkAttempts = 0;
//     const MAX_ATTEMPTS = 20;
//     const structureCheck = setInterval(function() {
//         if ($('.directoryClass').length > 0 || checkAttempts >= MAX_ATTEMPTS) {
//             clearInterval(structureCheck);
//             if ($('.directoryClass').length > 0) {
//                 processDirectories();
//             }
//         }
//         checkAttempts++;
//     }, 500);

//     // Directory click handler
//     $(document).on('click', '.directoryClass', function() {
//         addUploadButton($(this));
//     });

//     // Hook into ServerDirectoryScan to add buttons to new directories
//     if (typeof window.ServerDirectoryScan === 'function') {
//         const originalServerDirectoryScan = window.ServerDirectoryScan;
//         window.ServerDirectoryScan = function() {
//             const result = originalServerDirectoryScan.apply(this, arguments);
//             setTimeout(processDirectories, 500);
//             return result;
//         };
//     }
// });
$(document).ready(function() {
    const currentUsername = '<?php echo $username; ?>'.toLowerCase();
    
    function isUserDirectory(path) {
        const lowerPath = path.toLowerCase();
        const userPathPattern = `/media/users/${currentUsername}`.toLowerCase();
        return lowerPath === userPathPattern;
    }

    function addUploadButton($dirElement) {
        if ($dirElement.find('> .upload-button').length) {
            return;
        }

        const $dirLink = $dirElement.find('> a');
        let currentPath = $dirLink.attr('id')?.replace(/___SLASH___/g, '/');
        
        if (!currentPath || !isUserDirectory(currentPath)) {
            return;
        }

        const uploadPath = `/media/Users/${currentUsername}`;
        const uploadButton = $('<button>')
            .addClass('upload-button')
            .text('Upload')
            .css({
                'margin-left': '10px',
                'padding': '2px 8px',
                'background': '#4CAF50',
                'color': 'white',
                'border': 'none',
                'border-radius': '3px',
                'cursor': 'pointer',
                'display': 'inline-block',
                'vertical-align': 'middle',
                'font-size': '12px',
                'line-height': '1.5'
            });

        $dirLink.after(uploadButton);

        uploadButton.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const fileInput = $('<input>')
                .attr({
                    type: 'file',
                    multiple: true,
                    accept: '.jpg,.jpeg,.png,.gif,.tiff,.tif,.bmp,.webp,.pdf,.svs,.ndpi,.czi,.scn,.dcm'
                })
                .css('display', 'none');
            
            $('body').append(fileInput);
            fileInput.click();

            fileInput.on('change', function() {
                const files = Array.from(this.files);
                if (files.length) {
                    handleFileUpload(files, uploadPath, uploadButton);
                }
                fileInput.remove();
            });
        });
    }

    function handleFileUpload(files, directory, uploadButton) {
        const formData = new FormData();
        formData.append('directory', directory);
        
        files.forEach(file => {
            formData.append('files[]', file);
        });

        uploadButton.text('Uploading...').prop('disabled', true);

        $.ajax({
            url: '/api/upload.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    let message = `Upload complete! ${response.uploaded.length} files uploaded successfully.`;
                    if (response.failed && response.failed.length > 0) {
                        message += `\n${response.failed.length} files failed.`;
                    }
                    alert(message);
                    
                    if (typeof ServerDirectoryScan === 'function') {
                        ServerDirectoryScan(directory, true, false);
                    }
                } else {
                    alert('Upload failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Upload failed: ' + (xhr.responseJSON?.message || 'Server error'));
            },
            complete: function() {
                uploadButton.text('Upload').prop('disabled', false);
            }
        });
    }

    function processDirectories() {
        $('.directoryClass').each(function() {
            addUploadButton($(this));
        });
    }

    let checkAttempts = 0;
    const MAX_ATTEMPTS = 20;
    const structureCheck = setInterval(function() {
        if ($('.directoryClass').length > 0 || checkAttempts >= MAX_ATTEMPTS) {
            clearInterval(structureCheck);
            if ($('.directoryClass').length > 0) {
                processDirectories();
            }
        }
        checkAttempts++;
    }, 500);

    $(document).on('click', '.directoryClass', function() {
        addUploadButton($(this));
    });

    if (typeof window.ServerDirectoryScan === 'function') {
        const originalServerDirectoryScan = window.ServerDirectoryScan;
        window.ServerDirectoryScan = function() {
            const result = originalServerDirectoryScan.apply(this, arguments);
            setTimeout(processDirectories, 500);
            return result;
        };
    }
});
</script>
<script type="text/javascript" src="keyboard.js"></script>

</body>
</html>
