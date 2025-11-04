/**
 * Global Form Validation Library
 * Provides comprehensive validation for all forms
 */

// Email validation
function validateEmail(email) {
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
}

// Phone number validation (Sri Lankan format) - UPDATED to allow 12 digits
function validatePhone(phone) {
    const phoneRegex = /^(?:\+94|0)?[1-9]\d{8,11}$/;
    return phoneRegex.test(phone.replace(/[\s-]/g, ''));
}

// Credit card number validation (Luhn algorithm)
function validateCreditCard(cardNumber) {
    cardNumber = cardNumber.replace(/\s+/g, '');
    
    if (!/^\d{13,19}$/.test(cardNumber)) return false;
    
    let sum = 0;
    let isEven = false;
    
    for (let i = cardNumber.length - 1; i >= 0; i--) {
        let digit = parseInt(cardNumber.charAt(i), 10);
        
        if (isEven) {
            digit *= 2;
            if (digit > 9) digit -= 9;
        }
        
        sum += digit;
        isEven = !isEven;
    }
    
    return (sum % 10) === 0;
}

// CVV validation
function validateCVV(cvv) {
    return /^\d{3,4}$/.test(cvv);
}

// Expiry date validation
function validateExpiryDate(expiryDate) {
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiryDate)) return false;
    
    const [month, year] = expiryDate.split('/');
    const expiry = new Date(2000 + parseInt(year), parseInt(month) - 1);
    const now = new Date();
    
    return expiry >= now;
}

// Date of birth validation (must be at least 13 years old)
function validateDOB(dob) {
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age >= 13 && age <= 120;
}

// Empty input validation
function validateNotEmpty(value) {
    return value !== null && value !== undefined && value.trim() !== '';
}

// Password strength validation
function validatePassword(password) {
    return password.length >= 6;
}

// Username validation
function validateUsername(username) {
    return /^[a-zA-Z0-9_]{3,20}$/.test(username);
}

// OTP validation
function validateOTP(otp) {
    return /^\d{6}$/.test(otp);
}

// UPI ID validation
function validateUPI(upiId) {
    return /^[a-zA-Z0-9._-]+@[a-zA-Z0-9]+$/.test(upiId);
}

// Display error message - UPDATED to always show under input
function showError(inputElement, message) {
    // Find or create error message element
    let errorDiv = inputElement.parentElement.querySelector('.error-message');
    
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#ef4444';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.5rem';
        errorDiv.style.display = 'flex';
        errorDiv.style.alignItems = 'flex-start';
        errorDiv.style.gap = '0.5rem';
        inputElement.parentElement.appendChild(errorDiv);
    }
    
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle" style="margin-top: 2px;"></i><span>${message}</span>`;
    errorDiv.style.display = 'flex';
    
    inputElement.classList.add('is-invalid');
    inputElement.setAttribute('aria-invalid', 'true');
    inputElement.style.borderColor = '#ef4444';
}

// Clear error message - UPDATED
function clearError(inputElement) {
    const errorDiv = inputElement.parentElement.querySelector('.error-message');
    
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
    
    inputElement.classList.remove('is-invalid');
    inputElement.removeAttribute('aria-invalid');
    inputElement.style.borderColor = '';
}

// Validate single field
function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.type;
    const fieldName = field.name;
    
    clearError(field);
    
    // Check if required
    if (field.hasAttribute('required') && !validateNotEmpty(value)) {
        showError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if ((fieldType === 'email' || fieldName.includes('email')) && value) {
        if (!validateEmail(value)) {
            showError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Phone validation
    if ((fieldType === 'tel' || fieldName.includes('phone')) && value) {
        if (!validatePhone(value)) {
            showError(field, 'Please enter a valid phone number (e.g., +94771234567)');
            return false;
        }
    }
    
    // Password validation
    if (fieldType === 'password' && value && fieldName === 'password') {
        if (!validatePassword(value)) {
            showError(field, 'Password must be at least 6 characters');
            return false;
        }
    }
    
    // Confirm password validation
    if (fieldName === 'confirmPassword' && value) {
        const passwordField = field.form.querySelector('[name="password"]');
        if (passwordField && value !== passwordField.value) {
            showError(field, 'Passwords do not match');
            return false;
        }
    }
    
    // Date of birth validation
    if (fieldName === 'dob' && value) {
        if (!validateDOB(value)) {
            showError(field, 'You must be at least 13 years old');
            return false;
        }
    }
    
    // OTP validation
    if (fieldName === 'otp' && value) {
        if (!validateOTP(value)) {
            showError(field, 'OTP must be 6 digits');
            return false;
        }
    }
    
    return true;
}

// Validate entire form - UPDATED to collect field names
function validateForm(form) {
    let isValid = true;
    const errors = [];
    const fields = form.querySelectorAll('input, select, textarea');
    
    fields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
            
            // Get field label or name
            const label = form.querySelector(`label[for="${field.id}"]`);
            const fieldName = label ? label.textContent.replace('*', '').trim() : 
                            field.getAttribute('placeholder') || 
                            field.name || 
                            'Field';
            
            // Get error message
            const errorDiv = field.parentElement.querySelector('.error-message');
            const errorMessage = errorDiv ? errorDiv.textContent : 'Invalid value';
            
            errors.push({
                field: fieldName,
                message: errorMessage,
                element: field
            });
        }
    });
    
    return { isValid, errors };
}

// Initialize form validation - UPDATED to NOT show modal
function initFormValidation(formSelector) {
    const forms = document.querySelectorAll(formSelector);
    
    forms.forEach(form => {
        // Real-time validation on blur
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) {
                    validateField(field);
                }
            });
        });
        
        // Validate on submit - UPDATED to just prevent submission
        form.addEventListener('submit', (e) => {
            const result = validateForm(form);
            
            if (!result.isValid) {
                e.preventDefault();
                
                // Scroll to first error
                if (result.errors.length > 0) {
                    const firstErrorElement = result.errors[0].element;
                    if (firstErrorElement) {
                        firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstErrorElement.focus();
                    }
                }
            }
        });
    });
}

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initFormValidation('form');
});

// Close modal on ESC key (if modal exists)
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('validationErrorModal');
        if (modal) {
            modal.remove();
        }
    }
});
