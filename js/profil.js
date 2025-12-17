class ProfileManager {
    constructor() {
        this.profileForm = document.getElementById('profileForm');
        this.passwordForm = document.getElementById('passwordForm');
        this.deleteAccountForm = document.getElementById('deleteAccountForm');
        this.profilePicture = document.getElementById('profilePicture');
        this.profilePictureInput = document.getElementById('profilePictureInput');
        this.changePhotoBtn = document.getElementById('changePhotoBtn');
        this.refreshProfileBtn = document.getElementById('refreshProfile');
        this.confirmDeleteBtn = document.getElementById('confirmDeleteAccount');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadProfileStats();
    }

    setupEventListeners() {
        // Profile form submission
        if (this.profileForm) {
            this.profileForm.addEventListener('submit', (e) => this.handleProfileUpdate(e));
        }

        // Password form submission
        if (this.passwordForm) {
            this.passwordForm.addEventListener('submit', (e) => this.handlePasswordChange(e));
        }

        // Profile picture change
        if (this.changePhotoBtn) {
            this.changePhotoBtn.addEventListener('click', () => this.profilePictureInput.click());
        }

        if (this.profilePictureInput) {
            this.profilePictureInput.addEventListener('change', (e) => this.handleProfilePictureChange(e));
        }

        // Refresh profile
        if (this.refreshProfileBtn) {
            this.refreshProfileBtn.addEventListener('click', () => this.refreshProfileData());
        }

        // Delete account confirmation
        if (this.confirmDeleteBtn) {
            this.confirmDeleteBtn.addEventListener('click', () => this.handleAccountDeletion());
        }

        // Handle image loading errors
        if (this.profilePicture) {
            this.profilePicture.addEventListener('error', (e) => this.handleImageError(e));
        }
    }

    handleImageError(event) {
        console.error('Gambar tidak dapat dimuat:', event.target.src);
        // Ganti dengan gambar default dari ROOT
        event.target.src = '/assets/icons/default-avatar.png';
    }

    async handleProfileUpdate(event) {
        event.preventDefault();
        
        const formData = new FormData(this.profileForm);
        const submitBtn = this.profileForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('php/crud/user/update.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('Profile update response:', data);

            if (data.success) {
                this.showAlert('Profil berhasil diperbarui!', 'success');
                // Update displayed data
                this.updateProfileDisplay(data.user);
            } else {
                this.showAlert(data.message, 'danger');
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            this.showAlert('Terjadi kesalahan saat memperbarui profil.', 'danger');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    async handlePasswordChange(event) {
        event.preventDefault();
        
        const formData = new FormData(this.passwordForm);
        const submitBtn = this.passwordForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Validate password match
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            this.showAlert('Password baru dan konfirmasi password tidak cocok.', 'danger');
            return;
        }

        if (newPassword.length < 8) {
            this.showAlert('Password minimal 8 karakter.', 'danger');
            return;
        }

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengubah...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('ajax/ajax-password.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Password berhasil diubah!', 'success');
                this.passwordForm.reset();
            } else {
                this.showAlert(data.message, 'danger');
            }
        } catch (error) {
            console.error('Error changing password:', error);
            this.showAlert('Terjadi kesalahan saat mengubah password.', 'danger');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    async handleProfilePictureChange(event) {
        const file = event.target.files[0];
        if (!file) return;

        console.log('File selected:', file.name, 'Size:', file.size, 'Type:', file.type);

        // Validate file type and size
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!validTypes.includes(file.type)) {
            this.showAlert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.', 'danger');
            return;
        }

        if (file.size > maxSize) {
            this.showAlert('Ukuran file terlalu besar. Maksimal 2MB.', 'danger');
            return;
        }

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            this.profilePicture.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Show loading state on overlay
        const overlay = this.changePhotoBtn;
        const originalHTML = overlay.innerHTML;
        overlay.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        overlay.style.opacity = '1';
        
        try {
            const formData = new FormData();
            formData.append('foto_profil', file);

            console.log('Uploading profile picture...');

            const response = await fetch('php/crud/user/update.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('Upload response:', data);

            if (data.success) {
                this.showAlert('Foto profil berhasil diubah!', 'success');
                
                // Update profile picture dengan cache busting
                if (data.user && data.user.foto_profil) {
                    const newSrc = this.getImageUrl(data.user.foto_profil);
                    console.log('Setting new profile picture src:', newSrc);
                    
                    // Gunakan timeout kecil untuk memastikan preview di-replace
                    setTimeout(() => {
                        this.profilePicture.src = newSrc;
                    }, 100);
                }
                
                // Update session data jika diperlukan
                if (window.updateHeaderProfile) {
                    window.updateHeaderProfile();
                }
            } else {
                this.showAlert(data.message || 'Gagal mengubah foto profil', 'danger');
                // Revert to original image on error
                this.refreshProfileData();
            }
        } catch (error) {
            console.error('Error updating profile picture:', error);
            this.showAlert('Terjadi kesalahan saat mengubah foto profil: ' + error.message, 'danger');
            // Revert to original image on error
            this.refreshProfileData();
        } finally {
            overlay.innerHTML = originalHTML;
            overlay.style.opacity = '0';
            // Reset input to allow uploading same file again
            this.profilePictureInput.value = '';
        }
    }

    getImageUrl(path) {
        // Tambahkan timestamp untuk menghindari cache
        if (!path || path === '/assets/icons/default-avatar.png') {
            return '/assets/icons/default-avatar.png';
        }
        return path + '?t=' + new Date().getTime();
    }

    async handleAccountDeletion() {
        const password = document.getElementById('delete_password').value;
        
        if (!password) {
            this.showAlert('Password wajib diisi untuk menghapus akun.', 'danger');
            return;
        }

        const submitBtn = this.confirmDeleteBtn;
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menghapus...';
        submitBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('password', password);

            const response = await fetch('php/crud/user/delete.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Akun berhasil dihapus. Mengarahkan ke halaman login...', 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                this.showAlert(data.message, 'danger');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error deleting account:', error);
            this.showAlert('Terjadi kesalahan saat menghapus akun.', 'danger');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    async refreshProfileData() {
        if (this.refreshProfileBtn) {
            this.refreshProfileBtn.classList.add('btn-loading');
            const originalHTML = this.refreshProfileBtn.innerHTML;
            this.refreshProfileBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }

        try {
            const response = await fetch('php/crud/user/read.php');
            const data = await response.json();

            if (data.success) {
                this.updateProfileDisplay(data.data);
                this.showAlert('Data profil diperbarui!', 'success');
            } else {
                this.showAlert('Gagal memuat data profil', 'warning');
            }
        } catch (error) {
            console.error('Error refreshing profile:', error);
            this.showAlert('Terjadi kesalahan saat memuat data profil', 'danger');
        } finally {
            if (this.refreshProfileBtn) {
                this.refreshProfileBtn.classList.remove('btn-loading');
                this.refreshProfileBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
            }
        }
    }

    updateProfileDisplay(userData) {
        console.log('Updating profile display with:', userData);
        
        // Update form fields
        if (userData.nama) {
            document.getElementById('nama').value = userData.nama;
            const cardTitle = document.querySelector('.card-title');
            if (cardTitle) cardTitle.textContent = userData.nama;
        }
        if (userData.email) {
            document.getElementById('email').value = userData.email;
            const emailElement = document.querySelector('.text-muted');
            if (emailElement) emailElement.textContent = userData.email;
        }
        if (userData.telepon) {
            document.getElementById('telepon').value = userData.telepon;
        }
        if (userData.alamat) {
            document.getElementById('alamat').value = userData.alamat;
        }
        if (userData.foto_profil) {
            // Update profile picture dengan cache busting
            const newSrc = this.getImageUrl(userData.foto_profil);
            console.log('Updating profile picture to:', newSrc);
            this.profilePicture.src = newSrc;
        }
    }

    loadProfileStats() {
        // This can be expanded to load additional profile statistics
        console.log('Loading profile stats...');
    }

    showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        
        // Remove existing alerts
        const existingAlerts = alertContainer.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="fas ${this.getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        alertContainer.appendChild(alert);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    getAlertIcon(type) {
        const icons = {
            'success': 'fa-check-circle',
            'danger': 'fa-exclamation-triangle',
            'warning': 'fa-exclamation-circle',
            'info': 'fa-info-circle'
        };
        return icons[type] || 'fa-info-circle';
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.profileManager = new ProfileManager();
});