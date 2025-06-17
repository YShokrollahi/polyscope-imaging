// Dashboard Component
window.PolyscopeDashboard = {
  
    // Raphael paper instances
    dateTimePaper: null,
    diskUsagePaper: null,
    
    // Data storage
    serverData: {
      cpuUsage: [],
      memoryUsage: [],
      diskUsage: 0,
      currentLoad: 0
    },
  
    /**
     * Initialize dashboard
     */
    init() {
      this.setupGraphics();
      this.startRefreshTimer();
      this.setupCleanupButton();
    },
  
    /**
     * Setup Raphael graphics
     */
    setupGraphics() {
      // Date/Time display
      const dateTimeElement = document.getElementById('dateTime');
      if (dateTimeElement && window.Raphael) {
        this.dateTimePaper = window.Raphael(dateTimeElement, 200, 200);
      }
  
      // Disk usage display
      const diskElement = document.getElementById('diskUsage');
      if (diskElement && window.Raphael) {
        this.diskUsagePaper = window.Raphael(diskElement, 200, 200);
      }
    },
  
    /**
     * Start refresh timer
     */
    startRefreshTimer() {
      // Initial load
      this.refreshServerData();
      
      // Set up interval
      setInterval(() => {
        this.refreshServerData();
      }, PolyscopeConfig.timing.serverRefresh);
    },
  
    /**
     * Refresh server data
     */
    async refreshServerData() {
      try {
        const data = await PolyscopeAPI.endpoints.getServerData();
        this.updateDisplay(data);
      } catch (error) {
        console.warn('Failed to refresh server data:', error);
      }
    },
  
    /**
     * Update dashboard display
     */
    updateDisplay(serverData) {
      this.updateDateTime(serverData.dateTime);
      this.updateDiskUsage(serverData.diskUsage);
      this.updateSystemLoad(serverData.cpuMemUsage);
    },
  
    /**
     * Update date/time display
     */
    updateDateTime(dateTime) {
      if (!this.dateTimePaper || !dateTime) return;
  
      const timeText = PolyscopeUtils.time.formatTime(dateTime);
      const dateText = PolyscopeUtils.time.formatDate(dateTime);
  
      this.dateTimePaper.clear();
      
      // Time display
      this.dateTimePaper.text(100, 75, timeText).attr({
        font: "50px Arial, sans-serif",
        fill: "#2563eb",
        "text-anchor": "middle",
        "font-weight": "bold"
      });
      
      // Date display
      this.dateTimePaper.text(100, 150, dateText).attr({
        font: "25px Arial, sans-serif",
        fill: "#64748b",
        "text-anchor": "middle"
      });
    },
  
    /**
     * Update disk usage display
     */
    updateDiskUsage(diskUsagePercent) {
      if (!this.diskUsagePaper || typeof diskUsagePercent === 'undefined') return;
  
      const usage = parseInt(diskUsagePercent) / 100.0;
      this.serverData.diskUsage = usage;
  
      this.diskUsagePaper.clear();
  
      // Base circle
      const baseCircle = this.diskUsagePaper.circle(100, 100, 80);
      baseCircle.attr({
        "stroke": "#e2e8f0",
        "stroke-width": "4",
        "fill": "none"
      });
  
      // Usage arc
      if (usage > 0) {
        const angle = usage * 360;
        const usedArc = this.createSector(100, 100, 80, -90, -90 + angle);
        const usedPath = this.diskUsagePaper.path(usedArc);
        
        // Color based on usage level
        let color = "#10b981"; // Green for low usage
        if (usage > 0.8) color = "#ef4444"; // Red for high usage
        else if (usage > 0.6) color = "#f59e0b"; // Yellow for medium usage
        
        usedPath.attr({
          "fill": color,
          "stroke": "#ffffff",
          "stroke-width": "2"
        });
      }
  
      // Center text
      this.diskUsagePaper.text(100, 100, `${Math.round(usage * 100)}%`).attr({
        font: "24px Arial, sans-serif",
        fill: "#1e293b",
        "text-anchor": "middle",
        "font-weight": "bold"
      });
  
      // Update percentage display
      const percentageElement = document.getElementById('diskPercentage');
      if (percentageElement) {
        percentageElement.textContent = `${Math.round(usage * 100)}%`;
      }
    },
  
    /**
     * Update system load display
     */
    updateSystemLoad(cpuMemUsage) {
      if (!cpuMemUsage || !cpuMemUsage.cpu) return;
  
      const load = cpuMemUsage.cpu.weightedAverage || 0;
      this.serverData.currentLoad = load;
  
      const loadStatusElement = document.getElementById('loadStatus');
      const cpuMemoryElement = document.getElementById('cpuMemory');
  
      if (!loadStatusElement || !cpuMemoryElement) return;
  
      // Determine load level
      let loadLevel, statusText, statusClass;
      
      if (load < PolyscopeConfig.status.loadLevels.LOW.threshold) {
        loadLevel = PolyscopeConfig.status.loadLevels.LOW;
        statusText = `${load.toFixed(1)} - Low`;
      } else if (load < PolyscopeConfig.status.loadLevels.MEDIUM.threshold) {
        loadLevel = PolyscopeConfig.status.loadLevels.MEDIUM;
        statusText = `${load.toFixed(1)} - Medium`;
      } else {
        loadLevel = PolyscopeConfig.status.loadLevels.HIGH;
        statusText = `${load.toFixed(1)} - High`;
      }
  
      // Update display
      loadStatusElement.textContent = statusText;
      
      // Update CSS classes
      cpuMemoryElement.className = 'system-load-display';
      cpuMemoryElement.classList.add(loadLevel.class);
      
      // Update tooltip
      cpuMemoryElement.title = loadLevel.message;
  
      // Create visual load indicator
      this.updateLoadVisual(load);
    },
  
    /**
     * Update visual load indicator
     */
    updateLoadVisual(load) {
      const element = document.getElementById('cpuMemory');
      if (!element) return;
  
      // Clear existing content
      element.innerHTML = '';
  
      // Create load bars
      const maxBars = 10;
      const activeBars = Math.min(Math.ceil((load / 10) * maxBars), maxBars);
  
      for (let i = 0; i < maxBars; i++) {
        const bar = PolyscopeUtils.dom.createElement('div', {
          className: `load-bar ${i < activeBars ? 'active' : ''}`,
          style: `
            width: 15px;
            height: ${20 + i * 5}px;
            margin: 2px;
            background-color: ${i < activeBars ? this.getLoadColor(i, maxBars) : '#e2e8f0'};
            border-radius: 2px;
            display: inline-block;
            vertical-align: bottom;
          `
        });
        element.appendChild(bar);
      }
    },
  
    /**
     * Get color for load bar based on position
     */
    getLoadColor(index, total) {
      const ratio = index / total;
      if (ratio < 0.4) return '#10b981'; // Green
      if (ratio < 0.7) return '#f59e0b'; // Yellow
      return '#ef4444'; // Red
    },
  
    /**
     * Create SVG sector/arc path
     */
    createSector(x, y, r, startAngle, endAngle) {
      const flag = (endAngle - startAngle) > 180 ? 1 : 0;
      const a1 = (startAngle % 360) * Math.PI / 180;
      const a2 = (endAngle % 360) * Math.PI / 180;
      
      return [
        ["M", x, y],
        ["l", r * Math.cos(a1), r * Math.sin(a1)],
        ["A", r, r, 0, flag, 1, x + r * Math.cos(a2), y + r * Math.sin(a2)],
        ["z"]
      ];
    },
  
    /**
     * Setup cleanup button
     */
    setupCleanupButton() {
      const cleanupButton = document.getElementById('cleanupButton');
      if (!cleanupButton) return;
  
      cleanupButton.addEventListener('click', async () => {
        const ageThreshold = prompt("Enter the age threshold for file deletion (in hours):", "24");
        
        if (ageThreshold === null) return; // User cancelled
  
        try {
          cleanupButton.disabled = true;
          cleanupButton.textContent = 'Cleaning...';
  
          const response = await PolyscopeAPI.endpoints.cleanupJobs(ageThreshold);
          
          this.showCleanupResults(response);
          
        } catch (error) {
          PolyscopeUtils.error.handle(error, 'Cleanup jobs');
        } finally {
          cleanupButton.disabled = false;
          cleanupButton.textContent = 'Clean Up Jobs';
        }
      });
    },
  
    /**
     * Show cleanup results
     */
    showCleanupResults(response) {
      let message = `${response.message}\n\n`;
      message += `Directory: ${response.directoryPath}\n`;
      message += `Total files: ${response.totalFiles}\n`;
      message += `Age threshold: ${response.ageThreshold} hours\n`;
      message += `Deleted files: ${response.deletedFiles?.length || 0}\n`;
      message += `Kept files: ${response.keptFiles?.length || 0}\n`;
  
      if (response.deletedFiles?.length > 0) {
        message += "\nDeleted files:\n";
        response.deletedFiles.forEach(file => {
          message += `${file.path} (Age: ${file.age} hours)\n`;
        });
      }
  
      if (response.keptFiles?.length > 0) {
        message += "\nKept files:\n";
        response.keptFiles.forEach(file => {
          message += `${file.path} (Age: ${file.age} hours)\n`;
        });
      }
  
      if (response.errors?.length > 0) {
        message += "\nErrors:\n" + response.errors.join("\n");
      }
  
      alert(message);
    }
  };