// Global variables
var viewer = null;
var Seadragon;
Seadragon = OpenSeadragon;
OpenSeadragon.Utils = OpenSeadragon;

// Configuration variables that should be set by the template system
var ANNOTATIONS_PATH = null;
var PATIENT_ID = null;
var CHANNEL_ID = null;
var CONTENT_ID = null;
var VIEWER_VARNAME = null;

// Image synchronization functions
function SyncImage(viewer, viewerToSyncWith) {
    console.log('Syncing');
    viewer.viewport.panTo(viewerToSyncWith.viewport.getCenter());
    viewer.viewport.zoomTo(viewerToSyncWith.viewport.getZoom());
}

var hFuncHandler = function myHandler(inViewer) {
    var SourceViewer = inViewer.eventSource;
    var viewersToSync = [];
    
    for (var key in ViewerHash) {
        if (ViewerHash.hasOwnProperty(key)) {
            if(key != SourceViewer.id) {
                if(ViewerHash[key].id == SourceViewer.id) {
                    viewersToSync.push(key);
                }
            }
        }
    }
    
    for(var viewer = 0; viewer < viewersToSync.length; ++viewer) {
        TargetViewer = window[viewersToSync[viewer]];   
        
        if (!TargetViewer.isOpen()) {
            console.log('TargetViewer is not open');
        }
        console.log('Starting live sync...', SourceViewer.id, ' with ', TargetViewer.id);
        SyncImage(TargetViewer,SourceViewer)      
    }
}

function LiveSync(SourceViewer) {
    SourceViewer.addHandler("animation",hFuncHandler);
}

function UnLiveSync(SourceViewer) {
    SourceViewer.removeHandler("animation",hFuncHandler)
}

function SyncThemAll() {
    // Placeholder for future sync functionality
}

// Mouse tracking
var currentMousePos = { x: -1, y: -1 };
$(document).mousemove(function(event) {
    currentMousePos.x = event.pageX;
    currentMousePos.y = event.pageY;
});

// Utility functions
function rgbStringToHex(color) {
    var colorArray = color.split("(")[1].split(")")[0];
    colorArray = colorArray.split(",");
    var b = colorArray.map(function(x){
        x = parseInt(x).toString(16);
        return (x.length==1) ? "0"+x : x;
    })
    return "#"+b.join("");
}

// Function to detect and construct annotation path
function getAnnotationPath() {
    // Try to get from global variables first
    if (ANNOTATIONS_PATH && ANNOTATIONS_PATH !== '_ANNOTATIONS_LINK_') {
        return ANNOTATIONS_PATH;
    }
    
    // Try to construct from current URL
    var currentPath = window.location.pathname;
    console.log('Current path:', currentPath);
    
    // Extract the path components
    // Expected format: /customers/username/Path.../page/MS.../
    var pathParts = currentPath.split('/');
    var pageIndex = pathParts.indexOf('page');
    
    if (pageIndex > 0 && pageIndex < pathParts.length - 1) {
        // Get the MS directory name
        var msDirectory = pathParts[pageIndex + 1];
        
        // Look for channel directory (usually starts with _)
        // We need to find the channel directory name from the current context
        var channelDir = findChannelDirectory(msDirectory);
        
        if (channelDir) {
            // Construct the annotation path
            var basePath = pathParts.slice(0, pageIndex + 2).join('/'); // Up to /MS.../
            var annotationPath = basePath + '/' + channelDir + '/' + getDeepZoomFilesDirectory(channelDir) + '/annotations.txt';
            
            console.log('Constructed annotation path:', annotationPath);
            return annotationPath;
        }
    }
    
    // Fallback: try to find from the DZI path if available
    var dziPath = getDziPath();
    if (dziPath) {
        var annotationPath = dziPath.replace('.dzi', '_files/annotations.txt');
        console.log('Fallback annotation path from DZI:', annotationPath);
        return annotationPath;
    }
    
    console.error('Could not determine annotation path');
    return null;
}

function findChannelDirectory(msDirectory) {
    // This should be determined from your application logic
    // For now, we'll use a common pattern or try to detect it
    
    // Check if we have channel info from the page context
    if (typeof window.channelDirectory !== 'undefined') {
        return window.channelDirectory;
    }
    
    // Default pattern (you may need to adjust this)
    return '_UNKNOWNCHANNEL0001';
}

function getDeepZoomFilesDirectory(channelDir) {
    // Construct the deep zoom files directory name
    // Pattern seems to be: MS###_CHANNELNAME_MS###_HE.svsdeepzoom_files
    var msName = getCurrentMsDirectory();
    if (msName && channelDir) {
        var filesDir = msName + '_' + channelDir.substring(1) + '_' + msName + '_HE.svsdeepzoom_files';
        return filesDir;
    }
    return null;
}

function getCurrentMsDirectory() {
    var pathParts = window.location.pathname.split('/');
    var pageIndex = pathParts.indexOf('page');
    if (pageIndex > 0 && pageIndex < pathParts.length - 1) {
        return pathParts[pageIndex + 1];
    }
    return null;
}

function getDziPath() {
    // Try to get DZI path from viewer configuration
    if (window.viewer && window.viewer.tileSources) {
        return window.viewer.tileSources;
    }
    return null;
}

// UI Controls
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    
    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('expanded');
        toggleBtn.classList.remove('collapsed');
        toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
    } else {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
        toggleBtn.classList.add('collapsed');
        toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
    }
}

function toggleExportDropdown() {
    const dropdown = document.getElementById('exportDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

function exportImage() {
    console.log('Export image');
}

function exportAnnotations() {
    var annotationPath = getAnnotationPath();
    
    if (!annotationPath) {
        alert('Could not find annotations file path. Please check if annotations exist for this image.');
        return;
    }
    
    // Test if the annotation file exists by making a HEAD request
    var xhr = new XMLHttpRequest();
    xhr.open('HEAD', annotationPath, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                // File exists, proceed with download
                const link = document.createElement('a');
                link.href = annotationPath;
                
                // Construct filename
                var patientId = PATIENT_ID || 'Patient';
                var channelId = CHANNEL_ID || 'Channel';
                var filename = 'Annotations_' + patientId + '_' + channelId + '.txt';
                
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                console.log('Downloaded annotations from:', annotationPath);
            } else {
                console.error('Annotations file not found at:', annotationPath);
                alert('Annotations file not found. Status: ' + xhr.status);
            }
        }
    };
    xhr.send();
    
    document.getElementById('exportDropdown').style.display = 'none';
}

// Add new export functions
function exportAnnotationsCSV() {
    console.log('Exporting annotations as CSV...');
    
    var annotationPath = getAnnotationPath();
    if (!annotationPath) {
        alert('Could not find annotations file path.');
        return;
    }
    
    // Fetch the annotations file and convert to CSV
    fetch(annotationPath)
        .then(response => {
            if (!response.ok) {
                throw new Error('Annotations file not found');
            }
            return response.text();
        })
        .then(data => {
            // Convert annotations to CSV format
            var csvData = convertAnnotationsToCSV(data);
            downloadAsFile(csvData, 'annotations.csv', 'text/csv');
        })
        .catch(error => {
            console.error('Error loading annotations:', error);
            alert('Could not load annotations file for CSV export.');
        });
    
    document.getElementById('exportDropdown').style.display = 'none';
}

function exportAnnotationsJSON() {
    console.log('Exporting annotations as JSON...');
    
    var annotationPath = getAnnotationPath();
    if (!annotationPath) {
        alert('Could not find annotations file path.');
        return;
    }
    
    // Fetch the annotations file and convert to JSON
    fetch(annotationPath)
        .then(response => {
            if (!response.ok) {
                throw new Error('Annotations file not found');
            }
            return response.text();
        })
        .then(data => {
            // Convert annotations to JSON format
            var jsonData = convertAnnotationsToJSON(data);
            downloadAsFile(JSON.stringify(jsonData, null, 2), 'annotations.json', 'application/json');
        })
        .catch(error => {
            console.error('Error loading annotations:', error);
            alert('Could not load annotations file for JSON export.');
        });
    
    document.getElementById('exportDropdown').style.display = 'none';
}

function convertAnnotationsToCSV(annotationData) {
    // Parse annotation data and convert to CSV
    // This depends on your annotation format
    var lines = annotationData.split('\n');
    var csvRows = ['ID,Type,X,Y,Width,Height,Label,Color'];
    
    lines.forEach(function(line, index) {
        if (line.trim()) {
            // Parse your annotation format here
            // This is a placeholder - adjust based on your actual format
            csvRows.push(index + ',"annotation","0","0","0","0","' + line.trim() + '","#ff0000"');
        }
    });
    
    return csvRows.join('\n');
}

function convertAnnotationsToJSON(annotationData) {
    // Parse annotation data and convert to JSON
    var lines = annotationData.split('\n');
    var annotations = [];
    
    lines.forEach(function(line, index) {
        if (line.trim()) {
            annotations.push({
                id: index,
                type: 'annotation',
                coordinates: { x: 0, y: 0, width: 0, height: 0 },
                label: line.trim(),
                color: '#ff0000'
            });
        }
    });
    
    return {
        format: 'polyscope-annotations',
        version: '1.0',
        annotations: annotations
    };
}

function downloadAsFile(content, filename, mimeType) {
    var blob = new Blob([content], { type: mimeType });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}

function toggleSection(header) {
    const content = header.nextElementSibling;
    const icon = header.querySelector('i:last-child');
    
    if (content.classList.contains('expanded')) {
        content.classList.remove('expanded');
        header.classList.remove('active');
        if (icon) {
            icon.style.transform = 'rotate(0deg)';
        }
    } else {
        content.classList.add('expanded');
        header.classList.add('active');
        if (icon) {
            icon.style.transform = 'rotate(180deg)';
        }
    }
}

function toggleFullscreen() {
    console.log('Toggle fullscreen');
}

function takeScreenshot() {
    console.log('Take screenshot');
}

function goBack() {
    window.history.back();
}

// Image Control Functions
function updateImageControl(slider) {
    const value = slider.value;
    const valueSpan = slider.parentNode.querySelector('.slider-value');
    valueSpan.textContent = value + '%';
    
    // Apply image filters
    applyImageFilters();
}

function applyImageFilters() {
    const brightness = document.getElementById('brightness').value;
    const contrast = document.getElementById('contrast').value;
    const saturation = document.getElementById('saturation').value;
    
    const filterString = `brightness(${brightness}%) contrast(${contrast}%) saturate(${saturation}%)`;
    
    // Apply to all OpenSeadragon canvas elements
    var contentId = CONTENT_ID || '_CONTENTID_';
    const canvases = document.querySelectorAll('#' + contentId + ' canvas');
    canvases.forEach(canvas => {
        canvas.style.filter = filterString;
    });
    
    // Also apply to any tile images that might load later
    const tileImages = document.querySelectorAll('#' + contentId + ' img');
    tileImages.forEach(img => {
        img.style.filter = filterString;
    });
    
    console.log('Applied filters:', filterString);
}

function resetImageControls() {
    // Reset sliders to default values
    document.getElementById('brightness').value = 100;
    document.getElementById('contrast').value = 100;
    document.getElementById('saturation').value = 100;
    
    // Update display values
    document.querySelectorAll('.slider-value').forEach(span => {
        span.textContent = '100%';
    });
    
    // Apply the reset filters
    applyImageFilters();
    
    console.log('Image controls reset to default');
}

// Channel Management Functions
function detectImageChannels() {
    // This function detects available channels in the image
    // For now, we'll create some example channels - you can modify this based on your actual data
    const channels = [
        { id: 'channel1', name: 'DAPI', color: '#0080ff', visible: true, opacity: 100 },
        { id: 'channel2', name: 'GFP', color: '#00ff00', visible: true, opacity: 100 },
        { id: 'channel3', name: 'Texas Red', color: '#ff0000', visible: true, opacity: 100 },
        { id: 'channel4', name: 'Cy5', color: '#ff00ff', visible: false, opacity: 100 }
    ];
    
    return channels;
}

function createChannelControls() {
    const channels = detectImageChannels();
    
    // Create channel controls section HTML
    const channelControlsHTML = `
        <div class="section">
            <div class="section-header" onclick="toggleSection(this)">
                <span><i class="fas fa-layer-group"></i> Channel Controls</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="section-content">
                <div class="channel-controls" id="channelControls">
                    ${channels.map(channel => `
                        <div class="channel-item" data-channel-id="${channel.id}">
                            <div class="channel-info">
                                <div class="channel-color" style="background-color: ${channel.color}"></div>
                                <span class="channel-name">${channel.name}</span>
                            </div>
                            <div class="channel-controls-right">
                                <div class="channel-toggle ${channel.visible ? 'active' : ''}" 
                                     onclick="toggleChannel('${channel.id}', this)">
                                </div>
                                <input type="range" class="channel-opacity" 
                                       min="0" max="100" value="${channel.opacity}"
                                       oninput="updateChannelOpacity('${channel.id}', this.value)"
                                       ${!channel.visible ? 'disabled' : ''}>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="control-buttons">
                    <button class="action-btn secondary" onclick="showAllChannels()">
                        <i class="fas fa-eye"></i> Show All
                    </button>
                    <button class="action-btn secondary" onclick="hideAllChannels()">
                        <i class="fas fa-eye-slash"></i> Hide All
                    </button>
                </div>
            </div>
        </div>
    `;
    
    return channelControlsHTML;
}

function toggleChannel(channelId, toggleElement) {
    const isActive = toggleElement.classList.contains('active');
    const channelItem = toggleElement.closest('.channel-item');
    const opacitySlider = channelItem.querySelector('.channel-opacity');
    
    if (isActive) {
        // Hide channel
        toggleElement.classList.remove('active');
        opacitySlider.disabled = true;
        console.log('Hiding channel:', channelId);
    } else {
        // Show channel
        toggleElement.classList.add('active');
        opacitySlider.disabled = false;
        console.log('Showing channel:', channelId);
    }
    
    // Apply channel visibility changes
    applyChannelVisibility(channelId, !isActive);
}

function updateChannelOpacity(channelId, opacity) {
    console.log('Updating channel opacity:', channelId, opacity + '%');
    // Apply opacity changes to the specific channel
    applyChannelOpacity(channelId, opacity);
}

function applyChannelVisibility(channelId, visible) {
    // This function would integrate with your imaging system
    // For now, we'll just log the changes
    console.log('Apply channel visibility:', channelId, visible ? 'visible' : 'hidden');
    
    // Example: You could modify canvas layers, SVG elements, or WebGL textures here
    // This depends on how your imaging system handles multi-channel data
}

function applyChannelOpacity(channelId, opacity) {
    // This function would integrate with your imaging system
    console.log('Apply channel opacity:', channelId, opacity + '%');
    
    // Example implementation for canvas-based rendering:
    // const channelCanvas = document.querySelector(`[data-channel="${channelId}"]`);
    // if (channelCanvas) {
    //     channelCanvas.style.opacity = opacity / 100;
    // }
}

function showAllChannels() {
    const toggles = document.querySelectorAll('.channel-toggle');
    const sliders = document.querySelectorAll('.channel-opacity');
    
    toggles.forEach(toggle => {
        toggle.classList.add('active');
    });
    
    sliders.forEach(slider => {
        slider.disabled = false;
        slider.value = 100;
    });
    
    console.log('Showing all channels');
}

function hideAllChannels() {
    const toggles = document.querySelectorAll('.channel-toggle');
    const sliders = document.querySelectorAll('.channel-opacity');
    
    toggles.forEach(toggle => {
        toggle.classList.remove('active');
    });
    
    sliders.forEach(slider => {
        slider.disabled = true;
    });
    
    console.log('Hiding all channels');
}

// OpenSeadragon initialization
function initializeViewer() {
    var contentId = CONTENT_ID || "_CONTENTID_";
    var viewerVarName = VIEWER_VARNAME || "viewer";
    
    var viewerConfig = {
        id: contentId,
        prefixUrl: "../images/",
        tileSources: "_REL_PATH_TO_DZI_",
        preserveViewport: true,
        showRotationControl: true,
        showNavigator: true
    };

    window[viewerVarName] = OpenSeadragon(viewerConfig);

    if (window[viewerVarName].Annotations) {
        window[viewerVarName].Annotations();
        window[viewerVarName].DrawAnnotations(OpenSeadragon.ControlAnchor.BOTTOM_RIGHT);
    }

    var tileSource = "_REL_PATH_TO_DZI_";
    var isSvs = (tileSource.indexOf(".svsdeepzoom.dzi") != -1);
    var isNdpi = (tileSource.indexOf(".ndpideepzoom.dzi") != -1); 

    var ppm = 1000000 / 0.46;

    if(isSvs){
        ppm = 1000000 / 0.50;
    }

    if(isSvs || isNdpi){
        if (window[viewerVarName].scalebar) {
            window[viewerVarName].scalebar({
                minWidth: "75px",
                pixelsPerMeter: ppm,
                barThickness: 2
            });
        }
    }

    var handleResize = function() {
        var height = jQuery('#' + contentId).height();
        var couldHeight = jQuery('td.OSD').height() * 0.9;

        if(height == 0) {
            jQuery('#' + contentId).height(couldHeight + 'px');
        };
    };

    // Store viewer globally
    window.viewer = window[viewerVarName];
    
    console.log('Viewer initialized:', viewerVarName);
}

// Initialize ViewerHash
var ViewerHash = new Object();

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const exportBtn = document.querySelector('.export-btn-container');
    const dropdown = document.getElementById('exportDropdown');
    if (exportBtn && dropdown && !exportBtn.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    SyncThemAll();
    
    // Initialize configuration from page context if available
    if (typeof window.polyscopeConfig !== 'undefined') {
        ANNOTATIONS_PATH = window.polyscopeConfig.annotationsPath;
        PATIENT_ID = window.polyscopeConfig.patientId;
        CHANNEL_ID = window.polyscopeConfig.channelId;
        CONTENT_ID = window.polyscopeConfig.contentId;
        VIEWER_VARNAME = window.polyscopeConfig.viewerVarName;
    }
    
    initializeViewer();
    
    // Fix annotation path for enhanced annotation manager
    if (typeof window.annotationsPath === 'undefined') {
        window.annotationsPath = getAnnotationPath();
    }
    
    // Add debugging function for channels
    window.debugChannels = function() {
        console.log('=== CHANNEL DEBUG INFO ===');
        
        // Check viewer
        var viewer = window.viewer || window[VIEWER_VARNAME || 'viewer'];
        console.log('Viewer found:', !!viewer);
        if (viewer) {
            console.log('Viewer ID:', viewer.id);
            console.log('Viewer is open:', viewer.isOpen && viewer.isOpen());
            console.log('Viewer world item count:', viewer.world ? viewer.world.getItemCount() : 'No world');
        }
        
        // Check DOM elements
        var contentId = CONTENT_ID || '_CONTENTID_';
        var viewerDiv = document.getElementById(contentId);
        console.log('Viewer div found:', !!viewerDiv);
        if (viewerDiv) {
            var canvases = viewerDiv.querySelectorAll('canvas');
            console.log('Canvas elements found:', canvases.length);
            canvases.forEach(function(canvas, i) {
                console.log('Canvas', i, '- size:', canvas.width + 'x' + canvas.height, 'style:', canvas.style.cssText);
            });
            
            var images = viewerDiv.querySelectorAll('img');
            console.log('Image elements found:', images.length);
        }
        
        // Check channel manager
        console.log('Channel manager:', !!window.channelManager);
        if (window.channelManager) {
            console.log('Detected channels:', window.channelManager.getChannels());
        }
        
        // Check annotation path
        console.log('Annotation path:', getAnnotationPath());
        
        console.log('=== END DEBUG INFO ===');
    };
    
    // Auto-run debug after 5 seconds
    setTimeout(function() {
        if (window.debugChannels) {
            window.debugChannels();
        }
    }, 5000);
});