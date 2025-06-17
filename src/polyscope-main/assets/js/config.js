// Application Configuration
window.PolyscopeConfig = {
    // API Configuration
    api: {
      baseUrl: '',
      endpoints: {
        serverData: 'retrieveServerData.php',
        mounts: 'getPolyzoomerMounts.php',
        directoryContents: 'getDirectoryContents.php',
        uploadProject: 'issueUploadProject.php',
        projectStatus: 'getProjectStatus.php',
        updateEmail: 'issueUpdateEmail.php',
        autoCompleteEmails: 'autoCompleteEmails.php',
        cleanupJobs: 'cleanup_jobs_api.php',
        upload: '/api/upload.php'
      }
    },
  
    // Timing Configuration
    timing: {
      serverRefresh: 1000,          // 1 second
      mediaRefresh: 4000,           // 4 seconds
      projectStatus: 3000,          // 3 seconds
      automaticTimeout: 4000,       // 4 seconds
      emailTimeout: 30000,          // 30 seconds
      transitionSpeed: 250          // 250ms
    },
  
    // UI Configuration
    ui: {
      views: {
        DASHBOARD: { id: 'dashboard', name: 'Dashboard', index: 0 },
        DEVICE_SELECTION: { id: 'deviceSelection', name: 'Device Selection', index: 1 },
        PROCESSING: { id: 'processing', name: 'Processing', index: 2 }
      },
      maxFileSize: 100 * 1024 * 1024, // 100MB
      allowedFileTypes: [
        '.jpg', '.jpeg', '.png', '.gif', '.tiff', '.tif', 
        '.bmp', '.webp', '.pdf', '.svs', '.ndpi', '.czi', 
        '.scn', '.dcm'
      ]
    },
  
    // System Status
    status: {
      loadLevels: {
        LOW: { threshold: 2.0, class: 'load-low', message: 'Load low - Wait time short' },
        MEDIUM: { threshold: 6.0, class: 'load-medium', message: 'Load medium - Wait time approx. one hour' },
        HIGH: { threshold: Infinity, class: 'load-high', message: 'Load high - Wait time can be several hours' }
      },
      jobStates: [
        'pending', 'upload', 'uploading', 'uploaded', 'putToOwnFolder',
        'estimateSize', 'readyForQueue', 'inQueue', 'processing',
        'finished', 'emailSent', 'readyToBeRemoved'
      ]
    },
  
    // User Configuration (set dynamically)
    user: {
      username: '',
      isAdmin: false,
      userDirectory: ''
    }
  };
  
  // Initialize user configuration from PHP
  document.addEventListener('DOMContentLoaded', function() {
    // Extract username from the page (you'll need to pass this from PHP)
    const userElement = document.querySelector('.user-greeting');
    if (userElement) {
      const greeting = userElement.textContent;
      const username = greeting.replace('Hello, ', '').trim();
      PolyscopeConfig.user.username = username;
      PolyscopeConfig.user.isAdmin = username === 'yshokrollahi';
      PolyscopeConfig.user.userDirectory = `/media/Users/${username}`;
    }
  });