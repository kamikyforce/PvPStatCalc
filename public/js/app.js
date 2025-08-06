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