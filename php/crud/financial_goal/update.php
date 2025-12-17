<?php
define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/middleware/auth.php';
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: read.php');
    exit();
}

$db = new Database();
$success = '';
$error = '';

// Get existing data
$db->query('SELECT * FROM financial_goal WHERE id = :id AND user_id = :user_id');
$db->bind(':id', $_GET['id']);
$db->bind(':user_id', $_SESSION['user_id']);
$goal = $db->single();

if (!$goal) {
    header('Location: read.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Auto update status if terkumpul >= target_jumlah
        $status = ($_POST['terkumpul'] >= $_POST['target_jumlah']) ? 'tercapai' : $_POST['status'];
        
        $db->query('UPDATE financial_goal SET 
                   nama_goal = :nama_goal, 
                   target_jumlah = :target_jumlah, 
                   terkumpul = :terkumpul, 
                   tenggat_waktu = :tenggat_waktu, 
                   deskripsi = :deskripsi, 
                   status = :status 
                   WHERE id = :id AND user_id = :user_id');
        
        $db->bind(':nama_goal', $_POST['nama_goal']);
        $db->bind(':target_jumlah', $_POST['target_jumlah']);
        $db->bind(':terkumpul', $_POST['terkumpul']);
        $db->bind(':tenggat_waktu', $_POST['tenggat_waktu']);
        $db->bind(':deskripsi', $_POST['deskripsi']);
        $db->bind(':status', $status);
        $db->bind(':id', $_GET['id']);
        $db->bind(':user_id', $_SESSION['user_id']);
        
        if ($db->execute()) {
            $_SESSION['success_message'] = 'Target finansial berhasil diperbarui!';
            header('Location: read.php');
            exit();
        } else {
            $error = 'Gagal memperbarui target finansial';
        }
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Target Finansial - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        
        body {
            background: linear-gradient(135deg, #0c2461 0%, #1e3799 50%, #0a3d62 100%);
            min-height: 100vh;
            padding: 2rem 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            color: white;
            transition: all 0.4s ease;
            overflow: hidden;
            position: relative;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px 0 rgba(31, 38, 135, 0.5);
        }
        
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }
        
        .card-header {
            border-radius: 20px 20px 0 0 !important;
            border-bottom: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-weight: 600;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--primary-gradient);
            opacity: 0.1;
            z-index: 0;
        }
        
        .card-header h4 {
            position: relative;
            z-index: 1;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px 0 rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px 0 rgba(102, 126, 234, 0.6);
        }
        
        .btn-outline-light {
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.05);
        }
        
        .btn-outline-light:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.08);
            color: white;
            padding: 14px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(102, 126, 234, 0.6);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group-text {
            background: rgba(102, 126, 234, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px 0 0 12px;
            font-weight: 600;
        }
        
        /* Enhanced Date Input with Calendar Button */
        .date-input-group {
            position: relative;
            display: flex;
            align-items: stretch;
        }
        
        .date-input-group .form-control {
            flex: 1;
            padding-right: 4.5rem;
            border-radius: 12px 0 0 12px;
        }
        
        .calendar-btn {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 60px;
            background: rgba(102, 126, 234, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-left: none;
            border-radius: 0 12px 12px 0;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.3rem;
        }
        
        .calendar-btn:hover {
            background: rgba(102, 126, 234, 0.5);
            color: white;
        }
        
        .calendar-btn:active {
            transform: scale(0.95);
        }
        
        .page-title {
            color: white;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
            font-weight: 700;
            margin-bottom: 2rem;
            padding-bottom: 10px;
            display: inline-block;
            position: relative;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 3px;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            font-weight: 600;
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .status-toggle {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .status-btn {
            flex: 1;
            min-width: 130px;
            border-radius: 12px;
            padding: 14px 16px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .status-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 0;
        }
        
        .status-btn.active::before {
            opacity: 1;
        }
        
        .status-btn .btn-content {
            position: relative;
            z-index: 1;
        }
        
        .status-btn.aktif.active {
            border-color: rgba(102, 126, 234, 0.6);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .status-btn.tercapai.active {
            border-color: rgba(79, 172, 254, 0.6);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
        }
        
        .status-btn.tercapai::before {
            background: var(--success-gradient);
        }
        
        .status-btn.dibatalkan.active {
            border-color: rgba(255, 154, 158, 0.6);
            box-shadow: 0 5px 15px rgba(255, 154, 158, 0.3);
        }
        
        .status-btn.dibatalkan::before {
            background: var(--danger-gradient);
        }
        
        .status-btn:hover:not(.active) {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .progress-container {
            margin: 25px 0;
            position: relative;
        }
        
        .progress {
            height: 16px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .progress-bar {
            background: var(--success-gradient);
            border-radius: 10px;
            transition: width 0.8s cubic-bezier(0.22, 0.61, 0.36, 1);
            position: relative;
            overflow: hidden;
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background-image: linear-gradient(
                -45deg,
                rgba(255, 255, 255, 0.2) 25%,
                transparent 25%,
                transparent 50%,
                rgba(255, 255, 255, 0.2) 50%,
                rgba(255, 255, 255, 0.2) 75%,
                transparent 75%,
                transparent
            );
            background-size: 20px 20px;
            animation: move 1s linear infinite;
        }
        
        @keyframes move {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 20px 0;
            }
        }
        
        .progress-text {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 8px;
            width: 20px;
            text-align: center;
        }
        
        .card-body {
            padding: 2.5rem;
        }
        
        .floating-element {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
            }
        }
        
        .countup {
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .info-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .info-card h6 {
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .info-card p {
            margin-bottom: 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .remaining-days {
            font-weight: 700;
            color: #4facfe;
        }
        
        @media (max-width: 768px) {
            .status-btn {
                min-width: 100%;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
        }
        
        /* Particles background */
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }
    </style>
</head>
<body>
    <!-- Particles Background -->
    <div id="particles-js"></div>
    
    <div class="container">
        <!-- Header Navigation -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="/../../dashboard.php" class="btn btn-outline-light floating-element">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
            <h1 class="page-title animate__animated animate__fadeIn">Edit Target Finansial</h1>
            <div>
                <a href="read.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-list me-1"></i>Daftar Target
                </a>
                <a href="create.php" class="btn btn-primary pulse">
                    <i class="fas fa-plus me-1"></i>Tambah Baru
                </a>
            </div>
        </div>

        <!-- Main Card -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h4 class="mb-0 text-center"><i class="fas fa-edit me-2"></i>Edit Target Finansial</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success d-flex align-items-center animate__animated animate__bounceIn">
                                <i class="fas fa-check-circle me-2 fa-lg"></i>
                                <span class="fw-bold"><?= $success ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center animate__animated animate__shakeX">
                                <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
                                <span class="fw-bold"><?= $error ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Info Card -->
                        <div class="info-card animate__animated animate__fadeIn">
                            <h6><i class="fas fa-info-circle me-1"></i>Informasi Target</h6>
                            <p>Perbarui detail target finansial Anda. Progress akan otomatis terhitung berdasarkan jumlah yang terkumpul.</p>
                        </div>
                        
                        <form method="POST" id="goalForm">
                            <div class="mb-4">
                                <label for="nama_goal" class="form-label"><i class="fas fa-bullseye"></i>Nama Target</label>
                                <input type="text" class="form-control" id="nama_goal" name="nama_goal" 
                                       value="<?= htmlspecialchars($goal['nama_goal']) ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="target_jumlah" class="form-label"><i class="fas fa-flag"></i>Target Jumlah</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" id="target_jumlah" 
                                                   name="target_jumlah" min="0" 
                                                   value="<?= $goal['target_jumlah'] ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="terkumpul" class="form-label"><i class="fas fa-piggy-bank"></i>Jumlah Terkumpul</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" id="terkumpul" 
                                                   name="terkumpul" min="0" 
                                                   value="<?= $goal['terkumpul'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="progress-container">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="form-label"><i class="fas fa-chart-line"></i>Progress Target</span>
                                    <span class="countup" id="progressPercentage">
                                        <?= ($goal['target_jumlah'] > 0) ? number_format(min(100, ($goal['terkumpul'] / $goal['target_jumlah']) * 100), 1) : 0 ?>%
                                    </span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" 
                                         style="width: <?= ($goal['target_jumlah'] > 0) ? min(100, ($goal['terkumpul'] / $goal['target_jumlah']) * 100) : 0 ?>%">
                                    </div>
                                </div>
                                <div class="progress-text">
                                    <span id="collectedAmount">Rp <?= number_format($goal['terkumpul'], 0, ',', '.') ?></span>
                                    <span id="targetAmount">Rp <?= number_format($goal['target_jumlah'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="tenggat_waktu" class="form-label"><i class="fas fa-calendar-alt"></i>Tenggat Waktu</label>
                                <div class="date-input-group">
                                    <input type="date" class="form-control" id="tenggat_waktu" name="tenggat_waktu" 
                                           value="<?= $goal['tenggat_waktu'] ?>" required>
                                    <button type="button" class="calendar-btn" id="calendarBtn">
                                        <i class="fas fa-calendar-alt"></i>
                                    </button>
                                </div>
                                <?php
                                $today = new DateTime();
                                $deadline = new DateTime($goal['tenggat_waktu']);
                                $interval = $today->diff($deadline);
                                $remainingDays = $interval->format('%r%a');
                                ?>
                                <small class="text-muted mt-1 d-block">
                                    Sisa waktu: <span class="remaining-days"><?= $remainingDays ?> hari</span>
                                </small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="deskripsi" class="form-label"><i class="fas fa-align-left"></i>Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" placeholder="Tambahkan deskripsi atau catatan tentang target finansial ini..."><?= htmlspecialchars($goal['deskripsi']) ?></textarea>
                                <small class="text-muted mt-1">Opsional: Jelaskan tujuan, rencana, atau motivasi di balik target ini.</small>
                            </div>
                            
                            <!-- Status Toggle Buttons -->
                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-tasks"></i>Status</label>
                                <div class="status-toggle">
                                    <div class="status-btn aktif <?= $goal['status'] == 'aktif' ? 'active' : '' ?>" data-value="aktif">
                                        <div class="btn-content">
                                            <i class="fas fa-play-circle me-2"></i>Aktif
                                        </div>
                                    </div>
                                    <div class="status-btn tercapai <?= $goal['status'] == 'tercapai' ? 'active' : '' ?>" data-value="tercapai">
                                        <div class="btn-content">
                                            <i class="fas fa-check-circle me-2"></i>Tercapai
                                        </div>
                                    </div>
                                    <div class="status-btn dibatalkan <?= $goal['status'] == 'dibatalkan' ? 'active' : '' ?>" data-value="dibatalkan">
                                        <div class="btn-content">
                                            <i class="fas fa-times-circle me-2"></i>Dibatalkan
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="status" name="status" value="<?= $goal['status'] ?>">
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="read.php" class="btn btn-outline-light me-md-2 px-4">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i>Perbarui Target
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
        // Initialize particles background
        document.addEventListener('DOMContentLoaded', function() {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 60, density: { enable: true, value_area: 800 } },
                    color: { value: "#ffffff" },
                    shape: { type: "circle" },
                    opacity: { value: 0.3, random: true },
                    size: { value: 3, random: true },
                    line_linked: {
                        enable: true,
                        distance: 150,
                        color: "#ffffff",
                        opacity: 0.2,
                        width: 1
                    },
                    move: {
                        enable: true,
                        speed: 2,
                        direction: "none",
                        random: true,
                        straight: false,
                        out_mode: "out",
                        bounce: false
                    }
                },
                interactivity: {
                    detect_on: "canvas",
                    events: {
                        onhover: { enable: true, mode: "repulse" },
                        onclick: { enable: true, mode: "push" },
                        resize: true
                    }
                },
                retina_detect: true
            });
            
            // Calendar button functionality
            document.getElementById('calendarBtn').addEventListener('click', function() {
                document.getElementById('tenggat_waktu').showPicker();
            });
            
            // Auto-update progress when page loads
            updateProgressBar();
        });
        
        // Status toggle functionality
        document.querySelectorAll('.status-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.status-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update hidden input value
                document.getElementById('status').value = this.getAttribute('data-value');
                
                // Add animation effect
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
        
        // Progress bar update when terkumpul changes
        document.getElementById('terkumpul').addEventListener('input', function() {
            updateProgressBar();
        });
        
        // Progress bar update when target changes
        document.getElementById('target_jumlah').addEventListener('input', function() {
            updateProgressBar();
        });
        
        function updateProgressBar() {
            const target = parseFloat(document.getElementById('target_jumlah').value) || 0;
            const collected = parseFloat(document.getElementById('terkumpul').value) || 0;
            
            if (target > 0) {
                const progress = Math.min(100, (collected / target) * 100);
                document.querySelector('.progress-bar').style.width = `${progress}%`;
                
                // Update progress text with animation
                const percentageElement = document.getElementById('progressPercentage');
                animateCountUp(percentageElement, progress.toFixed(1) + '%');
                
                // Update amounts with formatting
                document.getElementById('collectedAmount').textContent = 
                    'Rp ' + collected.toLocaleString('id-ID');
                document.getElementById('targetAmount').textContent = 
                    'Rp ' + target.toLocaleString('id-ID');
                
                // Auto-update status if collected >= target
                if (collected >= target) {
                    document.querySelectorAll('.status-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    document.querySelector('.status-btn.tercapai').classList.add('active');
                    document.getElementById('status').value = 'tercapai';
                }
            }
        }
        
        // Count up animation for numbers
        function animateCountUp(element, targetValue) {
            const currentValue = parseFloat(element.textContent);
            const target = parseFloat(targetValue);
            const duration = 800;
            const steps = 60;
            const increment = (target - currentValue) / steps;
            let currentStep = 0;
            
            const timer = setInterval(() => {
                currentStep++;
                const newValue = currentValue + (increment * currentStep);
                
                if (currentStep >= steps) {
                    element.textContent = targetValue;
                    clearInterval(timer);
                } else {
                    element.textContent = newValue.toFixed(1) + '%';
                }
            }, duration / steps);
        }
        
        // Form validation and confirmation
        document.getElementById('goalForm').addEventListener('submit', function(e) {
            const target = parseFloat(document.getElementById('target_jumlah').value) || 0;
            const collected = parseFloat(document.getElementById('terkumpul').value) || 0;
            
            if (collected > target) {
                if (!confirm('Jumlah terkumpul melebihi target. Apakah Anda yakin ingin melanjutkan?')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // Add loading state to submit button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memperbarui...';
            submitBtn.disabled = true;
            
            return true;
        });
        
        // Date validation for deadline
        document.getElementById('tenggat_waktu').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert('Peringatan: Tenggat waktu yang dipilih sudah lewat dari hari ini.');
            }
        });
    </script>
</body>
</html>