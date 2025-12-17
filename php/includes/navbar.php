<?php
// Check if user session exists
$user_nama = $_SESSION['user_nama'] ?? 'Guest';
$user_email = $_SESSION['user_email'] ?? '';
$user_foto = $_SESSION['user_foto'] ?? 'assets/icons/default-avatar.png';
$user_id = $_SESSION['user_id'] ?? 0;
?>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top dashboard-navbar">
    <div class="container-fluid">
        <!-- Mobile menu toggle -->
        <button class="navbar-toggler me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Brand -->
        <a class="navbar-brand" href="dashboard.php">
            <img src="assets/icons/Dompt.png" alt="Finansialku" height="30" class="d-inline-block align-text-top me-2">
            <span class="brand-text">Finansialku</span>
        </a>

        <!-- Welcome and Motivation Messages -->
        <div class="navbar-nav mx-auto text-center d-none d-lg-flex">
            <div id="welcomeMotivationContainer" style="min-width: 500px; max-width: 700px;">
                <div id="logoAnimation" class="text-center">
                    <img src="assets/icons/Dompt.png" alt="Finansialku" height="40" class="blinking-logo">
                </div>
                <div id="welcomeMessage" class="welcome-text fw-bold fs-6" style="display: none;"></div>
                <div id="motivationMessage" class="motivation-text fs-6" style="display: none;"></div>
            </div>
        </div>

        <!-- Right side items -->
        <div class="d-flex align-items-center">
            <!-- Notification Bell -->
            <div class="notification-wrapper me-3 position-relative">
                <!-- Notification Bell Button -->
                <button class="btn btn-outline-light notification-btn position-relative" 
                        type="button" 
                        id="notificationBell"
                        aria-label="Notifikasi">
                    <i class="fas fa-bell"></i>
                    <!-- Notification Badge -->
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" 
                          id="notificationCount" 
                          style="display: none;">
                        0
                    </span>
                </button>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <!-- Notification Header -->
                    <div class="notification-header">
                        <h6 class="mb-0 fw-bold">Notifikasi</h6>
                        <div class="notification-actions">
                            <button class="btn btn-sm btn-outline-secondary" id="markAllReadBtn">
                                <i class="fas fa-check-double me-1"></i>Tandai semua dibaca
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="clearAllBtn">
                                <i class="fas fa-trash me-1"></i>Hapus semua
                            </button>
                        </div>
                    </div>
                    
                    <!-- Notification List -->
                    <div class="notification-list" id="notificationList">
                        <!-- Notifikasi akan dimuat secara dinamis -->
                        <div class="notification-empty-state">
                            <i class="fas fa-bell-slash fa-2x mb-2 text-muted"></i>
                            <p class="mb-0 text-muted">Tidak ada notifikasi</p>
                        </div>
                    </div>
                    
                    <!-- Notification Footer -->
                    <div class="notification-footer">
                        <a href="notifications.php" class="text-decoration-none">
                            <i class="fas fa-eye me-1"></i>Lihat semua notifikasi
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Theme Toggle Switch dengan tooltip di bawah -->
            <div class="theme-toggle-switch me-3 position-relative">
                <input type="checkbox" id="themeSwitch" class="theme-switch-checkbox">
                <label for="themeSwitch" class="theme-switch-label">
                    <span class="theme-switch-inner"></span>
                    <span class="theme-switch-switch">
                        <i class="fas fa-sun light-icon"></i>
                        <i class="fas fa-moon dark-icon"></i>
                    </span>
                </label>
                <!-- Tooltip di bawah tombol -->
                <div class="theme-tooltip theme-tooltip-bottom">Light Mode</div>
            </div>

            <!-- Enhanced User Menu -->
            <div class="dropdown">
                <button class="btn btn-outline-light d-flex align-items-center user-menu-btn" type="button" data-bs-toggle="dropdown">
                    <div class="user-avatar me-2">
                        <?php if ($user_foto && $user_foto !== 'assets/icons/default-avatar.png'): ?>
                            <img src="<?php echo htmlspecialchars($user_foto); ?>" 
                                 alt="User Avatar" 
                                 class="rounded-circle user-profile-img"
                                 width="32" 
                                 height="32"
                                 onerror="this.onerror=null; this.src='assets/icons/default-avatar.png'; this.classList.add('avatar-fallback'); this.classList.remove('user-profile-img');">
                        <?php else: ?>
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center user-profile-img">
                                <span class="avatar-initials">
                                    <?php 
                                    if ($user_nama !== 'Guest') {
                                        $names = explode(' ', $user_nama);
                                        $initials = '';
                                        foreach ($names as $name) {
                                            if (strlen($initials) < 2) {
                                                $initials .= strtoupper(substr($name, 0, 1));
                                            }
                                        }
                                        echo htmlspecialchars($initials);
                                    } else {
                                        echo 'G';
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <span class="d-none d-md-inline user-name"><?php echo htmlspecialchars($user_nama); ?></span>
                    <i class="fas fa-chevron-down ms-2 dropdown-arrow"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu">
                    <!-- User Info Header -->
                    <li class="dropdown-header user-info-header">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                                <?php if ($user_foto && $user_foto !== 'assets/icons/default-avatar.png'): ?>
                                    <img src="<?php echo htmlspecialchars($user_foto); ?>" 
                                         alt="User Avatar" 
                                         class="rounded-circle user-profile-img-large"
                                         width="48" 
                                         height="48"
                                         onerror="this.onerror=null; this.src='assets/icons/default-avatar.png'; this.classList.add('avatar-fallback'); this.classList.remove('user-profile-img-large');">
                                <?php else: ?>
                                    <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center user-profile-img-large">
                                        <span class="avatar-initials-large">
                                            <?php 
                                            if ($user_nama !== 'Guest') {
                                                $names = explode(' ', $user_nama);
                                                $initials = '';
                                                foreach ($names as $name) {
                                                    if (strlen($initials) < 2) {
                                                        $initials .= strtoupper(substr($name, 0, 1));
                                                    }
                                                }
                                                echo htmlspecialchars($initials);
                                            } else {
                                                echo 'GU';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="user-info">
                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($user_nama); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($user_email); ?></small>
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    
                    <!-- Profile Section -->
                    <li>
                        <a class="dropdown-item" href="lihat-profil.php">
                            <i class="fas fa-eye me-2"></i>Lihat Profil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="profil.php">
                            <i class="fas fa-edit me-2"></i>Edit Profil
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    
                    <!-- Financial Sections -->
                    <li>
                        <a class="dropdown-item" href="analisis.php">
                            <i class="fas fa-chart-line me-2"></i>Analisis Finansial
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="laporan.php">
                            <i class="fas fa-file-alt me-2"></i>Laporan Keuangan
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    
                    <!-- Account Actions -->
                    <li>
                        <a class="dropdown-item text-info" href="#" data-bs-toggle="modal" data-bs-target="#helpModal">
                            <i class="fas fa-question-circle me-2"></i>Bantuan & Support
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    
                    <!-- Logout -->
                    <li>
                        <a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bantuan & Dukungan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 help-card">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-3x mb-3"></i>
                                <h6>Panduan Pengguna</h6>
                                <p class="small text-muted">Pelajari cara menggunakan Finansialku</p>
                                <a href="docs/panduan-penggunaan.pdf" class="btn btn-outline-primary btn-sm">Buka Panduan</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 help-card">
                            <div class="card-body text-center">
                                <i class="fas fa-question-circle fa-3x mb-3"></i>
                                <h6>FAQ</h6>
                                <p class="small text-muted">Pertanyaan yang sering diajukan</p>
                                <a href="faq.php" class="btn btn-outline-info btn-sm">Lihat FAQ</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 help-card">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope fa-3x mb-3"></i>
                                <h6>Hubungi Kami</h6>
                                <p class="small text-muted">Butuh bantuan lebih lanjut?</p>
                                <a href="kontak.php" class="btn btn-outline-success btn-sm">Kontak Support</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 help-card">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <h6>Tentang Kami</h6>
                                <p class="small text-muted">Kenali tim pengembang</p>
                                <a href="about.php" class="btn btn-outline-warning btn-sm">Tentang Kami</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== VARIABLES THEME ===== */
:root {
    /* Light Theme Colors - Cerah & Vibrant */
    --light-primary: #3b82f6;
    --light-secondary: #8b5cf6;
    --light-accent: #10b981;
    --light-warning: #f59e0b;
    --light-danger: #ef4444;
    --light-success: #10b981;
    --light-info: #06b6d4;
    
    --light-bg: #ffffff;
    --light-bg-secondary: #f8fafc;
    --light-bg-card: #ffffff;
    --light-text: #1e293b;
    --light-text-secondary: #475569;
    --light-border: #e2e8f0;
    --light-shadow: rgba(0, 0, 0, 0.1);
    
    /* Gradient untuk light mode */
    --light-gradient-primary: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    --light-gradient-secondary: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
    --light-gradient-warning: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
    
    /* Dark Theme Colors - Gelap & Elegan */
    --dark-primary: #6366f1;
    --dark-secondary: #8b5cf6;
    --dark-accent: #10b981;
    --dark-warning: #f59e0b;
    --dark-danger: #ef4444;
    --dark-success: #10b981;
    --dark-info: #06b6d4;
    
    --dark-bg: #0f172a;
    --dark-bg-secondary: #1e293b;
    --dark-bg-card: #1e293b;
    --dark-text: #f8fafc;
    --dark-text-secondary: #cbd5e1;
    --dark-border: #334155;
    --dark-shadow: rgba(0, 0, 0, 0.3);
    
    /* Gradient untuk dark mode */
    --dark-gradient-primary: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    --dark-gradient-secondary: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
    --dark-gradient-accent: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
}

/* ===== NOTIFICATION STYLES ===== */
.notification-wrapper {
    position: relative;
}

.notification-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    position: relative;
    border: 1px solid var(--dark-border);
    background: transparent;
    color: var(--dark-text);
    transition: all 0.3s ease;
}

.notification-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-color: var(--dark-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--dark-shadow);
}

.notification-btn:active {
    transform: translateY(0);
}

.notification-btn .fa-bell {
    font-size: 1.2rem;
    transition: transform 0.3s ease;
}

.notification-btn:hover .fa-bell {
    transform: rotate(-15deg);
}

.notification-btn.notification-active .fa-bell {
    color: var(--dark-primary);
    animation: bellRing 0.5s ease;
}

@keyframes bellRing {
    0%, 100% { transform: rotate(0); }
    25% { transform: rotate(-25deg); }
    75% { transform: rotate(25deg); }
}

.notification-badge {
    font-size: 0.6rem;
    padding: 0.25rem 0.4rem;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--dark-bg);
    animation: badgePulse 2s infinite;
}

@keyframes badgePulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 380px;
    max-width: 90vw;
    background: var(--dark-bg-card);
    border: 1px solid var(--dark-border);
    border-radius: 12px;
    box-shadow: 0 10px 30px var(--dark-shadow);
    z-index: 1050;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 500px;
}

.notification-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(5px);
}

.notification-header {
    padding: 1rem 1.25rem;
    background: var(--dark-gradient-primary);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}

.notification-header h6 {
    color: white;
    margin: 0;
    font-size: 1rem;
}

.notification-actions {
    display: flex;
    gap: 0.5rem;
}

.notification-actions .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
    background: transparent;
}

.notification-actions .btn:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: white;
}

.notification-list {
    flex: 1;
    overflow-y: auto;
    max-height: 350px;
    padding: 0.5rem;
}

.notification-item {
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    border-radius: 8px;
    background: var(--dark-bg-secondary);
    border: 1px solid var(--dark-border);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.notification-item:hover {
    background: var(--hover-bg);
    transform: translateX(-3px);
    box-shadow: 0 4px 15px var(--dark-shadow);
}

.notification-item.unread {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.05));
    border-left: 3px solid var(--dark-primary);
}

.notification-item.unread:hover {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.1));
}

.notification-item-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.notification-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.notification-icon.success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.notification-icon.info {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.notification-icon.warning {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.notification-icon.danger {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.notification-details {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

.notification-message {
    color: var(--dark-text-secondary);
    font-size: 0.8rem;
    line-height: 1.4;
    margin-bottom: 0.25rem;
}

.notification-time {
    color: var(--dark-text-secondary);
    font-size: 0.7rem;
    opacity: 0.8;
}

.notification-actions-item {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.notification-actions-item .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.7rem;
}

.notification-empty-state {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--dark-text-secondary);
}

.notification-footer {
    padding: 0.75rem 1.25rem;
    border-top: 1px solid var(--dark-border);
    background: var(--dark-bg-secondary);
    flex-shrink: 0;
    text-align: center;
}

.notification-footer a {
    color: var(--dark-text);
    font-size: 0.875rem;
    transition: color 0.3s ease;
}

.notification-footer a:hover {
    color: var(--dark-primary);
    text-decoration: underline;
}

/* Light theme overrides for notifications */
[data-bs-theme="light"] .notification-btn {
    border-color: var(--light-border);
    color: var(--light-text);
    background: transparent;
}

[data-bs-theme="light"] .notification-btn:hover {
    background-color: rgba(0, 0, 0, 0.05);
    border-color: var(--light-primary);
    box-shadow: 0 4px 12px var(--light-shadow);
}

[data-bs-theme="light"] .notification-badge {
    border-color: var(--light-bg);
}

[data-bs-theme="light"] .notification-dropdown {
    background: var(--light-bg-card);
    border-color: var(--light-border);
    box-shadow: 0 10px 30px var(--light-shadow);
}

[data-bs-theme="light"] .notification-item {
    background: var(--light-bg-secondary);
    border-color: var(--light-border);
}

[data-bs-theme="light"] .notification-item:hover {
    background: var(--hover-bg);
}

[data-bs-theme="light"] .notification-item.unread {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.05));
    border-left-color: var(--light-primary);
}

[data-bs-theme="light"] .notification-item.unread:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(139, 92, 246, 0.1));
}

[data-bs-theme="light"] .notification-title {
    color: var(--light-text);
}

[data-bs-theme="light"] .notification-message,
[data-bs-theme="light"] .notification-time {
    color: var(--light-text-secondary);
}

[data-bs-theme="light"] .notification-footer {
    border-top-color: var(--light-border);
    background: var(--light-bg-secondary);
}

[data-bs-theme="light"] .notification-footer a {
    color: var(--light-text);
}

[data-bs-theme="light"] .notification-footer a:hover {
    color: var(--light-primary);
}

/* Scrollbar styling for notification list */
.notification-list::-webkit-scrollbar {
    width: 6px;
}

.notification-list::-webkit-scrollbar-track {
    background: var(--dark-bg-secondary);
    border-radius: 10px;
}

.notification-list::-webkit-scrollbar-thumb {
    background: var(--dark-border);
    border-radius: 10px;
}

.notification-list::-webkit-scrollbar-thumb:hover {
    background: var(--dark-primary);
}

[data-bs-theme="light"] .notification-list::-webkit-scrollbar-track {
    background: var(--light-bg-secondary);
}

[data-bs-theme="light"] .notification-list::-webkit-scrollbar-thumb {
    background: var(--light-border);
}

[data-bs-theme="light"] .notification-list::-webkit-scrollbar-thumb:hover {
    background: var(--light-primary);
}

/* ===== THEME TOGGLE SWITCH ===== */
.theme-toggle-switch {
    position: relative;
    display: inline-block;
}

.theme-switch-checkbox {
    display: none;
}

.theme-switch-label {
    display: block;
    width: 60px;
    height: 30px;
    background: var(--dark-gradient-primary);
    border-radius: 15px;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.3);
}

.theme-switch-label:hover {
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(99, 102, 241, 0.5);
}

.theme-switch-inner {
    position: absolute;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    border-radius: 15px;
    transition: all 0.3s ease;
    background: linear-gradient(90deg, 
        rgba(255, 255, 255, 0.1) 0%, 
        rgba(255, 255, 255, 0.2) 50%, 
        rgba(255, 255, 255, 0.1) 100%);
}

.theme-switch-switch {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 26px;
    height: 26px;
    background: white;
    border-radius: 50%;
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    z-index: 2;
}

.theme-switch-switch i {
    font-size: 12px;
    transition: all 0.3s ease;
    position: absolute;
}

.light-icon {
    color: #f59e0b;
    opacity: 0;
    transform: scale(0);
}

.dark-icon {
    color: #000000;
    opacity: 1;
    transform: scale(1);
}

.theme-switch-checkbox:checked + .theme-switch-label {
    background: var(--light-gradient-primary);
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
}

.theme-switch-checkbox:checked + .theme-switch-label:hover {
    box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
}

.theme-switch-checkbox:checked + .theme-switch-label .theme-switch-switch {
    left: calc(100% - 28px);
    background: white;
}

.theme-switch-checkbox:checked + .theme-switch-label .light-icon {
    opacity: 1;
    transform: scale(1);
}

.theme-switch-checkbox:checked + .theme-switch-label .dark-icon {
    opacity: 0;
    transform: scale(0);
}

/* Tooltip untuk theme toggle - DIBAWAH */
.theme-tooltip-bottom {
    position: absolute;
    top: calc(100% + 10px); /* Posisi di bawah tombol */
    left: 50%;
    transform: translateX(-50%);
    background: var(--dark-bg-card);
    color: var(--dark-text);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1000;
    border: 1px solid var(--dark-border);
    box-shadow: 0 2px 8px var(--dark-shadow);
    pointer-events: none;
}

.theme-toggle-switch:hover .theme-tooltip-bottom {
    opacity: 1;
    visibility: visible;
    top: calc(100% + 5px);
}

[data-bs-theme="light"] .theme-tooltip-bottom {
    background: var(--light-bg-card);
    color: var(--light-text);
    border: 1px solid var(--light-border);
    box-shadow: 0 2px 8px var(--light-shadow);
}

/* Animation untuk toggle */
@keyframes switchBounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.theme-switch-checkbox:checked + .theme-switch-label .theme-switch-switch {
    animation: switchBounce 0.3s ease;
}

/* ===== NAVBAR STYLES ===== */
/* Logo Animation */
.blinking-logo {
    animation: logoBlink 2s infinite alternate;
    transition: transform 0.3s ease;
}

@keyframes logoBlink {
    0% { 
        opacity: 1; 
        transform: scale(1); 
        filter: drop-shadow(0 0 5px currentColor);
    }
    100% { 
        opacity: 0.8; 
        transform: scale(1.05); 
        filter: drop-shadow(0 0 10px currentColor);
    }
}

/* Welcome and Motivation Messages Animation */
.welcome-animate {
    animation: slideInDown 0.5s ease-out;
}

.motivation-animate {
    animation: fadeIn 0.8s ease-out 0.3s both;
}

@keyframes slideInDown {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Navbar brand animation */
.navbar-brand {
    transition: transform 0.3s ease;
}

.navbar-brand:hover {
    transform: scale(1.05);
}

/* Brand text dengan gradient dinamis */
.brand-text {
    background: var(--dark-gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: bold;
}

[data-bs-theme="light"] .brand-text {
    background: var(--light-gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ===== PROFILE PHOTO FIXES ===== */
/* Untuk foto profil kecil di navbar */
.user-profile-img {
    width: 32px;
    height: 32px;
    object-fit: cover;
    object-position: center;
    border: 2px solid var(--dark-border);
    transition: all 0.3s ease;
    border-radius: 50%;
    display: block;
    flex-shrink: 0;
}

/* Untuk foto profil besar di dropdown */
.user-profile-img-large {
    width: 48px;
    height: 48px;
    object-fit: cover;
    object-position: center;
    border: 3px solid var(--dark-border);
    transition: all 0.3s ease;
    border-radius: 50%;
    display: block;
    flex-shrink: 0;
}

/* Hover effects */
.user-profile-img:hover,
.user-profile-img-large:hover {
    transform: scale(1.05);
    border-color: var(--dark-primary);
}

/* Avatar placeholder dengan inisial */
.avatar-placeholder {
    background: var(--dark-gradient-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    border: 2px solid var(--dark-border);
    flex-shrink: 0;
}

/* Untuk avatar kecil */
.user-profile-img.avatar-placeholder {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

/* Untuk avatar besar di dropdown */
.user-profile-img-large.avatar-placeholder {
    width: 48px;
    height: 48px;
    font-size: 18px;
}

/* Inisial teks */
.avatar-initials,
.avatar-initials-large {
    color: white;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
}

/* Fallback untuk gambar yang gagal load */
.avatar-fallback {
    background: var(--dark-gradient-primary);
    display: flex !important;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    border: 2px solid var(--dark-border);
}

/* User Menu Enhancements */
.user-menu-btn {
    transition: all 0.3s ease;
    border: 1px solid var(--dark-border);
    border-radius: 25px;
    padding: 5px 15px;
    background: transparent;
    color: var(--dark-text);
}

.user-menu-btn:hover {
    background-color: rgba(255,255,255,0.1);
    border-color: var(--dark-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--dark-shadow);
}

/* Pastikan warna nama user sesuai tema */
.user-name {
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--dark-text);
    font-weight: 500;
}

/* Light mode: warna nama user hitam */
[data-bs-theme="light"] .user-name {
    color: #000000 !important;
    font-weight: 600;
}

.user-dropdown-menu {
    min-width: 280px;
    border: 1px solid var(--dark-border);
    box-shadow: 0 0.5rem 1rem var(--dark-shadow);
    backdrop-filter: blur(10px);
    background-color: var(--dark-bg-card);
}

.user-info-header {
    padding: 1rem;
    background: var(--dark-gradient-primary);
    color: white;
    border-radius: 0.375rem 0.375rem 0 0;
}

.user-info-header .user-avatar img,
.user-info-header .user-avatar .avatar-placeholder {
    border: 2px solid rgba(255,255,255,0.3);
}

.user-info-header h6 {
    font-size: 1rem;
    color: white;
}

.user-info-header small {
    opacity: 0.9;
    color: rgba(255,255,255,0.8);
}

.dropdown-arrow {
    transition: transform 0.3s ease;
    color: var(--dark-text);
}

/* ===== DROPDOWN ITEM HOVER EFFECTS ===== */
.dropdown-item {
    transition: all 0.3s ease;
    border-radius: 0.25rem;
    margin: 0.125rem 0.5rem;
    padding: 0.5rem 0.75rem;
    position: relative;
    overflow: hidden;
    color: var(--dark-text) !important;
    background-color: transparent !important;
}

.dropdown-item::before {
    content: '';
    position: absolute;
    left: -100%;
    top: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(var(--bs-primary-rgb), 0.1), transparent);
    transition: left 0.5s ease;
}

.dropdown-item:hover::before {
    left: 100%;
}

.dropdown-item:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    padding-left: 1rem;
    transform: translateX(5px);
    color: var(--dark-primary) !important;
}

.dropdown-item i {
    transition: transform 0.3s ease;
    color: inherit;
}

.dropdown-item:hover i {
    transform: translateX(5px) scale(1.1);
}

/* Navbar Toggler Animation */
.navbar-toggler {
    transition: all 0.3s ease;
    border-color: var(--dark-border);
}

.navbar-toggler:hover {
    transform: scale(1.1);
    box-shadow: 0 0 10px var(--dark-primary);
    border-color: var(--dark-primary);
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.25);
}

/* ===== LIGHT THEME OVERRIDES ===== */
[data-bs-theme="light"] .dashboard-navbar {
    background-color: var(--light-bg) !important;
    box-shadow: 0 2px 10px var(--light-shadow);
}

[data-bs-theme="light"] .user-menu-btn {
    border-color: var(--light-border);
    color: var(--light-text);
    background: transparent;
}

[data-bs-theme="light"] .user-menu-btn:hover {
    background-color: rgba(0, 0, 0, 0.05);
    border-color: var(--light-primary);
    box-shadow: 0 4px 12px var(--light-shadow);
}

/* Pastikan nama user tetap hitam saat hover di light mode */
[data-bs-theme="light"] .user-menu-btn:hover .user-name {
    color: #000000 !important;
}

[data-bs-theme="light"] .user-dropdown-menu {
    background-color: var(--light-bg-card);
    border-color: var(--light-border);
    box-shadow: 0 0.5rem 1rem var(--light-shadow);
}

[data-bs-theme="light"] .user-info-header {
    background: var(--light-gradient-primary);
}

/* Warna teks di dropdown header light mode */
[data-bs-theme="light"] .user-info-header h6 {
    color: #ffffff !important;
}

[data-bs-theme="light"] .user-info-header small {
    color: rgba(255, 255, 255, 0.9) !important;
}

[data-bs-theme="light"] .user-profile-img,
[data-bs-theme="light"] .user-profile-img-large {
    border-color: var(--light-border);
}

[data-bs-theme="light"] .user-profile-img:hover,
[data-bs-theme="light"] .user-profile-img-large:hover {
    border-color: var(--light-primary);
}

[data-bs-theme="light"] .avatar-placeholder {
    background: var(--light-gradient-primary);
    border-color: var(--light-border);
}

[data-bs-theme="light"] .dropdown-arrow {
    color: #000000;
}

/* Warna teks dropdown items di light mode */
[data-bs-theme="light"] .dropdown-item {
    color: #1e293b !important;
}

[data-bs-theme="light"] .dropdown-item:hover {
    color: var(--light-primary) !important;
}

[data-bs-theme="light"] .navbar-toggler {
    border-color: var(--light-border);
}

[data-bs-theme="light"] .navbar-toggler:hover {
    border-color: var(--light-primary);
    box-shadow: 0 0 10px var(--light-primary);
}

/* Text colors untuk light/dark theme */
.welcome-text {
    color: var(--dark-text) !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.motivation-text {
    color: var(--dark-text-secondary) !important;
}

[data-bs-theme="light"] .welcome-text {
    color: var(--light-text) !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

[data-bs-theme="light"] .motivation-text {
    color: var(--light-text-secondary) !important;
}

/* Pastikan semua teks dalam navbar terlihat jelas */
.dashboard-navbar * {
    color: inherit;
}

/* ===== HELP MODAL THEME ===== */
.help-card {
    background: var(--dark-bg-card);
    border: 1px solid var(--dark-border);
    transition: all 0.3s ease;
}

.help-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px var(--dark-shadow);
    border-color: var(--dark-primary);
}

.help-card i {
    background: var(--dark-gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.help-card h6 {
    color: var(--dark-text);
    font-weight: 600;
}

.help-card .text-muted {
    color: var(--dark-text-secondary) !important;
}

.help-card .btn {
    border-color: var(--dark-border);
    color: var(--dark-text);
}

.help-card .btn:hover {
    border-color: var(--dark-primary);
    background-color: rgba(99, 102, 241, 0.1);
}

[data-bs-theme="light"] .help-card {
    background: var(--light-bg-card);
    border: 1px solid var(--light-border);
}

[data-bs-theme="light"] .help-card:hover {
    box-shadow: 0 10px 20px var(--light-shadow);
    border-color: var(--light-primary);
}

[data-bs-theme="light"] .help-card i {
    background: var(--light-gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

[data-bs-theme="light"] .help-card h6 {
    color: var(--light-text);
}

[data-bs-theme="light"] .help-card .text-muted {
    color: var(--light-text-secondary) !important;
}

[data-bs-theme="light"] .help-card .btn {
    border-color: var(--light-border);
    color: var(--light-text);
}

[data-bs-theme="light"] .help-card .btn:hover {
    border-color: var(--light-primary);
    background-color: rgba(59, 130, 246, 0.1);
}

/* ===== ANIMATION FOR PROFILE PHOTO CHANGES ===== */
@keyframes photoChange {
    0% {
        transform: scale(0.8);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.photo-updated {
    animation: photoChange 0.5s ease-out;
}

/* Loading state for profile photos */
.user-avatar.loading {
    background: linear-gradient(90deg, var(--dark-border) 25%, var(--dark-bg-secondary) 50%, var(--dark-border) 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

[data-bs-theme="light"] .user-avatar.loading {
    background: linear-gradient(90deg, var(--light-border) 25%, var(--light-bg-secondary) 50%, var(--light-border) 75%);
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* ===== TEXT GLOW ANIMATION ===== */
@keyframes textGlow {
    0%, 100% { 
        text-shadow: 0 0 5px currentColor;
    }
    50% { 
        text-shadow: 0 0 20px currentColor, 0 0 30px currentColor;
    }
}

.text-glow {
    animation: textGlow 2s infinite;
}

/* Custom Scrollbar */
.user-dropdown-menu::-webkit-scrollbar {
    width: 6px;
}

.user-dropdown-menu::-webkit-scrollbar-track {
    background: var(--dark-bg-secondary);
    border-radius: 10px;
}

.user-dropdown-menu::-webkit-scrollbar-thumb {
    background: var(--dark-border);
    border-radius: 10px;
}

.user-dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: var(--dark-primary);
}

[data-bs-theme="light"] .user-dropdown-menu::-webkit-scrollbar-track {
    background: var(--light-bg-secondary);
}

[data-bs-theme="light"] .user-dropdown-menu::-webkit-scrollbar-thumb {
    background: var(--light-border);
}

[data-bs-theme="light"] .user-dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: var(--light-primary);
}

/* Navbar Container */
.dashboard-navbar {
    box-shadow: 0 2px 10px var(--dark-shadow);
    z-index: 1030;
    transition: all 0.3s ease;
    background-color: var(--dark-bg) !important;
}

.dashboard-navbar.scrolled {
    background-color: rgba(15, 23, 42, 0.95) !important;
    backdrop-filter: blur(10px);
}

[data-bs-theme="light"] .dashboard-navbar.scrolled {
    background-color: rgba(255, 255, 255, 0.95) !important;
}

/* Pastikan ikon bulan selalu hitam */
.theme-switch-switch .dark-icon {
    color: #000000 !important;
}

/* Ikon matahari tetap kuning */
.theme-switch-switch .light-icon {
    color: #f59e0b !important;
}

/* Dark mode teks nama user putih */
[data-bs-theme="dark"] .user-name {
    color: #ffffff !important;
}

/* Teks info user di navbar */
[data-bs-theme="light"] .user-info h6 {
    color: #000000 !important;
}

[data-bs-theme="light"] .user-info small {
    color: #475569 !important;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    #welcomeMotivationContainer {
        min-width: 100% !important;
        max-width: 100% !important;
        margin: 10px 0;
    }
    
    .blinking-logo {
        height: 30px;
    }
    
    #welcomeMessage, #motivationMessage {
        font-size: 0.9rem !important;
    }
}

@media (max-width: 768px) {
    .user-name {
        display: none !important;
    }
    
    .dropdown-arrow {
        margin-left: 0 !important;
    }
    
    .user-profile-img {
        width: 28px;
        height: 28px;
    }
    
    .user-profile-img.avatar-placeholder {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
    
    .user-profile-img-large {
        width: 40px;
        height: 40px;
    }
    
    .user-profile-img-large.avatar-placeholder {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .theme-toggle-switch {
        margin-right: 0.5rem;
    }
    
    .theme-tooltip-bottom {
        display: none;
    }
    
    /* Notification responsive */
    .notification-dropdown {
        width: 320px;
        right: -50px;
    }
    
    .notification-header {
        padding: 0.75rem 1rem;
    }
    
    .notification-actions {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .notification-actions .btn {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
}

@media (max-width: 480px) {
    .notification-dropdown {
        width: 280px;
        right: -80px;
        max-width: calc(100vw - 40px);
    }
    
    .notification-btn {
        width: 38px;
        height: 38px;
    }
    
    .notification-badge {
        font-size: 0.55rem;
        min-width: 16px;
        height: 16px;
    }
}

/* Modal Enhancements */
#helpModal .modal-content {
    background-color: var(--dark-bg-card);
    border: 1px solid var(--dark-border);
}

#helpModal .modal-header {
    border-bottom-color: var(--dark-border);
}

#helpModal .modal-title {
    color: var(--dark-text);
    font-weight: 600;
}

#helpModal .modal-body {
    color: var(--dark-text-secondary);
}

[data-bs-theme="light"] #helpModal .modal-content {
    background-color: var(--light-bg-card);
    border: 1px solid var(--light-border);
}

[data-bs-theme="light"] #helpModal .modal-header {
    border-bottom-color: var(--light-border);
}

[data-bs-theme="light"] #helpModal .modal-title {
    color: var(--light-text);
}

[data-bs-theme="light"] #helpModal .modal-body {
    color: var(--light-text-secondary);
}

/* Smooth theme transition */
* {
    transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
}

/* Ensure text visibility */
.navbar-nav .nav-link,
.navbar-nav .dropdown-item,
.navbar-nav .text-muted {
    transition: color 0.3s ease;
}
</style>

<script>
// ===== NOTIFICATION MANAGER =====
class NotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.notificationStorageKey = 'financial_notifications';
        this.maxNotifications = 50;
        this.init();
    }

    init() {
        this.loadNotifications();
        this.setupEventListeners();
        this.renderNotifications();
        
        // Auto-generate initial notifications
        this.generateInitialNotifications();
        
        console.log('Notification Manager initialized');
    }

    setupEventListeners() {
        // Toggle notification dropdown
        const notificationBtn = document.getElementById('notificationBell');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        if (notificationBtn && notificationDropdown) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleNotificationDropdown();
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    this.hideNotificationDropdown();
                }
            });
        }

        // Mark all as read
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        // Clear all notifications
        const clearAllBtn = document.getElementById('clearAllBtn');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', () => {
                this.clearAllNotifications();
            });
        }

        // Listen for theme change events
        document.addEventListener('themeChanged', (e) => {
            const theme = e.detail.theme;
            const themeName = theme === 'dark' ? 'Mode Gelap' : 'Mode Terang';
            
            this.addNotification({
                title: 'Tema Diubah',
                message: `Tema aplikasi telah diubah ke ${themeName}.`,
                icon: theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun',
                iconColor: theme === 'dark' ? 'info' : 'warning',
                type: 'theme_changed',
                autoDismiss: true
            });
        });
        
        // Listen for new notification events
        document.addEventListener('newNotification', (e) => {
            this.addNotification(e.detail);
        });
    }

    toggleNotificationDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        const bell = document.getElementById('notificationBell');
        
        if (dropdown.classList.contains('show')) {
            this.hideNotificationDropdown();
        } else {
            this.showNotificationDropdown();
            bell.classList.add('notification-active');
            
            // Mark all as read when opening
            this.markAllAsRead();
        }
    }

    showNotificationDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.add('show');
    }

    hideNotificationDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        const bell = document.getElementById('notificationBell');
        
        dropdown.classList.remove('show');
        bell.classList.remove('notification-active');
    }

    addNotification(notification) {
        const newNotification = {
            id: Date.now(),
            title: notification.title,
            message: notification.message,
            icon: notification.icon || 'fas fa-info-circle',
            iconColor: notification.iconColor || 'info',
            type: notification.type || 'general',
            timestamp: new Date().toISOString(),
            read: false,
            autoDismiss: notification.autoDismiss || false
        };

        // Add to beginning of array
        this.notifications.unshift(newNotification);
        
        // Limit notifications
        if (this.notifications.length > this.maxNotifications) {
            this.notifications.pop();
        }

        // Save and update UI
        this.saveNotifications();
        this.updateUnreadCount();
        this.renderNotifications();
        this.showBellAnimation();

        // Auto-dismiss if enabled
        if (newNotification.autoDismiss) {
            setTimeout(() => {
                this.dismissNotification(newNotification.id);
            }, 5000);
        }
    }

    markAsRead(notificationId) {
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification && !notification.read) {
            notification.read = true;
            this.saveNotifications();
            this.updateUnreadCount();
            this.renderNotifications();
        }
    }

    markAllAsRead() {
        let hasUnread = false;
        
        this.notifications.forEach(notification => {
            if (!notification.read) {
                notification.read = true;
                hasUnread = true;
            }
        });

        if (hasUnread) {
            this.saveNotifications();
            this.updateUnreadCount();
            this.renderNotifications();
            this.showToast('Semua notifikasi ditandai sebagai sudah dibaca');
        }
    }

    dismissNotification(notificationId) {
        const index = this.notifications.findIndex(n => n.id === notificationId);
        if (index !== -1) {
            this.notifications.splice(index, 1);
            this.saveNotifications();
            this.updateUnreadCount();
            this.renderNotifications();
        }
    }

    clearAllNotifications() {
        if (this.notifications.length > 0) {
            this.notifications = [];
            this.saveNotifications();
            this.updateUnreadCount();
            this.renderNotifications();
            this.showToast('Semua notifikasi telah dihapus');
        }
    }

    loadNotifications() {
        try {
            const saved = localStorage.getItem(this.notificationStorageKey);
            if (saved) {
                this.notifications = JSON.parse(saved);
                this.updateUnreadCount();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.notifications = [];
        }
    }

    saveNotifications() {
        try {
            localStorage.setItem(this.notificationStorageKey, JSON.stringify(this.notifications));
        } catch (error) {
            console.error('Error saving notifications:', error);
        }
    }

    updateUnreadCount() {
        this.unreadCount = this.notifications.filter(n => !n.read).length;
        this.updateBadge();
    }

    updateBadge() {
        const badge = document.getElementById('notificationCount');
        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'flex';
                
                // Add pulse animation for new notifications
                if (this.unreadCount === 1) {
                    badge.classList.add('animate__animated', 'animate__pulse');
                    setTimeout(() => {
                        badge.classList.remove('animate__animated', 'animate__pulse');
                    }, 1000);
                }
            } else {
                badge.style.display = 'none';
            }
        }
    }

    renderNotifications() {
        const container = document.getElementById('notificationList');
        if (!container) return;

        if (this.notifications.length === 0) {
            container.innerHTML = `
                <div class="notification-empty-state">
                    <i class="fas fa-bell-slash fa-2x mb-2 text-muted"></i>
                    <p class="mb-0 text-muted">Tidak ada notifikasi</p>
                </div>
            `;
            return;
        }

        let html = '';
        
        this.notifications.forEach(notification => {
            const timeAgo = this.getTimeAgo(notification.timestamp);
            const isUnread = !notification.read ? 'unread' : '';
            
            html += `
                <div class="notification-item ${isUnread}" 
                     data-id="${notification.id}"
                     onclick="notificationManager.markAsRead(${notification.id})">
                    <div class="notification-item-content">
                        <div class="notification-icon ${notification.iconColor}">
                            <i class="${notification.icon}"></i>
                        </div>
                        <div class="notification-details">
                            <div class="notification-title">${notification.title}</div>
                            <div class="notification-message">${notification.message}</div>
                            <div class="notification-time">${timeAgo}</div>
                            
                            ${notification.type === 'theme_changed' ? `
                                <div class="notification-actions-item">
                                    <button class="btn btn-sm btn-primary" 
                                            onclick="event.stopPropagation(); notificationManager.handleAction('${notification.type}')">
                                        <i class="fas fa-check me-1"></i>Mengerti
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" 
                                            onclick="event.stopPropagation(); notificationManager.dismissNotification(${notification.id})">
                                        <i class="fas fa-times me-1"></i>Tutup
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    getTimeAgo(timestamp) {
        const now = new Date();
        const past = new Date(timestamp);
        const diff = Math.floor((now - past) / 1000); // difference in seconds

        if (diff < 60) {
            return 'Baru saja';
        } else if (diff < 3600) {
            const minutes = Math.floor(diff / 60);
            return `${minutes} menit yang lalu`;
        } else if (diff < 86400) {
            const hours = Math.floor(diff / 3600);
            return `${hours} jam yang lalu`;
        } else if (diff < 604800) {
            const days = Math.floor(diff / 86400);
            return `${days} hari yang lalu`;
        } else {
            return past.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
    }

    showBellAnimation() {
        const bell = document.getElementById('notificationBell');
        if (bell) {
            bell.classList.add('notification-active');
            
            // Remove animation class after animation completes
            setTimeout(() => {
                bell.classList.remove('notification-active');
            }, 500);
        }
    }

    generateInitialNotifications() {
        // Check if initial notifications already exist
        if (this.notifications.length === 0) {
            // Add welcome notification
            this.addNotification({
                title: 'Selamat Datang!',
                message: 'Selamat menggunakan aplikasi Finansialku. Mulai kelola keuangan Anda dengan lebih baik.',
                icon: 'fas fa-rocket',
                iconColor: 'info',
                type: 'welcome'
            });
            
            // Add tip notification
            this.addNotification({
                title: 'Tips Finansial',
                message: 'Ingat untuk selalu mencatat pengeluaran harian untuk pengelolaan keuangan yang lebih baik.',
                icon: 'fas fa-lightbulb',
                iconColor: 'warning',
                type: 'tip'
            });
        }
    }

    handleAction(actionType) {
        switch (actionType) {
            case 'theme_changed':
                this.showToast('Tema berhasil diubah!');
                break;
            case 'welcome':
                this.showToast('Selamat datang di Finansialku!');
                break;
        }
    }

    showToast(message, type = 'success') {
        // Use existing toast functionality from NavbarManager
        if (window.navbarManager && typeof window.navbarManager.showToast === 'function') {
            window.navbarManager.showToast(message, type);
        } else {
            // Fallback if NavbarManager not available
            console.log(`Toast [${type}]: ${message}`);
        }
    }
}

// ===== NAVBAR JAVASCRIPT =====
class NavbarManager {
    constructor() {
        this.messages = {
            welcome: [
                "Selamat datang kembali, <?php echo htmlspecialchars($user_nama); ?>!",
                "Semoga harimu menyenangkan!",
                "Keuanganmu dalam kendali!",
                "Tetap semangat mengatur keuangan!",
                "Waktunya merencanakan masa depan!",
                "Finansial sehat, hidup tenang!",
                "Selangkah lebih dekat ke tujuan finansial!",
                "Tetaplah konsisten!",
                "Keuanganmu semakin baik hari ini!",
                "Impian finansialmu semakin dekat!"
            ],
            motivation: [
                "Tabungan kecil hari ini, kebahagiaan besar esok hari.",
                "Setiap transaksi adalah langkah menuju kebebasan finansial.",
                "Konsistensi adalah kunci kesuksesan finansial.",
                "Rencanakan dengan bijak, belanjalah dengan cerdas.",
                "Investasi terbaik adalah investasi dalam diri sendiri.",
                "Jangan hanya menabung, alokasikan dengan tujuan.",
                "Setiap rupiah yang dikelola dengan baik akan membuahkan hasil.",
                "Kebebasan finansial dimulai dari keputusan hari ini.",
                "Pantau, analisis, tingkatkan - ulangi.",
                "Keuangan yang sehat adalah fondasi hidup yang sejahtera."
            ]
        };
        
        this.currentMessageIndex = 0;
        this.theme = localStorage.getItem('theme') || 'dark';
        this.messageInterval = null;
        this.initialize();
    }

    initialize() {
        // Set initial theme
        this.setTheme(this.theme);
        
        // Set initial switch state
        this.updateSwitchState();
        
        // Initialize event listeners
        this.setupEventListeners();
        
        // Setup profile photo handlers
        this.setupProfilePhotoHandlers();
        
        // Start message rotation
        this.startMessageRotation();
        
        // Setup welcome/motivation messages
        this.setupWelcomeMessages();
        
        // Setup dropdown animations
        this.setupDropdownAnimations();
        
        // Add scroll effect to navbar
        this.setupScrollEffect();
        
        console.log('Navbar Manager initialized');
    }

    setupEventListeners() {
        // Theme switch
        const themeSwitch = document.getElementById('themeSwitch');
        if (themeSwitch) {
            themeSwitch.addEventListener('change', () => this.toggleTheme());
            
            // Update tooltip text on hover
            const themeToggle = document.querySelector('.theme-toggle-switch');
            const tooltip = document.querySelector('.theme-tooltip-bottom');
            
            if (themeToggle && tooltip) {
                themeToggle.addEventListener('mouseenter', () => {
                    const isDark = this.theme === 'dark';
                    tooltip.textContent = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
                });
            }
        }

        // User dropdown hover effect
        const userMenuBtn = document.querySelector('.user-menu-btn');
        if (userMenuBtn) {
            userMenuBtn.addEventListener('mouseenter', () => {
                const arrow = userMenuBtn.querySelector('.dropdown-arrow');
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            });
            
            userMenuBtn.addEventListener('mouseleave', () => {
                const arrow = userMenuBtn.querySelector('.dropdown-arrow');
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            });
        }

        // Help modal
        const helpModal = document.getElementById('helpModal');
        if (helpModal) {
            helpModal.addEventListener('shown.bs.modal', () => {
                this.trackHelpModalOpen();
            });
        }

        // Mobile sidebar toggle
        const sidebarToggle = document.querySelector('[data-bs-target="#sidebar"]');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                this.trackSidebarToggle();
            });
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                this.closeAllDropdowns();
            }
        });
    }

    updateSwitchState() {
        const themeSwitch = document.getElementById('themeSwitch');
        if (themeSwitch) {
            themeSwitch.checked = this.theme === 'light';
        }
    }

    setupProfilePhotoHandlers() {
        // Handle image loading errors and setup proper display
        document.querySelectorAll('.user-profile-img, .user-profile-img-large').forEach(img => {
            if (img.tagName === 'IMG') {
                // Set initial display
                img.style.objectFit = 'cover';
                img.style.objectPosition = 'center';
                
                // Handle loading errors
                img.addEventListener('error', (e) => {
                    const target = e.target;
                    const userName = '<?php echo htmlspecialchars($user_nama); ?>';
                    const initials = this.getInitials(userName);
                    
                    // Replace with placeholder
                    const placeholder = document.createElement('div');
                    placeholder.className = target.className.replace('rounded-circle', '') + ' avatar-placeholder';
                    placeholder.style.width = target.style.width || target.getAttribute('width') + 'px';
                    placeholder.style.height = target.style.height || target.getAttribute('height') + 'px';
                    
                    const initialsSpan = document.createElement('span');
                    initialsSpan.className = target.classList.contains('user-profile-img-large') ? 'avatar-initials-large' : 'avatar-initials';
                    initialsSpan.textContent = initials;
                    
                    placeholder.appendChild(initialsSpan);
                    target.parentNode.replaceChild(placeholder, target);
                });
                
                // Add loading animation
                img.addEventListener('loadstart', () => {
                    img.classList.add('loading');
                });
                
                img.addEventListener('load', () => {
                    img.classList.remove('loading');
                    img.classList.add('photo-updated');
                    setTimeout(() => img.classList.remove('photo-updated'), 500);
                    
                    // Ensure image is properly displayed
                    this.fixImageDisplay(img);
                });
                
                // Check if image is already loaded
                if (img.complete) {
                    this.fixImageDisplay(img);
                }
            }
        });
    }

    fixImageDisplay(img) {
        // Ensure image maintains aspect ratio
        if (img.naturalWidth && img.naturalHeight) {
            const aspectRatio = img.naturalWidth / img.naturalHeight;
            
            // If image is not square, adjust object-position
            if (Math.abs(aspectRatio - 1) > 0.1) {
                console.log('Image is not square, adjusting display');
                
                // Calculate optimal crop
                if (aspectRatio > 1) {
                    // Landscape image, crop sides
                    img.style.objectPosition = 'center';
                } else {
                    // Portrait image, crop top/bottom
                    img.style.objectPosition = 'center 30%';
                }
            }
            
            // Ensure object-fit is cover
            img.style.objectFit = 'cover';
        }
    }

    getInitials(name) {
        if (!name || name === 'Guest') return 'G';
        
        return name
            .split(' ')
            .map(part => part.charAt(0))
            .join('')
            .toUpperCase()
            .substring(0, 2);
    }

    setupWelcomeMessages() {
        const welcomeContainer = document.getElementById('welcomeMotivationContainer');
        const welcomeMessage = document.getElementById('welcomeMessage');
        const motivationMessage = document.getElementById('motivationMessage');
        const logoAnimation = document.getElementById('logoAnimation');

        if (welcomeContainer && welcomeMessage && motivationMessage) {
            // Hide logo after 3 seconds and show messages
            setTimeout(() => {
                if (logoAnimation) {
                    logoAnimation.style.opacity = '0';
                    setTimeout(() => {
                        logoAnimation.style.display = 'none';
                    }, 500);
                }
                
                welcomeMessage.style.display = 'block';
                motivationMessage.style.display = 'block';
                
                // Add animation classes
                welcomeMessage.classList.add('welcome-animate');
                motivationMessage.classList.add('motivation-animate');
                
                // Show initial messages
                this.displayRandomMessages();
            }, 3000);
        }
    }

    displayRandomMessages() {
        const welcomeMessage = document.getElementById('welcomeMessage');
        const motivationMessage = document.getElementById('motivationMessage');
        
        if (welcomeMessage && motivationMessage) {
            const welcomeIndex = Math.floor(Math.random() * this.messages.welcome.length);
            const motivationIndex = Math.floor(Math.random() * this.messages.motivation.length);
            
            // Add glow effect before changing
            welcomeMessage.classList.add('text-glow');
            motivationMessage.classList.add('text-glow');
            
            // Fade out
            welcomeMessage.style.opacity = '0';
            motivationMessage.style.opacity = '0';
            
            // Update text after fade out
            setTimeout(() => {
                welcomeMessage.textContent = this.messages.welcome[welcomeIndex];
                motivationMessage.textContent = this.messages.motivation[motivationIndex];
                
                // Remove glow
                welcomeMessage.classList.remove('text-glow');
                motivationMessage.classList.remove('text-glow');
                
                // Fade in
                setTimeout(() => {
                    welcomeMessage.style.opacity = '1';
                    motivationMessage.style.opacity = '1';
                    
                    // Add animation
                    welcomeMessage.classList.remove('welcome-animate');
                    void welcomeMessage.offsetWidth; // Trigger reflow
                    welcomeMessage.classList.add('welcome-animate');
                    
                    motivationMessage.classList.remove('motivation-animate');
                    void motivationMessage.offsetWidth; // Trigger reflow
                    motivationMessage.classList.add('motivation-animate');
                }, 100);
            }, 300);
        }
    }

    startMessageRotation() {
        if (this.messageInterval) {
            clearInterval(this.messageInterval);
        }
        
        this.messageInterval = setInterval(() => {
            this.displayRandomMessages();
        }, 15000); // Change every 15 seconds
    }

    toggleTheme() {
        const themeSwitch = document.getElementById('themeSwitch');
        const tooltip = document.querySelector('.theme-tooltip-bottom');
        
        // Add click animation
        if (themeSwitch) {
            themeSwitch.style.transform = 'scale(0.95)';
            setTimeout(() => {
                themeSwitch.style.transform = 'scale(1)';
            }, 150);
        }
        
        if (this.theme === 'dark') {
            this.setTheme('light');
            
            // Update tooltip
            if (tooltip) {
                tooltip.textContent = 'Switch to Dark Mode';
            }
            
            // Dispatch theme changed event
            this.dispatchThemeChangeEvent('light');
            
        } else {
            this.setTheme('dark');
            
            // Update tooltip
            if (tooltip) {
                tooltip.textContent = 'Switch to Light Mode';
            }
            
            // Dispatch theme changed event
            this.dispatchThemeChangeEvent('dark');
        }
        
        // Update switch state
        this.updateSwitchState();
    }

    dispatchThemeChangeEvent(theme) {
        const event = new CustomEvent('themeChanged', {
            detail: { 
                theme: theme,
                timestamp: new Date().toISOString()
            }
        });
        document.dispatchEvent(event);
    }

    setTheme(theme) {
        this.theme = theme;
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Update all theme-dependent elements
        this.updateThemeColors(theme);
        
        // Update switch visual state
        this.updateSwitchVisuals(theme);
    }

    updateSwitchVisuals(theme) {
        const themeSwitch = document.getElementById('themeSwitch');
        if (themeSwitch) {
            // Trigger CSS transition
            themeSwitch.checked = theme === 'light';
        }
    }

    updateThemeColors(theme) {
        // Update CSS variables based on theme
        const root = document.documentElement;
        
        if (theme === 'light') {
            // Apply light theme colors
            root.style.setProperty('--light-primary', '#3b82f6');
            root.style.setProperty('--light-secondary', '#8b5cf6');
            root.style.setProperty('--light-accent', '#10b981');
            
            // Update gradient
            document.querySelectorAll('.brand-text, .help-card i').forEach(el => {
                el.style.background = 'linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%)';
                el.style.webkitBackgroundClip = 'text';
                el.style.backgroundClip = 'text';
            });
        } else {
            // Apply dark theme colors
            root.style.setProperty('--dark-primary', '#6366f1');
            root.style.setProperty('--dark-secondary', '#8b5cf6');
            root.style.setProperty('--dark-accent', '#10b981');
            
            // Update gradient
            document.querySelectorAll('.brand-text, .help-card i').forEach(el => {
                el.style.background = 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)';
                el.style.webkitBackgroundClip = 'text';
                el.style.backgroundClip = 'text';
            });
        }
        
        // Update text colors to ensure visibility
        this.fixTextColors();
    }

    fixTextColors() {
        // Ensure all text elements have proper color
        const textElements = document.querySelectorAll('.welcome-text, .motivation-text, .user-name, .dropdown-item, .navbar-nav .nav-link');
        
        textElements.forEach(el => {
            const computedStyle = window.getComputedStyle(el);
            const currentColor = computedStyle.color;
            
            // If color is too close to background, adjust it
            if (this.theme === 'dark') {
                if (currentColor === 'rgb(255, 255, 255)' || currentColor === 'rgba(255, 255, 255, 0.55)') {
                    el.style.color = 'var(--dark-text)';
                }
            } else {
                if (currentColor === 'rgb(0, 0, 0)' || currentColor === 'rgba(0, 0, 0, 0.55)') {
                    el.style.color = 'var(--light-text)';
                }
            }
        });
    }

    setupDropdownAnimations() {
        // Add animation to dropdown items
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('mouseenter', (e) => {
                const icon = e.currentTarget.querySelector('i');
                if (icon) {
                    icon.style.transform = 'translateX(5px) scale(1.1)';
                }
            });
            
            item.addEventListener('mouseleave', (e) => {
                const icon = e.currentTarget.querySelector('i');
                if (icon) {
                    icon.style.transform = 'translateX(0) scale(1)';
                }
            });
        });
    }

    setupScrollEffect() {
        const navbar = document.querySelector('.dashboard-navbar');
        if (navbar) {
            let lastScroll = 0;
            
            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;
                
                if (currentScroll <= 0) {
                    navbar.classList.remove('scrolled');
                    navbar.style.transform = 'translateY(0)';
                    return;
                }
                
                if (currentScroll > lastScroll && currentScroll > 100) {
                    // Scroll down
                    navbar.style.transform = 'translateY(-100%)';
                } else if (currentScroll < lastScroll) {
                    // Scroll up
                    navbar.classList.add('scrolled');
                    navbar.style.transform = 'translateY(0)';
                }
                
                lastScroll = currentScroll;
            });
        }
    }

    closeAllDropdowns() {
        // Close all Bootstrap dropdowns
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            const dropdown = menu.closest('.dropdown');
            if (dropdown) {
                const btn = dropdown.querySelector('[data-bs-toggle="dropdown"]');
                if (btn) {
                    const bsDropdown = bootstrap.Dropdown.getInstance(btn);
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                }
            }
        });
    }

    showToast(message, type = 'info') {
        // Check if toast container exists
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        const toastId = 'toast-' + Date.now();
        const typeIcons = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-times-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        };
        
        const iconClass = typeIcons[type] || typeIcons.info;
        const iconColor = type === 'success' ? 'text-success' : 
                         type === 'error' ? 'text-danger' : 
                         type === 'warning' ? 'text-warning' : 'text-info';
        
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="${iconClass} ${iconColor} me-2"></i>
                    <strong class="me-auto">Finansialku</strong>
                    <small class="text-muted">baru saja</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });
        
        toast.show();
        
        // Remove toast after it's hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    trackHelpModalOpen() {
        console.log('Help modal opened - User seeking assistance');
        // You can add analytics tracking here
        // Example: sendEventToAnalytics('help_modal_opened');
    }

    trackSidebarToggle() {
        console.log('Sidebar toggled');
        // You can add analytics tracking here
        // Example: sendEventToAnalytics('sidebar_toggled');
    }

    // Method to update user info
    updateUserInfo(name, email, photo) {
        const userAvatarSmall = document.querySelector('.user-menu-btn .user-avatar');
        const userAvatarLarge = document.querySelector('.user-info-header .user-avatar');
        const userName = document.querySelectorAll('.user-name');
        const userEmail = document.querySelector('.user-info small');
        
        // Update photos with fade effect
        if (userAvatarSmall) {
            this.updateAvatarElement(userAvatarSmall, photo, name, false);
        }
        
        if (userAvatarLarge) {
            this.updateAvatarElement(userAvatarLarge, photo, name, true);
        }
        
        // Update name and email
        userName.forEach(el => {
            el.textContent = name;
        });
        
        if (userEmail) {
            userEmail.textContent = email;
        }
        
        // Show success notification
        this.showToast('Profil Diperbarui', 'Informasi profil Anda telah berhasil diperbarui', 'success');
    }

    updateAvatarElement(avatarContainer, photo, name, isLarge) {
        const className = isLarge ? 'user-profile-img-large' : 'user-profile-img';
        const initials = this.getInitials(name);
        
        // Fade out current avatar
        const currentAvatar = avatarContainer.querySelector('img, .avatar-placeholder');
        if (currentAvatar) {
            currentAvatar.style.opacity = '0';
            currentAvatar.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                // Remove current avatar
                avatarContainer.innerHTML = '';
                
                if (photo && photo !== 'assets/icons/default-avatar.png') {
                    // Create new image element
                    const img = document.createElement('img');
                    img.src = photo;
                    img.alt = `Avatar ${name}`;
                    img.className = `rounded-circle ${className}`;
                    img.style.width = isLarge ? '48px' : '32px';
                    img.style.height = isLarge ? '48px' : '32px';
                    img.style.objectFit = 'cover';
                    img.style.objectPosition = 'center';
                    
                    // Handle image errors
                    img.onerror = () => {
                        this.createAvatarFallback(avatarContainer, initials, isLarge);
                    };
                    
                    img.onload = () => {
                        img.classList.add('photo-updated');
                        setTimeout(() => img.classList.remove('photo-updated'), 500);
                        this.fixImageDisplay(img);
                    };
                    
                    avatarContainer.appendChild(img);
                } else {
                    // Create placeholder
                    this.createAvatarFallback(avatarContainer, initials, isLarge);
                }
                
                // Fade in new avatar
                setTimeout(() => {
                    const newAvatar = avatarContainer.querySelector('img, .avatar-placeholder');
                    if (newAvatar) {
                        newAvatar.style.opacity = '0';
                        newAvatar.style.transition = 'opacity 0.3s ease';
                        
                        setTimeout(() => {
                            newAvatar.style.opacity = '1';
                        }, 10);
                    }
                }, 10);
            }, 300);
        }
    }

    createAvatarFallback(container, initials, isLarge) {
        const placeholder = document.createElement('div');
        placeholder.className = isLarge ? 'user-profile-img-large avatar-placeholder' : 'user-profile-img avatar-placeholder';
        
        const initialsSpan = document.createElement('span');
        initialsSpan.className = isLarge ? 'avatar-initials-large' : 'avatar-initials';
        initialsSpan.textContent = initials;
        
        placeholder.appendChild(initialsSpan);
        container.appendChild(placeholder);
    }

    // Validation method for profile photos
    validateProfilePhoto(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                // Check if image is square (within 10% tolerance)
                const isSquare = Math.abs(img.width - img.height) / img.width <= 0.1;
                
                if (isSquare && img.width >= 100 && img.height >= 100) {
                    resolve({
                        valid: true,
                        width: img.width,
                        height: img.height,
                        aspectRatio: img.width / img.height
                    });
                } else {
                    resolve({
                        valid: false,
                        message: 'Foto profil harus persegi (minimal 100x100 pixels)'
                    });
                }
            };
            img.onerror = () => reject(new Error('Gagal memuat gambar'));
            img.src = url;
        });
    }

    // Cleanup method
    destroy() {
        if (this.messageInterval) {
            clearInterval(this.messageInterval);
        }
        
        // Remove event listeners
        document.removeEventListener('click', this.closeAllDropdowns);
        
        console.log('Navbar Manager destroyed');
    }
}

// Initialize navbar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.navbarManager = new NavbarManager();
    window.notificationManager = new NotificationManager();
    
    // Make navbarManager globally available
    window.updateUserNavbar = function(name, email, photo) {
        if (window.navbarManager) {
            window.navbarManager.updateUserInfo(name, email, photo);
        }
    };
    
    window.validateProfilePhoto = function(url) {
        if (window.navbarManager) {
            return window.navbarManager.validateProfilePhoto(url);
        }
        return Promise.reject('Navbar manager not initialized');
    };
    
    // Make notification manager globally available
    window.addNotification = function(detail) {
        if (window.notificationManager) {
            window.notificationManager.addNotification(detail);
        }
    };
    
    window.showNotification = function(title, message, icon = 'fas fa-info-circle', iconColor = 'info') {
        if (window.notificationManager) {
            window.notificationManager.addNotification({
                title,
                message,
                icon,
                iconColor
            });
        }
    };
});

// Export for use in other modules (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NavbarManager;
}

// Handle page visibility change
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && window.navbarManager) {
        // Refresh messages when user returns to tab
        window.navbarManager.startMessageRotation();
    }
});

// Handle beforeunload to cleanup
window.addEventListener('beforeunload', () => {
    if (window.navbarManager) {
        window.navbarManager.destroy();
    }
});
</script>