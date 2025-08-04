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

// Annotation type mappings
var annotationTypes = {
    0: "Line",
    1: "Arrow", 
    2: "Rectangle",
    3: "Ellipse",
    4: "Free Hand Drawing",
    5: "Text",
    6: "Dot"
};

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

// Function to get annotation data from enhanced annotation manager
function getAnnotationData() {
    // Try to get annotations from the enhanced annotation manager first
    if (window.enhancedAnnotationManager && window.enhancedAnnotationManager.annotations) {
        return window.enhancedAnnotationManager.annotations;
    }
    
    // Fallback: return empty array if no annotations found
    return [];
}

// Function to load raw annotation data from file
function loadRawAnnotationData() {
    return new Promise(function(resolve, reject) {
        var annotationPath = getAnnotationPath();
        if (!annotationPath) {
            reject(new Error('Could not find annotations file path'));
            return;
        }
        
        // First try to get from enhanced annotation manager
        if (window.enhancedAnnotationManager && window.enhancedAnnotationManager.annotations) {
            resolve(window.enhancedAnnotationManager.annotations);
            return;
        }
        
        // Fallback: load from file
        fetch(annotationPath)
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Annotations file not found');
                }
                return response.text();
            })
            .then(function(data) {
                // Parse the raw annotation data
                var annotations = parseRawAnnotationData(data);
                resolve(annotations);
            })
            .catch(function(error) {
                reject(error);
            });
    });
}

// Function to parse raw annotation text data
function parseRawAnnotationData(rawData) {
    var annotations = [];
    var lines = rawData.split('\n');
    
    lines.forEach(function(line, index) {
        if (!line || line.trim() === '') return;
        
        try {
            var parts = line.split(',');
            if (parts.length < 4) return;
            
            var active = parseInt(parts[0]);
            var id = parseInt(parts[1]);
            var type = parseInt(parts[2]);
            
            // Skip inactive annotations
            if (active !== 1) return;
            
            // Get coordinates and other data
            var contentStart = line.indexOf('[');
            var contentEnd = line.lastIndexOf(']');
            var content = line.substring(contentStart + 1, contentEnd);
            
            // Get color
            var colorStart = contentEnd + 2;
            var colorEnd = line.indexOf(',', colorStart);
            var color = line.substring(colorStart, colorEnd);
            
            // Get remaining parts (zoom, date)
            var remainingParts = line.substring(colorEnd + 1).split(',');
            var zoom = remainingParts[0] || '1.0';
            var dateStr = remainingParts.slice(1).join(',') || '';
            
            annotations.push({
                id: id,
                type: type,
                typeName: annotationTypes[type] || 'Unknown',
                content: content,
                color: color,
                zoom: zoom,
                date: dateStr,
                active: active,
                raw: line
            });
        } catch (e) {
            console.error("Error parsing annotation line:", e);
        }
    });
    
    return annotations;
}

// Function to detect and construct annotation path
function getAnnotationPath() {
    // Try to get from global variables first
    if (ANNOTATIONS_PATH && ANNOTATIONS_PATH !== '_ANNOTATIONS_LINK_') {
        return ANNOTATIONS_PATH;
    }
    
    // Try to get from polyscopeConfig
    if (window.polyscopeConfig && window.polyscopeConfig.annotationsPath) {
        return window.polyscopeConfig.annotationsPath;
    }
    
    // Try to get from enhanced annotation manager
    if (window.enhancedAnnotationManager && window.enhancedAnnotationManager.annotationPath) {
        return window.enhancedAnnotationManager.annotationPath;
    }
    
    console.error('Could not determine annotation path');
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
    console.log('Export image functionality - to be implemented');
    alert('Image export functionality will be implemented based on your specific requirements.');
    document.getElementById('exportDropdown').style.display = 'none';
}

// Enhanced export functions with proper annotation parsing

function exportAnnotationsTXT() {
    console.log('Exporting annotations as TXT...');
    
    loadRawAnnotationData()
        .then(function(annotations) {
            if (!annotations || annotations.length === 0) {
                alert('No annotations found to export.');
                return;
            }
            
            // Convert to TXT format (original format)
            var txtData = convertAnnotationsToTXT(annotations);
            
            // Generate filename with timestamp
            var patientId = PATIENT_ID || 'Patient';
            var timestamp = getTimestamp();
            var filename = 'Annotations_' + patientId + '_' + timestamp + '.txt';
            
            downloadAsFile(txtData, filename, 'text/plain');
            console.log('TXT export completed:', annotations.length, 'annotations');
        })
        .catch(function(error) {
            console.error('Error exporting TXT:', error);
            alert('Could not export annotations as TXT: ' + error.message);
        });
    
    document.getElementById('exportDropdown').style.display = 'none';
}

function exportAnnotationsCSV() {
    console.log('Exporting annotations as CSV...');
    
    loadRawAnnotationData()
        .then(function(annotations) {
            if (!annotations || annotations.length === 0) {
                alert('No annotations found to export.');
                return;
            }
            
            // Convert to CSV format
            var csvData = convertAnnotationsToCSV(annotations);
            
            // Generate filename with timestamp
            var patientId = PATIENT_ID || 'Patient';
            var timestamp = getTimestamp();
            var filename = 'Annotations_' + patientId + '_' + timestamp + '.csv';
            
            downloadAsFile(csvData, filename, 'text/csv');
            console.log('CSV export completed:', annotations.length, 'annotations');
        })
        .catch(function(error) {
            console.error('Error exporting CSV:', error);
            alert('Could not export annotations as CSV: ' + error.message);
        });
    
    document.getElementById('exportDropdown').style.display = 'none';
}

function exportAnnotationsJSON() {
    console.log('Exporting annotations as JSON...');
    
    loadRawAnnotationData()
        .then(function(annotations) {
            if (!annotations || annotations.length === 0) {
                alert('No annotations found to export.');
                return;
            }
            
            // Convert to JSON format
            var jsonData = convertAnnotationsToJSON(annotations);
            
            // Generate filename with timestamp
            var patientId = PATIENT_ID || 'Patient';
            var timestamp = getTimestamp();
            var filename = 'Annotations_' + patientId + '_' + timestamp + '.json';
            
            downloadAsFile(JSON.stringify(jsonData, null, 2), filename, 'application/json');
            console.log('JSON export completed:', annotations.length, 'annotations');
        })
        .catch(function(error) {
            console.error('Error exporting JSON:', error);
            alert('Could not export annotations as JSON: ' + error.message);
        });
    
    document.getElementById('exportDropdown').style.display = 'none';
}

// Conversion functions

function convertAnnotationsToTXT(annotations) {
    // Convert back to original TXT format
    var txtLines = [];
    
    annotations.forEach(function(annotation) {
        if (annotation.raw) {
            // Use original raw format if available
            txtLines.push(annotation.raw);
        } else {
            // Reconstruct the format: active,id,type,[content],color,zoom,date
            var line = annotation.active + ',' + 
                      annotation.id + ',' + 
                      annotation.type + ',' + 
                      '[' + annotation.content + '],' + 
                      annotation.color + ',' + 
                      (annotation.zoom || '1.0') + ',' + 
                      (annotation.date || '');
            txtLines.push(line);
        }
    });
    
    return txtLines.join('\n');
}

function convertAnnotationsToCSV(annotations) {
    // Create CSV with comprehensive annotation data
    var csvRows = [];
    
    // Header row
    csvRows.push('ID,Type,TypeName,Color,Coordinates,Zoom,Date,Details');
    
    annotations.forEach(function(annotation) {
        // Parse coordinates for better display
        var coordinates = parseCoordinatesFromContent(annotation.content);
        var coordStr = coordinates.map(function(coord) {
            return '(' + coord.x + ',' + coord.y + ')';
        }).join(';');
        
        // Extract additional details based on type
        var details = extractAnnotationDetails(annotation);
        
        // Escape quotes and commas for CSV
        var row = [
            annotation.id,
            annotation.type,
            '"' + (annotation.typeName || annotationTypes[annotation.type] || 'Unknown') + '"',
            annotation.color,
            '"' + coordStr + '"',
            annotation.zoom || '1.0',
            '"' + (annotation.date || '') + '"',
            '"' + details + '"'
        ];
        
        csvRows.push(row.join(','));
    });
    
    return csvRows.join('\n');
}

function convertAnnotationsToJSON(annotations) {
    // Create structured JSON format
    var jsonAnnotations = annotations.map(function(annotation) {
        var coordinates = parseCoordinatesFromContent(annotation.content);
        var details = extractAnnotationDetails(annotation);
        
        return {
            id: annotation.id,
            type: {
                code: annotation.type,
                name: annotation.typeName || annotationTypes[annotation.type] || 'Unknown'
            },
            coordinates: coordinates,
            color: annotation.color,
            zoom: parseFloat(annotation.zoom || '1.0'),
            date: annotation.date || '',
            details: details,
            active: annotation.active === 1
        };
    });
    
    return {
        format: 'Polyscope Annotations Export',
        version: '1.0',
        exportDate: new Date().toISOString(),
        patientId: PATIENT_ID || 'Unknown',
        channelId: CHANNEL_ID || 'Unknown',
        totalAnnotations: annotations.length,
        annotations: jsonAnnotations
    };
}

// Helper functions for parsing annotation data

function parseCoordinatesFromContent(content) {
    var coordinates = [];
    var regex = /\(([^)]+)\)/g;
    var match;
    
    while ((match = regex.exec(content)) !== null) {
        var coordPair = match[1].split(',');
        if (coordPair.length >= 2) {
            coordinates.push({
                x: parseFloat(coordPair[0]),
                y: parseFloat(coordPair[1])
            });
        }
    }
    
    return coordinates;
}

function extractAnnotationDetails(annotation) {
    var details = '';
    
    switch (parseInt(annotation.type)) {
        case 5: // Text annotation
            var textMatch = annotation.content.match(/\("([^"]*)"\)/);
            if (textMatch && textMatch[1]) {
                details = 'Text: ' + textMatch[1];
            }
            break;
        case 0: // Line
        case 1: // Arrow
            var coords = parseCoordinatesFromContent(annotation.content);
            if (coords.length >= 2) {
                var length = Math.sqrt(
                    Math.pow(coords[1].x - coords[0].x, 2) + 
                    Math.pow(coords[1].y - coords[0].y, 2)
                );
                details = 'Length: ' + length.toFixed(2);
            }
            break;
        case 2: // Rectangle
            var coords = parseCoordinatesFromContent(annotation.content);
            if (coords.length >= 2) {
                var width = Math.abs(coords[1].x - coords[0].x);
                var height = Math.abs(coords[1].y - coords[0].y);
                var area = width * height;
                details = 'Width: ' + width.toFixed(2) + ', Height: ' + height.toFixed(2) + ', Area: ' + area.toFixed(2);
            }
            break;
        case 3: // Ellipse
            var coords = parseCoordinatesFromContent(annotation.content);
            if (coords.length >= 2) {
                var rx = Math.abs(coords[1].x - coords[0].x) / 2;
                var ry = Math.abs(coords[1].y - coords[0].y) / 2;
                var area = Math.PI * rx * ry;
                details = 'Radii: ' + rx.toFixed(2) + 'x' + ry.toFixed(2) + ', Area: ' + area.toFixed(2);
            }
            break;
        case 4: // Free Hand Drawing
            var coords = parseCoordinatesFromContent(annotation.content);
            details = 'Points: ' + coords.length;
            break;
        case 6: // Dot
            var coords = parseCoordinatesFromContent(annotation.content);
            if (coords.length >= 1) {
                details = 'Position: (' + coords[0].x.toFixed(2) + ', ' + coords[0].y.toFixed(2) + ')';
            }
            break;
        default:
            details = 'Type: ' + (annotationTypes[annotation.type] || 'Unknown');
    }
    
    return details;
}

// Helper function to generate timestamp for unique filenames
function getTimestamp() {
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var seconds = String(now.getSeconds()).padStart(2, '0');
    
    return year + month + day + '_' + hours + minutes + seconds;
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
    
    console.log('Downloaded file:', filename);
}

// Rest of your existing functions remain the same...
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
    
    console.log('Main.js initialized with config:', {
        ANNOTATIONS_PATH: ANNOTATIONS_PATH,
        PATIENT_ID: PATIENT_ID,
        CHANNEL_ID: CHANNEL_ID,
        CONTENT_ID: CONTENT_ID,
        VIEWER_VARNAME: VIEWER_VARNAME
    });
});