function resetForm() {
    const form = document.getElementById('statForm');
    form.reset();
    
    const inputs = form.querySelectorAll('input[type="number"]');
    inputs.forEach(input => {
        input.value = '';
        input.setCustomValidity('');
    });
    
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.style.display = 'none';
    }
    
    const resultDiv = document.querySelector('.result');
    if (resultDiv) {
        resultDiv.remove();
    }
    
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => alert.remove());
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('statForm');
    const inputs = form.querySelectorAll('input[type="number"]');
    
    inputs.forEach(input => {
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

function showExportButton() {
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.style.display = 'inline-block';
    }
}

function exportToJson() {
    const form = document.getElementById('statForm');
    const formData = new FormData(form);
    
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
    
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '/export';
    exportForm.style.display = 'none';
    
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

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('statForm');
    const inputs = form.querySelectorAll('input[type="number"]');
    
    inputs.forEach(input => {
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
    
    const statsGrid = document.querySelector('.stats-grid');
    if (statsGrid) {
        showExportButton();
    }
});
