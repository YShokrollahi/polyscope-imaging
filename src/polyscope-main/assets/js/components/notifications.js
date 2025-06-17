// Notifications Component
window.PolyscopeNotifications = {
  
    container: null,
    notifications: new Map(),
    nextId: 1,
  
    /**
     * Initialize notifications
     */
    init() {
      this.container = document.getElementById('notifications');
      if (!this.container) {
        console.warn('Notifications container not found');
        return;
      }
    },
  
    /**
     * Show notification
     */
    show(options = {}) {
      if (!this.container) return null;
  
      const config = {
        id: this.nextId++,
        type: 'info', // info, success, warning, error
        title: '',
        message: '',
        duration: 4000, // milliseconds, 0 for permanent
        closable: true,
        ...options
      };
  
      const notification = this.createNotification(config);
      this.container.appendChild(notification);
      this.notifications.set(config.id, { element: notification, config });
  
      // Trigger show animation
      requestAnimationFrame(() => {
        notification.classList.add('show');
      });
  
      // Auto-hide if duration is set
      if (config.duration > 0) {
        setTimeout(() => {
          this.hide(config.id);
        }, config.duration);
      }
  
      return config.id;
    },
  
    /**
     * Create notification element
     */
    createNotification(config) {
      const notification = PolyscopeUtils.dom.createElement('div', {
        className: `notification ${config.type}`,
        'data-id': config.id
      });
  
      // Create content
      const content = PolyscopeUtils.dom.createElement('div', {
        className: 'notification-content'
      });
  
      // Title
      if (config.title) {
        const title = PolyscopeUtils.dom.createElement('div', {
          className: 'notification-title',
          textContent: config.title
        });
        content.appendChild(title);
      }
  
      // Message
      if (config.message) {
        const message = PolyscopeUtils.dom.createElement('div', {
          className: 'notification-message',
          textContent: config.message
        });
        content.appendChild(message);
      }
  
      notification.appendChild(content);
  
      // Close button
      if (config.closable) {
        const closeBtn = PolyscopeUtils.dom.createElement('button', {
          className: 'notification-close',
          innerHTML: '&times;'
        });
  
        closeBtn.addEventListener('click', () => {
          this.hide(config.id);
        });
  
        notification.appendChild(closeBtn);
      }
  
      // Progress bar for timed notifications
      if (config.duration > 0) {
        const progressBar = PolyscopeUtils.dom.createElement('div', {
          className: 'notification-progress'
        });
  
        const progressFill = PolyscopeUtils.dom.createElement('div', {
          className: 'notification-progress-fill'
        });
  
        progressBar.appendChild(progressFill);
        notification.appendChild(progressBar);
  
        // Animate progress bar
        requestAnimationFrame(() => {
          progressFill.style.transition = `width ${config.duration}ms linear`;
          progressFill.style.width = '0%';
        });
      }
  
      return notification;
    },
  
    /**
     * Hide notification
     */
    hide(id) {
      const notificationData = this.notifications.get(id);
      if (!notificationData) return;
  
      const { element } = notificationData;
      
      element.classList.remove('show');
      
      // Remove from DOM after animation
      setTimeout(() => {
        if (element.parentNode) {
          element.parentNode.removeChild(element);
        }
        this.notifications.delete(id);
      }, 300);
    },
  
    /**
     * Hide all notifications
     */
    hideAll() {
      this.notifications.forEach((_, id) => {
        this.hide(id);
      });
    },
  
    /**
     * Show success notification
     */
    success(title, message, duration = 4000) {
      return this.show({
        type: 'success',
        title,
        message,
        duration
      });
    },
  
    /**
     * Show warning notification
     */
    warning(title, message, duration = 5000) {
      return this.show({
        type: 'warning',
        title,
        message,
        duration
      });
    },
  
    /**
     * Show error notification
     */
    error(title, message, duration = 0) {
      return this.show({
        type: 'error',
        title,
        message,
        duration // Error notifications don't auto-hide by default
      });
    },
  
    /**
     * Show info notification
     */
    info(title, message, duration = 4000) {
      return this.show({
        type: 'info',
        title,
        message,
        duration
      });
    },
  
    /**
     * Update existing notification
     */
    update(id, options = {}) {
      const notificationData = this.notifications.get(id);
      if (!notificationData) return false;
  
      const { element, config } = notificationData;
      const newConfig = { ...config, ...options };
  
      // Update title
      if (options.title !== undefined) {
        const titleElement = element.querySelector('.notification-title');
        if (titleElement) {
          titleElement.textContent = options.title;
        } else if (options.title) {
          // Create title element if it doesn't exist
          const title = PolyscopeUtils.dom.createElement('div', {
            className: 'notification-title',
            textContent: options.title
          });
          const content = element.querySelector('.notification-content');
          content.insertBefore(title, content.firstChild);
        }
      }
  
      // Update message
      if (options.message !== undefined) {
        const messageElement = element.querySelector('.notification-message');
        if (messageElement) {
          messageElement.textContent = options.message;
        }
      }
  
      // Update type
      if (options.type && options.type !== config.type) {
        element.classList.remove(config.type);
        element.classList.add(options.type);
      }
  
      // Update stored config
      this.notifications.set(id, { element, config: newConfig });
  
      return true;
    },
  
    /**
     * Get notification count
     */
    getCount() {
      return this.notifications.size;
    },
  
    /**
     * Check if notification exists
     */
    exists(id) {
      return this.notifications.has(id);
    }
  };