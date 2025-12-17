<?php
session_start();
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/koneksi.php';

// Cek apakah user adalah superadmin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_level'] !== 'superadmin') {
    header('Location: ../auth/login.php');
    exit();
}

$db = new Database();
$message = '';

// Handle backup request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    $backup_type = $_POST['backup_type'];
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    
    // Generate filename
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.sql';
    $filepath = __DIR__ . '/../../../backups/' . $filename;
    
    // Create backups directory if not exists
    if (!is_dir(__DIR__ . '/../../../backups')) {
        mkdir(__DIR__ . '/../../../backups', 0777, true);
    }
    
    try {
        // Get database credentials from config
        require_once __DIR__ . '/../../php/config.php';
        
        // Create backup using mysqldump (requires exec permission)
        $command = "mysqldump --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " " . DB_NAME . " > " . $filepath;
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            // Save backup record to database
            $db->query("INSERT INTO backups (filename, filepath, type, description, size, created_by) 
                       VALUES (:filename, :filepath, :type, :description, :size, :created_by)");
            $db->bind(':filename', $filename);
            $db->bind(':filepath', $filepath);
            $db->bind(':type', $backup_type);
            $db->bind(':description', $description);
            $db->bind(':size', filesize($filepath));
            $db->bind(':created_by', $_SESSION['admin_id']);
            $db->execute();
            
            $message = "success:Backup berhasil dibuat: " . $filename;
        } else {
            $message = "error:Gagal membuat backup. Periksa konfigurasi server.";
        }
    } catch (Exception $e) {
        $message = "error:" . $e->getMessage();
    }
}

// Handle restore request
if (isset($_GET['action']) && $_GET['action'] === 'restore' && isset($_GET['id'])) {
    $db->query("SELECT * FROM backups WHERE id = :id");
    $db->bind(':id', $_GET['id']);
    $backup = $db->single();
    
    if ($backup && file_exists($backup['filepath'])) {
        $command = "mysql --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " " . DB_NAME . " < " . $backup['filepath'];
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            $message = "success:Database berhasil direstore dari backup: " . $backup['filename'];
        } else {
            $message = "error:Gagal restore database.";
        }
    } else {
        $message = "error:File backup tidak ditemukan.";
    }
}

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $db->query("SELECT * FROM backups WHERE id = :id");
    $db->bind(':id', $_GET['id']);
    $backup = $db->single();
    
    if ($backup) {
        // Delete file
        if (file_exists($backup['filepath'])) {
            unlink($backup['filepath']);
        }
        
        // Delete record
        $db->query("DELETE FROM backups WHERE id = :id");
        $db->bind(':id', $_GET['id']);
        $db->execute();
        
        $message = "success:Backup berhasil dihapus.";
    }
}

// Get all backups
$db->query("SELECT b.*, a.nama as creator_name FROM backups b 
           LEFT JOIN admins a ON b.created_by = a.id 
           ORDER BY b.created_at DESC");
$backups = $db->resultSet();

// Calculate backup stats
$total_size = 0;
foreach ($backups as $backup) {
    $total_size += $backup['size'];
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Database - Superadmin</title>
    <link rel="icon" type="image/png" href="../../../assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .backup-card {
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }
        .backup-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .backup-type-full {
            border-left: 5px solid #28a745;
        }
        .backup-type-partial {
            border-left: 5px solid #007bff;
        }
        .backup-type-structure {
            border-left: 5px solid #6c757d;
        }
        .file-size {
            font-family: monospace;
        }
        .progress-backup {
            height: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ . '/../../../php/includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-0">Backup & Restore Database</h3>
                        <p class="text-muted mb-0">Kelola backup dan restore data</p>
                    </div>
                    <div>
                        <span class="badge bg-info me-3">
                            <?= count($backups) ?> Backup
                        </span>
                        <span class="badge bg-warning">
                            <?= round($total_size / 1024 / 1024, 2) ?> MB
                        </span>
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if ($message): 
                    $msg_type = explode(':', $message)[0];
                    $msg_text = explode(':', $message)[1];
                ?>
                    <div class="alert alert-<?= $msg_type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                        <?= $msg_text ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Backup Stats -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Backup</h5>
                                <h2 class="mb-0"><?= count($backups) ?></h2>
                                <small><?= round($total_size / 1024 / 1024, 2) ?> MB total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Backup Hari Ini</h5>
                                <?php
                                $db->query("SELECT COUNT(*) as total FROM backups WHERE DATE(created_at) = CURDATE()");
                                $today = $db->single()['total'];
                                ?>
                                <h2 class="mb-0"><?= $today ?></h2>
                                <small><?= date('d M Y') ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Database Size</h5>
                                <?php
                                $db->query("SELECT 
                                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                                    FROM information_schema.tables 
                                    WHERE table_schema = :dbname");
                                $db->bind(':dbname', DB_NAME);
                                $db_size = $db->single()['size_mb'];
                                ?>
                                <h2 class="mb-0"><?= $db_size ?> MB</h2>
                                <small>Ukuran saat ini</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Space Available</h5>
                                <?php
                                $free_space = round(disk_free_space(__DIR__) / 1024 / 1024 / 1024, 2);
                                ?>
                                <h2 class="mb-0"><?= $free_space ?> GB</h2>
                                <small>Disk space tersedia</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Create Backup Form -->
                <div class="card mb-4">
                    <div class="card-header bg-dark">
                        <h5 class="mb-0">Buat Backup Baru</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tipe Backup</label>
                                    <select name="backup_type" class="form-select" required>
                                        <option value="full">Full Backup (Semua data)</option>
                                        <option value="partial">Partial Backup (Data penting)</option>
                                        <option value="structure">Structure Only (Tanpa data)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <input type="text" name="description" class="form-control" 
                                           placeholder="Contoh: Backup sebelum update sistem">
                                </div>
                                <div class="col-md-2 d-flex align-items-end mb-3">
                                    <button type="submit" name="create_backup" class="btn btn-primary w-100">
                                        <i class="fas fa-database me-2"></i> Backup
                                    </button>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Perhatian:</strong> Proses backup mungkin memakan waktu beberapa menit 
                                tergantung ukuran database. Jangan tutup halaman ini selama proses berlangsung.
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Backup List -->
                <div class="card">
                    <div class="card-header bg-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Backup</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-light" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($backups)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada backup</h5>
                                <p class="text-muted">Buat backup pertama Anda menggunakan form di atas</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama File</th>
                                            <th>Tipe</th>
                                            <th>Ukuran</th>
                                            <th>Dibuat</th>
                                            <th>Oleh</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($backups as $backup): 
                                            $file_time = strtotime($backup['created_at']);
                                            $time_diff = time() - $file_time;
                                            $days_old = floor($time_diff / (60 * 60 * 24));
                                        ?>
                                        <tr class="backup-type-<?= $backup['type'] ?>">
                                            <td>
                                                <i class="fas fa-database me-2 text-primary"></i>
                                                <strong><?= $backup['filename'] ?></strong>
                                                <?php if ($backup['description']): ?>
                                                    <br><small class="text-muted"><?= $backup['description'] ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $backup['type'] === 'full' ? 'success' : 
                                                    ($backup['type'] === 'partial' ? 'primary' : 'secondary')
                                                ?>">
                                                    <?= ucfirst($backup['type']) ?>
                                                </span>
                                            </td>
                                            <td class="file-size">
                                                <?= round($backup['size'] / 1024 / 1024, 2) ?> MB
                                            </td>
                                            <td>
                                                <?= date('d/m/Y H:i', $file_time) ?>
                                                <br>
                                                <small class="text-<?= $days_old > 30 ? 'danger' : 'muted' ?>">
                                                    <?= $days_old ?> hari lalu
                                                </small>
                                            </td>
                                            <td><?= htmlspecialchars($backup['creator_name'] ?? 'System') ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="../../../backups/<?= $backup['filename'] ?>" 
                                                       class="btn btn-info" 
                                                       download
                                                       title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <a href="?action=restore&id=<?= $backup['id'] ?>" 
                                                       class="btn btn-warning"
                                                       onclick="return confirm('Restore database dari backup ini? Semua data saat ini akan diganti.')"
                                                       title="Restore">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?= $backup['id'] ?>" 
                                                       class="btn btn-danger"
                                                       onclick="return confirm('Hapus backup ini secara permanen?')"
                                                       title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Backup Management -->
                            <div class="mt-4 border-top pt-3">
                                <h6 class="mb-3">Manajemen Backup</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-grid">
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="cleanupOldBackups()">
                                                <i class="fas fa-broom me-2"></i> 
                                                Bersihkan Backup Lama (>30 hari)
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-grid">
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteAllBackups()">
                                                <i class="fas fa-trash-alt me-2"></i> 
                                                Hapus Semua Backup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Backup Schedule -->
                <div class="card mt-4">
                    <div class="card-header bg-dark">
                        <h5 class="mb-0">Jadwal Backup Otomatis</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <p class="mb-0">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    Backup otomatis akan dibuat setiap hari pukul 02:00 waktu server.
                                    Backup akan disimpan selama 30 hari.
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="autoBackup" checked>
                                    <label class="form-check-label" for="autoBackup">
                                        Aktifkan Backup Otomatis
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Frekuensi</label>
                                <select class="form-select" id="backupFrequency">
                                    <option value="daily" selected>Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Waktu</label>
                                <input type="time" class="form-control" id="backupTime" value="02:00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Simpan Selama</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="retentionDays" value="30" min="1" max="365">
                                    <span class="input-group-text">hari</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" onclick="saveSchedule()">
                                <i class="fas fa-save me-2"></i> Simpan Jadwal
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cleanupOldBackups() {
            if (confirm('Bersihkan backup yang lebih lama dari 30 hari?')) {
                fetch('../../../ajax/cleanup-backups.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                    });
            }
        }
        
        function deleteAllBackups() {
            if (confirm('HAPUS SEMUA BACKUP? Tindakan ini tidak dapat dibatalkan!')) {
                if (prompt('Ketik "DELETE" untuk konfirmasi:') === 'DELETE') {
                    fetch('../../../ajax/delete-all-backups.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            alert('Error: ' + error.message);
                        });
                }
            }
        }
        
        function saveSchedule() {
            const frequency = document.getElementById('backupFrequency').value;
            const time = document.getElementById('backupTime').value;
            const days = document.getElementById('retentionDays').value;
            const enabled = document.getElementById('autoBackup').checked;
            
            const data = {
                frequency: frequency,
                time: time,
                retention_days: days,
                enabled: enabled
            };
            
            fetch('../../../ajax/save-backup-schedule.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Jadwal backup berhasil disimpan!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
        
        // Check disk space regularly
        setInterval(() => {
            fetch('../../../ajax/check-disk-space.php')
                .then(response => response.json())
                .then(data => {
                    if (data.free_space_gb < 1) {
                        document.querySelector('.card.bg-warning').classList.add('bg-danger');
                        document.querySelector('.card.bg-warning .card-title').innerHTML = 
                            '<i class="fas fa-exclamation-triangle me-2"></i>Low Disk Space';
                    }
                });
        }, 60000);
    </script>
</body>
</html>