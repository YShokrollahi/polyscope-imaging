// Global variables
var viewer = null;
var Seadragon;
Seadragon = OpenSeadragon;
OpenSeadragon.Utils = OpenSeadragon;

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
    const link = document.createElement('a');
    link.href = '_ANNOTATIONS_LINK_';
    link.download = 'Annotations__PATIENT_ID__CHANNEL_ID_.txt';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    document.getElementById('exportDropdown').style.display = 'none';
}

// Add new export functions
function exportAnnotationsCSV() {
    console.log('Exporting annotations as CSV...');
    exportAnnotations(); // Use existing function for now
}

function exportAnnotationsJSON() {
    console.log('Exporting annotations as JSON...');
    // TODO: Implement JSON export
    alert('JSON export coming soon!');
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
    const canvases = document.querySelectorAll('#_CONTENTID_ canvas');
    canvases.forEach(canvas => {
        canvas.style.filter = filterString;
    });
    
    // Also apply to any tile images that might load later
    const tileImages = document.querySelectorAll('#_CONTENTID_ img');
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
    var _VIEWER_VARNAME_ = OpenSeadragon({
        id: "_CONTENTID_",
        prefixUrl: "../images/",
        tileSources: "_REL_PATH_TO_DZI_",
        preserveViewport: true,
        showRotationControl: true,
        showNavigator: true
    });

    _VIEWER_VARNAME_.Annotations();
    _VIEWER_VARNAME_.DrawAnnotations(OpenSeadragon.ControlAnchor.BOTTOM_RIGHT);

    var tileSource = "_REL_PATH_TO_DZI_";
    var isSvs = (tileSource.indexOf(".svsdeepzoom.dzi") != -1);
    var isNdpi = (tileSource.indexOf(".ndpideepzoom.dzi") != -1); 

    var ppm = 1000000 / 0.46;

    if(isSvs){
        ppm = 1000000 / 0.50;
    }

    if(isSvs || isNdpi){
        _VIEWER_VARNAME_.scalebar({
            minWidth: "75px",
            pixelsPerMeter: ppm,
            barThickness: 2
        });
    }

    var handleResize = function() {
        var height = jQuery('#_CONTENTID_').height();
        var couldHeight = jQuery('td.OSD').height() * 0.9;

        if(height == 0) {
            jQuery('#_CONTENTID_').height(couldHeight + 'px');
        };
    };

    // Store viewer globally
    window._VIEWER_VARNAME_ = _VIEWER_VARNAME_;
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
    initializeViewer();
    
    // Fix annotation path for enhanced annotation manager
    if (typeof window.annotationsPath === 'undefined') {
        window.annotationsPath = '_ANNOTATIONS_LINK_';
    }
    
    // Add debugging function for channels
    window.debugChannels = function() {
        console.log('=== CHANNEL DEBUG INFO ===');
        
        // Check viewer
        var viewer = window.viewer || window._VIEWER_VARNAME_;
        console.log('Viewer found:', !!viewer);
        if (viewer) {
            console.log('Viewer ID:', viewer.id);
            console.log('Viewer is open:', viewer.isOpen && viewer.isOpen());
            console.log('Viewer world item count:', viewer.world ? viewer.world.getItemCount() : 'No world');
        }
        
        // Check DOM elements
        var viewerDiv = document.getElementById('_CONTENTID_');
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
        
        console.log('=== END DEBUG INFO ===');
    };
    
    // Auto-run debug after 5 seconds
    setTimeout(function() {
        if (window.debugChannels) {
            window.debugChannels();
        }
    }, 5000);
});