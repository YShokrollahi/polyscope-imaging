/**
 * Results Page Checker
 * Handles checking if user has processed slides and manages the "My Results" button
 */

class ResultsChecker {
    constructor() {
        this.username = window.PolyscopeConfig?.user?.username;
        this.resultsUrl = `/customers/${this.username}-mdanderson-org/`;
        this.resultsButton = null;
        this.hasResults = false;
        this.init();
    }

    init() {
        if (!this.username) {
            console.warn('ResultsChecker: Username not found');
            return;
        }

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.resultsButton = document.querySelector('a[href*="/customers/"]');
        if (!this.resultsButton) {
            console.warn('ResultsChecker: Results button not found');
            return;
        }

        // Ensure PolyscopeUI is initialized
        if (window.PolyscopeUI && typeof window.PolyscopeUI.init === 'function') {
            window.PolyscopeUI.init();
        }

        // Check if results page exists
        this.checkResultsAvailability();
        
        // Add click handler to show notification if no results
        this.resultsButton.addEventListener('click', (e) => this.handleResultsClick(e));
    }

    async checkResultsAvailability() {
        try {
            // Add loading state
            this.setButtonState('checking');
            
            const response = await fetch(this.resultsUrl, {
                method: 'HEAD', // Only get headers, not content
                cache: 'no-cache'
            });

            this.hasResults = response.ok && response.status === 200;
            this.setButtonState(this.hasResults ? 'available' : 'unavailable');

        } catch (error) {
            console.warn('ResultsChecker: Error checking results availability:', error);
            this.hasResults = false;
            this.setButtonState('unavailable');
        }
    }

    setButtonState(state) {
        if (!this.resultsButton) return;

        // Remove existing state classes
        this.resultsButton.classList.remove('results-checking', 'results-unavailable');
        
        switch (state) {
            case 'checking':
                this.resultsButton.classList.add('results-checking');
                this.resultsButton.innerHTML = '⏳ Checking Results';
                this.resultsButton.setAttribute('title', 'Checking if results are available...');
                break;
                
            case 'available':
                this.resultsButton.innerHTML = '📋 My Results';
                this.resultsButton.setAttribute('title', 'View your processed slide results');
                break;
                
            case 'unavailable':
                this.resultsButton.classList.add('results-unavailable');
                this.resultsButton.innerHTML = '📋 My Results (None yet)';
                this.resultsButton.setAttribute('title', 'No processed slides available yet. Process some slides first!');
                break;
        }
    }

    handleResultsClick(e) {
        if (!this.hasResults) {
            e.preventDefault();
            
            // Show notification using your PolyscopeUI notification system
            if (window.PolyscopeUI && window.PolyscopeUI.notify) {
                // Use your notification system with correct parameter order: (message, type, title, duration)
                const notification = window.PolyscopeUI.info(
                    'You haven\'t processed any slides yet. Upload and process some pathology slides first to see your results here.',
                    'No Results Available Yet',
                    5000
                );
                
                // Add custom class to the notification for additional styling
                if (notification) {
                    notification.classList.add('results-notification');
                }
                
            } else if (window.PolyscopeUI && window.PolyscopeUI.info) {
                // Alternative using the info method directly
                window.PolyscopeUI.info(
                    'You haven\'t processed any slides yet. Upload and process some pathology slides first to see your results here.',
                    'No Results Available Yet',
                    5000
                );
            } else {
                // Fallback alert if PolyscopeUI isn't available
                alert('No results available yet.\n\nYou haven\'t processed any slides yet. Upload and process some pathology slides first to see your results here.');
            }

            // Highlight the upload area to guide users
            this.highlightUploadArea();
        }
    }

    highlightUploadArea() {
        const uploadZone = document.getElementById('uploadZone');
        const uploadBtn = document.getElementById('uploadBtn');
        
        if (uploadZone) {
            uploadZone.classList.add('highlight-pulse');
            setTimeout(() => {
                uploadZone.classList.remove('highlight-pulse');
            }, 3000);
        }
        
        if (uploadBtn) {
            uploadBtn.classList.add('btn-pulse');
            setTimeout(() => {
                uploadBtn.classList.remove('btn-pulse');
            }, 3000);
        }
    }

    // Method to refresh the check (can be called after processing completes)
    refreshCheck() {
        this.checkResultsAvailability();
    }
}

// Initialize when script loads
if (typeof window !== 'undefined') {
    window.ResultsChecker = ResultsChecker;
    
    // Auto-initialize
    new ResultsChecker();
}