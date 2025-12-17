<!-- Footer -->
<footer class="dashboard-footer mt-5">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="footer-content">
                    <p class="mb-0">
                        &copy; 2025 <strong>Finansialku</strong>. All rights reserved.
                        <span class="d-none d-md-inline">•</span>
                        <span class="d-block d-md-inline mt-1 mt-md-0">
                            Dibuat dengan <i class="fas fa-heart text-danger"></i> untuk masa depan finansial yang lebih baik
                        </span>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="footer-links text-md-end">
                    <a href="about.php" class="text-muted me-3">Tentang Kami</a>
                    <a href="faq.php" class="text-muted me-3">FAQ</a>
                    <a href="kontak.php" class="text-muted me-3">Kontak</a>
                    <a href="kebijakan-privasi.php" class="text-muted">Privasi</a>
                </div>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="system-status">
                    <div class="status-item">
                        <span class="status-indicator online"></span>
                        <small class="text-muted">Database: <span id="dbStatus">Online</span></small>
                    </div>
                    <div class="status-item">
                        <span class="status-indicator online"></span>
                        <small class="text-muted">Server: <span id="serverStatus">Online</span></small>
                    </div>
                    <div class="status-item">
                        <span class="status-indicator online"></span>
                        <small class="text-muted">Response: <span id="responseTime">-</span></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.dashboard-footer {
    background: var(--bs-dark);
    color: var(--bs-gray-400);
    padding: 2rem 0;
    margin-top: auto;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-content p {
    font-size: 0.875rem;
}

.footer-links a {
    text-decoration: none;
    font-size: 0.875rem;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: var(--bs-primary) !important;
}

.system-status {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    justify-content: flex-end;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.status-indicator.online {
    background-color: var(--bs-success);
    animation: pulse 2s infinite;
}

.status-indicator.offline {
    background-color: var(--bs-danger);
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .dashboard-footer {
        padding: 1.5rem 0;
    }
    
    .footer-content,
    .footer-links {
        text-align: center !important;
    }
    
    .footer-links {
        margin-top: 1rem;
    }
    
    .footer-links a {
        display: inline-block;
        margin: 0 0.5rem;
    }
    
    .system-status {
        justify-content: center;
        margin-top: 1rem;
    }
}
</style>

<script>
// System status monitoring
document.addEventListener('DOMContentLoaded', function() {
    updateSystemStatus();
    setInterval(updateSystemStatus, 30000); // Update every 30 seconds
});

function updateSystemStatus() {
    const startTime = performance.now();
    
    fetch('ajax/cek-koneksi.php')
        .then(response => {
            const responseTime = performance.now() - startTime;
            document.getElementById('responseTime').textContent = `${Math.round(responseTime)}ms`;
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const dbStatus = document.getElementById('dbStatus');
            const dbIndicator = document.querySelector('#dbStatus').closest('.status-item').querySelector('.status-indicator');
            
            if (data.connected) {
                dbStatus.textContent = 'Online';
                dbIndicator.className = 'status-indicator online';
            } else {
                dbStatus.textContent = 'Offline';
                dbIndicator.className = 'status-indicator offline';
            }
        })
        .catch(error => {
            console.error('Error checking system status:', error);
            document.getElementById('dbStatus').textContent = 'Error';
            document.querySelector('#dbStatus').closest('.status-item').querySelector('.status-indicator').className = 'status-indicator offline';
        });
}
</script>