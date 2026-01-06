<!-- Sidebar -->
<aside class="col-lg-2 col-md-3 d-none d-md-block">
    <div class="sticky-top pt-3">
        <div class="card shadow mb-3">
            <div class="card-body p-3">
                <div class="text-center mb-3">
                    <div class="mb-3">
                        <div class="bg-gradient-danger rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-user-shield text-white fa-2x"></i>
                        </div>
                    </div>
                    <h6 class="mb-0"><?php echo htmlspecialchars($_SESSION['admin_nama']); ?></h6>
                    <small class="text-muted">Super Admin</small>
                    
                    <div class="mt-3">
                        <span class="badge bg-danger">Full Access</span>
                        <span class="badge bg-warning">System Owner</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Login Terakhir:</small>
                    <div class="text-primary">
                        <?php 
                        $db = new Database();
                        $db->query('SELECT last_login FROM admins WHERE id = :id');
                        $db->bind(':id', $_SESSION['admin_id']);
                        $last_login = $db->single()['last_login'];
                        echo $last_login ? date('d M H:i', strtotime($last_login)) : 'Belum pernah';
                        ?>
                    </div>
                </div>
                
                <div class="d-grid">
                    <a href="pengaturan.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-cog me-1"></i>Pengaturan
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card shadow">
            <div class="card-body p-0">
                <nav class="nav flex-column">
                    <div class="nav-link text-muted ps-3 small">MAIN NAVIGATION</div>
                    
                    <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    
                    <div class="nav-link text-muted ps-3 small mt-3">ADMIN MANAGEMENT</div>
                    <a href="kelola-admin.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelola-admin.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users-cog me-2"></i>Kelola Admin
                    </a>
                    <a href="tambah-admin.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tambah-admin.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-plus me-2"></i>Tambah Admin
                    </a>
                    
                    <div class="nav-link text-muted ps-3 small mt-3">USER MANAGEMENT</div>
                    <a href="kelola-user.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelola-user.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users me-2"></i>Kelola User
                    </a>
                    
                    <div class="nav-link text-muted ps-3 small mt-3">SYSTEM</div>
                    <a href="pengaturan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pengaturan.php' ? 'active' : ''; ?>">
                        <i class="fas fa-cog me-2"></i>Pengaturan Sistem
                    </a>
                    
                    <div class="nav-link text-muted ps-3 small mt-3">OTHER</div>
                    <a href="../admin/dashboard/index.php" class="nav-link">
                        <i class="fas fa-exchange-alt me-2"></i>Switch to Admin
                    </a>
                    <a href="../../admin/logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="card shadow mt-3">
            <div class="card-header bg-gradient-info text-white py-2">
                <h6 class="mb-0"><i class="fas fa-server me-2"></i>System Status</h6>
            </div>
            <div class="card-body p-3">
                <?php
                $db = new Database();
                
                // Database status
                $db_status = $db->isConnected() ? 'Online' : 'Offline';
                $db_color = $db->isConnected() ? 'success' : 'danger';
                
                // Active admins
                $db->query('SELECT COUNT(DISTINCT admin_id) as count FROM admin_sessions 
                           WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)');
                $active_admins = $db->single()['count'];
                
                // System uptime (simulated)
                $uptime = '24 days, 7 hours';
                ?>
                <div class="d-flex justify-content-between mb-2">
                    <small>Database:</small>
                    <span class="badge bg-<?php echo $db_color; ?>"><?php echo $db_status; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <small>Admins Online:</small>
                    <strong><?php echo $active_admins; ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <small>Uptime:</small>
                    <small class="text-muted"><?php echo $uptime; ?></small>
                </div>
                <div class="d-flex justify-content-between">
                    <small>PHP Version:</small>
                    <small class="text-muted"><?php echo phpversion(); ?></small>
                </div>
            </div>
        </div>
    </div>
</aside>