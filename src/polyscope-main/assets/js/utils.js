// Utility Functions
window.PolyscopeUtils = {
  
    // DOM Utilities
    dom: {
      /**
       * Create element with attributes and children
       */
      createElement(tag, attributes = {}, children = []) {
        const element = document.createElement(tag);
        
        Object.entries(attributes).forEach(([key, value]) => {
          if (key === 'className') {
            element.className = value;
          } else if (key === 'textContent') {
            element.textContent = value;
          } else if (key === 'innerHTML') {
            element.innerHTML = value;
          } else {
            element.setAttribute(key, value);
          }
        });
        
        children.forEach(child => {
          if (typeof child === 'string') {
            element.appendChild(document.createTextNode(child));
          } else {
            element.appendChild(child);
          }
        });
        
        return element;
      },
  
      /**
       * Toggle element visibility
       */
      toggle(element, show) {
        if (typeof show === 'undefined') {
          show = element.style.display === 'none';
        }
        element.style.display = show ? '' : 'none';
      },
  
      /**
       * Add/remove classes
       */
      toggleClass(element, className, force) {
        if (typeof force !== 'undefined') {
          element.classList.toggle(className, force);
        } else {
          element.classList.toggle(className);
        }
      }
    },
  
    // String Utilities
    string: {
      /**
       * Clean string for use as ID/class
       */
      cleanString(str) {
        return str.replace(/[`~!@#$%^&*()|+\=?;:'",.<>\{\}\[\]\\\/ ]/gi, '-');
      },
  
      /**
       * Pack/unpack paths for URL encoding
       */
      packPath(path) {
        return path.replace(/\//g, '___SLASH___');
      },
  
      unpackPath(packed) {
        return packed.replace(/___SLASH___/g, '/');
      },
  
      /**
       * Format file size
       */
      formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
      },
  
      /**
       * Truncate string with ellipsis
       */
      truncate(str, length = 50) {
        return str.length > length ? str.substring(0, length) + '...' : str;
      }
    },
  
    // Time Utilities
    time: {
      /**
       * Pad number with zeros
       */
      pad(num, size = 2) {
        return String(num).padStart(size, '0');
      },
  
      /**
       * Format time for display
       */
      formatTime(dateTime) {
        return `${this.pad(dateTime.hour)}:${this.pad(dateTime.minute)}:${this.pad(dateTime.second)}`;
      },
  
      /**
       * Format date for display
       */
      formatDate(dateTime) {
        return `${dateTime.year}-${this.pad(dateTime.month)}-${this.pad(dateTime.day)}`;
      },
  
      /**
       * Get relative time string
       */
      timeAgo(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diffInSeconds = Math.floor((now - time) / 1000);
  
        if (diffInSeconds < 60) return 'just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        return `${Math.floor(diffInSeconds / 86400)}d ago`;
      }
    },
  
    // Array Utilities
    array: {
      /**
       * Get differences between arrays
       */
      difference(arr1, arr2) {
        return arr1.filter(x => !arr2.includes(x));
      },
  
      /**
       * Remove item from array
       */
      remove(arr, item) {
        const index = arr.indexOf(item);
        if (index > -1) {
          arr.splice(index, 1);
        }
        return arr;
      },
  
      /**
       * Get unique values
       */
      unique(arr) {
        return [...new Set(arr)];
      }
    },
  
    // Validation Utilities
    validation: {
      /**
       * Validate email address
       */
      isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
      },
  
      /**
       * Check if file type is allowed
       */
      isAllowedFileType(filename) {
        const extension = '.' + filename.split('.').pop().toLowerCase();
        return PolyscopeConfig.ui.allowedFileTypes.includes(extension);
      },
  
      /**
       * Check if file size is allowed
       */
      isValidFileSize(size) {
        return size <= PolyscopeConfig.ui.maxFileSize;
      }
    },
  
    // Animation Utilities
    animation: {
      /**
       * Fade in element
       */
      fadeIn(element, duration = 300) {
        element.style.opacity = '0';
        element.style.display = '';
        
        const start = performance.now();
        
        const animate = (timestamp) => {
          const elapsed = timestamp - start;
          const progress = Math.min(elapsed / duration, 1);
          
          element.style.opacity = progress.toString();
          
          if (progress < 1) {
            requestAnimationFrame(animate);
          }
        };
        
        requestAnimationFrame(animate);
      },
  
      /**
       * Fade out element
       */
      fadeOut(element, duration = 300) {
        const start = performance.now();
        const startOpacity = parseFloat(element.style.opacity) || 1;
        
        const animate = (timestamp) => {
          const elapsed = timestamp - start;
          const progress = Math.min(elapsed / duration, 1);
          
          element.style.opacity = (startOpacity * (1 - progress)).toString();
          
          if (progress >= 1) {
            element.style.display = 'none';
          } else {
            requestAnimationFrame(animate);
          }
        };
        
        requestAnimationFrame(animate);
      }
    },
  
    // Event Utilities
    events: {
      /**
       * Debounce function calls
       */
      debounce(func, wait, immediate) {
        let timeout;
        return function executedFunction(...args) {
          const later = () => {
            timeout = null;
            if (!immediate) func(...args);
          };
          const callNow = immediate && !timeout;
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
          if (callNow) func(...args);
        };
      },
  
      /**
       * Throttle function calls
       */
      throttle(func, limit) {
        let inThrottle;
        return function(...args) {
          if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
          }
        };
      },
  
      /**
       * Custom event dispatcher
       */
      emit(eventName, detail = {}) {
        const event = new CustomEvent(eventName, { detail });
        document.dispatchEvent(event);
      },
  
      /**
       * Custom event listener
       */
      on(eventName, callback) {
        document.addEventListener(eventName, callback);
      },
  
      /**
       * Remove custom event listener
       */
      off(eventName, callback) {
        document.removeEventListener(eventName, callback);
      }
    },
  
    // Local Storage Utilities
    storage: {
      /**
       * Set item in localStorage
       */
      set(key, value) {
        try {
          localStorage.setItem(key, JSON.stringify(value));
          return true;
        } catch (e) {
          console.warn('Failed to save to localStorage:', e);
          return false;
        }
      },
  
      /**
       * Get item from localStorage
       */
      get(key, defaultValue = null) {
        try {
          const item = localStorage.getItem(key);
          return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
          console.warn('Failed to read from localStorage:', e);
          return defaultValue;
        }
      },
  
      /**
       * Remove item from localStorage
       */
      remove(key) {
        try {
          localStorage.removeItem(key);
          return true;
        } catch (e) {
          console.warn('Failed to remove from localStorage:', e);
          return false;
        }
      }
    },
  
    // Error Handling
    error: {
      /**
       * Handle and display errors
       */
      handle(error, context = '') {
        console.error(`Error in ${context}:`, error);
        
        let message = 'An unexpected error occurred.';
        if (error.message) {
          message = error.message;
        } else if (typeof error === 'string') {
          message = error;
        }
        
        window.PolyscopeNotifications?.show({
          type: 'error',
          title: 'Error',
          message: message,
          duration: 5000
        });
      },
  
      /**
       * Create error object
       */
      create(message, code = null, details = null) {
        const error = new Error(message);
        error.code = code;
        error.details = details;
        return error;
      }
    },
  
    // File Utilities
    file: {
      /**
       * Get file extension
       */
      getExtension(filename) {
        return filename.split('.').pop().toLowerCase();
      },
  
      /**
       * Get file name without extension
       */
      getBaseName(filename) {
        return filename.substring(0, filename.lastIndexOf('.'));
      },
  
      /**
       * Check if path is a directory
       */
      isDirectory(path) {
        return !path.includes('.');
      },
  
      /**
       * Get parent directory
       */
      getParentDir(path) {
        return path.substring(0, path.lastIndexOf('/'));
      }
    }
  };