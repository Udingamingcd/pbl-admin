<?php
define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/middleware/auth.php';
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/koneksi.php';

$db = new Database();
$db->query('SELECT * FROM financial_goal WHERE user_id = :user_id ORDER BY created_at DESC');
$db->bind(':user_id', $_SESSION['user_id']);
$goals = $db->resultSet();

// Auto update status for goals that reached 100%
foreach ($goals as $goal) {
    if ($goal['status'] == 'aktif' && $goal['terkumpul'] >= $goal['target_jumlah']) {
        $db->query('UPDATE financial_goal SET status = "tercapai" WHERE id = :id');
        $db->bind(':id', $goal['id']);
        $db->execute();
    }
}

// Refresh goals after update
$db->query('SELECT * FROM financial_goal WHERE user_id = :user_id ORDER BY created_at DESC');
$db->bind(':user_id', $_SESSION['user_id']);
$goals = $db->resultSet();

// Separate active and achieved goals
$active_goals = array_filter($goals, function($goal) {
    return $goal['status'] == 'aktif';
});
$achieved_goals = array_filter($goals, function($goal) {
    return $goal['status'] == 'tercapai';
});
$cancelled_goals = array_filter($goals, function($goal) {
    return $goal['status'] == 'dibatalkan';
});

$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Target Finansial - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --success-dark: #3a9fc4;
            --danger: #f72585;
            --danger-dark: #d41a6f;
            --warning: #f8961e;
            --warning-dark: #e08515;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #adb5bd;
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-card: rgba(30, 41, 59, 0.8);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            color: var(--light);
            line-height: 1.6;
            padding: 0;
            overflow-x: hidden;
        }
        
        .app-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Header Styles */
        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
            position: relative;
        }
        
        .app-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100px;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), transparent);
        }
        
        .app-title {
            font-size: 2.25rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }
        
        .btn-modern {
            border: none;
            border-radius: 16px;
            padding: 0.875rem 1.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s;
        }
        
        .btn-modern:hover::before {
            left: 100%;
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
        }
        
        .btn-primary-modern:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(67, 97, 238, 0.5);
            color: white;
        }
        
        .btn-secondary-modern {
            background: var(--glass-bg);
            color: white;
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
        }
        
        .btn-secondary-modern:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            color: white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        /* Stats Cards - Glass Morphism */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: var(--glass-bg);
            border-radius: 24px;
            padding: 2rem 1.5rem;
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 24px 24px 0 0;
        }
        
        .stat-card:nth-child(2)::before {
            background: linear-gradient(90deg, var(--success), var(--success-dark));
        }
        
        .stat-card:nth-child(3)::before {
            background: linear-gradient(90deg, var(--gray), var(--gray-light));
        }
        
        .stat-card:nth-child(4)::before {
            background: linear-gradient(90deg, var(--warning), var(--warning-dark));
        }
        
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            font-size: 1.75rem;
            background: rgba(255, 255, 255, 0.1);
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .stat-card:nth-child(2) .stat-icon {
            color: var(--success);
        }
        
        .stat-card:nth-child(3) .stat-icon {
            color: var(--gray);
        }
        
        .stat-card:nth-child(4) .stat-icon {
            color: var(--warning);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--light), var(--gray-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--gray-light);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Tabs - Modern Design */
        .tabs-modern {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            padding: 0.75rem;
            background: var(--glass-bg);
            border-radius: 20px;
            width: fit-content;
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
        }
        
        .tab-modern {
            padding: 1rem 2rem;
            border-radius: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--gray-light);
            text-decoration: none;
            position: relative;
        }
        
        .tab-modern.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
        }
        
        .tab-modern:not(.active):hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            transform: translateY(-2px);
        }
        
        .tab-badge {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-radius: 12px;
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 700;
            min-width: 30px;
        }
        
        .tab-modern.active .tab-badge {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Goals Grid - Enhanced Cards */
        .goals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
        }
        
        .goal-card {
            background: var(--glass-bg);
            border-radius: 24px;
            padding: 2rem;
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .goal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 24px 24px 0 0;
        }
        
        .goal-card.achieved::before {
            background: linear-gradient(90deg, var(--success), var(--success-dark));
        }
        
        .goal-card.cancelled::before {
            background: linear-gradient(90deg, var(--gray), var(--gray-light));
        }
        
        .goal-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .goal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .goal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            margin: 0;
            line-height: 1.4;
        }
        
        .goal-status {
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: rgba(67, 97, 238, 0.2);
            color: var(--primary);
            backdrop-filter: blur(10px);
        }
        
        .goal-status.achieved {
            background: rgba(76, 201, 240, 0.2);
            color: var(--success);
        }
        
        .goal-status.cancelled {
            background: rgba(108, 117, 125, 0.2);
            color: var(--gray);
        }
        
        .goal-progress {
            margin-bottom: 2rem;
        }
        
        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        
        .progress-label {
            font-size: 0.9rem;
            color: var(--gray-light);
            font-weight: 500;
        }
        
        .progress-percentage {
            font-size: 0.9rem;
            font-weight: 700;
            color: white;
        }
        
        .progress-bar-container {
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 12px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            transition: width 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        
        .progress-bar.achieved {
            background: linear-gradient(90deg, var(--success), var(--success-dark));
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        .goal-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        
        .goal-detail {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: var(--gray-light);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .detail-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: white;
        }
        
        .goal-actions {
            display: flex;
            gap: 1rem;
            margin-top: auto;
        }
        
        .goal-action-btn {
            flex: 1;
            padding: 1rem;
            border-radius: 16px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
        }
        
        .goal-action-edit {
            background: rgba(248, 150, 30, 0.15);
            color: var(--warning);
            border: 1px solid rgba(248, 150, 30, 0.3);
        }
        
        .goal-action-edit:hover {
            background: rgba(248, 150, 30, 0.25);
            transform: translateY(-3px);
            color: var(--warning);
            box-shadow: 0 8px 20px rgba(248, 150, 30, 0.3);
        }
        
        .goal-action-delete {
            background: rgba(247, 37, 133, 0.15);
            color: var(--danger);
            border: 1px solid rgba(247, 37, 133, 0.3);
        }
        
        .goal-action-delete:hover {
            background: rgba(247, 37, 133, 0.25);
            transform: translateY(-3px);
            color: var(--danger);
            box-shadow: 0 8px 20px rgba(247, 37, 133, 0.3);
        }
        
        /* Empty State */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 2rem;
            grid-column: 1 / -1;
            background: var(--glass-bg);
            border-radius: 24px;
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
        }
        
        .empty-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            opacity: 0.7;
            color: var(--gray-light);
        }
        
        .empty-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }
        
        .empty-description {
            font-size: 1.1rem;
            color: var(--gray-light);
            margin-bottom: 2rem;
            max-width: 500px;
        }
        
        /* Alert Messages */
        .alert-modern {
            border-radius: 16px;
            padding: 1.25rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: none;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-left: 4px solid var(--success);
            animation: slideIn 0.5s ease-out;
        }
        
        .alert-danger {
            border-left-color: var(--danger);
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease forwards;
        }
        
        .stagger-animation > * {
            opacity: 0;
            animation: fadeIn 0.6s ease forwards;
        }
        
        .stagger-animation > *:nth-child(1) { animation-delay: 0.1s; }
        .stagger-animation > *:nth-child(2) { animation-delay: 0.2s; }
        .stagger-animation > *:nth-child(3) { animation-delay: 0.3s; }
        .stagger-animation > *:nth-child(4) { animation-delay: 0.4s; }
        .stagger-animation > *:nth-child(5) { animation-delay: 0.5s; }
        .stagger-animation > *:nth-child(6) { animation-delay: 0.6s; }
        
        /* Card Counter Animation */
        @keyframes countUp {
            from { transform: scale(0.8) rotate(-5deg); opacity: 0; }
            to { transform: scale(1) rotate(0); opacity: 1; }
        }
        
        .count-animation {
            animation: countUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        
        /* Interactive Progress Bar */
        .progress-bar-container {
            position: relative;
        }
        
        .progress-tooltip {
            position: absolute;
            top: -40px;
            left: 0;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-size: 0.8rem;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            pointer-events: none;
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
        }
        
        .progress-bar-container:hover .progress-tooltip {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Delete Confirmation Modal */
        .modal-custom {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px);
        }
        
        .modal-custom .modal-content {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            color: var(--light);
            backdrop-filter: blur(30px);
        }
        
        .modal-custom .modal-header {
            border-bottom: 1px solid var(--glass-border);
            padding: 2rem 2rem 1rem;
        }
        
        .modal-custom .modal-title {
            font-weight: 700;
            color: white;
            font-size: 1.5rem;
        }
        
        .modal-custom .btn-close {
            filter: invert(1);
            opacity: 0.7;
            transition: all 0.3s ease;
        }
        
        .modal-custom .btn-close:hover {
            opacity: 1;
            transform: rotate(90deg);
        }
        
        .modal-custom .modal-body {
            padding: 2rem;
        }
        
        .modal-custom .modal-footer {
            border-top: 1px solid var(--glass-border);
            padding: 1.5rem 2rem;
        }
        
        .confirmation-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(247, 37, 133, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2.5rem;
            color: var(--danger);
            animation: pulse 2s infinite;
        }
        
        /* Improved Button Styles */
        .btn-cancel {
            background: var(--glass-bg);
            color: white;
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-confirm-delete {
            background: linear-gradient(135deg, var(--danger), var(--danger-dark));
            color: white;
            border: none;
            border-radius: 16px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(247, 37, 133, 0.3);
        }
        
        .btn-confirm-delete:hover {
            background: linear-gradient(135deg, var(--danger-dark), var(--danger));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(247, 37, 133, 0.4);
        }
        
        /* Floating Action Button */
        .fab-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }
        
        .fab {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
        }
        
        .fab:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 12px 35px rgba(67, 97, 238, 0.6);
            color: white;
        }
        
        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .app-container {
                padding: 1.5rem;
            }
            
            .goals-grid {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .app-container {
                padding: 1rem;
            }
            
            .app-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.5rem;
            }
            
            .app-title {
                font-size: 1.75rem;
            }
            
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .tabs-modern {
                width: 100%;
                overflow-x: auto;
                justify-content: flex-start;
            }
            
            .goals-grid {
                grid-template-columns: 1fr;
            }
            
            .goal-details {
                grid-template-columns: 1fr;
            }
            
            .fab-container {
                bottom: 1rem;
                right: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .goal-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <h1 class="app-title">Target Finansial</h1>
            <div class="d-flex gap-2">
                <a href="../../../dashboard.php" class="btn-modern btn-secondary-modern">
                    <i class="fas fa-arrow-left"></i>
                    <span class="d-none d-md-inline">Kembali</span>
                </a>
                <a href="create.php" class="btn-modern btn-primary-modern">
                    <i class="fas fa-plus"></i>
                    <span class="d-none d-md-inline">Tambah Target</span>
                </a>
            </div>
        </header>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
            <div class="alert-modern alert-success">
                <i class="fas fa-check-circle text-success"></i>
                <span><?= $success ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-modern alert-danger">
                <i class="fas fa-exclamation-triangle text-danger"></i>
                <span><?= $error ?></span>
            </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="stats-container stagger-animation">
            <div class="stat-card fade-in">
                <div class="stat-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="stat-number count-animation"><?= count($active_goals) ?></div>
                <div class="stat-label">Target Aktif</div>
            </div>
            <div class="stat-card fade-in">
                <div class="stat-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-number count-animation"><?= count($achieved_goals) ?></div>
                <div class="stat-label">Tercapai</div>
            </div>
            <div class="stat-card fade-in">
                <div class="stat-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-number count-animation"><?= count($cancelled_goals) ?></div>
                <div class="stat-label">Dibatalkan</div>
            </div>
            <div class="stat-card fade-in">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number count-animation"><?= count($goals) ?></div>
                <div class="stat-label">Total Target</div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-modern">
            <a href="#active" class="tab-modern active" data-tab="active">
                <i class="fas fa-bullseye"></i>
                <span>Aktif</span>
                <?php if (!empty($active_goals)): ?>
                    <span class="tab-badge"><?= count($active_goals) ?></span>
                <?php endif; ?>
            </a>
            <a href="#achieved" class="tab-modern" data-tab="achieved">
                <i class="fas fa-trophy"></i>
                <span>Tercapai</span>
                <?php if (!empty($achieved_goals)): ?>
                    <span class="tab-badge"><?= count($achieved_goals) ?></span>
                <?php endif; ?>
            </a>
            <a href="#cancelled" class="tab-modern" data-tab="cancelled">
                <i class="fas fa-ban"></i>
                <span>Dibatalkan</span>
                <?php if (!empty($cancelled_goals)): ?>
                    <span class="tab-badge"><?= count($cancelled_goals) ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Goals Content -->
        <div class="tab-content">
            <!-- Active Goals -->
            <div class="tab-pane active" id="active-content">
                <?php if (empty($active_goals)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 class="empty-title">Belum Ada Target Aktif</h3>
                        <p class="empty-description">Mulai rencanakan masa depan finansial Anda dengan menetapkan target.</p>
                        <a href="create.php" class="btn-modern btn-primary-modern">
                            <i class="fas fa-plus"></i>
                            Tambah Target Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="goals-grid stagger-animation">
                        <?php foreach ($active_goals as $goal): ?>
                            <?php
                            $progress = $goal['target_jumlah'] > 0 ? 
                                ($goal['terkumpul'] / $goal['target_jumlah']) * 100 : 0;
                            $progress = min(100, $progress);
                            
                            $days_left = ceil((strtotime($goal['tenggat_waktu']) - time()) / (60 * 60 * 24));
                            ?>
                            
                            <div class="goal-card fade-in">
                                <div class="goal-header">
                                    <h3 class="goal-title"><?= htmlspecialchars($goal['nama_goal']) ?></h3>
                                    <div class="goal-status">Aktif</div>
                                </div>
                                
                                <div class="goal-progress">
                                    <div class="progress-info">
                                        <span class="progress-label">Progress</span>
                                        <span class="progress-percentage"><?= number_format($progress, 1) ?>%</span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-tooltip">Rp <?= number_format($goal['terkumpul'], 0, ',', '.') ?> dari Rp <?= number_format($goal['target_jumlah'], 0, ',', '.') ?></div>
                                        <div class="progress-bar" style="width: <?= $progress ?>%"></div>
                                    </div>
                                </div>
                                
                                <div class="goal-details">
                                    <div class="goal-detail">
                                        <span class="detail-label">Target</span>
                                        <span class="detail-value">Rp <?= number_format($goal['target_jumlah'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="goal-detail">
                                        <span class="detail-label">Terkumpul</span>
                                        <span class="detail-value">Rp <?= number_format($goal['terkumpul'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="goal-detail">
                                        <span class="detail-label">Tenggat</span>
                                        <span class="detail-value"><?= date('d M Y', strtotime($goal['tenggat_waktu'])) ?></span>
                                    </div>
                                    <div class="goal-detail">
                                        <span class="detail-label">Sisa Waktu</span>
                                        <span class="detail-value <?= $days_left < 30 ? 'text-warning' : '' ?>"><?= $days_left ?> hari</span>
                                    </div>
                                </div>
                                
                                <?php if ($goal['deskripsi']): ?>
                                    <div class="mb-3">
                                        <span class="detail-label">Deskripsi</span>
                                        <p class="detail-value mt-1" style="font-weight: normal;"><?= htmlspecialchars($goal['deskripsi']) ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="goal-actions">
                                    <a href="update.php?id=<?= $goal['id'] ?>" class="goal-action-btn goal-action-edit">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <button type="button" class="goal-action-btn goal-action-delete" 
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-goal-id="<?= $goal['id'] ?>" 
                                            data-goal-name="<?= htmlspecialchars($goal['nama_goal']) ?>">
                                        <i class="fas fa-trash"></i>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Achieved Goals -->
            <div class="tab-pane" id="achieved-content">
                <?php if (empty($achieved_goals)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3 class="empty-title">Belum Ada Target Tercapai</h3>
                        <p class="empty-description">Target yang berhasil dicapai akan muncul di sini.</p>
                    </div>
                <?php else: ?>
                    <div class="goals-grid stagger-animation">
                        <?php foreach ($achieved_goals as $goal): ?>
                            <div class="goal-card achieved fade-in">
                                <div class="goal-header">
                                    <h3 class="goal-title"><?= htmlspecialchars($goal['nama_goal']) ?></h3>
                                    <div class="goal-status achieved">Tercapai</div>
                                </div>
                                
                                <div class="goal-progress">
                                    <div class="progress-info">
                                        <span class="progress-label">Progress</span>
                                        <span class="progress-percentage">100%</span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-tooltip">Target tercapai!</div>
                                        <div class="progress-bar achieved" style="width: 100%"></div>
                                    </div>
                                </div>
                                
                                <div class="goal-details">
                                    <div class="goal-detail">
                                        <span class="detail-label">Target</span>
                                        <span class="detail-value">Rp <?= number_format($goal['target_jumlah'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="goal-detail">
                                        <span class="detail-label">Terkumpul</span>
                                        <span class="detail-value">Rp <?= number_format($goal['terkumpul'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="goal-detail">
                                        <span class="detail-label">Tercapai Pada</span>
                                        <span class="detail-value"><?= date('d M Y', strtotime($goal['updated_at'] ?? $goal['created_at'])) ?></span>
                                    </div>
                                </div>
                                
                                <div class="goal-actions">
                                    <a href="update.php?id=<?= $goal['id'] ?>" class="goal-action-btn goal-action-edit">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <button type="button" class="goal-action-btn goal-action-delete" 
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-goal-id="<?= $goal['id'] ?>" 
                                            data-goal-name="<?= htmlspecialchars($goal['nama_goal']) ?>">
                                        <i class="fas fa-trash"></i>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Cancelled Goals -->
            <div class="tab-pane" id="cancelled-content">
                <?php if (empty($cancelled_goals)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-ban"></i>
                        </div>
                        <h3 class="empty-title">Belum Ada Target Dibatalkan</h3>
                        <p class="empty-description">Target yang dibatalkan akan muncul di sini.</p>
                    </div>
                <?php else: ?>
                    <div class="goals-grid stagger-animation">
                        <?php foreach ($cancelled_goals as $goal): ?>
                            <div class="goal-card cancelled fade-in">
                                <div class="goal-header">
                                    <h3 class="goal-title"><?= htmlspecialchars($goal['nama_goal']) ?></h3>
                                    <div class="goal-status cancelled">Dibatalkan</div>
                                </div>
                                
                                <div class="goal-details">
                                    <div class="goal-detail">
                                        <span class="detail-label">Target</span>
                                        <span class="detail-value">Rp <?= number_format($goal['target_jumlah'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="goal-detail">
                                        <span class="detail-label">Terkumpul</span>
                                        <span class="detail-value">Rp <?= number_format($goal['terkumpul'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="goal-detail">
                                        <span class="detail-label">Dibatalkan Pada</span>
                                        <span class="detail-value"><?= date('d M Y', strtotime($goal['updated_at'] ?? $goal['created_at'])) ?></span>
                                    </div>
                                </div>
                                
                                <div class="goal-actions">
                                    <a href="update.php?id=<?= $goal['id'] ?>" class="goal-action-btn goal-action-edit">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <button type="button" class="goal-action-btn goal-action-delete" 
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-goal-id="<?= $goal['id'] ?>" 
                                            data-goal-name="<?= htmlspecialchars($goal['nama_goal']) ?>">
                                        <i class="fas fa-trash"></i>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fab-container">
        <a href="create.php" class="fab">
            <i class="fas fa-plus"></i>
        </a>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade modal-custom" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="confirmation-icon">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <h4 class="mb-3">Hapus Target?</h4>
                    <p class="text-muted">Anda akan menghapus target: <strong id="goalName"></strong></p>
                    <p class="text-danger">Tindakan ini tidak dapat dibatalkan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="confirmDelete" class="btn btn-confirm-delete">
                        <i class="fas fa-trash me-2"></i>Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabs = document.querySelectorAll('.tab-modern');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all tabs and panes
                    tabs.forEach(t => t.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding tab pane
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(`${tabId}-content`).classList.add('active');
                    
                    // Add animation to newly shown content
                    const goalCards = document.querySelectorAll(`#${tabId}-content .goal-card`);
                    goalCards.forEach((card, index) => {
                        card.style.animationDelay = `${0.1 + index * 0.1}s`;
                        card.classList.add('fade-in');
                    });
                });
            });
            
            // Add staggered animation to goal cards
            const goalCards = document.querySelectorAll('.goal-card');
            goalCards.forEach((card, index) => {
                card.style.animationDelay = `${0.1 + index * 0.1}s`;
            });
            
            // Progress bar animation with delay
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach((bar, index) => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500 + (index * 100));
            });
            
            // Interactive progress tooltips
            const progressContainers = document.querySelectorAll('.progress-bar-container');
            progressContainers.forEach(container => {
                const tooltip = container.querySelector('.progress-tooltip');
                
                container.addEventListener('mousemove', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const percentage = (x / rect.width) * 100;
                    
                    // Update tooltip position
                    tooltip.style.left = `${Math.min(Math.max(percentage, 0), 100)}%`;
                    tooltip.style.transform = `translateX(-50%) translateY(0)`;
                });
            });
            
            // Count animation for stats
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(number => {
                const target = parseInt(number.textContent);
                let current = 0;
                const increment = target / 40;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        number.textContent = target;
                        clearInterval(timer);
                    } else {
                        number.textContent = Math.floor(current);
                    number.style.transform = `scale(${1 + (current/target * 0.1)})`;
                    number.style.opacity = 0.7 + (current/target * 0.3);
                    number.style.filter = `blur(${1 - (current/target)}px)`;
                    number.style.textShadow = `0 0 ${10 * (current/target)}px rgba(255,255,255,0.5)`;
                    number.style.background = `linear-gradient(135deg, var(--light), var(--gray-light) ${(current/target * 100)}%)`;
                    number.style.webkitBackgroundClip = 'text';
                    number.style.webkitTextFillColor = 'transparent';
                    number.style.backgroundClip = 'text';
                    number.style.color = 'transparent';
                    number.style.display = 'inline-block';
                    number.style.transform = `translateY(${10 - (current/target * 10)}px)`;
                    number.style.opacity = current/target;
                }
                }, 30);
            });
            
            // Delete confirmation modal
            const deleteModal = document.getElementById('deleteModal');
            const goalNameElement = document.getElementById('goalName');
            const confirmDeleteButton = document.getElementById('confirmDelete');
            
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const goalId = button.getAttribute('data-goal-id');
                const goalName = button.getAttribute('data-goal-name');
                
                goalNameElement.textContent = goalName;
                confirmDeleteButton.href = `delete.php?id=${goalId}`;
            });
            
            // Enhanced delete confirmation with loading state
            confirmDeleteButton.addEventListener('click', function(e) {
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="loading-spinner me-2"></span>Menghapus...';
                this.disabled = true;
                
                // Add visual feedback
                this.style.transform = 'scale(0.95)';
                this.style.opacity = '0.8';
                
                // Simulate loading for better UX
                setTimeout(() => {
                    window.location.href = this.href;
                }, 1500);
            });
            
            // Add hover effects to cards
            goalCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-12px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Add parallax effect to stats cards
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const statCards = document.querySelectorAll('.stat-card');
                
                statCards.forEach((card, index) => {
                    const rate = scrolled * -0.1 * (index + 1);
                    card.style.transform = `translateY(${rate}px)`;
                });
            });
        });
    </script>
</body>
</html>