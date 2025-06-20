// Enhanced DZI Processor with UI Integration
class DZIProcessor {
    constructor(fileManager) {
      console.log('=== DZI Processor Constructor Started ===');
      this.fileManager = fileManager;
      this.activeJobs = new Map();
      this.statusCheckInterval = null;
      
      // Processing steps mapping - Updated with server status mapping
      this.processingSteps = {
        'pending': { step: 1, label: 'Pending', description: 'Job queued for processing', icon: '⏳' },
        'checksum': { step: 2, label: 'Checksum', description: 'Computing file checksum', icon: '🔍' },
        'upload': { step: 3, label: 'Upload', description: 'Uploading file', icon: '📤' },
        'uploading': { step: 3, label: 'Uploading', description: 'File upload in progress', icon: '📤' },
        'uploaded': { step: 3, label: 'Uploaded', description: 'File uploaded successfully', icon: '✅' },
        'prepare': { step: 4, label: 'Prepare', description: 'Preparing for processing', icon: '⚙️' },
        'queue': { step: 5, label: 'Queued', description: 'Waiting in processing queue', icon: '🕐' },
        'inqueue': { step: 5, label: 'Queued', description: 'Waiting in processing queue', icon: '🕐' }, // Server format
        'processing': { step: 6, label: 'Processing', description: 'Converting to DZI format', icon: '🔄' },
        'finished': { step: 7, label: 'Finished', description: 'DZI conversion complete', icon: '🎉' },
        'completed': { step: 7, label: 'Completed', description: 'DZI conversion complete', icon: '🎉' },
        'failed': { step: 0, label: 'Failed', description: 'Processing failed', icon: '❌' },
        'error': { step: 0, label: 'Error', description: 'An error occurred', icon: '❌' }
      };
      
      // Status mapping from server format to UI format
      this.statusMapping = {
        'inQueue': 'queue',
        'inqueue': 'queue',
        'finished': 'finished',
        'completed': 'completed',
        'processing': 'processing',
        'failed': 'failed',
        'error': 'error',
        'pending': 'pending',
        'checksum': 'checksum',
        'upload': 'upload',
        'uploading': 'uploading',
        'uploaded': 'uploaded',
        'prepare': 'prepare'
      };
      
      // Initialize UI
      setTimeout(() => {
        this.initializeUI();
      }, 500);
      
      console.log('=== DZI Processor Constructor Complete ===');
    }
    
    // Map server status to UI status
    mapServerStatus(serverStatus) {
      const mapped = this.statusMapping[serverStatus] || serverStatus;
      console.log('Status mapping:', serverStatus, '->', mapped);
      return mapped;
    }
    
    // Enhanced job status updates with proper status mapping
    updateJobStatuses(serverJobs) {
      let hasActiveJobs = false;
      let statusChanged = false;
      
      console.log('=== updateJobStatuses called ===');
      console.log('Server jobs:', serverJobs);
      console.log('Active jobs in UI:', Array.from(this.activeJobs.entries()));
      
      // Update existing jobs with server status
      serverJobs.forEach(serverJobWrapper => {
        // Fix: Access the actual job data from the wrapper
        const serverJob = serverJobWrapper.data || serverJobWrapper;
        
        console.log('Processing server job:', serverJob.guid, 'raw status:', serverJob.status);
        
        if (this.activeJobs.has(serverJob.guid)) {
          const localJob = this.activeJobs.get(serverJob.guid);
          const oldStatus = localJob.status;
          
          // Map server status to UI status
          const mappedStatus = this.mapServerStatus(serverJob.status);
          localJob.status = mappedStatus || localJob.status;
          
          console.log('Job status comparison:', serverJob.guid, 'old:', oldStatus, 'new:', localJob.status);
          
          if (oldStatus !== localJob.status) {
            console.log('✅ Job status updated:', serverJob.guid, oldStatus, '->', localJob.status);
            statusChanged = true;
            
            // Show notification for important status changes
            if (localJob.status === 'finished' || localJob.status === 'completed') {
              this.fileManager.showSuccess(`DZI processing completed for ${localJob.origFilename}!`);
            } else if (localJob.status === 'failed' || localJob.status === 'error') {
              this.fileManager.showError(`DZI processing failed for ${localJob.origFilename}`);
            }
          } else {
            console.log('Job status unchanged:', serverJob.guid, 'status:', localJob.status);
          }
          
          // Mark job as completed for removal tracking
          if (['finished', 'completed', 'failed', 'error'].includes(localJob.status)) {
            if (!localJob.completedTime) {
              localJob.completedTime = Date.now();
              console.log('Marked job as completed:', serverJob.guid);
            }
          } else {
            // Job is still active
            hasActiveJobs = true;
            console.log('Job still active:', serverJob.guid, 'status:', localJob.status);
          }
        } else {
          console.log('⚠️ Server job not found in UI:', serverJob.guid, 'status:', serverJob.status);
          
          // Optionally add missing jobs if they're recent
          if (serverJob.status && !['finished', 'completed', 'failed', 'error'].includes(this.mapServerStatus(serverJob.status))) {
            console.log('Adding missing active job to UI:', serverJob.guid);
            const filename = this.extractFilenameFromPath(serverJob.origFilename);
            const jobData = {
              guid: serverJob.guid,
              id: serverJob.id || serverJob.guid,
              name: serverJob.name || filename,
              origFilename: filename,
              status: this.mapServerStatus(serverJob.status),
              addedTime: Date.now()
            };
            this.activeJobs.set(serverJob.guid, jobData);
            statusChanged = true;
            hasActiveJobs = true;
            console.log('Added missing job:', jobData);
          }
        }
      });
      
      // Remove jobs that have been completed for more than 2 minutes
      const now = Date.now();
      this.activeJobs.forEach((job, guid) => {
        if (job.completedTime && (now - job.completedTime > 120000)) { // 2 minutes
          this.activeJobs.delete(guid);
          console.log('Removed completed job after delay:', guid);
          statusChanged = true;
        }
      });
      
      // Update UI if anything changed
      if (statusChanged) {
        console.log('Status changed, updating UI');
        this.updateProcessingUI();
      } else {
        console.log('No status changes detected');
      }
      
      // Continue checking as long as we have active jobs
      if (!hasActiveJobs && this.activeJobs.size === 0) {
        console.log('No active jobs remaining, stopping status checks');
        this.stopStatusChecking();
      } else {
        console.log('Active jobs remaining:', hasActiveJobs, 'total UI jobs:', this.activeJobs.size);
      }
    }
    
    // Helper function to extract filename from full path
    extractFilenameFromPath(fullPath) {
      if (!fullPath) return 'Unknown file';
      return fullPath.split('/').pop() || fullPath;
    }
    
    // Initialize UI enhancements
    initializeUI() {
      // Don't add view switching buttons since navigation handles this now
      this.createProcessingStatusUI();
    }
    
    // Add view switching buttons to header
    addViewSwitchingButtons() {
      const headerContent = document.querySelector('.header-content');
      const userInfo = headerContent.querySelector('.user-info');
      
      // Add view switching buttons before user info
      const viewButtons = document.createElement('div');
      viewButtons.style.display = 'flex';
      viewButtons.style.gap = 'var(--space-2)';
      viewButtons.innerHTML = `
        <button id="showFileManagerBtn" class="btn btn-outline">📁 File Manager</button>
        <button id="showProcessingBtn" class="btn btn-outline">⚙️ Processing</button>
      `;
      
      headerContent.insertBefore(viewButtons, userInfo);
      
      // Add event listeners
      document.getElementById('showFileManagerBtn').addEventListener('click', () => {
        this.showFileManagerView();
      });
      
      document.getElementById('showProcessingBtn').addEventListener('click', () => {
        this.showProcessingView();
      });
    }
    
    // Create processing status UI
    createProcessingStatusUI() {
      const processingView = document.getElementById('processingView');
      const processingContent = document.getElementById('processingContent');
      
      processingContent.innerHTML = `
        <div class="file-toolbar">
          <div class="toolbar-left">
            <h3 style="margin: 0; color: var(--text-primary);">Processing Status</h3>
          </div>
          <div class="toolbar-right">
            <button id="refreshStatusBtn" class="btn btn-outline">🔄 Refresh Status</button>
          </div>
        </div>
        <div id="processingJobs" style="padding: 1.5rem;"></div>
      `;
      
      // Add refresh button handler
      document.getElementById('refreshStatusBtn').addEventListener('click', () => {
        console.log('Manual refresh button clicked');
        this.manualStatusCheck();
      });
      
      this.updateProcessingUI();
    }
    
    // Update processing UI with current jobs
    updateProcessingUI() {
      const jobsContainer = document.getElementById('processingJobs');
      if (!jobsContainer) return;
      
      if (this.activeJobs.size === 0) {
        jobsContainer.innerHTML = `
          <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <h3>No active processing jobs</h3>
            <p>Process some files from the file manager to see status here</p>
          </div>
        `;
        return;
      }
      
      let jobsHTML = '';
      this.activeJobs.forEach((job, guid) => {
        const stepInfo = this.processingSteps[job.status] || this.processingSteps['pending'];
        const progressPercent = Math.min(100, (stepInfo.step / 7) * 100);
        
        jobsHTML += `
          <div class="processing-job-card" style="
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
          ">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
              <div style="flex: 1;">
                <h4 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">
                  ${job.origFilename}
                </h4>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">
                  Job ID: ${guid.substring(0, 8)}... | Status: ${job.status}
                </p>
              </div>
              <div style="text-align: right;">
                <span class="status-indicator status-${job.status.toLowerCase()}" style="
                  display: inline-flex;
                  align-items: center;
                  gap: 0.5rem;
                  padding: 0.5rem 1rem;
                  border-radius: 20px;
                  font-size: 0.875rem;
                  font-weight: 500;
                ">
                  <span>${stepInfo.icon}</span>
                  <span>${stepInfo.label}</span>
                </span>
              </div>
            </div>
            
            <div style="margin-bottom: 1rem;">
              <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-size: 0.875rem; color: var(--text-secondary);">
                  ${stepInfo.description}
                </span>
                <span style="font-size: 0.875rem; color: var(--text-secondary);">
                  Step ${stepInfo.step} of 7
                </span>
              </div>
              <div style="
                width: 100%;
                height: 8px;
                background: var(--bg-tertiary);
                border-radius: 4px;
                overflow: hidden;
              ">
                <div style="
                  width: ${progressPercent}%;
                  height: 100%;
                  background: ${stepInfo.step === 0 ? 'var(--error-color)' : 'var(--primary-color)'};
                  border-radius: 4px;
                  transition: width 0.3s ease;
                "></div>
              </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <small style="color: var(--text-muted);">
                Started: ${new Date(job.addedTime).toLocaleTimeString()}
              </small>
              ${stepInfo.step === 7 ? `
                <button class="btn btn-success btn-sm" onclick="alert('DZI files ready for viewing!')">
                  View Result
                </button>
              ` : stepInfo.step === 0 ? `
                <button class="btn btn-outline btn-sm" onclick="window.dziProcessor.retryJob('${guid}')">
                  Retry
                </button>
              ` : ''}
            </div>
          </div>
        `;
      });
      
      jobsContainer.innerHTML = jobsHTML;
    }
    
    // View switching methods
    showProcessingView() {
      document.getElementById('fileManagerView').classList.remove('active');
      document.getElementById('fileManagerView').style.display = 'none';
      document.getElementById('processingView').classList.add('active');
      document.getElementById('processingView').style.display = 'block';
      this.updateProcessingUI();
    }
    
    showFileManagerView() {
      document.getElementById('fileManagerView').classList.add('active');
      document.getElementById('fileManagerView').style.display = 'block';
      document.getElementById('processingView').classList.remove('active');
      document.getElementById('processingView').style.display = 'none';
    }
    
    async processSelectedFiles() {
      console.log('=== processSelectedFiles() Called ===');
      
      if (this.fileManager.selectedFiles.size === 0) {
        PolyscopeUI.error('No files selected for processing');
        return;
      }
      
      const selectedFiles = Array.from(this.fileManager.selectedFiles);
      console.log('Selected files array:', selectedFiles);
      
      const dziCompatibleFiles = selectedFiles.filter(path => {
        const ext = path.toLowerCase().split('.').pop();
        return [
          'svs', 'ndpi', 'czi', 'scn', 'mrxs', 'vsi', 'vms',
          'tiff', 'tif', 'jpg', 'jpeg', 'png', 'bmp', 'gif', 'webp',
          'dcm', 'dicom', 'nii', 'nrrd',
          'jp2', 'j2k', 'jpx', 'jpm'
        ].includes(ext);
      });
      
      if (dziCompatibleFiles.length === 0) {
        PolyscopeUI.error('No DZI-compatible image files selected');
        return;
      }
      
      const filesToProcess = dziCompatibleFiles.map(relativePath => {
        const cleanPath = relativePath.replace(/^\/+/, '');
        return '/media/Users/' + PolyscopeConfig.user.username + '/' + cleanPath;
      });
      
      try {
        this.fileManager.showLoading('Starting DZI processing...');
        
        const formData = new URLSearchParams();
        formData.append('path', JSON.stringify(filesToProcess));
        formData.append('isDir', JSON.stringify(false));
        
        const response = await fetch('/issueUploadProject.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData.toString()
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const responseText = await response.text();
        if (!responseText || responseText.trim() === '') {
          throw new Error('Empty response from server');
        }
        
        const result = JSON.parse(responseText);
        console.log('Processing result:', result);
        
        if (result.error) {
          throw new Error(result.error);
        }
        
        this.handleProcessingResult(result, filesToProcess);
        PolyscopeUI.success('DZI processing started! Switching to status view...');
        
        setTimeout(() => {
          this.showProcessingView();
        }, 1000);
        
        this.startStatusChecking();
        
      } catch (error) {
        console.error('Error starting DZI processing:', error);
        PolyscopeUI.error('Error: ' + error.message);
      } finally {
        this.fileManager.hideLoading();
      }
    }
    
    handleProcessingResult(result, originalFilePaths) {
      console.log('=== handleProcessingResult ===');
      console.log('Result:', result);
      
      if (result.typ === 'single') {
        this.handleSingleJob(result, originalFilePaths[0]);
      } else if (result.typ === 'multiple') {
        this.handleMultipleJobs(result, originalFilePaths);
      } else if (result.guid) {
        this.handleSingleJob(result, originalFilePaths[0]);
      } else if (result.jobs && Array.isArray(result.jobs)) {
        this.handleMultipleJobs(result, originalFilePaths);
      } else {
        console.warn('Unknown result format, but likely successful:', result);
      }
    }
    
    handleSingleJob(result, originalFilePath) {
      console.log('=== handleSingleJob ===');
      const filename = originalFilePath.split('/').pop();
      
      if (result.guid) {
        const jobData = {
          guid: result.guid,
          id: result.id || result.guid,
          name: result.name || filename,
          origFilename: filename,
          status: 'checksum',
          addedTime: Date.now()
        };
        
        this.activeJobs.set(result.guid, jobData);
        console.log('Added single job:', jobData);
      }
    }
    
    handleMultipleJobs(result, originalFilePaths) {
      console.log('=== handleMultipleJobs ===');
      
      if (result.jobs && Array.isArray(result.jobs)) {
        result.jobs.forEach((job, index) => {
          const originalFilename = originalFilePaths[index] ? 
            originalFilePaths[index].split('/').pop() : 'Unknown file';
          
          if (job.guid) {
            const jobData = {
              guid: job.guid,
              id: job.id || job.guid,
              name: job.name || originalFilename,
              origFilename: originalFilename,
              status: 'checksum',
              addedTime: Date.now()
            };
            
            this.activeJobs.set(job.guid, jobData);
            console.log('Added job:', jobData);
          }
        });
      }
    }
    
    async checkJobStatus() {
      if (this.activeJobs.size === 0) {
        this.stopStatusChecking();
        return;
      }
      
      try {
        const response = await fetch('/getProjectStatus.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          }
        });
        
        if (!response.ok) {
          console.warn('Status check failed:', response.status);
          return;
        }
        
        const responseText = await response.text();
        if (!responseText || responseText.trim() === '') {
          console.warn('Empty status response');
          return;
        }
        
        let statusData;
        try {
          statusData = JSON.parse(responseText);
        } catch (parseError) {
          console.warn('Status response not valid JSON');
          return;
        }
        
        if (statusData && statusData.valid && statusData.jobs) {
          console.log('Status update received for', statusData.jobs.length, 'jobs');
          this.updateJobStatuses(statusData.jobs);
        }
        
      } catch (error) {
        console.warn('Status check error:', error.message);
      }
    }
    
    startStatusChecking() {
      if (this.statusCheckInterval) {
        clearInterval(this.statusCheckInterval);
      }
      
      this.updateProcessingUI();
      
      this.statusCheckInterval = setInterval(() => {
        this.checkJobStatus();
      }, 5000);
      
      console.log('Started status checking with UI updates');
    }
    
    stopStatusChecking() {
      if (this.statusCheckInterval) {
        clearInterval(this.statusCheckInterval);
        this.statusCheckInterval = null;
        console.log('Stopped status checking');
      }
    }
    
    retryJob(guid) {
      console.log('Retrying job:', guid);
      PolyscopeUI.info('Job retry requested');
    }
    
    manualStatusCheck() {
      console.log('=== Manual Status Check ===');
      this.checkJobStatus();
    }
    
    showJobState() {
      console.log('=== Current Job State ===');
      console.log('Active jobs count:', this.activeJobs.size);
      this.activeJobs.forEach((job, guid) => {
        console.log('Job:', guid, job);
      });
    }
    
    destroy() {
      this.stopStatusChecking();
      this.activeJobs.clear();
    }
  }