@extends('layouts.app')

@section('title', $title ?? 'PvP Calculator')

@section('content')
<div class="header">
    <h1>{{ $title ?? 'PvP Calculator' }}</h1>
    <p class="subtitle">{{ $subtitle ?? '' }}</p>
</div>

@include('partials.form')

@if(isset($stats))
    @include('partials.results')
@endif
@endsection

@push('scripts')
<script>
    // Enhanced form validation with real-time feedback
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('statForm');
        const inputs = form.querySelectorAll('input[type="number"]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fix the errors in the form before submitting.',
                    confirmButtonColor: '#d33'
                });
            }
        });
    });
    
    function validateField(field) {
        const value = parseFloat(field.value);
        const min = parseFloat(field.getAttribute('min')) || 0;
        const max = parseFloat(field.getAttribute('max')) || Infinity;
        const fieldName = field.labels[0].textContent;
        
        clearFieldError(field);
        
        if (field.hasAttribute('required') && (!field.value || field.value.trim() === '')) {
            showFieldError(field, `${fieldName} is required`);
            return false;
        }
        
        if (field.value && (isNaN(value) || value < min || value > max)) {
            showFieldError(field, `${fieldName} must be between ${min} and ${max}`);
            return false;
        }
        
        return true;
    }
    
    function showFieldError(field, message) {
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function clearFieldError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
</script>
@endpush