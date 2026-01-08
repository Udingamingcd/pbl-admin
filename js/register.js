class RegisterForm {
    constructor() {
        this.form = document.getElementById('registerForm');
        this.passwordInput = document.getElementById('password');
        this.confirmPasswordInput = document.getElementById('confirm_password');
        this.passwordStrength = document.querySelector('.password-strength');
        this.fileUpload = document.querySelector('.file-upload-input');
        this.fileUploadDisplay = document.querySelector('.file-upload-display');
        this.isInitialized = false;
        
        // Weak passwords database
        this.weakPasswords = new Set([
            'password', '123456', '123456789', '12345678', '12345', 
            '1234567', '1234567890', 'qwerty', 'abc123', 'password1',
            '123123', '000000', '111111', 'admin', 'letmein', 'welcome',
            'monkey', 'sunshine', 'password123', '1234', '123'
        ]);
        
        this.init();
    }

    init() {
        if (this.isInitialized) return;
        
        this.setupEventListeners();
        this.setupPasswordStrength();
        this.setupFileUpload();
        this.setupPasswordToggleButtons();
        this.isInitialized = true;
        
        console.log('RegisterForm initialized successfully');
    }

    setupEventListeners() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Real-time password confirmation check
        if (this.confirmPasswordInput) {
            this.confirmPasswordInput.addEventListener('input', () => this.validatePasswordMatch());
        }

        // Real-time validation on blur
        const inputs = this.form.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearValidation(input));
        });

        // File upload button click
        const uploadBtn = this.fileUploadDisplay?.querySelector('.file-upload-btn');
        if (uploadBtn) {
            uploadBtn.addEventListener('click', () => this.fileUpload?.click());
        }

        // Drag and drop for file upload
        this.setupDragAndDrop();

        // Modal event listeners
        this.setupModalEvents();
    }

    setupPasswordToggleButtons() {
        // Setup existing toggle buttons in the input group
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            // Find the associated input field
            const inputGroup = button.closest('.input-group');
            const inputField = inputGroup?.querySelector('input[type="password"], input[type="text"]');
            
            if (inputField && button) {
                // Add click event listener
                button.addEventListener('click', () => {
                    this.togglePasswordVisibility(inputField, button);
                });
                
                // Initialize button state
                this.updateToggleButtonState(inputField, button);
            }
        });
    }

    updateToggleButtonState(inputField, toggleBtn) {
        if (!inputField || !toggleBtn) return;
        
        const icon = toggleBtn.querySelector('i');
        if (inputField.type === 'password') {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            toggleBtn.setAttribute('aria-label', 'Show password');
        } else {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            toggleBtn.setAttribute('aria-label', 'Hide password');
        }
    }

    togglePasswordVisibility(inputField, toggleBtn) {
        if (!inputField || !toggleBtn) return;
        
        // Toggle password visibility
        const type = inputField.type === 'password' ? 'text' : 'password';
        inputField.type = type;
        
        // Update button icon and state
        this.updateToggleButtonState(inputField, toggleBtn);
        
        // Add visual feedback
        toggleBtn.style.transform = 'scale(1.1)';
        setTimeout(() => {
            toggleBtn.style.transform = 'scale(1)';
        }, 200);
    }

    setupModalEvents() {
        // Continue with weak password button
        const continueWeakBtn = document.getElementById('continueWeakPassword');
        if (continueWeakBtn) {
            continueWeakBtn.addEventListener('click', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('weakPasswordModal'));
                if (modal) modal.hide();
                this.submitForm();
            });
        }
    }

    setupDragAndDrop() {
        if (!this.fileUploadDisplay) return;

        this.fileUploadDisplay.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.fileUploadDisplay.style.borderColor = 'var(--primary-color)';
            this.fileUploadDisplay.style.background = 'rgba(67, 97, 238, 0.05)';
        });

        this.fileUploadDisplay.addEventListener('dragleave', (e) => {
            e.preventDefault();
            this.fileUploadDisplay.style.borderColor = '#dee2e6';
            this.fileUploadDisplay.style.background = 'white';
        });

        this.fileUploadDisplay.addEventListener('drop', (e) => {
            e.preventDefault();
            this.fileUploadDisplay.style.borderColor = '#dee2e6';
            this.fileUploadDisplay.style.background = 'white';
            
            if (e.dataTransfer.files.length) {
                this.fileUpload.files = e.dataTransfer.files;
                this.handleFileUpload(e);
            }
        });
    }

    validateField(field) {
        if (!field.value.trim()) {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        } else {
            if (field.checkValidity()) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        }
    }

    clearValidation(field) {
        if (field.value.trim()) {
            field.classList.remove('is-invalid');
        }
    }

    setupPasswordStrength() {
        if (this.passwordInput) {
            this.passwordInput.addEventListener('input', () => {
                this.updatePasswordStrength();
                this.validatePasswordMatch();
            });
        }
    }

    setupFileUpload() {
        if (this.fileUpload && this.fileUploadDisplay) {
            this.fileUpload.addEventListener('change', (e) => this.handleFileUpload(e));
        }
    }

    updatePasswordStrength() {
        const password = this.passwordInput.value;
        const strength = this.calculatePasswordStrength(password);
        
        if (!this.passwordStrength) return;
        
        const progressBar = this.passwordStrength.querySelector('.progress-bar');
        const strengthLabel = this.passwordStrength.querySelector('.strength-label');
        const passwordValidFeedback = document.getElementById('passwordValidFeedback');
        
        if (!progressBar || !strengthLabel) return;
        
        // Remove all strength classes
        this.passwordStrength.className = 'password-strength mt-2';
        
        // Add current strength class
        this.passwordStrength.classList.add(`password-${strength.level}`);
        
        // Update progress bar and label
        progressBar.style.width = `${strength.score * 20}%`;
        strengthLabel.textContent = strength.label;
        
        // Update valid feedback message based on strength
        if (passwordValidFeedback) {
            const strengthMessages = {
                'very-weak': 'Password sangat lemah - tinggi risiko keamanan',
                'weak': 'Password lemah - disarankan untuk ditingkatkan',
                'medium': 'Password cukup - dapat digunakan',
                'strong': 'Password kuat - baik untuk keamanan',
                'very-strong': 'Password sangat kuat - keamanan optimal'
            };
            passwordValidFeedback.textContent = strengthMessages[strength.level] || 'Password valid!';
        }
        
        // Validasi yang lebih fleksibel
        const meetsLengthRequirement = password.length >= 8;
        
        if (password.length === 0) {
            this.passwordInput.classList.remove('is-invalid', 'is-valid');
        } else if (meetsLengthRequirement) {
            // Semua password dengan panjang minimal 8 dianggap valid
            this.passwordInput.classList.remove('is-invalid');
            this.passwordInput.classList.add('is-valid');
        } else {
            this.passwordInput.classList.remove('is-valid');
            this.passwordInput.classList.add('is-invalid');
            this.passwordInput.setCustomValidity('Password minimal 8 karakter');
        }
    }

    calculatePasswordStrength(password) {
        let score = 0;
        
        if (!password) {
            return { score: 0, level: 'very-weak', label: 'Sangat Lemah' };
        }
        
        // Check for very weak passwords
        if (this.weakPasswords.has(password.toLowerCase())) {
            return { score: 0, level: 'very-weak', label: 'Sangat Berisiko' };
        }
        
        // Check for sequential numbers
        if (/^(0123456789|1234567890|123456789)$/.test(password)) {
            return { score: 0, level: 'very-weak', label: 'Sangat Berisiko' };
        }
        
        // Check for repeated characters
        if (/(.)\1{4,}/.test(password)) {
            return { score: 0, level: 'very-weak', label: 'Sangat Berisiko' };
        }
        
        // Length check
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (password.length >= 16) score++;
        
        // Character variety checks
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^a-zA-Z0-9]/.test(password)) score++;
        
        // Bonus points for mixed case and special chars
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/[^a-zA-Z0-9]/.test(password) && /[0-9]/.test(password)) score++;
        
        // Deduct points for common patterns
        if (/^[0-9]+$/.test(password)) score = Math.max(0, score - 2);
        if (/^[a-zA-Z]+$/.test(password)) score = Math.max(0, score - 2);
        
        // Strength levels
        let level, label;
        if (score <= 2) {
            level = 'very-weak';
            label = 'Sangat Lemah';
        } else if (score <= 4) {
            level = 'weak';
            label = 'Lemah';
        } else if (score <= 7) {
            level = 'medium';
            label = 'Cukup';
        } else if (score <= 9) {
            level = 'strong';
            label = 'Kuat';
        } else {
            level = 'very-strong';
            label = 'Sangat Kuat';
        }
        
        return {
            score: Math.min(score, 10),
            level: level,
            label: label
        };
    }

    validatePasswordMatch() {
        const password = this.passwordInput.value;
        const confirmPassword = this.confirmPasswordInput.value;
        const confirmPasswordInvalidFeedback = document.getElementById('confirmPasswordInvalidFeedback');
        
        this.confirmPasswordInput.classList.remove('is-invalid', 'is-valid');
        this.confirmPasswordInput.setCustomValidity('');
        
        if (!confirmPassword) {
            return;
        }
        
        if (password !== confirmPassword) {
            this.confirmPasswordInput.classList.add('is-invalid');
            this.confirmPasswordInput.classList.remove('is-valid');
            this.confirmPasswordInput.setCustomValidity('Password tidak cocok');
            
            if (confirmPasswordInvalidFeedback) {
                confirmPasswordInvalidFeedback.textContent = 'Password tidak cocok';
            }
        } else {
            const meetsLengthRequirement = password.length >= 8;
            
            if (meetsLengthRequirement) {
                this.confirmPasswordInput.classList.remove('is-invalid');
                this.confirmPasswordInput.classList.add('is-valid');
                this.confirmPasswordInput.setCustomValidity('');
            } else {
                this.confirmPasswordInput.classList.remove('is-valid');
                this.confirmPasswordInput.classList.add('is-invalid');
                this.confirmPasswordInput.setCustomValidity('Password utama harus minimal 8 karakter');
                
                if (confirmPasswordInvalidFeedback) {
                    confirmPasswordInvalidFeedback.textContent = 'Password utama harus minimal 8 karakter';
                }
            }
        }
    }

    handleFileUpload(event) {
        const file = event.target.files[0];
        const display = this.fileUploadDisplay;
        const preview = display.querySelector('.file-upload-preview');
        const text = display.querySelector('.file-upload-text');
        const subtext = display.querySelector('.file-upload-subtext');
        
        if (!file || !display || !preview || !text) return;
        
        // Validate file type and size
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 2 * 1024 * 1024;
        
        if (!validTypes.includes(file.type)) {
            this.showErrorModal('Format File Tidak Didukung', 'Gunakan format JPG, PNG, atau GIF.');
            event.target.value = '';
            return;
        }
        
        if (file.size > maxSize) {
            this.showErrorModal('Ukuran File Terlalu Besar', 'Maksimal ukuran file adalah 2MB.');
            event.target.value = '';
            return;
        }
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            const existingImg = preview.querySelector('img');
            if (existingImg) existingImg.remove();
            
            const icon = preview.querySelector('i');
            if (icon) icon.style.display = 'none';
            
            if (subtext) subtext.style.display = 'none';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Preview foto profil';
            img.style.width = '50px';
            img.style.height = '50px';
            img.style.borderRadius = '50%';
            img.style.objectFit = 'cover';
            img.style.border = '2px solid var(--primary-color)';
            preview.prepend(img);
            
            text.textContent = file.name;
            text.style.color = 'var(--success-color)';
            display.classList.add('has-file');
        };
        reader.readAsDataURL(file);
    }

    async handleSubmit(event) {
        event.preventDefault();
        
        console.log('Form submission started');
        
        // Validate all required fields
        let isValid = true;
        const requiredFields = this.form.querySelectorAll('input[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
                console.log('Field required missing:', field.id);
            }
        });
        
        if (!isValid) {
            this.showErrorModal('Form Tidak Lengkap', 'Harap isi semua field yang wajib diisi.');
            this.form.classList.add('was-validated');
            this.scrollToFirstError();
            return;
        }

        // Validasi password match
        if (this.passwordInput.value !== this.confirmPasswordInput.value) {
            console.log('Password tidak cocok');
            this.showErrorModal(
                'Password Tidak Cocok', 
                'Password dan konfirmasi password tidak cocok. Harap pastikan kedua field password sama.'
            );
            this.confirmPasswordInput.classList.add('is-invalid');
            this.scrollToElement(this.confirmPasswordInput);
            return;
        }

        // Validasi password strength - lebih fleksibel
        const passwordStrength = this.calculatePasswordStrength(this.passwordInput.value);
        console.log('Password strength:', passwordStrength);
        
        // Tampilkan warning untuk password lemah, tapi beri opsi untuk melanjutkan
        if (passwordStrength.level === 'very-weak') {
            console.log('Password sangat lemah, menampilkan modal warning');
            this.showWeakPasswordModal();
            this.scrollToElement(this.passwordInput);
            return;
        }

        // Validate terms agreement
        const agreeTerms = document.getElementById('agree_terms');
        if (!agreeTerms.checked) {
            console.log('Terms not agreed');
            this.showErrorModal('Persetujuan Diperlukan', 'Anda harus menyetujui syarat dan ketentuan.');
            this.scrollToElement(agreeTerms);
            return;
        }

        // Jika semua validasi passed, lanjutkan submit
        this.submitForm();
    }

    async submitForm() {
        // Show loading state
        const submitBtn = this.form.querySelector('.register-btn');
        const spinner = submitBtn.querySelector('.spinner-border');
        const btnText = submitBtn.querySelector('.btn-text');
        
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        if (btnText) btnText.textContent = 'Mendaftarkan...';

        try {
            const formData = new FormData(this.form);
            
            console.log('Sending request to server...');
            
            const response = await fetch('ajax/ajax-register.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            console.log('Server response:', data);
            
            if (data.success) {
                console.log('Registration successful');
                this.showSuccessModal();
            } else {
                console.log('Registration failed:', data.message);
                if (data.message && (
                    data.message.toLowerCase().includes('email sudah terdaftar') ||
                    data.message.toLowerCase().includes('email already') ||
                    data.message.toLowerCase().includes('email exists')
                )) {
                    this.showEmailRegisteredModal();
                } else {
                    this.showErrorModal('Pendaftaran Gagal', data.message || 'Terjadi kesalahan saat mendaftar.');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            this.showErrorModal(
                'Kesalahan Jaringan', 
                'Terjadi kesalahan jaringan. Silakan periksa koneksi internet Anda dan coba lagi.'
            );
        } finally {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            if (btnText) btnText.textContent = 'Daftar Sekarang';
        }
    }

    scrollToFirstError() {
        const firstError = this.form.querySelector('.is-invalid');
        if (firstError) {
            this.scrollToElement(firstError);
        }
    }

    scrollToElement(element) {
        element.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
        
        element.style.transition = 'all 0.3s ease';
        element.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.25)';
        
        setTimeout(() => {
            element.style.boxShadow = '';
        }, 2000);
    }

    showEmailRegisteredModal() {
        console.log('Showing email registered modal');
        const email = document.getElementById('email').value;
        const registeredEmailElement = document.getElementById('registeredEmail');
        
        if (registeredEmailElement) {
            registeredEmailElement.textContent = email;
        }
        
        const modalElement = document.getElementById('emailRegisteredModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Email registered modal element not found');
            this.showErrorModal('Email Sudah Terdaftar', `Email ${email} sudah terdaftar di sistem kami.`);
        }
    }

    showWeakPasswordModal() {
        console.log('Showing weak password modal');
        const modalElement = document.getElementById('weakPasswordModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Weak password modal element not found');
            this.showErrorModal(
                'Password Terlalu Lemah', 
                'Password Anda terlalu lemah dan berisiko terhadap keamanan akun. Silakan gunakan password yang lebih kuat.'
            );
        }
    }

    showErrorModal(title, message) {
        console.log('Showing error modal:', title, message);
        
        const errorModal = document.getElementById('errorModal');
        const errorTitle = document.getElementById('errorModalTitle');
        const errorMessage = document.getElementById('errorModalMessage');
        
        if (errorModal && errorTitle && errorMessage) {
            errorTitle.textContent = title;
            errorMessage.textContent = message;
            
            const modal = new bootstrap.Modal(errorModal);
            modal.show();
        } else {
            console.error('Error modal elements not found');
            alert(`${title}: ${message}`);
        }
    }

    showSuccessModal() {
        console.log('Showing success modal');
        const modalElement = document.getElementById('successModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            this.createCelebrationEffect();
        } else {
            console.error('Success modal element not found');
            window.location.href = 'index.php?registered=true';
        }
    }

    createCelebrationEffect() {
        const colors = ['#4361ee', '#3a0ca3', '#4cc9f0', '#f72585', '#7209b7'];
        
        for (let i = 0; i < 15; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.cssText = `
                    position: fixed;
                    width: 12px;
                    height: 12px;
                    background: ${colors[Math.floor(Math.random() * colors.length)]};
                    top: -10px;
                    left: ${Math.random() * 100}%;
                    opacity: ${Math.random() + 0.5};
                    border-radius: 2px;
                    z-index: 9999;
                    animation: confetti-fall ${(Math.random() * 3) + 2}s linear forwards;
                `;
                
                document.body.appendChild(confetti);
                
                setTimeout(() => {
                    if (confetti.parentNode) {
                        confetti.remove();
                    }
                }, 3000);
            }, i * 100);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing register form');
    
    const loadingScreen = document.getElementById('loading');
    
    if (loadingScreen && loadingScreen.style.display !== 'none') {
        document.addEventListener('registerPageReady', function() {
            console.log('Register page ready, initializing form');
            setTimeout(() => {
                window.registerForm = new RegisterForm();
            }, 100);
        });
    } else {
        console.log('No loading screen, initializing immediately');
        setTimeout(() => {
            window.registerForm = new RegisterForm();
        }, 100);
    }
    
    checkDatabaseConnection();
});

// Database connection check
function checkDatabaseConnection() {
    const indicator = document.getElementById('db-indicator');
    
    if (!indicator) {
        console.log('Database indicator not found');
        return;
    }
    
    console.log('Checking database connection...');
    
    fetch('ajax/cek-koneksi.php')
        .then(response => response.json())
        .then(data => {
            console.log('Database connection response:', data);
            if (data.connected) {
                indicator.innerHTML = `
                    <i class="fas fa-database text-success me-2"></i>
                    <span>Database terkoneksi - Siap menerima pendaftaran</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                `;
                indicator.classList.remove('alert-info');
                indicator.classList.add('alert-success');
            } else {
                indicator.innerHTML = `
                    <i class="fas fa-database text-danger me-2"></i>
                    <span>Database tidak terhubung - Pendaftaran tidak dapat diproses</span>
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
                <span>Gagal mengecek koneksi database - Silakan refresh halaman</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            `;
            indicator.classList.remove('alert-info');
            indicator.classList.add('alert-warning');
        });
}

// Add confetti animation styles
const confettiStyles = `
@keyframes confetti-fall {
    0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}

.confetti {
    pointer-events: none;
}
`;

if (!document.querySelector('#confetti-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'confetti-styles';
    styleSheet.textContent = confettiStyles;
    document.head.appendChild(styleSheet);
}