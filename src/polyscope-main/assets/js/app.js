// Main Application Controller
window.PolyscopeApp = {
  
    initialized: false,
    components: {},
  
    /**
     * Initialize the application
     */
    async init() {
      if (this.initialized) return;
  
      try {
        // Show loading overlay
        this.showLoading('Initializing Polyscope...');
  
        // Initialize core components
        await this.initializeComponents();
  
        // Setup global error handling
        this.setupErrorHandling();
  
        // Setup global event listeners
        this.setupGlobalEvents();
  
        // Mark as initialized
        this.initialized = true;
  
        // Hide loading overlay
        this.hideLoading();
  
        console.log('Polyscope application initialized successfully');
        
      } catch (error) {
        this.hideLoading();
        PolyscopeUtils.error.handle(error, 'Application initialization');
      }
    },
  
    /**
     * Initialize all components
     */
    async initializeComponents() {
      const components = [
        'PolyscopeNavigation',
        'PolyscopeDashboard',
        'PolyscopeDeviceManager',
        'PolyscopeFileManager', 
        'PolyscopeJobManager',
        'PolyscopeEmailManager',
        'PolyscopeNotifications'
      ];
  
      for (const componentName of components) {
        try {
          const component = window[componentName];
          if (component && typeof component.init === 'function') {
            await component.init();
            this.components[componentName] = component;
            console.log(`${componentName} initialized`);
          } else {
            console.warn(`Component ${componentName} not found or invalid`);
          }
        } catch (error) {
          console.error(`Failed to initialize ${componentName}:`, error);
        }
      }
    },
  
    /**
     * Setup global error handling
     */
    setupErrorHandling() {
      // Handle unhandled promise rejections
      window.addEventListener('unhandledrejection', (event) => {
        console.error('Unhandled promise rejection:', event.reason);
        PolyscopeUtils.error.handle(event.reason, 'Unhandled Promise');
        event.preventDefault();
      });
  
      // Handle general errors
      window.addEventListener('error', (event) => {
        console.error('Global error:', event.error);
        PolyscopeUtils.error.handle(event.error, 'Global Error');
      });
    },
  
    /**
     * Setup global event listeners
     */
    setupGlobalEvents() {
      // Handle visibility change (tab focus/blur)
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          this.onAppHidden();
        } else {
          this.onAppVisible();
        }
      });
  
      // Handle window resize
      const debouncedResize = PolyscopeUtils.events.debounce(() => {
        this.onWindowResize();
      }, 250);
      
      window.addEventListener('resize', debouncedResize);
  
      // Handle online/offline status
      window.addEventListener('online', () => {
        PolyscopeNotifications?.show({
          type: 'success',
          title: 'Connection Restored',
          message: 'Internet connection is back online.',
          duration: 3000
        });
      });
  
      window.addEventListener('offline', () => {
        PolyscopeNotifications?.show({
          type: 'warning',
          title: 'Connection Lost',
          message: 'Internet connection lost. Some features may not work.',
          duration: 5000
        });
      });
    },
  
    /**
     * Handle app becoming hidden
     */
    onAppHidden() {
      // Pause non-critical timers to save resources
      Object.values(this.components).forEach(component => {
        if (component.pause && typeof component.pause === 'function') {
          component.pause();
        }
      });
    },
  
    /**
     * Handle app becoming visible
     */
    onAppVisible() {
      // Resume timers and refresh data
      Object.values(this.components).forEach(component => {
        if (component.resume && typeof component.resume === 'function') {
          component.resume();
        }
      });
  
      // Refresh dashboard data
      if (this.components.PolyscopeDashboard) {
        this.components.PolyscopeDashboard.refreshServerData();
      }
    },
  
    /**
     * Handle window resize
     */
    onWindowResize() {
      // Notify components of resize
      Object.values(this.components).forEach(component => {
        if (component.onResize && typeof component.onResize === 'function') {
          component.onResize();
        }
      });
    },
  
    /**
     * Show loading overlay
     */
    showLoading(message = 'Loading...') {
      const overlay = document.getElementById('loadingOverlay');
      if (overlay) {
        const messageElement = overlay.querySelector('p');
        if (messageElement) {
          messageElement.textContent = message;
        }
        overlay.classList.add('active');
      }
    },
  
    /**
     * Hide loading overlay
     */
    hideLoading() {
      const overlay = document.getElementById('loadingOverlay');
      if (overlay) {
        overlay.classList.remove('active');
      }
    },
  
    /**
     * Restart application
     */
    async restart() {
      if (!this.initialized) return;
  
      try {
        this.showLoading('Restarting application...');
  
        // Cleanup components
        Object.values(this.components).forEach(component => {
          if (component.destroy && typeof component.destroy === 'function') {
            component.destroy();
          }
        });
  
        this.components = {};
        this.initialized = false;
  
        // Wait a bit
        await new Promise(resolve => setTimeout(resolve, 500));
  
        // Reinitialize
        await this.init();
  
      } catch (error) {
        this.hideLoading();
        PolyscopeUtils.error.handle(error, 'Application restart');
      }
    },
  
    /**
     * Get component instance
     */
    getComponent(name) {
      return this.components[name];
    },
  
    /**
     * Check if component is available
     */
    hasComponent(name) {
      return name in this.components;
    },
  
    /**
     * Show notification
     */
    notify(options) {
      if (this.components.PolyscopeNotifications) {
        this.components.PolyscopeNotifications.show(options);
      } else {
        // Fallback to console
        console.log('Notification:', options.message);
      }
    },
  
    /**
     * Get application status
     */
    getStatus() {
      return {
        initialized: this.initialized,
        components: Object.keys(this.components),
        currentView: this.components.PolyscopeNavigation?.currentView,
        online: navigator.onLine,
        visible: !document.hidden
      };
    }
  };
  
  // Initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', () => {
    window.PolyscopeApp.init();
  });
  
  // Export for global access
  window.App = window.PolyscopeApp;