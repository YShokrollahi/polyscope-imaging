// Modern File Manager for Pathology Images
window.PolyscopeFileManager = {
  
    // Current state
    currentPath: '',
    userBaseDir: '',
    selectedFiles: new Set(),
    fileTree: {},
    
    // Elements
    elements: {
      container: null,
      breadcrumb: null,
      fileList: null,
      uploadZone: null,
      toolbar: null
    },
  
    /**
     * Initialize file manager
     */
    init() {
      this.setupElements();
      this.setupUserDirectory();
      this.setupEventListeners();
      this.setupDragAndDrop();
      this.loadDirectory();
    },
  
    /**
     * Setup DOM elements
     */
    setupElements() {
      this.elements.container = document.getElementById('fileManager');
      this.elements.breadcrumb = document.getElementById('breadcrumb');
      this.elements.fileList = document.getElementById('fileList');
      this.elements.uploadZone = document.getElementById('uploadZone');
      this.elements.toolbar = document.getElementById('fileToolbar');
      
      if (!this.elements.container) {
        console.error('File manager container not found');
        return;
      }
    },
  
    /**
     * Setup user directory path
     */
    setupUserDirectory() {
      const username = PolyscopeConfig.user.username;
      this.userBaseDir = `/media/Users/${username}`;
      this.currentPath = this.userBaseDir;
    },
  
    /**
     * Setup event listeners
     */
    setupEventListeners() {
      // Toolbar buttons
      this.setupToolbarEvents();
      
      // File selection events
      this.setupFileEvents();
      
      // Upload events
      this.setupUploadEvents();
      
      // Keyboard shortcuts
      this.setupKeyboardEvents();
    },
  
    /**
     * Setup toolbar event listeners
     */
    setupToolbarEvents() {
      // New folder button
      const newFolderBtn = document.getElementById('newFolderBtn');
      if (newFolderBtn) {
        newFolderBtn.addEventListener('click', () => this.createFolder());
      }
  
      // Upload button
      const uploadBtn = document.getElementById('uploadBtn');
      if (uploadBtn) {
        uploadBtn.addEventListener('click', () => this.triggerFileUpload());
      }
  
      // Delete button
      const deleteBtn = document.getElementById('deleteBtn');
      if (deleteBtn) {
        deleteBtn.addEventListener('click', () => this.deleteSelected());
      }
  
      // Refresh button
      const refreshBtn = document.getElementById('refreshBtn');
      if (refreshBtn) {
        refreshBtn.addEventListener('click', () => this.loadDirectory());
      }
  
      // Process selected button
      const processBtn = document.getElementById('processBtn');
      if (processBtn) {
        processBtn.addEventListener('click', () => this.processSelectedFiles());
      }
    },
  
    /**
     * Setup file interaction events
     */
    setupFileEvents() {
      if (!this.elements.fileList) return;
  
      this.elements.fileList.addEventListener('click', (e) => {
        const fileItem = e.target.closest('.file-item');
        if (!fileItem) return;
  
        const filePath = fileItem.dataset.path;
        const isDirectory = fileItem.dataset.type === 'directory';
  
        if (e.detail === 2) { // Double click
          if (isDirectory) {
            this.navigateToFolder(filePath);
          } else {
            this.previewFile(filePath);
          }
        } else { // Single click
          this.toggleFileSelection(fileItem, e.ctrlKey || e.metaKey);
        }
      });
  
      // Context menu
      this.elements.fileList.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        const fileItem = e.target.closest('.file-item');
        if (fileItem) {
          this.showContextMenu(e, fileItem);
        }
      });
    },
  
    /**
     * Setup upload events
     */
    setupUploadEvents() {
      // Hidden file input for upload
      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.multiple = true;
      fileInput.accept = '.jpg,.jpeg,.png,.tiff,.tif,.svs,.ndpi,.czi,.scn,.dcm';
      fileInput.style.display = 'none';
      document.body.appendChild(fileInput);
  
      fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
          this.handleFileUpload(Array.from(e.target.files));
        }
      });
  
      this.fileInput = fileInput;
    },
  
    /**
     * Setup drag and drop
     */
    setupDragAndDrop() {
      if (!this.elements.uploadZone) return;
  
      ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        this.elements.uploadZone.addEventListener(eventName, this.preventDefaults, false);
      });
  
      ['dragenter', 'dragover'].forEach(eventName => {
        this.elements.uploadZone.addEventListener(eventName, () => {
          this.elements.uploadZone.classList.add('drag-active');
        }, false);
      });
  
      ['dragleave', 'drop'].forEach(eventName => {
        this.elements.uploadZone.addEventListener(eventName, () => {
          this.elements.uploadZone.classList.remove('drag-active');
        }, false);
      });
  
      this.elements.uploadZone.addEventListener('drop', (e) => {
        const files = Array.from(e.dataTransfer.files);
        this.handleFileUpload(files);
      }, false);
    },
  
    /**
     * Prevent default drag behaviors
     */
    preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    },
  
    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardEvents() {
      document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
          return;
        }
  
        switch (e.key) {
          case 'Delete':
          case 'Backspace':
            e.preventDefault();
            this.deleteSelected();
            break;
          case 'F2':
            e.preventDefault();
            this.renameSelected();
            break;
          case 'a':
          case 'A':
            if (e.ctrlKey || e.metaKey) {
              e.preventDefault();
              this.selectAll();
            }
            break;
          case 'Escape':
            e.preventDefault();
            this.clearSelection();
            break;
        }
      });
    },
  
    /**
     * Load directory contents
     */
    async loadDirectory(path = null) {
      try {
        const targetPath = path || this.currentPath;
        
        PolyscopeApp.showLoading('Loading directory...');
        
        const response = await PolyscopeAPI.post('/api/fileManager.php', {
          action: 'listDirectory',
          path: targetPath
        });
  
        if (response.success) {
          this.currentPath = targetPath;
          this.renderFileList(response.files);
          this.updateBreadcrumb();
          this.updateToolbar();
        } else {
          throw new Error(response.message || 'Failed to load directory');
        }
  
      } catch (error) {
        PolyscopeUtils.error.handle(error, 'Load Directory');
      } finally {
        PolyscopeApp.hideLoading();
      }
    },
  
    /**
     * Render file list
     */
    renderFileList(files) {
      if (!this.elements.fileList) return;
  
      // Sort files: directories first, then by name
      files.sort((a, b) => {
        if (a.type !== b.type) {
          return a.type === 'directory' ? -1 : 1;
        }
        return a.name.localeCompare(b.name);
      });
  
      // Clear current list
      this.elements.fileList.innerHTML = '';
  
      // Add parent directory link if not at root
      if (this.currentPath !== this.userBaseDir) {
        const parentItem = this.createFileItem({
          name: '..',
          type: 'directory',
          path: this.getParentPath(),
          size: 0,
          modified: null
        }, true);
        this.elements.fileList.appendChild(parentItem);
      }
  
      // Add files
      files.forEach(file => {
        const fileItem = this.createFileItem(file);
        this.elements.fileList.appendChild(fileItem);
      });
  
      // Clear selection
      this.clearSelection();
    },
  
    /**
     * Create file item element
     */
    createFileItem(file, isParent = false) {
      const item = PolyscopeUtils.dom.createElement('div', {
        className: `file-item ${file.type} ${isParent ? 'parent-dir' : ''}`,
        'data-path': file.path,
        'data-type': file.type,
        'data-name': file.name
      });
  
      // File icon
      const icon = PolyscopeUtils.dom.createElement('div', {
        className: 'file-icon',
        innerHTML: this.getFileIcon(file.type, file.name)
      });
  
      // File info
      const info = PolyscopeUtils.dom.createElement('div', {
        className: 'file-info'
      });
  
      const name = PolyscopeUtils.dom.createElement('div', {
        className: 'file-name',
        textContent: file.name
      });
  
      const details = PolyscopeUtils.dom.createElement('div', {
        className: 'file-details'
      });
  
      if (!isParent) {
        if (file.type === 'file') {
          details.textContent = `${PolyscopeUtils.string.formatFileSize(file.size)} • ${this.formatDate(file.modified)}`;
        } else {
          details.textContent = `Folder • ${this.formatDate(file.modified)}`;
        }
      }
  
      info.appendChild(name);
      info.appendChild(details);
  
      // Selection checkbox (not for parent dir)
      if (!isParent) {
        const checkbox = PolyscopeUtils.dom.createElement('input', {
          type: 'checkbox',
          className: 'file-checkbox'
        });
        item.appendChild(checkbox);
      }
  
      item.appendChild(icon);
      item.appendChild(info);
  
      return item;
    },
  
    /**
     * Get file icon based on type and extension
     */
    getFileIcon(type, filename) {
      if (type === 'directory') {
        return '📁';
      }
  
      const ext = PolyscopeUtils.file.getExtension(filename);
      
      // Pathology image formats
      if (['svs', 'ndpi', 'czi', 'scn'].includes(ext)) {
        return '🔬'; // Microscope for pathology slides
      }
      
      // Standard images
      if (['jpg', 'jpeg', 'png', 'tiff', 'tif'].includes(ext)) {
        return '🖼️';
      }
      
      // DICOM
      if (ext === 'dcm') {
        return '🏥';
      }
  
      return '📄';
    },
  
    /**
     * Format date for display
     */
    formatDate(timestamp) {
      if (!timestamp) return '';
      const date = new Date(timestamp * 1000);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });
    },
  
    /**
     * Get parent directory path
     */
    getParentPath() {
      return this.currentPath.substring(0, this.currentPath.lastIndexOf('/'));
    },
  
    /**
     * Update breadcrumb navigation
     */
    updateBreadcrumb() {
      if (!this.elements.breadcrumb) return;
  
      const pathParts = this.currentPath.replace(this.userBaseDir, '').split('/').filter(Boolean);
      const breadcrumbItems = [];
  
      // Home (user root)
      breadcrumbItems.push({
        name: '🏠 Home',
        path: this.userBaseDir
      });
  
      // Path segments
      let currentPath = this.userBaseDir;
      pathParts.forEach(part => {
        currentPath += '/' + part;
        breadcrumbItems.push({
          name: part,
          path: currentPath
        });
      });
  
      // Render breadcrumb
      this.elements.breadcrumb.innerHTML = '';
      breadcrumbItems.forEach((item, index) => {
        const isLast = index === breadcrumbItems.length - 1;
        
        const breadcrumbItem = PolyscopeUtils.dom.createElement('span', {
          className: `breadcrumb-item ${isLast ? 'active' : ''}`,
          textContent: item.name
        });
  
        if (!isLast) {
          breadcrumbItem.style.cursor = 'pointer';
          breadcrumbItem.addEventListener('click', () => {
            this.loadDirectory(item.path);
          });
        }
  
        this.elements.breadcrumb.appendChild(breadcrumbItem);
  
        if (!isLast) {
          this.elements.breadcrumb.appendChild(PolyscopeUtils.dom.createElement('span', {
            className: 'breadcrumb-separator',
            textContent: ' / '
          }));
        }
      });
    },
  
    /**
     * Handle file upload
     */
    async handleFileUpload(files) {
      try {
        const validFiles = files.filter(file => this.validateFile(file));
        
        if (validFiles.length === 0) {
          throw new Error('No valid files selected');
        }
  
        PolyscopeApp.showLoading(`Uploading ${validFiles.length} files...`);
  
        const response = await PolyscopeAPI.endpoints.uploadFiles(validFiles, this.currentPath);
  
        if (response.success) {
          PolyscopeNotifications.success(
            'Upload Complete',
            `${response.uploaded.length} files uploaded successfully`
          );
          
          // Reload directory to show new files
          await this.loadDirectory();
        } else {
          throw new Error(response.message || 'Upload failed');
        }
  
      } catch (error) {
        PolyscopeUtils.error.handle(error, 'File Upload');
      } finally {
        PolyscopeApp.hideLoading();
      }
    },
  
    /**
     * Validate file for upload
     */
    validateFile(file) {
      // Check file size
      if (!PolyscopeUtils.validation.isValidFileSize(file.size)) {
        PolyscopeNotifications.warning(
          'File Too Large',
          `${file.name} exceeds maximum file size`
        );
        return false;
      }
  
      // Check file type
      if (!PolyscopeUtils.validation.isAllowedFileType(file.name)) {
        PolyscopeNotifications.warning(
          'Invalid File Type',
          `${file.name} is not a supported file type`
        );
        return false;
      }
  
      return true;
    },
  
    /**
     * Trigger file upload dialog
     */
    triggerFileUpload() {
      if (this.fileInput) {
        this.fileInput.click();
      }
    },
  
    /**
     * Create new folder
     */
    async createFolder() {
      const folderName = prompt('Enter folder name:');
      if (!folderName) return;
  
      try {
        const response = await PolyscopeAPI.post('/api/fileManager.php', {
          action: 'createFolder',
          path: this.currentPath,
          name: folderName
        });
  
        if (response.success) {
          PolyscopeNotifications.success('Folder Created', `Folder "${folderName}" created successfully`);
          await this.loadDirectory();
        } else {
          throw new Error(response.message || 'Failed to create folder');
        }
  
      } catch (error) {
        PolyscopeUtils.error.handle(error, 'Create Folder');
      }
    },
  
    /**
     * Navigate to folder
     */
    navigateToFolder(path) {
      this.loadDirectory(path);
    },
  
    /**
     * Toggle file selection
     */
    toggleFileSelection(fileItem, multiSelect = false) {
      const checkbox = fileItem.querySelector('.file-checkbox');
      if (!checkbox) return;
  
      if (!multiSelect) {
        this.clearSelection();
      }
  
      checkbox.checked = !checkbox.checked;
      fileItem.classList.toggle('selected', checkbox.checked);
  
      const filePath = fileItem.dataset.path;
      if (checkbox.checked) {
        this.selectedFiles.add(filePath);
      } else {
        this.selectedFiles.delete(filePath);
      }
  
      this.updateToolbar();
    },
  
    /**
     * Clear all selections
     */
    clearSelection() {
      this.selectedFiles.clear();
      const checkboxes = this.elements.fileList.querySelectorAll('.file-checkbox');
      checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        checkbox.closest('.file-item').classList.remove('selected');
      });
      this.updateToolbar();
    },
  
    /**
     * Select all files
     */
    selectAll() {
      const checkboxes = this.elements.fileList.querySelectorAll('.file-checkbox');
      checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        const fileItem = checkbox.closest('.file-item');
        fileItem.classList.add('selected');
        this.selectedFiles.add(fileItem.dataset.path);
      });
      this.updateToolbar();
    },
  
    /**
     * Update toolbar based on selection
     */
    updateToolbar() {
      const hasSelection = this.selectedFiles.size > 0;
      const deleteBtn = document.getElementById('deleteBtn');
      const processBtn = document.getElementById('processBtn');
  
      if (deleteBtn) deleteBtn.disabled = !hasSelection;
      if (processBtn) processBtn.disabled = !hasSelection;
    },
  
    /**
     * Process selected files for DZI creation
     */
    processSelectedFiles() {
      if (this.selectedFiles.size === 0) {
        PolyscopeNotifications.warning('No Selection', 'Please select files to process');
        return;
      }
  
      // Transition to processing view
      PolyscopeNavigation.showView('processing');
      
      // Trigger job creation
      if (window.PolyscopeJobManager) {
        window.PolyscopeJobManager.createJobs(Array.from(this.selectedFiles));
      }
    }
  };