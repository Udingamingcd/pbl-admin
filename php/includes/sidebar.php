<?php
// Determine current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="sidebar">
    <div class="offcanvas-header">
        <div class="sidebar-brand">
            <img src="assets/icons/Dompt.png" alt="Finansialku" height="40">
            <span>Finansialku</span>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    
    <div class="offcanvas-body p-0">
        <!-- User Profile Summary -->
        <div class="sidebar-user-profile p-3 border-bottom">
            <div class="d-flex align-items-center">
                <div class="user-avatar me-3">
                    <?php if ($user_foto): ?>
                        <img src="<?php echo htmlspecialchars($user_foto); ?>" alt="User Avatar" class="rounded-circle" width="50" height="50">
                    <?php else: ?>
                        <div class="avatar-placeholder rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h6 class="mb-1"><?php echo htmlspecialchars($user_nama); ?></h6>
                    <small class="text-muted"><?php echo htmlspecialchars($user_email); ?></small>
                    <div class="user-status">
                        <span class="status-indicator online"></span>
                        <small>Online</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-3"></i>
                        <span>Dashboard</span>
                        <span class="badge bg-primary ms-auto">New</span>
                    </a>
                </li>
                
                <!-- Transaksi Section -->
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array($current_page, ['transaksi.php', 'php/crud/transaksi/create.php']) ? 'active' : ''; ?>" 
                       href="php/crud/transaksi/read.php">
                        <i class="fas fa-exchange-alt me-3"></i>
                        <span>Transaksi</span>
                        <span class="badge bg-success ms-auto" id="transaksiCount">5</span>
                    </a>
                </li>
                
                <!-- Budget Section -->
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array($current_page, ['budget.php', 'php/crud/budget/create.php']) ? 'active' : ''; ?>" 
                       href="php/crud/budget/read.php">
                        <i class="fas fa-chart-pie me-3"></i>
                        <span>Budget</span>
                    </a>
                </li>
                
                <!-- Financial Goals -->
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array($current_page, ['goals.php', 'php/crud/financial_goal/create.php']) ? 'active' : ''; ?>" 
                       href="php/crud/financial_goal/read.php">
                        <i class="fas fa-bullseye me-3"></i>
                        <span>Target Finansial</span>
                        <span class="badge bg-warning ms-auto" id="targetCount">2</span>
                    </a>
                </li>
                
                <!-- Reports -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'laporan.php' ? 'active' : ''; ?>" href="laporan.php">
                        <i class="fas fa-file-alt me-3"></i>
                        <span>Laporan</span>
                    </a>
                </li>
                
                <!-- Analysis -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'analisis.php' ? 'active' : ''; ?>" href="analisis.php">
                        <i class="fas fa-chart-line me-3"></i>
                        <span>Analisis</span>
                    </a>
                </li>
                
                <li class="nav-item divider">
                    <hr class="my-2">
                </li>
                
                <!-- Account Management -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'profil.php' ? 'active' : ''; ?>" href="profil.php">
                        <i class="fas fa-user me-3"></i>
                        <span>Profil Saya</span>
                    </a>
                </li>
                
                <!-- Settings -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'pengaturan.php' ? 'active' : ''; ?>" href="pengaturan.php">
                        <i class="fas fa-cog me-3"></i>
                        <span>Pengaturan Akun</span>
                    </a>
                </li>

                <!-- Tema Section -->
                <li class="nav-item">
                    <a class="nav-link" href="#" id="themeSidebarToggle">
                        <i class="fas fa-palette me-3"></i>
                        <span>Tema</span>
                    </a>
                </li>

                <!-- Help & Support -->
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#helpModal">
                        <i class="fas fa-question-circle me-3"></i>
                        <span>Bantuan</span>
                    </a>
                </li>
                
                <li class="nav-item divider">
                    <hr class="my-2">
                </li>
                
                <!-- Logout -->
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt me-3"></i>
                        <span>Keluar</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Quick Stats -->
        <div class="sidebar-stats p-3 border-top">
            <h6 class="sidebar-section-title">Statistik Cepat</h6>
            <div class="stats-grid">
                <div class="stat-item">
                    <small class="text-muted">Saldo Bulan Ini</small>
                    <div class="stat-value text-success" id="sidebarSaldo">Rp 2.5M</div>
                </div>
                <div class="stat-item">
                    <small class="text-muted">Pengeluaran</small>
                    <div class="stat-value text-danger" id="sidebarPengeluaran">Rp 700K</div>
                </div>
                <div class="stat-item">
                    <small class="text-muted">Budget Tersisa</small>
                    <div class="stat-value text-warning" id="sidebarBudget">Rp 1.8M</div>
                </div>
                <div class="stat-item">
                    <small class="text-muted">Target</small>
                    <div class="stat-value text-info" id="sidebarTarget">65%</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="sidebar-actions p-3 border-top">
            <h6 class="sidebar-section-title">Aksi Cepat</h6>
            <div class="d-grid gap-2">
                <a href="php/crud/transaksi/create.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-2"></i>Tambah Transaksi
                </a>
                <a href="php/crud/budget/create.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-chart-pie me-2"></i>Atur Budget
                </a>
                <a href="profil.php" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-user-edit me-2"></i>Edit Profil
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    // Update sidebar stats
    function updateSidebarStats() {
        // In real implementation, fetch from API
        const stats = {
            saldo: 'Rp 2.5M',
            pengeluaran: 'Rp 700K',
            budget: 'Rp 1.8M',
            target: '65%'
        };
        
        document.getElementById('sidebarSaldo').textContent = stats.saldo;
        document.getElementById('sidebarPengeluaran').textContent = stats.pengeluaran;
        document.getElementById('sidebarBudget').textContent = stats.budget;
        document.getElementById('sidebarTarget').textContent = stats.target;
    }
    
    updateSidebarStats();
    
    // Add click effects to sidebar items
    const sidebarLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all links
            sidebarLinks.forEach(l => l.classList.remove('active'));
            // Add active class to clicked link
            this.classList.add('active');
        });
    });

    // Theme toggle from sidebar
    document.getElementById('themeSidebarToggle').addEventListener('click', function(e) {
        e.preventDefault();
        if (window.themeManager) {
            window.themeManager.createThemeSelector();
        }
    });
});
</script>