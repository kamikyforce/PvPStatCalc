function resetForm() {
    const form = document.getElementById('statForm');
    form.reset();
    
    // Explicitly clear all input values
    const inputs = form.querySelectorAll('input[type="number"]');
    inputs.forEach(input => {
        input.value = '';
        input.setCustomValidity('');
    });
    
    // Hide export button
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.style.display = 'none';
    }
    
    // Remove any existing results
    const resultDiv = document.querySelector('.result');
    if (resultDiv) {
        resultDiv.remove();
    }
    
    // Remove any alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => alert.remove());
}

// Form validation with English messages
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('statForm');
    const inputs = form.querySelectorAll('input[type="number"]');
    
    inputs.forEach(input => {
        // Custom validation messages in English
        input.addEventListener('invalid', function() {
            if (this.validity.valueMissing) {
                this.setCustomValidity('Please enter a value for ' + this.labels[0].textContent);
            } else if (this.validity.rangeUnderflow) {
                this.setCustomValidity('Value must be at least ' + this.min);
            } else if (this.validity.rangeOverflow) {
                this.setCustomValidity('Value must be no more than ' + this.max);
            } else if (this.validity.badInput) {
                this.setCustomValidity('Please enter a valid number');
            } else {
                this.setCustomValidity('');
            }
        });
        
        input.addEventListener('input', function() {
            // Clear custom validity on input
            this.setCustomValidity('');
            
            if (this.value < 0) {
                this.value = 0;
            }
            
            const max = parseInt(this.getAttribute('max'));
            if (this.value > max) {
                this.value = max;
            }
        });
    });
});

// Show export button when stats are calculated
function showExportButton() {
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.style.display = 'inline-block';
    }
}

// Export character build to JSON
function exportToJson() {
    const form = document.getElementById('statForm');
    const formData = new FormData(form);
    
    // Validate that all fields have values
    let hasValues = true;
    for (let [key, value] of formData.entries()) {
        if (!value || value.trim() === '') {
            hasValues = false;
            break;
        }
    }
    
    if (!hasValues) {
        alert('Please calculate stats first before exporting!');
        return;
    }
    
    // Create a temporary form for export
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '/export';
    exportForm.style.display = 'none';
    
    // Copy form data
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        exportForm.appendChild(input);
    }
    
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
}

// Call showExportButton when stats are displayed
document.addEventListener('DOMContentLoaded', function() {
    // Check if stats are already displayed (after calculation)
    const statsGrid = document.querySelector('.stats-grid');
    if (statsGrid) {
        showExportButton();
    }
});

// Online visitor tracking
function updateOnlineStatus() {
    fetch('/api/online_status.php')
        .then(response => response.json())
        .then(data => {
            // Update online count
            const onlineCountElement = document.getElementById('online-count');
            if (onlineCountElement) {
                onlineCountElement.textContent = data.total_online;
            }
            
            // Update online indicators for each country
            Object.keys(data.online_countries).forEach(countryCode => {
                const countryElement = document.querySelector(`[data-country="${countryCode}"]`);
                if (countryElement) {
                    const onlineIndicator = countryElement.querySelector('.online-indicator');
                    if (onlineIndicator) {
                        onlineIndicator.textContent = `🟢${data.online_countries[countryCode].online_count}`;
                    }
                }
            });
        })
        .catch(error => console.error('Error updating online status:', error));
}

// Update online status every 30 seconds
setInterval(updateOnlineStatus, 30000);

// Update immediately when page loads
document.addEventListener('DOMContentLoaded', updateOnlineStatus);

// Send heartbeat every 2 minutes to stay "online"
setInterval(() => {
    fetch('/api/online_status.php', { method: 'POST' });
}, 120000);


// Real-time visitor counter with sophisticated animations
// Real-time visitor counter with improved error handling
class RealTimeVisitorCounter {
    constructor() {
        this.eventSource = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;
        this.reconnectDelay = 2000;
        this.lastData = null;
        this.heartbeatTimeout = null;
        
        this.init();
    }
    
    init() {
        // Wait a bit before connecting to ensure page is loaded
        setTimeout(() => {
            this.connect();
            this.setupHeartbeat();
            this.setupVisibilityHandler();
        }, 1000);
    }
    
    connect() {
        if (this.eventSource) {
            this.eventSource.close();
        }
        
        console.log('🔄 Connecting to real-time visitor stream...');
        
        try {
            this.eventSource = new EventSource('/api/realtime_visitors.php');
            
            this.eventSource.addEventListener('visitor_update', (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleVisitorUpdate(data);
                    this.isConnected = true;
                    this.reconnectAttempts = 0;
                    this.resetHeartbeatTimeout();
                } catch (error) {
                    console.error('❌ Error parsing visitor data:', error);
                }
            });
            
            this.eventSource.addEventListener('heartbeat', (event) => {
                console.log('💓 Heartbeat received');
                this.resetHeartbeatTimeout();
            });
            
            this.eventSource.addEventListener('error', (event) => {
                console.error('❌ SSE Error event:', event);
                this.handleError();
            });
            
            this.eventSource.onopen = () => {
                console.log('✅ Real-time connection established');
                this.isConnected = true;
                this.showConnectionStatus('connected');
                this.reconnectAttempts = 0;
            };
            
            this.eventSource.onerror = (error) => {
                console.error('❌ Real-time connection error:', error);
                this.handleError();
            };
            
        } catch (error) {
            console.error('❌ Failed to create EventSource:', error);
            this.handleError();
        }
    }
    
    handleError() {
        this.isConnected = false;
        this.showConnectionStatus('disconnected');
        this.clearHeartbeatTimeout();
        
        if (this.eventSource) {
            this.eventSource.close();
        }
        
        this.handleReconnect();
    }
    
    resetHeartbeatTimeout() {
        this.clearHeartbeatTimeout();
        // If no heartbeat received in 60 seconds, reconnect
        this.heartbeatTimeout = setTimeout(() => {
            console.log('💔 Heartbeat timeout, reconnecting...');
            this.handleError();
        }, 60000);
    }
    
    clearHeartbeatTimeout() {
        if (this.heartbeatTimeout) {
            clearTimeout(this.heartbeatTimeout);
            this.heartbeatTimeout = null;
        }
    }
    
    handleVisitorUpdate(data) {
        // Animate counter changes
        this.updateTotalVisitors(data.total_visitors);
        this.updateOnlineCount(data.total_online);
        this.updateVisitorList(data.visitor_stats, data.online_countries);
        
        // Store last data for comparison
        this.lastData = data;
        
        // Show last update time
        this.updateTimestamp(data.timestamp);
    }
    
    updateTotalVisitors(newTotal) {
        const element = document.querySelector('.total-visitors');
        if (element && parseInt(element.textContent) !== newTotal) {
            this.animateNumberChange(element, newTotal);
        }
    }
    
    updateOnlineCount(newOnline) {
        const element = document.getElementById('online-count');
        if (element && parseInt(element.textContent) !== newOnline) {
            this.animateNumberChange(element, newOnline);
            
            // Pulse effect for online indicator
            const onlineIndicator = element.closest('.online-status');
            if (onlineIndicator) {
                onlineIndicator.classList.add('pulse-animation');
                setTimeout(() => onlineIndicator.classList.remove('pulse-animation'), 1000);
            }
        }
    }
    
    updateVisitorList(visitorStats, onlineCountries) {
        const container = document.querySelector('.visitor-list');
        if (!container) return;
        
        // Clear existing list
        container.innerHTML = '';
        
        // Rebuild list with animations
        Object.entries(visitorStats).forEach(([countryCode, country], index) => {
            const countryElement = this.createCountryElement(countryCode, country, onlineCountries[countryCode]);
            
            // Stagger animations
            setTimeout(() => {
                countryElement.style.opacity = '0';
                countryElement.style.transform = 'translateX(-20px)';
                container.appendChild(countryElement);
                
                // Animate in
                requestAnimationFrame(() => {
                    countryElement.style.transition = 'all 0.3s ease';
                    countryElement.style.opacity = '1';
                    countryElement.style.transform = 'translateX(0)';
                });
            }, index * 50);
        });
    }
    
    createCountryElement(countryCode, country, onlineData) {
        const div = document.createElement('div');
        div.className = 'country-item';
        div.setAttribute('data-country', countryCode);
        
        const onlineCount = onlineData ? onlineData.online_count : 0;
        const onlineIndicator = onlineCount > 0 ? 
            `<span class="online-indicator animate-pulse">🟢${onlineCount}</span>` : '';
        
        div.innerHTML = `
            <div style="display: flex; align-items: center; margin: 2px 0;">
                <span style="font-size: 16px; margin-right: 5px;">${country.flag}</span>
                <span style="flex: 1;">${country.name}</span>
                <span style="font-weight: bold; margin-left: 5px;">${country.count}</span>
                ${onlineIndicator}
            </div>
        `;
        
        return div;
    }
    
    animateNumberChange(element, newValue) {
        const oldValue = parseInt(element.textContent) || 0;
        
        if (oldValue === newValue) return;
        
        // Add change animation class
        element.classList.add('number-change');
        
        // Animate the number counting up/down
        const duration = 500;
        const steps = 20;
        const stepValue = (newValue - oldValue) / steps;
        let currentStep = 0;
        
        const interval = setInterval(() => {
            currentStep++;
            const currentValue = Math.round(oldValue + (stepValue * currentStep));
            element.textContent = currentValue;
            
            if (currentStep >= steps) {
                clearInterval(interval);
                element.textContent = newValue;
                element.classList.remove('number-change');
            }
        }, duration / steps);
    }
    
    updateTimestamp(timestamp) {
        const element = document.querySelector('.last-update');
        if (element) {
            const date = new Date(timestamp * 1000);
            element.textContent = `Last update: ${date.toLocaleTimeString()}`;
        }
    }
    
    showConnectionStatus(status) {
        const indicator = document.querySelector('.connection-status');
        if (indicator) {
            indicator.className = `connection-status ${status}`;
            indicator.textContent = status === 'connected' ? '🟢 Live' : 
                                 status === 'disconnected' ? '🔴 Reconnecting...' : '⚠️ Failed';
        }
    }
    
    handleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`🔄 Reconnecting... Attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);
            
            const delay = this.reconnectDelay * Math.min(this.reconnectAttempts, 5); // Max 10 second delay
            setTimeout(() => {
                this.connect();
            }, delay);
        } else {
            console.error('❌ Max reconnection attempts reached');
            this.showConnectionStatus('failed');
        }
    }
    
    setupHeartbeat() {
        // Send heartbeat every 30 seconds to maintain online status
        setInterval(() => {
            if (this.isConnected) {
                fetch('/api/online_status.php', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ heartbeat: true })
                }).catch(error => console.error('Heartbeat failed:', error));
            }
        }, 30000);
    }
    
    setupVisibilityHandler() {
        // Reconnect when tab becomes visible again
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && !this.isConnected) {
                console.log('🔄 Tab visible again, reconnecting...');
                this.reconnectAttempts = 0; // Reset attempts when tab becomes visible
                this.connect();
            }
        });
    }
    
    disconnect() {
        this.clearHeartbeatTimeout();
        if (this.eventSource) {
            this.eventSource.close();
            this.isConnected = false;
        }
    }
}

// Initialize real-time visitor counter when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.visitorCounter = new RealTimeVisitorCounter();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.visitorCounter) {
        window.visitorCounter.disconnect();
    }
});