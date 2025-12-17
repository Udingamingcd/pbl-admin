// FILE: js/login.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('Login script loaded');
    
    // Toggle password visibility
    const togglePassword = document.querySelector('.toggle-password');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    // Form validation and submission
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            // Enhanced validation
            if (!validateForm()) {
                this.classList.add('was-validated');
                return;
            }
            
            // Show loading state - tanpa spinner
            const submitBtn = this.querySelector('.login-btn');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';
            submitBtn.style.opacity = '0.7';
            
            // Get form data
            const formData = new FormData(this);
            
            try {
                console.log('Sending login request...');
                
                const response = await fetch('ajax/ajax-login.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Response received:', response);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showAlert(data.message, 'danger');
                }
                
            } catch (error) {
                console.error('Login error:', error);
                showAlert('Terjadi kesalahan saat login. Periksa koneksi internet Anda.', 'danger');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                submitBtn.style.opacity = '1';
            }
        });

        // Real-time validation
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        emailInput.addEventListener('input', function() {
            validateEmail(this);
        });

        passwordInput.addEventListener('input', function() {
            validatePassword(this);
        });
    }

    // Check database connection on page load
    checkDatabaseConnection();
});

function checkDatabaseConnection() {
    const indicator = document.getElementById('db-indicator');
    
    if (!indicator) return;
    
    fetch('ajax/cek-koneksi.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.connected) {
                indicator.innerHTML = `
                    <i class="fas fa-database text-success me-2"></i>
                    <span>Database terkoneksi</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                `;
                indicator.classList.remove('alert-info');
                indicator.classList.add('alert-success');
            } else {
                indicator.innerHTML = `
                    <i class="fas fa-database text-danger me-2"></i>
                    <span>Database tidak terhubung</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                `;
                indicator.classList.remove('alert-info');
                indicator.classList.add('alert-danger');
            }
        })
        .catch(error => {
            console.error('Error checking database connection:', error);
            indicator.innerHTML = `
                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                <span>Gagal mengecek koneksi database</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            `;
            indicator.classList.remove('alert-info');
            indicator.classList.add('alert-warning');
        });
}

function showAlert(message, type) {
    // Remove existing alerts (except db indicator)
    const existingAlerts = document.querySelectorAll('.alert-dismissible');
    existingAlerts.forEach(alert => {
        if (alert.id !== 'db-indicator') {
            alert.remove();
        }
    });
    
    // Create new alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert after database indicator or at the top of form
    const dbIndicator = document.getElementById('db-indicator');
    const form = document.getElementById('loginForm');
    
    if (dbIndicator && dbIndicator.parentNode) {
        dbIndicator.parentNode.insertBefore(alert, dbIndicator.nextSibling);
    } else if (form && form.parentNode) {
        form.parentNode.insertBefore(alert, form);
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Enhanced form validation
function validateForm() {
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    let isValid = true;

    // Reset previous states
    email.classList.remove('is-invalid', 'is-valid');
    password.classList.remove('is-invalid', 'is-valid');

    // Email validation
    if (!email.value || !isValidEmail(email.value)) {
        email.classList.add('is-invalid');
        isValid = false;
    } else {
        email.classList.add('is-valid');
    }

    // Password validation
    if (!password.value || password.value.length < 8) {
        password.classList.add('is-invalid');
        isValid = false;
    } else {
        password.classList.add('is-valid');
    }

    return isValid;
}

function validateEmail(input) {
    if (!input.value || !isValidEmail(input.value)) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }
}

function validatePassword(input) {
    if (!input.value || input.value.length < 8) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}