<?php
define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/middleware/auth.php';
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/koneksi.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new Database();
        
        // Format numbers from formatted input (remove thousand separators)
        $target_jumlah = isset($_POST['target_jumlah']) ? str_replace('.', '', $_POST['target_jumlah']) : 0;
        $terkumpul = isset($_POST['terkumpul']) ? str_replace('.', '', $_POST['terkumpul']) : 0;
        
        // Auto update status if terkumpul >= target_jumlah
        $status = ($terkumpul >= $target_jumlah) ? 'tercapai' : ($_POST['status'] ?? 'aktif');
        
        $db->query('INSERT INTO financial_goal (user_id, nama_goal, target_jumlah, terkumpul, tenggat_waktu, deskripsi, status) 
                   VALUES (:user_id, :nama_goal, :target_jumlah, :terkumpul, :tenggat_waktu, :deskripsi, :status)');
        
        $db->bind(':user_id', $_SESSION['user_id']);
        $db->bind(':nama_goal', $_POST['nama_goal']);
        $db->bind(':target_jumlah', $target_jumlah);
        $db->bind(':terkumpul', $terkumpul ?? 0);
        $db->bind(':tenggat_waktu', $_POST['tenggat_waktu']);
        $db->bind(':deskripsi', $_POST['deskripsi']);
        $db->bind(':status', $status);
        
        if ($db->execute()) {
            $_SESSION['success_message'] = 'Target finansial berhasil ditambahkan!';
            header('Location: read.php');
            exit();
        } else {
            $error = 'Gagal menambahkan target finansial';
        }
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Format values for display
$target_jumlah_display = isset($_POST['target_jumlah']) ? number_format($_POST['target_jumlah'], 0, ',', '.') : '';
$terkumpul_display = isset($_POST['terkumpul']) ? number_format($_POST['terkumpul'], 0, ',', '.') : '0';
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Target Finansial - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3B82F6;
            --primary-dark: #2563EB;
            --primary-light: #60A5FA;
            --secondary: #8B5CF6;
            --success: #10B981;
            --success-light: #34D399;
            --warning: #F59E0B;
            --warning-light: #FBBF24;
            --danger: #EF4444;
            --danger-light: #F87171;
            --dark: #111827;
            --darker: #0A0F1C;
            --light: #F9FAFB;
            --lighter: #FFFFFF;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --gray-900: #111827;
            
            --glass: rgba(255, 255, 255, 0.1);
            --glass-dark: rgba(0, 0, 0, 0.3);
            --glass-border: rgba(255, 255, 255, 0.15);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 25px 50px rgba(0, 0, 0, 0.3);
            --shadow-xl: 0 35px 60px rgba(0, 0, 0, 0.4);
            --radius: 24px;
            --radius-lg: 32px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, #1E293B 100%);
            min-height: 100vh;
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--light);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Enhanced Background with Better Contrast */
        .background-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: 
                radial-gradient(circle at 15% 20%, rgba(59, 130, 246, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 85% 30%, rgba(139, 92, 246, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 50% 80%, rgba(16, 185, 129, 0.08) 0%, transparent 40%);
        }
        
        .noise-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><filter id="a"><feTurbulence baseFrequency=".005" numOctaves="3"/></filter><rect width="100%" height="100%" filter="url(%23a)" opacity=".03"/></svg>');
            z-index: -1;
            pointer-events: none;
        }
        
        .app-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 2rem 1rem;
            position: relative;
        }
        
        /* Enhanced Header with Better Typography */
        .header {
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
        }
        
        .page-title {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
            line-height: 1.1;
            text-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
        }
        
        .page-subtitle {
            font-size: 1.3rem;
            color: var(--gray-300);
            font-weight: 500;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.5;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        
        .form-wrapper {
            width: 100%;
            max-width: 1200px;
        }
        
        /* Premium Glass Card */
        .glass-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.02) 100%);
            backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            box-shadow: 
                var(--shadow-xl),
                inset 0 1px 0 rgba(255, 255, 255, 0.1),
                inset 0 -1px 0 rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, transparent 30%, transparent 70%, rgba(139, 92, 246, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }
        
        .glass-card:hover::before {
            opacity: 1;
        }
        
        .glass-card:hover {
            transform: translateY(-8px) scale(1.005);
            box-shadow: 
                var(--shadow-xl),
                0 0 0 1px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }
        
        /* Enhanced Card Header */
        .card-header {
            padding: 3rem 3rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, transparent 100%);
        }
        
        .card-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 3rem;
            right: 3rem;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
        }
        
        .card-title {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            font-size: 2rem;
            font-weight: 700;
            color: var(--lighter);
            line-height: 1.2;
        }
        
        .card-title-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 
                0 8px 32px rgba(59, 130, 246, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(2deg); }
        }
        
        .card-body {
            padding: 3rem;
        }
        
        /* Enhanced Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            margin-bottom: 2rem;
        }
        
        .form-section {
            margin-bottom: 3rem;
            position: relative;
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--lighter);
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            position: relative;
            line-height: 1.3;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
            transition: width 0.4s ease;
        }
        
        .section-title:hover::after {
            width: 120px;
        }
        
        .section-title i {
            color: var(--primary-light);
            font-size: 1.6rem;
            width: 40px;
            text-align: center;
        }
        
        /* Enhanced Form Controls */
        .form-group {
            margin-bottom: 2.5rem;
            position: relative;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: var(--lighter);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            letter-spacing: -0.01em;
        }
        
        .form-label .required {
            color: var(--danger-light);
            margin-left: 4px;
        }
        
        .input-wrapper {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .form-control {
            width: 100%;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            color: var(--lighter);
            padding: 1.3rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            line-height: 1.5;
        }
        
        .form-control::placeholder {
            color: var(--gray-400);
            font-weight: 400;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 
                0 0 0 4px rgba(59, 130, 246, 0.15),
                0 8px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .form-control:hover:not(:focus) {
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        
        .input-icon {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus + .input-icon {
            color: var(--primary-light);
            transform: translateY(-50%) scale(1.2);
        }
        
        /* Enhanced Currency Input */
        .currency-input {
            position: relative;
        }
        
        .currency-symbol {
            position: absolute;
            left: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-light);
            font-weight: 700;
            font-size: 1.4rem;
            z-index: 2;
        }
        
        .currency-input .form-control {
            padding-left: 4.5rem;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        /* Enhanced Status Buttons */
        .status-buttons {
            margin-top: 1rem;
        }
        
        .status-button-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }
        
        .status-radio {
            display: none;
        }
        
        .status-button {
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .status-button:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .status-radio:checked + .status-button {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-color: var(--primary);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
        }
        
        .status-radio:checked + .status-button::before {
            content: '';
            position: absolute;
            top: 10px;
            right: 10px;
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
        }
        
        .status-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .status-radio:checked + .status-button .status-icon {
            background: rgba(59, 130, 246, 0.2);
        }
        
        #status_aktif:checked + .status-button .status-icon {
            color: var(--primary);
        }
        
        #status_tercapai:checked + .status-button .status-icon {
            color: var(--success);
        }
        
        #status_dibatalkan:checked + .status-button .status-icon {
            color: var(--danger);
        }
        
        .status-content {
            flex: 1;
        }
        
        .status-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--lighter);
            margin-bottom: 0.25rem;
        }
        
        .status-desc {
            font-size: 0.9rem;
            color: var(--gray-400);
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
            border-radius: 16px 0 0 16px;
        }
        
        .calendar-btn {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 60px;
            background: rgba(59, 130, 246, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-left: none;
            border-radius: 0 16px 16px 0;
            color: var(--lighter);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.3rem;
        }
        
        .calendar-btn:hover {
            background: rgba(59, 130, 246, 0.5);
            color: white;
        }
        
        .calendar-btn:active {
            transform: scale(0.95);
        }
        
        /* Premium Progress Section */
        .progress-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
            border-radius: 20px;
            padding: 2.5rem;
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .progress-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .progress-section:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.04) 100%);
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .progress-label {
            font-weight: 700;
            color: var(--lighter);
            font-size: 1.2rem;
        }
        
        .progress-percentage {
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .progress-bar-container {
            position: relative;
            height: 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 10px;
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: shimmer 2.5s infinite;
        }
        
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .progress-text {
            font-size: 1.1rem;
            color: var(--gray-300);
            text-align: center;
            font-weight: 500;
            line-height: 1.4;
        }
        
        /* Enhanced Date Info */
        .date-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1.5rem;
            font-size: 1.1rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .date-info.urgent {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger-light);
            border: 1px solid rgba(239, 68, 68, 0.3);
            animation: pulseWarning 2s infinite;
        }
        
        .date-info.warning {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning-light);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        
        .date-info.normal {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success-light);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        /* Enhanced Character Counter */
        .character-count {
            text-align: right;
            font-size: 0.95rem;
            margin-top: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .character-count.warning {
            color: var(--warning-light);
        }
        
        .character-count.danger {
            color: var(--danger-light);
            animation: shake 0.5s ease-in-out;
        }
        
        /* Premium Form Actions */
        .form-actions {
            display: flex;
            gap: 2rem;
            justify-content: flex-end;
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn {
            padding: 1.3rem 3rem;
            border: none;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 
                0 8px 32px rgba(59, 130, 246, 0.3),
                0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 
                0 15px 40px rgba(59, 130, 246, 0.4),
                0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: var(--lighter);
        }
        
        .btn-outline:hover {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.1);
            transform: translateY(-3px);
        }
        
        /* Enhanced Alerts */
        .alert {
            padding: 1.8rem 2.5rem;
            border-radius: 16px;
            margin-bottom: 3rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            border: none;
            animation: slideInDown 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            line-height: 1.5;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success-light);
            border-left: 4px solid var(--success);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger-light);
            border-left: 4px solid var(--danger);
        }
        
        .alert-icon {
            font-size: 2rem;
            flex-shrink: 0;
        }
        
        /* Tips Section */
        .tips-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .tip-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .tip-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }
        
        .tip-item i {
            font-size: 1.3rem;
            width: 30px;
            flex-shrink: 0;
        }
        
        .tip-item span {
            color: var(--gray-200);
            font-weight: 500;
            line-height: 1.4;
        }
        
        /* Animations */
        @keyframes pulseWarning {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.5);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .form-grid {
                gap: 3rem;
            }
        }
        
        @media (max-width: 968px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            
            .card-body {
                padding: 2.5rem;
            }
            
            .page-title {
                font-size: 3rem;
            }
            
            .status-button-group {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .card-title {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }
            
            .card-header {
                padding: 2.5rem 2rem 2rem;
            }
            
            .card-body {
                padding: 2rem;
            }
            
            .page-title {
                font-size: 2.5rem;
            }
            
            .page-subtitle {
                font-size: 1.1rem;
            }
            
            .status-button-group {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .status-button {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem 1rem;
            }
            
            .status-icon {
                width: 40px;
                height: 40px;
                font-size: 1.3rem;
            }
            
            .status-content {
                text-align: center;
            }
        }
        
        /* Utility Classes for Better Readability */
        .text-highlight {
            color: var(--lighter);
            font-weight: 600;
        }
        
        .text-muted {
            color: var(--gray-400);
        }
        
        .text-success {
            color: var(--success-light);
        }
        
        .text-warning {
            color: var(--warning-light);
        }
        
        .text-danger {
            color: var(--danger-light);
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--dark);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
    <!-- Enhanced Background -->
    <div class="background-wrapper"></div>
    <div class="noise-overlay"></div>

    <div class="app-container">
        <!-- Premium Header -->
        <div class="header animate__animated animate__fadeInDown">
            <h1 class="page-title">Buat Target Finansial Baru</h1>
            <p class="page-subtitle">Rencanakan masa depan finansial Anda dengan tools yang powerful dan mudah digunakan</p>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="form-wrapper">
                <div class="glass-card animate__animated animate__fadeInUp">
                    <!-- Premium Card Header -->
                    <div class="card-header">
                        <div class="card-title">
                            <div class="card-title-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div>
                                <div>Formulir Target Finansial</div>
                                <div class="text-muted" style="font-size: 1.1rem; margin-top: 0.5rem;">
                                    Isi detail target Anda dengan lengkap dan akurat
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Card Body -->
                    <div class="card-body">
                        <!-- Premium Alert Messages -->
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle alert-icon"></i>
                                <div>
                                    <div class="text-highlight" style="font-size: 1.2rem; margin-bottom: 0.5rem;">Sukses!</div>
                                    <div><?= $success ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-triangle alert-icon"></i>
                                <div>
                                    <div class="text-highlight" style="font-size: 1.2rem; margin-bottom: 0.5rem;">Perhatian!</div>
                                    <div><?= $error ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="financialForm">
                            <div class="form-grid">
                                <!-- Left Column - Target Information -->
                                <div>
                                    <div class="form-section">
                                        <h3 class="section-title">
                                            <i class="fas fa-bullseye"></i>
                                            Informasi Target
                                        </h3>
                                        
                                        <div class="form-group">
                                            <label class="form-label">
                                                Nama Target
                                                <span class="required">*</span>
                                            </label>
                                            <div class="input-wrapper">
                                                <input type="text" class="form-control input-with-icon" 
                                                       id="nama_goal" name="nama_goal"
                                                       value="<?= htmlspecialchars($_POST['nama_goal'] ?? '') ?>"
                                                       placeholder="Contoh: Tabungan Liburan ke Bali, Dana Pendidikan Anak, dll."
                                                       required>
                                                <i class="fas fa-tag input-icon"></i>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.9rem; margin-top: 0.5rem;">
                                                Berikan nama yang jelas dan mudah diingat
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">
                                                Target Jumlah
                                                <span class="required">*</span>
                                            </label>
                                            <div class="currency-input">
                                                <span class="currency-symbol">Rp</span>
                                                <input type="text" class="form-control currency-input-field" 
                                                       id="target_jumlah" name="target_jumlah"
                                                       value="<?= $target_jumlah_display ?>"
                                                       placeholder="0"
                                                       required
                                                       data-original-value="<?= $_POST['target_jumlah'] ?? '' ?>">
                                            </div>
                                            <div class="text-muted" style="font-size: 0.9rem; margin-top: 0.5rem;">
                                                Tentukan jumlah total yang ingin Anda kumpulkan
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Jumlah Terkumpul Saat Ini</label>
                                            <div class="currency-input">
                                                <span class="currency-symbol">Rp</span>
                                                <input type="text" class="form-control currency-input-field" 
                                                       id="terkumpul" name="terkumpul"
                                                       value="<?= $terkumpul_display ?>"
                                                       placeholder="0"
                                                       data-original-value="<?= $_POST['terkumpul'] ?? '0' ?>">
                                            </div>
                                            <div class="text-muted" style="font-size: 0.9rem; margin-top: 0.5rem;">
                                                Masukkan jumlah yang sudah terkumpul (jika ada)
                                            </div>
                                        </div>

                                        <!-- Interactive Progress Visualization -->
                                        <div class="progress-section" id="progressSection">
                                            <div class="progress-header">
                                                <span class="progress-label">Progress Pencapaian</span>
                                                <span class="progress-percentage" id="progressPercentage">0%</span>
                                            </div>
                                            <div class="progress-bar-container">
                                                <div class="progress-fill" id="progressFill" style="width: 0%"></div>
                                            </div>
                                            <div class="progress-text" id="progressText">
                                                Tetapkan target jumlah untuk melihat progress visual
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column - Timeline & Additional Info -->
                                <div>
                                    <div class="form-section">
                                        <h3 class="section-title">
                                            <i class="fas fa-calendar-check"></i>
                                            Timeline & Status
                                        </h3>
                                        
                                        <div class="form-group">
                                            <label class="form-label">
                                                Tenggat Waktu
                                                <span class="required">*</span>
                                            </label>
                                            <div class="date-input-group">
                                                <input type="date" class="form-control" 
                                                       id="tenggat_waktu" name="tenggat_waktu"
                                                       value="<?= $_POST['tenggat_waktu'] ?? '' ?>"
                                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                                                       required>
                                                <button type="button" class="calendar-btn" id="calendarBtn">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </button>
                                            </div>
                                            <div class="date-info" id="dateInfo"></div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Status Target</label>
                                            <div class="status-buttons">
                                                <div class="status-button-group">
                                                    <input type="radio" id="status_aktif" name="status" value="aktif" 
                                                           <?= (($_POST['status'] ?? 'aktif') == 'aktif') ? 'checked' : '' ?> 
                                                           class="status-radio">
                                                    <label for="status_aktif" class="status-button">
                                                        <div class="status-icon">
                                                            <i class="fas fa-bullseye"></i>
                                                        </div>
                                                        <div class="status-content">
                                                            <div class="status-title">Aktif</div>
                                                            <div class="status-desc">Sedang berjalan</div>
                                                        </div>
                                                    </label>

                                                    <input type="radio" id="status_tercapai" name="status" value="tercapai" 
                                                           <?= (($_POST['status'] ?? '') == 'tercapai') ? 'checked' : '' ?> 
                                                           class="status-radio">
                                                    <label for="status_tercapai" class="status-button">
                                                        <div class="status-icon">
                                                            <i class="fas fa-check-circle"></i>
                                                        </div>
                                                        <div class="status-content">
                                                            <div class="status-title">Tercapai</div>
                                                            <div class="status-desc">Target sudah terpenuhi</div>
                                                        </div>
                                                    </label>

                                                    <input type="radio" id="status_dibatalkan" name="status" value="dibatalkan" 
                                                           <?= (($_POST['status'] ?? '') == 'dibatalkan') ? 'checked' : '' ?> 
                                                           class="status-radio">
                                                    <label for="status_dibatalkan" class="status-button">
                                                        <div class="status-icon">
                                                            <i class="fas fa-times-circle"></i>
                                                        </div>
                                                        <div class="status-content">
                                                            <div class="status-title">Dibatalkan</div>
                                                            <div class="status-desc">Dihentikan sementara</div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.9rem; margin-top: 0.5rem;">
                                                Status akan otomatis berubah ketika target tercapai
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <h3 class="section-title">
                                            <i class="fas fa-edit"></i>
                                            Deskripsi & Catatan
                                        </h3>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Detail Target Finansial</label>
                                            <textarea class="form-control" id="deskripsi" name="deskripsi"
                                                      rows="5" 
                                                      placeholder="Jelaskan tujuan finansial ini secara detail. Apa motivasi Anda? Bagaimana rencana mencapainya? Apakah ada tantangan khusus?"
                                                      maxlength="500"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                                            <div class="character-count" id="charCount">0/500 karakter tersisa</div>
                                        </div>
                                    </div>

                                    <!-- Tips Section -->
                                    <div class="form-section">
                                        <h3 class="section-title">
                                            <i class="fas fa-lightbulb"></i>
                                            Tips Sukses Finansial
                                        </h3>
                                        <div class="tips-container">
                                            <div class="tip-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                <span>Tetapkan target yang SMART (Specific, Measurable, Achievable, Relevant, Time-bound)</span>
                                            </div>
                                            <div class="tip-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                <span>Review progress secara berkala dan sesuaikan strategi jika diperlukan</span>
                                            </div>
                                            <div class="tip-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                <span>Rayakan setiap pencapaian kecil untuk menjaga motivasi</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Form Actions -->
                            <div class="form-actions">
                                <a href="read.php" class="btn btn-outline">
                                    <i class="fas fa-arrow-left"></i>
                                    Kembali ke Daftar
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-paper-plane"></i>
                                    Simpan Target Finansial
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced initialization with better performance
            initializeForm();
            
            function initializeForm() {
                // Elements with better caching
                const elements = {
                    form: document.getElementById('financialForm'),
                    targetInput: document.getElementById('target_jumlah'),
                    collectedInput: document.getElementById('terkumpul'),
                    progressFill: document.getElementById('progressFill'),
                    progressPercentage: document.getElementById('progressPercentage'),
                    progressText: document.getElementById('progressText'),
                    dueDateInput: document.getElementById('tenggat_waktu'),
                    dateInfo: document.getElementById('dateInfo'),
                    descTextarea: document.getElementById('deskripsi'),
                    charCount: document.getElementById('charCount'),
                    submitBtn: document.getElementById('submitBtn'),
                    calendarBtn: document.getElementById('calendarBtn'),
                    statusAktif: document.getElementById('status_aktif'),
                    statusTercapai: document.getElementById('status_tercapai'),
                    statusDibatalkan: document.getElementById('status_dibatalkan')
                };

                let achievementShown = false;

                // Enhanced number formatting for Rupiah
                function formatRupiah(number) {
                    return new Intl.NumberFormat('id-ID').format(number);
                }

                // Format currency input with thousand separators
                function formatCurrencyInput(input) {
                    let value = input.value.replace(/[^\d]/g, '');
                    
                    if (value === '') {
                        input.value = '';
                        input.dataset.originalValue = '';
                        return '';
                    }
                    
                    let numericValue = parseInt(value);
                    input.dataset.originalValue = numericValue;
                    
                    // Format with thousand separators
                    let formattedValue = numericValue.toLocaleString('id-ID');
                    input.value = formattedValue;
                    
                    return numericValue;
                }

                // Get numeric value from formatted input
                function getNumericValue(input) {
                    if (input.dataset.originalValue) {
                        return parseInt(input.dataset.originalValue);
                    }
                    
                    // Fallback: parse formatted value
                    let value = input.value.replace(/[^\d]/g, '');
                    return value ? parseInt(value) : 0;
                }

                // Calendar button functionality
                elements.calendarBtn.addEventListener('click', function() {
                    elements.dueDateInput.showPicker();
                });

                // Optimized progress update with debouncing
                let progressTimeout;
                function updateProgress() {
                    clearTimeout(progressTimeout);
                    progressTimeout = setTimeout(() => {
                        const target = getNumericValue(elements.targetInput);
                        const collected = getNumericValue(elements.collectedInput);
                        
                        if (target > 0) {
                            const percentage = Math.min((collected / target) * 100, 100);
                            
                            // Smooth progress animation
                            elements.progressFill.style.width = `${percentage}%`;
                            elements.progressPercentage.textContent = `${percentage.toFixed(1)}%`;
                            
                            // Enhanced progress text with better readability
                            if (collected > 0) {
                                elements.progressText.innerHTML = `
                                    <span class="text-highlight">Rp ${formatRupiah(collected)}</span> terkumpul dari 
                                    <span class="text-highlight">Rp ${formatRupiah(target)}</span>
                                    <div style="margin-top: 0.5rem; font-size: 0.95rem; color: var(--gray-400);">
                                        ${percentage >= 100 ? '🎉 Target tercapai!' : '💪 Lanjutkan perjuangan!'}
                                    </div>
                                `;
                                
                                // Auto-update status to "tercapai" when target is reached
                                if (percentage >= 100 && !achievementShown) {
                                    elements.statusTercapai.checked = true;
                                    showAchievement();
                                    achievementShown = true;
                                } else if (percentage < 100) {
                                    achievementShown = false;
                                }
                            } else {
                                elements.progressText.innerHTML = `
                                    🚀 <span class="text-highlight">Mulai menabung</span> untuk mencapai target Anda!
                                    <div style="margin-top: 0.5rem; font-size: 0.95rem; color: var(--gray-400);">
                                        Setiap langkah kecil membawa Anda lebih dekat ke tujuan
                                    </div>
                                `;
                            }
                        } else {
                            elements.progressFill.style.width = '0%';
                            elements.progressPercentage.textContent = '0%';
                            elements.progressText.innerHTML = `
                                🎯 <span class="text-highlight">Tetapkan target</span> untuk memulai perjalanan finansial Anda
                                <div style="margin-top: 0.5rem; font-size: 0.95rem; color: var(--gray-400);">
                                    Masukkan jumlah target di atas untuk melihat progress visual
                                </div>
                            `;
                        }
                    }, 100);
                }

                // Enhanced date calculation
                function updateDaysRemaining() {
                    const selectedDate = new Date(elements.dueDateInput.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (selectedDate && selectedDate > today) {
                        const diffTime = selectedDate - today;
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        
                        let urgencyClass = 'normal';
                        let icon = '📅';
                        let message = `Masih ada waktu ${diffDays} hari untuk mencapai target`;
                        
                        if (diffDays <= 7) {
                            urgencyClass = 'warning';
                            icon = '⏰';
                            message = `Cepat! Tinggal ${diffDays} hari lagi menuju tenggat waktu`;
                        }
                        if (diffDays <= 3) {
                            urgencyClass = 'urgent';
                            icon = '🚨';
                            message = `Mendesak! Hanya ${diffDays} hari tersisa!`;
                        }
                        if (diffDays > 30) {
                            message = `Anda memiliki ${diffDays} hari untuk mencapainya - Rencanakan dengan baik!`;
                        }
                        
                        elements.dateInfo.className = `date-info ${urgencyClass}`;
                        elements.dateInfo.innerHTML = `
                            ${icon}
                            <span>${message}</span>
                        `;
                    } else {
                        elements.dateInfo.innerHTML = '';
                    }
                }

                // Enhanced character counter
                function updateCharCount() {
                    const count = elements.descTextarea.value.length;
                    const remaining = 500 - count;
                    
                    elements.charCount.textContent = `${count}/500 karakter (${remaining} tersisa)`;
                    
                    // Visual feedback with better color coding
                    elements.charCount.className = 'character-count';
                    if (remaining < 100) {
                        elements.charCount.className = 'character-count warning';
                    }
                    if (remaining < 20) {
                        elements.charCount.className = 'character-count danger';
                    }
                    
                    // Textarea styling
                    if (count > 0) {
                        elements.descTextarea.style.borderColor = 'var(--success)';
                    } else {
                        elements.descTextarea.style.borderColor = 'rgba(255, 255, 255, 0.2)';
                    }
                }

                // Enhanced input validation
                function validateInput(input, minValue = 0, maxValue = null) {
                    const value = getNumericValue(input);
                    let isValid = value >= minValue;
                    
                    if (maxValue !== null) {
                        isValid = isValid && value <= maxValue;
                    }
                    
                    // Enhanced visual feedback
                    if (isValid) {
                        input.style.borderColor = 'var(--success)';
                        input.style.background = 'rgba(16, 185, 129, 0.05)';
                    } else {
                        input.style.borderColor = 'var(--danger)';
                        input.style.background = 'rgba(239, 68, 68, 0.05)';
                    }
                    
                    return isValid;
                }

                // Enhanced event listeners for currency inputs
                const currencyInputs = document.querySelectorAll('.currency-input-field');
                currencyInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        formatCurrencyInput(this);
                        addInputEffect(this);
                        
                        // Update progress calculation
                        updateProgress();
                    });
                    
                    // Format initial values on page load
                    if (input.value) {
                        formatCurrencyInput(input);
                    }
                });

                // Other event listeners
                const debouncedUpdateProgress = debounce(updateProgress, 150);
                
                elements.targetInput.addEventListener('input', function() {
                    validateInput(this, 1000);
                    addInputEffect(this);
                });

                elements.collectedInput.addEventListener('input', function() {
                    const targetValue = getNumericValue(elements.targetInput);
                    validateInput(this, 0, targetValue);
                    addInputEffect(this);
                });

                elements.dueDateInput.addEventListener('change', function() {
                    updateDaysRemaining();
                    addInputEffect(this);
                });

                document.getElementById('nama_goal').addEventListener('input', function() {
                    addInputEffect(this);
                });

                elements.descTextarea.addEventListener('input', updateCharCount);

                // Status button interactions
                const statusButtons = document.querySelectorAll('.status-button');
                statusButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        addInputEffect(this);
                    });
                });

                // Enhanced form submission
                elements.form.addEventListener('submit', function(e) {
                    // Remove formatting before submission
                    const targetValue = getNumericValue(elements.targetInput);
                    const collectedValue = getNumericValue(elements.collectedInput);
                    
                    // Set the raw numeric values back to the inputs
                    elements.targetInput.value = targetValue;
                    elements.collectedInput.value = collectedValue;
                    
                    // Validation
                    const isTargetValid = validateInput(elements.targetInput, 1000);
                    const isCollectedValid = validateInput(elements.collectedInput, 0, targetValue);
                    const isDateValid = elements.dueDateInput.value;
                    const isNameValid = document.getElementById('nama_goal').value.trim().length >= 3;
                    
                    if (!isTargetValid || !isCollectedValid || !isDateValid || !isNameValid) {
                        e.preventDefault();
                        showValidationErrors(!isTargetValid, !isCollectedValid, !isDateValid, !isNameValid);
                        
                        // Reformat the values for display
                        setTimeout(() => {
                            formatCurrencyInput(elements.targetInput);
                            formatCurrencyInput(elements.collectedInput);
                        }, 100);
                    } else {
                        // Enhanced loading state
                        elements.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan Target...';
                        elements.submitBtn.disabled = true;
                        elements.submitBtn.style.opacity = '0.8';
                    }
                });

                // Initialize all components
                updateCharCount();
                updateDaysRemaining();
                updateProgress();
                
                // Initial validation
                validateInput(elements.targetInput, 1000);
                validateInput(elements.collectedInput, 0);
            }

            // Utility functions
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            function addInputEffect(input) {
                input.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    input.style.transform = 'scale(1)';
                }, 150);
            }

            function showValidationErrors(targetError, collectedError, dateError, nameError) {
                const errors = [];
                if (nameError) errors.push(document.getElementById('nama_goal'));
                if (targetError) errors.push(document.getElementById('target_jumlah'));
                if (collectedError) errors.push(document.getElementById('terkumpul'));
                if (dateError) errors.push(document.getElementById('tenggat_waktu'));
                
                errors.forEach(field => {
                    field.style.animation = 'shake 0.5s ease-in-out';
                    setTimeout(() => {
                        field.style.animation = '';
                    }, 500);
                });
                
                if (errors.length > 0) {
                    errors[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            function showAchievement() {
                // Create celebration effect
                const celebration = document.createElement('div');
                celebration.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: linear-gradient(135deg, var(--success) 0%, var(--primary) 100%);
                    color: white;
                    padding: 2rem 3rem;
                    border-radius: 20px;
                    font-size: 1.5rem;
                    font-weight: 700;
                    z-index: 10000;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
                    animation: zoomIn 0.5s ease-out;
                `;
                celebration.innerHTML = `
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
                        <div>Target Tercapai!</div>
                        <div style="font-size: 1rem; margin-top: 0.5rem; opacity: 0.9;">Selamat! Anda telah mencapai target finansial</div>
                    </div>
                `;
                
                document.body.appendChild(celebration);
                
                setTimeout(() => {
                    celebration.remove();
                }, 3000);
            }
        });
    </script>
</body>
</html>