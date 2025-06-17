// API Management
window.PolyscopeAPI = {
  
    /**
     * Make HTTP request
     */
    async request(url, options = {}) {
      const config = {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          ...options.headers
        },
        ...options
      };
  
      try {
        const response = await fetch(url, config);
        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
  
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          return await response.json();
        } else {
          return await response.text();
        }
      } catch (error) {
        console.error('API Request failed:', error);
        throw error;
      }
    },
  
    /**
     * GET request
     */
    async get(endpoint, params = {}) {
      const url = new URL(endpoint, window.location.origin);
      Object.entries(params).forEach(([key, value]) => {
        url.searchParams.append(key, value);
      });
      
      return this.request(url.toString());
    },
  
    /**
     * POST request
     */
    async post(endpoint, data = {}, options = {}) {
      const config = {
        method: 'POST',
        ...options
      };
  
      if (data instanceof FormData) {
        // Don't set Content-Type for FormData, browser will set it
        delete config.headers?.['Content-Type'];
        config.body = data;
      } else {
        config.body = JSON.stringify(data);
      }
  
      return this.request(endpoint, config);
    },
  
    /**
     * Legacy XMLHttpRequest for compatibility
     */
    legacyRequest(endpoint, params = null, onSuccess = null, onError = null) {
      return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4) {
            if (xhr.status === 200) {
              try {
                const response = JSON.parse(xhr.responseText);
                if (onSuccess) onSuccess(response);
                resolve(response);
              } catch (e) {
                const error = new Error('Invalid JSON response');
                if (onError) onError(error);
                reject(error);
              }
            } else {
              const error = new Error(`HTTP ${xhr.status}: ${xhr.statusText}`);
              if (onError) onError(error);
              reject(error);
            }
          }
        };
  
        xhr.onerror = function() {
          const error = new Error('Network error');
          if (onError) onError(error);
          reject(error);
        };
  
        if (params) {
          xhr.open('POST', endpoint, true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          xhr.send(params);
        } else {
          xhr.open('GET', endpoint, true);
          xhr.send();
        }
      });
    },
  
    // Specific API endpoints
    endpoints: {
      /**
       * Get server data (CPU, memory, disk usage)
       */
      async getServerData() {
        return PolyscopeAPI.get(PolyscopeConfig.api.endpoints.serverData);
      },
  
      /**
       * Get available mounts/devices
       */
      async getMounts() {
        return PolyscopeAPI.get(PolyscopeConfig.api.endpoints.mounts);
      },
  
      /**
       * Get directory contents
       */
      async getDirectoryContents(path, isRoot = false) {
        const params = {
          path: JSON.stringify(path),
          link: JSON.stringify("[link]"),
          first: isRoot ? "true" : "false"
        };
        
        return PolyscopeAPI.legacyRequest(
          PolyscopeConfig.api.endpoints.directoryContents,
          new URLSearchParams(params).toString()
        );
      },
  
      /**
       * Upload project for processing
       */
      async uploadProject(paths, isDir = false) {
        const params = {
          path: JSON.stringify(paths),
          isDir: JSON.stringify(isDir)
        };
        
        return PolyscopeAPI.legacyRequest(
          PolyscopeConfig.api.endpoints.uploadProject,
          new URLSearchParams(params).toString()
        );
      },
  
      /**
       * Get project status
       */
      async getProjectStatus() {
        return PolyscopeAPI.get(PolyscopeConfig.api.endpoints.projectStatus);
      },
  
      /**
       * Update email for project
       */
      async updateEmail(email, projectGuid) {
        const params = {
          email: JSON.stringify(email),
          guid: JSON.stringify(projectGuid)
        };
        
        return PolyscopeAPI.legacyRequest(
          PolyscopeConfig.api.endpoints.updateEmail,
          new URLSearchParams(params).toString()
        );
      },
  
      /**
       * Get/add autocomplete emails
       */
      async autoCompleteEmails(intent = 0, email = '') {
        let params = `intent=${intent}`;
        if (intent === 1 && email) {
          params += `&email=${JSON.stringify(email)}`;
        }
        
        return PolyscopeAPI.legacyRequest(
          PolyscopeConfig.api.endpoints.autoCompleteEmails,
          params
        );
      },
  
      /**
       * Cleanup old jobs
       */
      async cleanupJobs(ageThreshold) {
        const data = { ageThreshold };
        return PolyscopeAPI.post(PolyscopeConfig.api.endpoints.cleanupJobs, data);
      },
  
      /**
       * Upload files to directory
       */
      async uploadFiles(files, directory) {
        const formData = new FormData();
        formData.append('directory', directory);
        
        Array.from(files).forEach(file => {
          formData.append('files[]', file);
        });
  
        return PolyscopeAPI.post(PolyscopeConfig.api.endpoints.upload, formData, {
          headers: {} // Let browser set Content-Type for FormData
        });
      }
    }
  };