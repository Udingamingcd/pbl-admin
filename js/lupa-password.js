class ForgotPasswordForm {
    constructor() {
        this.form = document.getElementById('forgotPasswordForm');
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    async handleSubmit(event) {
        event.preventDefault();
        
        if (!this.form.checkValidity()) {
            event.stopPropagation();
            this.form.classList.add('was-validated');
            return;
        }

        // Show loading state
        const submitBtn = this.form.querySelector('.login-btn');
        const spinner = submitBtn.querySelector('.spinner-border');
        const btnText = submitBtn.querySelector('span:not(.spinner-border)');
        
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Mengirim...';

        try {
            const formData = new FormData(this.form);
            
            const response = await fetch('ajax/ajax-forgot-password.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccessModal();
                this.form.reset();
                this.form.classList.remove('was-validated');
            } else {
                this.showAlert(data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Terjadi kesalahan saat mengirim permintaan. Silakan coba lagi.', 'danger');
        } finally {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'Kirim Link Reset';
        }
    }

    showAlert(message, type) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert-dismissible:not(#db-indicator)');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert after database indicator or at the top of form
        const dbIndicator = document.getElementById('db-indicator');
        if (dbIndicator) {
            dbIndicator.parentNode.insertBefore(alert, dbIndicator.nextSibling);
        } else {
            this.form.parentNode.insertBefore(alert, this.form);
        }
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    showSuccessModal() {
        const modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.forgotPasswordForm = new ForgotPasswordForm();
    
    // Check database connection
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
                    <i class="fas fa-check-circle text-success me-2"></i>
                    <span>Terhubung ke database</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                `;
                indicator.classList.remove('alert-info');
                indicator.classList.add('alert-success');
            } else {
                indicator.innerHTML = `
                    <i class="fas fa-times-circle text-danger me-2"></i>
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