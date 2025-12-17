<?php
/**
 * offline.php - Halaman offline untuk Finansialku
 * Ditampilkan ketika koneksi internet atau database terputus
 */

// Set header untuk status offline
header("HTTP/1.0 503 Service Unavailable");
header("Retry-After: 30"); // Coba lagi setelah 30 detik

// Ambil parameter dari URL
$reason = $_GET['reason'] ?? 'unknown';
$fromPage = $_GET['from'] ?? '/index.php';
$timestamp = $_GET['timestamp'] ?? date('c');
$details = isset($_GET['details']) ? urldecode($_GET['details']) : '';

// Map alasan ke pesan yang lebih user-friendly
$reasonMessages = [
    'internet' => [
        'title' => 'Koneksi Internet Terputus',
        'message' => 'Perangkat Anda tidak terhubung ke internet.',
        'icon' => 'wifi-slash',
        'color' => 'danger'
    ],
    'database' => [
        'title' => 'Database Tidak Tersedia',
        'message' => 'Server database sedang tidak dapat diakses.',
        'icon' => 'database',
        'color' => 'warning'
    ],
    'fetch_error' => [
        'title' => 'Gagal Terhubung ke Server',
        'message' => 'Tidak dapat menghubungi server aplikasi.',
        'icon' => 'server',
        'color' => 'info'
    ],
    'timeout' => [
        'title' => 'Waktu Koneksi Habis',
        'message' => 'Server tidak merespons dalam waktu yang ditentukan.',
        'icon' => 'clock',
        'color' => 'secondary'
    ],
    'unknown' => [
        'title' => 'Koneksi Bermasalah',
        'message' => 'Terjadi masalah dengan koneksi jaringan.',
        'icon' => 'question-circle',
        'color' => 'dark'
    ]
];

// Pilih pesan berdasarkan alasan
$currentReason = $reasonMessages[$reason] ?? $reasonMessages['unknown'];

// Format timestamp
try {
    $dateTime = new DateTime($timestamp);
    $formattedTime = $dateTime->format('d M Y H:i:s');
    $relativeTime = time() - $dateTime->getTimestamp();
} catch (Exception $e) {
    $formattedTime = date('d M Y H:i:s');
    $relativeTime = 0;
}

// Hitung waktu yang lalu
if ($relativeTime < 60) {
    $timeAgo = 'Baru saja';
} elseif ($relativeTime < 3600) {
    $timeAgo = floor($relativeTime / 60) . ' menit yang lalu';
} elseif ($relativeTime < 86400) {
    $timeAgo = floor($relativeTime / 3600) . ' jam yang lalu';
} else {
    $timeAgo = floor($relativeTime / 86400) . ' hari yang lalu';
}

// Nama halaman asal
$pageName = basename($fromPage);
if ($pageName === 'index.php') {
    $pageName = 'Halaman Login';
} elseif ($pageName === 'dashboard.php') {
    $pageName = 'Dashboard';
} else {
    $pageName = ucfirst(str_replace(['.php', '_'], ['', ' '], $pageName));
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Halaman offline Finansialku - Aplikasi manajemen keuangan">
    <meta name="robots" content="noindex, nofollow">
    
    <title>Koneksi Terputus - Finansialku</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/icons/Dompt.png">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/offline.css" rel="stylesheet">
    <link href="css/animasi.css" rel="stylesheet">
    
    <style>
        /* Inline styles untuk halaman offline */
        .reconnect-counter {
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--offline-primary);
        }
        
        .connection-quality {
            height: 4px;
            background: linear-gradient(90deg, 
                #28a745 0%, 
                #28a745 var(--quality, 0%), 
                #dee2e6 var(--quality, 0%), 
                #dee2e6 100%);
            border-radius: 2px;
            margin: 0.5rem 0;
            transition: background 0.5s ease;
        }
    </style>
</head>
<body class="offline-body">
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="offline-content">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="offline-icon">
                            <i class="fas fa-<?php echo $currentReason['icon']; ?>"></i>
                            <div class="broken-wallet">
                                <i class="fas fa-wallet"></i>
                                <div class="tear"></div>
                            </div>
                        </div>
                        
                        <h1 class="offline-title"><?php echo htmlspecialchars($currentReason['title']); ?></h1>
                        
                        <div class="reason-badge <?php echo $reason; ?> mb-3">
                            <i class="fas fa-<?php echo $currentReason['icon']; ?> me-2"></i>
                            <?php echo strtoupper($reason); ?>
                        </div>
                    </div>
                    
                    <!-- Details Card -->
                    <div class="offline-details mb-4">
                        <div class="detail-item">
                            <span class="detail-label">Waktu Kejadian:</span>
                            <span class="detail-value"><?php echo $formattedTime; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Dari Halaman:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($pageName); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value">
                                <span class="badge bg-<?php echo $currentReason['color']; ?>">
                                    <?php echo $currentReason['title']; ?>
                                </span>
                            </span>
                        </div>
                        <?php if ($details): ?>
                        <div class="detail-item">
                            <span class="detail-label">Detail:</span>
                            <span class="detail-value small"><?php echo htmlspecialchars($details); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Message -->
                    <div class="text-center mb-4">
                        <p class="offline-text">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo $currentReason['message']; ?>
                            <br>
                            <small class="text-muted">Sistem akan mencoba menghubungkan kembali secara otomatis.</small>
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="offline-actions mb-4">
                        <button class="btn btn-try-again" onclick="attemptReconnection()" id="reconnectBtn">
                            <i class="fas fa-sync-alt me-2"></i>Coba Sambung Kembali
                        </button>
                        <button class="btn btn-home" onclick="goToHomepage()">
                            <i class="fas fa-home me-2"></i>Ke Halaman Utama
                        </button>
                    </div>
                    
                    <!-- Reconnection Status -->
                    <div class="reconnect-status" id="reconnectStatus" style="display: none;">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="ms-2" id="reconnectMessage">Mencoba menyambung...</span>
                        </div>
                        <div class="connection-progress mt-2">
                            <div class="progress-label">
                                <span>Progress:</span>
                                <span class="count" id="attemptCount">Percobaan ke-0</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     id="reconnectProgress" 
                                     role="progressbar" 
                                     style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tips Section -->
                    <div class="offline-tips">
                        <h6><i class="fas fa-lightbulb me-2"></i>Tips Pemecahan Masalah</h6>
                        <ul class="tips-list">
                            <?php if ($reason === 'internet'): ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Periksa koneksi WiFi atau data seluler Anda</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Coba restart router/modem Anda</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Nonaktifkan VPN atau proxy jika digunakan</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Pastikan mode pesawat dimatikan</span>
                            </li>
                            <?php elseif ($reason === 'database'): ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Server database mungkin sedang dalam perawatan</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Coba kembali dalam beberapa menit</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Hubungi administrator sistem jika masalah berlanjut</span>
                            </li>
                            <?php else: ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Refresh halaman ini dengan menekan F5</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Clear cache dan cookies browser Anda</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Coba gunakan browser yang berbeda</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Restart perangkat Anda</span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <!-- Footer -->
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Halaman ini dibuat: <?php echo date('d M Y H:i:s'); ?>
                            <span class="mx-2">•</span>
                            <i class="fas fa-redo me-1"></i>
                            Auto-refresh dalam: <span id="countdown">30</span> detik
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Network Status Indicator -->
    <div class="network-status offline" id="networkStatus">
        <i class="fas fa-wifi-slash me-2"></i>
        <span>OFFLINE</span>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Configuration
        const config = {
            maxReconnectAttempts: 10,
            reconnectDelay: 3000, // 3 seconds
            countdownStart: 30,
            checkEndpoint: 'php/check_connection.php'
        };
        
        // State
        let reconnectAttempts = 0;
        let countdown = config.countdownStart;
        let isReconnecting = false;
        let countdownInterval;
        
        // DOM Elements
        const reconnectBtn = document.getElementById('reconnectBtn');
        const reconnectStatus = document.getElementById('reconnectStatus');
        const reconnectMessage = document.getElementById('reconnectMessage');
        const reconnectProgress = document.getElementById('reconnectProgress');
        const attemptCount = document.getElementById('attemptCount');
        const countdownElement = document.getElementById('countdown');
        const networkStatus = document.getElementById('networkStatus');
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Start auto-refresh countdown
            startCountdown();
            
            // Start auto-reconnection
            setTimeout(attemptReconnection, 2000);
            
            // Update network status indicator
            updateNetworkStatus();
            
            // Listen for online/offline events
            window.addEventListener('online', handleOnline);
            window.addEventListener('offline', handleOffline);
        });
        
        function startCountdown() {
            countdownInterval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.reload();
                }
            }, 1000);
        }
        
        function updateNetworkStatus() {
            if (navigator.onLine) {
                networkStatus.innerHTML = '<i class="fas fa-wifi me-2"></i><span>ONLINE</span>';
                networkStatus.className = 'network-status online';
            } else {
                networkStatus.innerHTML = '<i class="fas fa-wifi-slash me-2"></i><span>OFFLINE</span>';
                networkStatus.className = 'network-status offline';
            }
        }
        
        function handleOnline() {
            console.log('Internet connection restored');
            updateNetworkStatus();
            
            // Try to reconnect immediately
            if (!isReconnecting) {
                attemptReconnection();
            }
        }
        
        function handleOffline() {
            console.log('Internet connection lost');
            updateNetworkStatus();
            
            // Stop reconnection attempts
            isReconnecting = false;
            reconnectStatus.style.display = 'none';
            reconnectBtn.disabled = true;
        }
        
        async function attemptReconnection() {
            if (isReconnecting || reconnectAttempts >= config.maxReconnectAttempts) {
                return;
            }
            
            isReconnecting = true;
            reconnectAttempts++;
            
            // Update UI
            reconnectBtn.disabled = true;
            reconnectStatus.style.display = 'block';
            attemptCount.textContent = `Percobaan ke-${reconnectAttempts}`;
            
            // Calculate progress
            const progress = Math.min(100, (reconnectAttempts / config.maxReconnectAttempts) * 100);
            reconnectProgress.style.width = `${progress}%`;
            
            // Check internet connection first
            if (!navigator.onLine) {
                reconnectMessage.textContent = 'Menunggu koneksi internet...';
                setTimeout(() => {
                    isReconnecting = false;
                    reconnectBtn.disabled = false;
                    reconnectStatus.style.display = 'none';
                }, config.reconnectDelay);
                return;
            }
            
            // Try to connect to database
            reconnectMessage.textContent = 'Memeriksa koneksi database...';
            
            try {
                // Add cache-busting parameter
                const url = `${config.checkEndpoint}?_t=${Date.now()}`;
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache'
                    },
                    timeout: 5000
                });
                
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.status === 'connected') {
                        // Connection successful!
                        reconnectMessage.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Koneksi berhasil!';
                        reconnectProgress.className = 'progress-bar bg-success';
                        
                        // Redirect back after short delay
                        setTimeout(() => {
                            const fromPage = new URLSearchParams(window.location.search).get('from') || 'index.php';
                            window.location.href = fromPage;
                        }, 1500);
                        
                        return;
                    } else {
                        throw new Error(data.message || 'Database disconnected');
                    }
                } else {
                    throw new Error(`HTTP ${response.status}`);
                }
                
            } catch (error) {
                console.error('Reconnection attempt failed:', error);
                
                if (reconnectAttempts >= config.maxReconnectAttempts) {
                    reconnectMessage.innerHTML = '<i class="fas fa-exclamation-triangle text-warning me-2"></i>Gagal menyambung. Silakan coba manual.';
                    reconnectProgress.className = 'progress-bar bg-warning';
                    reconnectBtn.disabled = false;
                    reconnectBtn.innerHTML = '<i class="fas fa-redo me-2"></i>Coba Lagi';
                } else {
                    reconnectMessage.textContent = `Mencoba lagi dalam ${config.reconnectDelay/1000} detik...`;
                    
                    // Retry after delay
                    setTimeout(() => {
                        isReconnecting = false;
                        attemptReconnection();
                    }, config.reconnectDelay);
                }
            }
        }
        
        function goToHomepage() {
            window.location.href = 'index.php';
        }
        
        // Expose functions globally
        window.attemptReconnection = attemptReconnection;
        window.goToHomepage = goToHomepage;
        
        // Add connection quality indicator
        function updateConnectionQuality() {
            if ('connection' in navigator) {
                const connection = navigator.connection;
                const quality = connection.downlink / 10 * 100; // Convert to percentage
                
                document.documentElement.style.setProperty('--quality', `${quality}%`);
            }
        }
        
        // Update connection quality periodically
        if ('connection' in navigator) {
            navigator.connection.addEventListener('change', updateConnectionQuality);
            updateConnectionQuality();
        }
    </script>
</body>
</html>