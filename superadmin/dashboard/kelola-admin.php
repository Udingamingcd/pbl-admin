<?php
session_start();

/*
|--------------------------------------------------------------------------
| Load Config & Koneksi (PATH SUDAH BENAR)
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/koneksi.php';

/*
|--------------------------------------------------------------------------
| Cek Akses Superadmin
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_level'] !== 'superadmin') {
    header('Location: ../auth/login.php');
    exit();
}

$db = new Database();

/*
|--------------------------------------------------------------------------
| Handle Action (DELETE / TOGGLE STATUS)
|--------------------------------------------------------------------------
*/
if (isset($_GET['action'])) {

    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $db->query("DELETE FROM admins WHERE id = :id AND level = 'admin'");
        $db->bind(':id', $_GET['id']);
        $db->execute();

        $_SESSION['success'] = "Admin berhasil dihapus!";
        header('Location: kelola-admin.php');
        exit();
    }

    if ($_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
        $db->query("
            UPDATE admins 
            SET status = IF(status = 'aktif', 'nonaktif', 'aktif') 
            WHERE id = :id
        ");
        $db->bind(':id', $_GET['id']);
        $db->execute();

        $_SESSION['success'] = "Status admin berhasil diubah!";
        header('Location: kelola-admin.php');
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Tambah Admin Baru
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_admin'])) {

    $nama     = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_SPECIAL_CHARS);
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $db->query("
            INSERT INTO admins (nama, email, password, level, status, created_by) 
            VALUES (:nama, :email, :password, 'admin', 'aktif', :created_by)
        ");
        $db->bind(':nama', $nama);
        $db->bind(':email', $email);
        $db->bind(':password', $password);
        $db->bind(':created_by', $_SESSION['admin_id']);
        $db->execute();

        $_SESSION['success'] = "Admin berhasil ditambahkan!";
        header('Location: kelola-admin.php');
        exit();

    } catch (Exception $e) {
        $error = "Email sudah terdaftar!";
    }
}

/*
|--------------------------------------------------------------------------
| Ambil Data Admin
|--------------------------------------------------------------------------
*/
$db->query("
    SELECT a.*, creator.nama AS creator_name
    FROM admins a
    LEFT JOIN admins creator ON a.created_by = creator.id
    WHERE a.level = 'admin'
    ORDER BY a.created_at DESC
");
$admins = $db->resultSet();
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Kelola Admin - Superadmin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="../../assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../../php/includes/sidebar.php'; ?>

        <!-- CONTENT -->
        <main class="col-md-9 col-lg-10 px-4 py-4">
            <h3 class="mb-4">Kelola Admin</h3>

            <!-- SUCCESS MESSAGE -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- ERROR MESSAGE -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <!-- FORM TAMBAH ADMIN -->
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Tambah Admin Baru</strong>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <input type="text" name="nama" class="form-control" placeholder="Nama Admin" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Password" minlength="8" required>
                            </div>
                        </div>
                        <button type="submit" name="tambah_admin" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Admin
                        </button>
                    </form>
                </div>
            </div>

            <!-- TABEL ADMIN -->
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <strong>Daftar Admin</strong>
                    <span class="badge bg-info">Total: <?= count($admins) ?></span>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td>#<?= $admin['id'] ?></td>
                                <td><?= htmlspecialchars($admin['nama']) ?></td>
                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $admin['status'] === 'aktif' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($admin['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($admin['creator_name'] ?? '-') ?></td>
                                <td><?= date('d/m/Y', strtotime($admin['created_at'])) ?></td>
                                <td>
                                    <a href="?action=toggle_status&id=<?= $admin['id'] ?>"
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-sync"></i>
                                    </a>
                                    <a href="?action=delete&id=<?= $admin['id'] ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Yakin hapus admin ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
