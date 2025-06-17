<?php
session_start();
include 'auth/session_timeout.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /auth/login.php');
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$isAdmin = ($username === 'yshokrollahi');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Polyscope - Pathology Image Manager</title>
    <link rel="icon" type="image/png" href="favicon.png">
    
    <!-- External Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
    
    <style>
    /* Inline CSS for File Manager */
    :root {
      --primary-color: #2563eb;
      --primary-hover: #1d4ed8;
      --secondary-color: #64748b;
      --success-color: #059669;
      --warning-color: #d97706;
      --error-color: #dc2626;
      --bg-primary: #ffffff;
      --bg-secondary: #f8fafc;
      --bg-tertiary: #f1f5f9;
      --text-primary: #1e293b;
      --text-secondary: #64748b;
      --text-muted: #94a3b8;
      --border-color: #e2e8f0;
      --border-radius: 8px;
      --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
      --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
      --space-2: 0.5rem;
      --space-3: 0.75rem;
      --space-4: 1rem;
      --space-6: 1.5rem;
    }
    
    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background-color: var(--bg-secondary);
      color: var(--text-primary);
      line-height: 1.6;
    }
    
    .app-header {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
      color: white;
      padding: var(--space-4) var(--space-6);
      box-shadow: var(--shadow-md);
    }
    
    .header-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .app-title {
      font-size: 1.5rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: var(--space-3);
    }
    
    .user-info {
      display: flex;
      align-items: center;
      gap: var(--space-4);
    }
    
    .btn {
      padding: var(--space-2) var(--space-4);
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 500;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: var(--space-2);
      transition: all 0.2s;
    }
    
    .btn-primary { background: var(--primary-color); color: white; }
    .btn-secondary { background: var(--secondary-color); color: white; }
    .btn-success { background: var(--success-color); color: white; }
    .btn-outline { background: transparent; color: white; border: 1px solid rgba(255,255,255,0.3); }
    .btn:hover { opacity: 0.9; transform: translateY(-1px); }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    
    .main-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: var(--space-6);
    }
    
    .file-manager {
      background: var(--bg-primary);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-md);
      overflow: hidden;
    }
    
    .file-toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: var(--space-4) var(--space-6);
      border-bottom: 1px solid var(--border-color);
      background: var(--bg-tertiary);
    }
    
    .toolbar-left, .toolbar-right {
      display: flex;
      gap: var(--space-2);
    }
    
    .breadcrumb {
      display: flex;
      align-items: center;
      padding: var(--space-4) var(--space-6);
      border-bottom: 1px solid var(--border-color);
      background: var(--bg-secondary);
      font-size: 0.875rem;
    }
    
    .breadcrumb-item {
      color: var(--text-secondary);
      cursor: pointer;
      padding: var(--space-2);
      border-radius: var(--border-radius);
      transition: all 0.2s;
    }
    
    .breadcrumb-item:hover { background: var(--bg-tertiary); }
    .breadcrumb-item.active { color: var(--primary-color); font-weight: 500; }
    
    .upload-zone {
      margin: var(--space-6);
      padding: var(--space-6);
      border: 2px dashed var(--border-color);
      border-radius: var(--border-radius);
      text-align: center;
      transition: all 0.2s;
      cursor: pointer;
    }
    
    .upload-zone:hover, .upload-zone.drag-active {
      border-color: var(--primary-color);
      background: rgba(37, 99, 235, 0.05);
    }
    
    .upload-icon {
      font-size: 3rem;
      margin-bottom: var(--space-4);
      opacity: 0.5;
    }
    
    .file-list {
      min-height: 400px;
    }
    
    .file-item {
      display: flex;
      align-items: center;
      padding: var(--space-3) var(--space-6);
      border-bottom: 1px solid var(--border-color);
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .file-item:hover { background: var(--bg-tertiary); }
    .file-item.selected { background: rgba(37, 99, 235, 0.1); border-color: var(--primary-color); }
    
    .file-checkbox {
      margin-right: var(--space-3);
    }
    
    .file-icon {
      font-size: 1.5rem;
      margin-right: var(--space-3);
      width: 24px;
      text-align: center;
    }
    
    .file-info {
      flex: 1;
    }
    
    .file-name {
      font-weight: 500;
      margin-bottom: 2px;
    }
    
    .file-details {
      font-size: 0.75rem;
      color: var(--text-muted);
    }
    
    .processing-view {
      display: none;
      padding: var(--space-6);
    }
    
    .processing-view.active {
      display: block;
    }
    
    .job-item {
      display: flex;
      align-items: center;
      padding: var(--space-4);
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      margin-bottom: var(--space-3);
      background: var(--bg-primary);
    }
    
    .status-indicator {
      display: inline-flex;
      align-items: center;
      gap: var(--space-2);
      padding: var(--space-1) var(--space-3);
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .status-pending { background: rgba(245, 158, 11, 0.1); color: #d97706; }
    .status-processing { background: rgba(59, 130, 246, 0.1); color: #2563eb; }
    .status-completed { background: rgba(5, 150, 105, 0.1); color: #059669; }
    .status-failed { background: rgba(220, 38, 38, 0.1); color: #dc2626; }
    
    /* Utility Classes */
    .hidden { display: none !important; }
    .loading { opacity: 0.6; pointer-events: none; }
    
    /* Loading Overlay */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.9);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s;
    }
    
    .loading-overlay.active {
      opacity: 1;
      visibility: visible;
    }
    
    .spinner {
      width: 40px;
      height: 40px;
      border: 4px solid var(--border-color);
      border-top: 4px solid var(--primary-color);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-bottom: var(--space-4);
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .header-content, .toolbar-left, .toolbar-right {
        flex-direction: column;
        gap: var(--space-2);
      }
      
      .file-item {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .main-container {
        padding: var(--space-4);
      }
    }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="app-title">
                <span>🔬</span>
                <span>Polyscope - Pathology Image Manager</span>
            </div>
            
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
                <?php if ($isAdmin): ?>
                    <a href="auth/admin_dashboard.php" class="btn btn-outline">Dashboard</a>
                <?php endif; ?>
                <a href="auth/logout.php" class="btn btn-outline">Sign Out</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- File Manager View -->
        <div id="fileManagerView" class="view active">
            <div class="file-manager" id="fileManager">
                <!-- Toolbar -->
                <div class="file-toolbar" id="fileToolbar">
                    <div class="toolbar-left">
                        <button id="newFolderBtn" class="btn btn-secondary">
                            📁 New Folder
                        </button>
                        <button id="uploadBtn" class="btn btn-primary">
                            📤 Upload Files
                        </button>
                        <button id="refreshBtn" class="btn btn-outline">
                            🔄 Refresh
                        </button>
                    </div>
                    
                    <div class="toolbar-right">
                        <button id="deleteBtn" class="btn btn-outline" disabled>
                            🗑️ Delete
                        </button>
                        <button id="processBtn" class="btn btn-success" disabled>
                            ⚙️ Process to DZI
                        </button>
                    </div>
                </div>
                
                <!-- Breadcrumb -->
                <div class="breadcrumb" id="breadcrumb">
                    <span class="breadcrumb-item active">🏠 Home</span>
                </div>
                
                <!-- Upload Zone -->
                <div class="upload-zone" id="uploadZone">
                    <div class="upload-icon">📤</div>
                    <h3>Drop files here to upload</h3>
                    <p>Or click to browse files</p>
                    <p class="file-details">Supported: .svs, .ndpi, .czi, .scn, .jpg, .png, .tiff, .dcm</p>
                </div>
                
                <!-- File List -->
                <div class="file-list" id="fileList">
                    <!-- Files will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Processing View -->
        <div id="processingView" class="view processing-view">
            <h2>DZI Processing Queue</h2>
            <div id="jobsList">
                <!-- Processing jobs will appear here -->
            </div>
        </div>
    </main>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
        <p>Loading...</p>
    </div>

    <!-- Inline JavaScript for immediate functionality -->
 <!-- Replace your entire JavaScript section with this -->
 <!-- Complete DZI Integration - Replace your entire JavaScript section with this -->
 <script>
    // Basic configuration
    window.PolyscopeConfig = {
      user: {
        username: '<?php echo $username; ?>',
        isAdmin: <?php echo $isAdmin ? 'true' : 'false'; ?>
      },
      api: {
        endpoints: {
          fileManager: '/api/fileManager.php',
          upload: '/api/upload.php'
        }
      }
    };

    // Basic utilities
    window.PolyscopeUtils = {
      string: {
        formatFileSize: function(bytes) {
          if (bytes === 0) return '0 Bytes';
          const k = 1024;
          const sizes = ['Bytes', 'KB', 'MB', 'GB'];
          const i = Math.floor(Math.log(bytes) / Math.log(k));
          return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
      },
      dom: {
        createElement: function(tag, attributes) {
          attributes = attributes || {};
          const element = document.createElement(tag);
          Object.keys(attributes).forEach(function(key) {
            const value = attributes[key];
            if (key === 'className') {
              element.className = value;
            } else if (key === 'textContent') {
              element.textContent = value;
            } else {
              element.setAttribute(key, value);
            }
          });
          return element;
        }
      }
    };

    // Basic App controller
    window.PolyscopeApp = {
      showLoading: function(message) {
        message = message || 'Loading...';
        const overlay = document.getElementById('loadingOverlay');
        const text = overlay.querySelector('p');
        if (text) text.textContent = message;
        overlay.classList.add('active');
      },
      
      hideLoading: function() {
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.remove('active');
      }
    };

    // DZI Processing Integration
    class DZIProcessor {
        constructor(fileManager) {
            this.fileManager = fileManager;
            this.activeJobs = new Map();
            this.statusCheckInterval = null;
            
            // Start monitoring job status
            this.startStatusMonitoring();
        }
        
        // Main function to process selected files using your existing system
        async processSelectedFiles() {
            if (this.fileManager.selectedFiles.size === 0) {
                this.fileManager.showError('No files selected for processing');
                return;
            }
            
            // Check if selected files are suitable for DZI processing
            const selectedFiles = Array.from(this.fileManager.selectedFiles);
            const pathologyFiles = selectedFiles.filter(path => {
                const ext = path.toLowerCase().split('.').pop();
                return ['svs', 'ndpi', 'czi', 'scn', 'mrxs', 'vsi', 'vms', 'tiff', 'tif'].includes(ext);
            });
            
            if (pathologyFiles.length === 0) {
                this.fileManager.showError('Please select pathology slide files (.svs, .ndpi, .czi, .scn, etc.) for DZI processing');
                return;
            }
            
            if (pathologyFiles.length !== selectedFiles.length) {
                const proceed = confirm('Some selected files are not pathology slides. Only ' + pathologyFiles.length + ' suitable files will be processed. Continue?');
                if (!proceed) return;
            }
            
            try {
                this.fileManager.showLoading('Starting DZI processing for ' + pathologyFiles.length + ' file(s)...');
                
                // Convert selected file paths to full system paths
                const filesToProcess = pathologyFiles.map(relativePath => {
                    return '/media/Users/' + PolyscopeConfig.user.username + '/' + relativePath;
                });
                
                console.log('Processing files for DZI:', filesToProcess);
                
                // Call your existing issueUploadProject.php
                const response = await fetch('/issueUploadProject.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'path=' + JSON.stringify(filesToProcess) + '&isDir=false'
                });
                
                const result = await response.json();
                console.log('DZI processing response:', result);
                
                if (result && result.typ) {
                    if (result.typ === 'single') {
                        this.handleSingleJobResult(result);
                    } else if (result.typ === 'multiple') {
                        this.handleMultipleJobsResult(result);
                    }
                    
                    this.fileManager.showSuccess('DZI processing started! ' + pathologyFiles.length + ' job(s) added to queue.');
                    this.showProcessingDialog();
                } else {
                    this.fileManager.showError('Failed to start DZI processing');
                }
                
            } catch (error) {
                this.fileManager.showError('Error starting DZI processing: ' + error.message);
                console.error('DZI processing error:', error);
            } finally {
                this.fileManager.hideLoading();
            }
        }
        
        handleSingleJobResult(result) {
            this.activeJobs.set(result.guid, {
                guid: result.guid,
                id: result.id,
                name: result.name,
                status: 'checksum',
                email: 'EMAIL_PLACE_HOLDER',
                addedTime: Date.now()
            });
        }
        
        handleMultipleJobsResult(result) {
            if (result.jobs) {
                result.jobs.forEach(job => {
                    this.activeJobs.set(job.guid, {
                        guid: job.guid,
                        id: job.id,
                        name: job.name,
                        fileId: job.fileId,
                        status: 'checksum',
                        email: 'EMAIL_PLACE_HOLDER',
                        addedTime: Date.now()
                    });
                });
            }
        }
        
        // Show processing status dialog
        showProcessingDialog() {
            // Switch to processing view
            document.getElementById('fileManagerView').style.display = 'none';
            document.getElementById('processingView').style.display = 'block';
            document.getElementById('processingView').classList.add('active');
            
            this.updateProcessingView();
        }
        
        // Add button to switch back to file manager
        showFileManagerView() {
            document.getElementById('processingView').style.display = 'none';
            document.getElementById('processingView').classList.remove('active');
            document.getElementById('fileManagerView').style.display = 'block';
        }
        
        // Start monitoring job status using your existing getProjectStatus.php
        startStatusMonitoring() {
            if (this.statusCheckInterval) {
                clearInterval(this.statusCheckInterval);
            }
            
            this.statusCheckInterval = setInterval(() => {
                this.checkJobStatus();
            }, 3000); // Check every 3 seconds like your old system
        }
        
        async checkJobStatus() {
            try {
                const response = await fetch('/getProjectStatus.php', {
                    method: 'POST'
                });
                
                const statusData = await response.json();
                
                if (statusData && statusData.valid && statusData.jobs) {
                    this.updateJobStatuses(statusData.jobs);
                    this.updateProcessingView();
                }
                
            } catch (error) {
                console.error('Error checking job status:', error);
            }
        }
        
        updateJobStatuses(serverJobs) {
            // Update status of tracked jobs
            serverJobs.forEach(serverJob => {
                const jobData = serverJob.data;
                if (this.activeJobs.has(jobData.guid)) {
                    const localJob = this.activeJobs.get(jobData.guid);
                    localJob.status = jobData.status;
                    localJob.origFilename = jobData.origFilename;
                    localJob.email = jobData.email;
                    localJob.finalPath = jobData.finalPath;
                    localJob.finalFilename = jobData.finalFilename;
                }
            });
            
            // Add new jobs that might have been started elsewhere
            serverJobs.forEach(serverJob => {
                const jobData = serverJob.data;
                if (!this.activeJobs.has(jobData.guid)) {
                    this.activeJobs.set(jobData.guid, {
                        guid: jobData.guid,
                        id: jobData.id,
                        status: jobData.status,
                        origFilename: jobData.origFilename,
                        email: jobData.email,
                        finalPath: jobData.finalPath,
                        finalFilename: jobData.finalFilename,
                        addedTime: Date.now()
                    });
                }
            });
            
            // Remove completed jobs after some time
            this.cleanupCompletedJobs();
        }
        
        updateProcessingView() {
            const jobsList = document.getElementById('jobsList');
            if (!jobsList) return;
            
            // Add back to file manager button if not exists
            let backButton = document.querySelector('#processingView .back-to-files');
            if (!backButton) {
                backButton = document.createElement('button');
                backButton.className = 'btn btn-secondary back-to-files';
                backButton.innerHTML = '← Back to File Manager';
                backButton.onclick = () => this.showFileManagerView();
                
                const header = document.querySelector('#processingView h2');
                if (header) {
                    header.appendChild(backButton);
                    header.style.display = 'flex';
                    header.style.justifyContent = 'space-between';
                    header.style.alignItems = 'center';
                }
            }
            
            jobsList.innerHTML = '';
            
            if (this.activeJobs.size === 0) {
                jobsList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-muted);"><p>No active DZI processing jobs</p><p>Select files in the file manager and click "Process to DZI" to start processing</p></div>';
                return;
            }
            
            // Sort jobs by newest first
            const sortedJobs = Array.from(this.activeJobs.values()).sort((a, b) => (b.addedTime || 0) - (a.addedTime || 0));
            
            sortedJobs.forEach(job => {
                const jobItem = this.createJobItem(job);
                jobsList.appendChild(jobItem);
            });
        }
        
        createJobItem(job) {
            const item = document.createElement('div');
            item.className = 'job-item';
            item.dataset.guid = job.guid;
            
            const statusClass = this.getStatusClass(job.status);
            const statusText = this.getStatusText(job.status);
            const progress = this.getStatusProgress(job.status);
            
            item.innerHTML = 
                '<div style="display: flex; align-items: center; padding: 1rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); margin-bottom: 0.5rem; background: var(--bg-primary);">' +
                '<div style="flex: 1;">' +
                '<div style="font-weight: 500; margin-bottom: 0.25rem;">' + this.escapeHtml(job.origFilename || job.name || 'Processing file') + '</div>' +
                '<div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;">ID: ' + job.id + ' | GUID: ' + job.guid + '</div>' +
                '<div style="display: flex; align-items: center; gap: 0.5rem;">' +
                '<span class="status-indicator ' + statusClass + '">' + statusText + '</span>' +
                '<div style="flex: 1; background: var(--bg-tertiary); border-radius: 4px; height: 6px; overflow: hidden;">' +
                '<div style="background: var(--primary-color); height: 100%; width: ' + progress + '%; transition: width 0.3s;"></div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div style="display: flex; gap: 0.5rem;">' +
                this.getJobActions(job) +
                '</div>' +
                '</div>';
            
            return item;
        }
        
        getStatusClass(status) {
            const statusMap = {
                'checksum': 'status-processing',
                'upload': 'status-processing',
                'uploading': 'status-processing',
                'uploaded': 'status-processing',
                'putToOwnFolder': 'status-processing',
                'estimateSize': 'status-processing',
                'readyForQueue': 'status-processing',
                'inQueue': 'status-processing',
                'processing': 'status-processing',
                'finished': 'status-completed',
                'emailSent': 'status-completed',
                'readyToBeRemoved': 'status-completed'
            };
            
            return statusMap[status] || 'status-pending';
        }
        
        getStatusText(status) {
            const statusTexts = {
                'checksum': 'Calculating Checksum',
                'upload': 'Preparing Upload',
                'uploading': 'Uploading',
                'uploaded': 'Upload Complete',
                'putToOwnFolder': 'Organizing Files',
                'estimateSize': 'Estimating Size',
                'readyForQueue': 'Ready for Processing',
                'inQueue': 'Queued for Processing',
                'processing': 'Converting to DZI',
                'finished': 'DZI Conversion Complete',
                'emailSent': 'Email Notification Sent',
                'readyToBeRemoved': 'Ready for Cleanup'
            };
            
            return statusTexts[status] || status;
        }
        
        getStatusProgress(status) {
            const progressMap = {
                'checksum': 10,
                'upload': 20,
                'uploading': 30,
                'uploaded': 40,
                'putToOwnFolder': 50,
                'estimateSize': 60,
                'readyForQueue': 70,
                'inQueue': 75,
                'processing': 85,
                'finished': 100,
                'emailSent': 100,
                'readyToBeRemoved': 100
            };
            
            return progressMap[status] || 5;
        }
        
        getJobActions(job) {
            let actions = '';
            
            // Email input for jobs that need it
            if (job.email === 'EMAIL_PLACE_HOLDER' || job.email === '') {
                actions += '<button class="btn btn-outline" onclick="window.dziProcessor.showEmailDialog(\'' + job.guid + '\')">✉️ Set Email</button>';
            } else if (job.email && job.email !== 'EMAIL_PLACE_HOLDER') {
                actions += '<span style="font-size: 0.75rem; color: var(--text-muted);">📧 ' + this.escapeHtml(job.email) + '</span>';
            }
            
            // View DZI link for completed jobs
            if (job.status === 'finished' && job.email && job.email !== 'EMAIL_PLACE_HOLDER') {
                const cleanEmail = job.email.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
                actions += '<button class="btn btn-success" onclick="window.dziProcessor.viewDZI(\'' + job.guid + '\', \'' + cleanEmail + '\')">👁️ View DZI</button>';
            }
            
            return actions;
        }
        
        // Show email dialog for a specific job
        async showEmailDialog(guid) {
            const job = this.activeJobs.get(guid);
            if (!job) return;
            
            const currentEmail = (job.email && job.email !== 'EMAIL_PLACE_HOLDER') ? job.email : '';
            const email = prompt('Enter email address for job:\n' + (job.origFilename || job.name) + '\n\nNotification will be sent when DZI conversion is complete.', currentEmail);
            
            if (email && email.trim() && email.includes('@')) {
                try {
                    this.fileManager.showLoading('Updating email...');
                    await this.updateJobEmail(guid, email.trim());
                    this.fileManager.showSuccess('Email updated successfully');
                } catch (error) {
                    this.fileManager.showError('Failed to update email: ' + error.message);
                } finally {
                    this.fileManager.hideLoading();
                }
            }
        }
        
        // Update job email using your existing system
        async updateJobEmail(guid, email) {
            const response = await fetch('/issueUpdateEmail.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'email=' + JSON.stringify(email) + '&guid=' + JSON.stringify(guid)
            });
            
            return await response.json();
        }
        
        // View DZI file using your existing customer system
        viewDZI(guid, cleanEmail) {
            // Open your customer viewer - this matches your existing system
            const viewerUrl = '/customers/' + cleanEmail + '/';
            window.open(viewerUrl, '_blank');
        }
        
        cleanupCompletedJobs() {
            // Remove jobs that have been completed for more than 30 minutes
            const cutoffTime = Date.now() - (30 * 60 * 1000);
            
            this.activeJobs.forEach((job, guid) => {
                if (job.status === 'readyToBeRemoved' && job.completedTime && job.completedTime < cutoffTime) {
                    this.activeJobs.delete(guid);
                } else if (job.status === 'emailSent' && !job.completedTime) {
                    job.completedTime = Date.now();
                }
            });
        }
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        destroy() {
            if (this.statusCheckInterval) {
                clearInterval(this.statusCheckInterval);
            }
        }
    }

    // Enhanced File Manager with DZI Processing
    class EnhancedPolyscopeFileManager {
        constructor() {
            this.currentPath = '';
            this.selectedFiles = new Set();
            this.isUploading = false;
            
            this.initializeEventListeners();
            this.loadDirectory('');
            
            // Initialize DZI processor
            this.dziProcessor = new DZIProcessor(this);
            window.dziProcessor = this.dziProcessor; // Make globally accessible for onclick handlers
        }
        
        initializeEventListeners() {
            // Toolbar buttons
            document.getElementById('newFolderBtn').addEventListener('click', () => this.showNewFolderDialog());
            document.getElementById('uploadBtn').addEventListener('click', () => this.showUploadDialog());
            document.getElementById('refreshBtn').addEventListener('click', () => this.refreshCurrentDirectory());
            document.getElementById('deleteBtn').addEventListener('click', () => this.deleteSelectedFiles());
            document.getElementById('processBtn').addEventListener('click', () => this.processSelectedFiles());
            
            // Upload zone drag and drop
            const uploadZone = document.getElementById('uploadZone');
            uploadZone.addEventListener('click', () => this.showUploadDialog());
            uploadZone.addEventListener('dragover', (e) => this.handleDragOver(e));
            uploadZone.addEventListener('dragleave', (e) => this.handleDragLeave(e));
            uploadZone.addEventListener('drop', (e) => this.handleDrop(e));
            
            // File selection handling
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Delete' && this.selectedFiles.size > 0) {
                    this.deleteSelectedFiles();
                }
            });
        }
        
        async loadDirectory(path) {
            try {
                this.showLoading('Loading directory...');
                
                const response = await this.apiCall('listDirectory', { path: path });
                
                if (response.success) {
                    this.currentPath = response.currentPath || '';
                    this.renderFileList(response.files);
                    this.updateBreadcrumb();
                    this.clearSelection();
                } else {
                    this.showError(response.error || 'Failed to load directory');
                }
            } catch (error) {
                this.showError('Error loading directory: ' + error.message);
            } finally {
                this.hideLoading();
            }
        }
        
        renderFileList(files) {
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = '';
            
            if (files.length === 0) {
                fileList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-muted);"><p>This folder is empty</p><p>Drag and drop files here or use the upload button</p></div>';
                return;
            }
            
            // Sort files: directories first, then by name
            files.sort(function(a, b) {
                if (a.type !== b.type) {
                    return a.type === 'directory' ? -1 : 1;
                }
                return a.name.localeCompare(b.name);
            });
            
            const self = this;
            files.forEach(function(file) {
                const fileItem = self.createFileItem(file);
                fileList.appendChild(fileItem);
            });
        }
        
        createFileItem(file) {
            const item = document.createElement('div');
            item.className = 'file-item';
            item.dataset.path = file.path;
            item.dataset.type = file.type;
            
            const icon = this.getFileIcon(file);
            const formattedSize = this.formatFileSize(file.size);
            const formattedDate = new Date(file.modified * 1000).toLocaleDateString();
            
            item.innerHTML = '<input type="checkbox" class="file-checkbox" data-path="' + file.path + '">' +
                '<div class="file-icon">' + icon + '</div>' +
                '<div class="file-info">' +
                '<div class="file-name">' + this.escapeHtml(file.name) + '</div>' +
                '<div class="file-details">' +
                (file.type === 'directory' ? 'Folder' : formattedSize) + ' • ' + formattedDate +
                (file.isPathologySlide ? ' • Pathology Slide' : '') +
                '</div></div>';
            
            // Add click handlers
            const checkbox = item.querySelector('.file-checkbox');
            const self = this;
            
            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                self.toggleFileSelection(file.path, checkbox.checked);
            });
            
            item.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox') {
                    if (file.type === 'directory') {
                        self.navigateToDirectory(file.path);
                    } else {
                        // Toggle selection for files
                        checkbox.checked = !checkbox.checked;
                        self.toggleFileSelection(file.path, checkbox.checked);
                    }
                }
            });
            
            item.addEventListener('dblclick', function(e) {
                e.preventDefault();
                if (file.type === 'directory') {
                    self.navigateToDirectory(file.path);
                } else if (file.isPathologySlide) {
                    self.viewFile(file.path);
                }
            });
            
            return item;
        }
        
        getFileIcon(file) {
            if (file.type === 'directory') return '📁';
            
            const ext = file.extension ? file.extension.toLowerCase() : '';
            
            // Pathology slide formats
            if (['svs', 'ndpi', 'czi', 'scn', 'mrxs', 'vsi', 'vms'].indexOf(ext) !== -1) {
                return '🔬';
            }
            
            // Image formats
            if (['jpg', 'jpeg', 'png', 'tiff', 'tif', 'bmp'].indexOf(ext) !== -1) {
                return '🖼️';
            }
            
            // Medical formats
            if (['dcm', 'nii', 'nrrd'].indexOf(ext) !== -1) {
                return '🏥';
            }
            
            // Documents
            if (ext === 'pdf') return '📄';
            
            return '📄';
        }
        
        toggleFileSelection(path, selected) {
            if (selected) {
                this.selectedFiles.add(path);
            } else {
                this.selectedFiles.delete(path);
            }
            
            this.updateSelectionUI();
        }
        
        updateSelectionUI() {
            const selectedCount = this.selectedFiles.size;
            
            // Update toolbar buttons
            const deleteBtn = document.getElementById('deleteBtn');
            const processBtn = document.getElementById('processBtn');
            
            deleteBtn.disabled = selectedCount === 0;
            processBtn.disabled = selectedCount === 0;
            
            // Update visual selection
            const self = this;
            document.querySelectorAll('.file-item').forEach(function(item) {
                const path = item.dataset.path;
                const isSelected = self.selectedFiles.has(path);
                
                if (isSelected) {
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                }
                item.querySelector('.file-checkbox').checked = isSelected;
            });
            
            // Update toolbar text and check if any pathology slides are selected
            const selectedFiles = Array.from(this.selectedFiles);
            const pathologyFiles = selectedFiles.filter(path => {
                const ext = path.toLowerCase().split('.').pop();
                return ['svs', 'ndpi', 'czi', 'scn', 'mrxs', 'vsi', 'vms', 'tiff', 'tif'].includes(ext);
            });
            
            if (selectedCount > 0) {
                deleteBtn.textContent = '🗑️ Delete (' + selectedCount + ')';
                if (pathologyFiles.length > 0) {
                    processBtn.textContent = '⚙️ Process to DZI (' + pathologyFiles.length + ')';
                    processBtn.disabled = false;
                } else {
                    processBtn.textContent = '⚙️ Process to DZI (no slides)';
                    processBtn.disabled = true;
                }
            } else {
                deleteBtn.textContent = '🗑️ Delete';
                processBtn.textContent = '⚙️ Process to DZI';
            }
        }
        
        clearSelection() {
            this.selectedFiles.clear();
            this.updateSelectionUI();
        }
        
        navigateToDirectory(path) {
            this.loadDirectory(path);
        }
        
        updateBreadcrumb() {
            const breadcrumb = document.getElementById('breadcrumb');
            breadcrumb.innerHTML = '';
            
            // Add home
            const homeItem = document.createElement('span');
            homeItem.className = 'breadcrumb-item';
            homeItem.textContent = '🏠 Home';
            const self = this;
            homeItem.addEventListener('click', function() {
                self.navigateToDirectory('');
            });
            breadcrumb.appendChild(homeItem);
            
            if (this.currentPath) {
                const pathParts = this.currentPath.split('/').filter(function(part) {
                    return part;
                });
                let currentPath = '';
                
                pathParts.forEach(function(part, index) {
                    currentPath += '/' + part;
                    
                    const separator = document.createElement('span');
                    separator.textContent = ' / ';
                    separator.style.color = 'var(--text-muted)';
                    breadcrumb.appendChild(separator);
                    
                    const pathItem = document.createElement('span');
                    pathItem.className = 'breadcrumb-item';
                    pathItem.textContent = part;
                    
                    if (index === pathParts.length - 1) {
                        pathItem.classList.add('active');
                    } else {
                        const targetPath = currentPath;
                        pathItem.addEventListener('click', function() {
                            self.navigateToDirectory(targetPath);
                        });
                    }
                    
                    breadcrumb.appendChild(pathItem);
                });
            } else {
                homeItem.classList.add('active');
            }
        }
        
        async showNewFolderDialog() {
            const folderName = prompt('Enter folder name:');
            
            if (folderName && folderName.trim()) {
                try {
                    this.showLoading('Creating folder...');
                    
                    const response = await this.apiCall('createFolder', {
                        path: this.currentPath,
                        name: folderName.trim()
                    });
                    
                    if (response.success) {
                        this.showSuccess('Folder created successfully');
                        this.refreshCurrentDirectory();
                    } else {
                        this.showError(response.error || 'Failed to create folder');
                    }
                } catch (error) {
                    this.showError('Error creating folder: ' + error.message);
                } finally {
                    this.hideLoading();
                }
            }
        }
        
        showUploadDialog() {
            const input = document.createElement('input');
            input.type = 'file';
            input.multiple = true;
            input.accept = '.svs,.ndpi,.czi,.scn,.mrxs,.vsi,.vms,.jpg,.jpeg,.png,.tiff,.tif,.bmp,.dcm,.nii,.nrrd,.pdf';
            
            const self = this;
            input.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    self.uploadFiles(Array.from(e.target.files));
                }
            });
            
            input.click();
        }
        
        async uploadFiles(files) {
            if (this.isUploading) {
                this.showError('Upload already in progress');
                return;
            }
            
            this.isUploading = true;
            
            try {
                this.showLoading('Uploading ' + files.length + ' file(s)...');
                
                const formData = new FormData();
                let targetDirectory = '/media/Users/' + PolyscopeConfig.user.username;
                
                // Add current path if not empty
                if (this.currentPath && this.currentPath.trim() !== '') {
                    const cleanPath = this.currentPath.replace(/^\/+/, '');
                    targetDirectory += '/' + cleanPath;
                }
                
                console.log('Upload target directory:', targetDirectory);
                
                files.forEach(function(file) {
                    formData.append('files[]', file);
                });
                
                formData.append('directory', targetDirectory.replace(/\//g, '___SLASH___'));
                
                const response = await fetch('/api/upload.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                console.log('Upload result:', result);
                
                if (result.success) {
                    this.showSuccess(result.uploaded.length + ' file(s) uploaded successfully');
                    if (result.failed.length > 0) {
                        this.showError(result.failed.length + ' file(s) failed to upload');
                    }
                    this.refreshCurrentDirectory();
                } else {
                    this.showError(result.message || 'Upload failed');
                }
                
            } catch (error) {
                this.showError('Upload error: ' + error.message);
            } finally {
                this.isUploading = false;
                this.hideLoading();
            }
        }
        
        async deleteSelectedFiles() {
            if (this.selectedFiles.size === 0) return;
            
            const fileCount = this.selectedFiles.size;
            if (!confirm('Are you sure you want to delete ' + fileCount + ' item(s)?')) {
                return;
            }
            
            try {
                this.showLoading('Deleting files...');
                
                const filePaths = Array.from(this.selectedFiles);
                const response = await this.apiCall('deleteFiles', { files: filePaths });
                
                if (response.success) {
                    this.showSuccess(response.message);
                    this.refreshCurrentDirectory();
                } else {
                    this.showError(response.error || 'Delete failed');
                }
            } catch (error) {
                this.showError('Error deleting files: ' + error.message);
            } finally {
                this.hideLoading();
            }
        }
        
        // DZI Processing - delegate to DZI processor
        async processSelectedFiles() {
            await this.dziProcessor.processSelectedFiles();
        }
        
        refreshCurrentDirectory() {
            console.log('Refreshing directory:', this.currentPath);
            this.loadDirectory(this.currentPath);
        }
        
        handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            document.getElementById('uploadZone').classList.add('drag-active');
        }
        
        handleDragLeave(e) {
            e.preventDefault();
            document.getElementById('uploadZone').classList.remove('drag-active');
        }
        
        handleDrop(e) {
            e.preventDefault();
            document.getElementById('uploadZone').classList.remove('drag-active');
            
            const files = Array.from(e.dataTransfer.files);
            if (files.length > 0) {
                this.uploadFiles(files);
            }
        }
        
        viewFile(path) {
            console.log('Opening file viewer for:', path);
            this.showError('File viewer integration needed');
        }
        
        async apiCall(action, data) {
            data = data || {};
            const response = await fetch('/api/fileManager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(Object.assign({
                    action: action
                }, data))
            });
            
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            
            return await response.json();
        }
        
        showLoading(message) {
            PolyscopeApp.showLoading(message);
        }
        
        hideLoading() {
            PolyscopeApp.hideLoading();
        }
        
        showSuccess(message) {
            alert('✅ ' + message);
        }
        
        showError(message) {
            alert('❌ ' + message);
        }
        
        formatFileSize(bytes) {
            return PolyscopeUtils.string.formatFileSize(bytes);
        }
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Polyscope File Manager with DZI Processing initialized');
        
        // Initialize the enhanced file manager
        window.fileManager = new EnhancedPolyscopeFileManager();
        console.log('Enhanced Polyscope File Manager fully initialized');
    });
    </script>
</body>
</html>