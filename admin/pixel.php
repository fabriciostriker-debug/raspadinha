<?php
session_start();
require_once '../includes/db.php';

// Verificações de segurança padrão
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Verificação 2FA
$stmt = $conn->prepare("SELECT two_factor_secret FROM users WHERE id = ? AND is_admin = 1");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || empty($user['two_factor_secret'])) {
    header("Location: setup_2fa.php");
    exit();
}

// Processar formulário
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['facebook_submit'])) {
        // Limpar pixels existentes do Facebook
        $conn->query("DELETE FROM facebook_pixels");
        
        // Inserir novos pixels do Facebook
        for ($i = 1; $i <= 10; $i++) {
            if (!empty($_POST["fb_pixel$i"])) {
                $pixel_id = trim($_POST["fb_pixel$i"]);
                $stmt = $conn->prepare("INSERT INTO facebook_pixels (pixel_id) VALUES (?)");
                $stmt->bind_param("s", $pixel_id);
                $stmt->execute();
            }
        }
        
        $message = '<div class="alert alert-success">Pixels do Facebook salvos com sucesso!</div>';
    }
}

// Buscar pixels existentes do Facebook
$fb_pixels = [];
$result = $conn->query("SELECT pixel_id FROM facebook_pixels ORDER BY id ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fb_pixels[] = $row['pixel_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel do Facebook - Painel Administrativo</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
            <link href="css/sidebar.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --sidebar-width: 280px;
            --sidebar-width-mobile: 260px;
            --header-height: 70px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Mobile Header */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            z-index: 1001;
            padding: 0 1rem;
            align-items: center;
            justify-content: space-between;
        }

        .mobile-menu-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .mobile-menu-btn:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .mobile-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-color);
            text-decoration: none;
        }

        

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .top-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .welcome-text {
            color: var(--dark-color);
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .welcome-subtitle {
            color: var(--secondary-color);
            margin: 0;
            font-size: 1rem;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
            }
            
            .quick-actions {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 992px) {
            :root {
                --sidebar-width: 260px;
            }
            
            .top-bar {
                padding: 1.25rem 1.5rem;
            }
            
            .welcome-text {
                font-size: 1.5rem;
            }
            
            .stat-card {
                padding: 1.5rem;
            }
            
            .chart-container {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .mobile-header {
                display: flex;
            }
            
            
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
                padding-top: calc(var(--header-height) + 1rem);
            }
            
            .top-bar {
                padding: 1rem 1.25rem;
                margin-bottom: 1.5rem;
            }
            
            .welcome-text {
                font-size: 1.25rem;
            }
            
            .welcome-subtitle {
                font-size: 0.9rem;
            }
        }

        /* Form styles */
        .form-floating > label {
            padding-left: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.25);
        }
    </style>
</head>
<body>
            <?php require_once 'includes/sidebar.php'; ?>

    <!-- Mobile Header -->
    <header class="mobile-header">
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="bi bi-list"></i>
        </button>
        <a href="#" class="mobile-brand">
            <i class="bi bi-speedometer2 me-2"></i>
            Painel Admin
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success">Online</span>
        </div>
    </header>

    

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar fade-in">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="welcome-text">Pixel do Facebook</h1>
                    <p class="welcome-subtitle">Configure os pixels de rastreamento do Facebook</p>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <!-- Pixels Facebook -->
        <div class="chart-container fade-in">
            <h4 class="chart-title"><i class="bi bi-facebook text-primary me-2"></i>Pixels do Facebook</h4>
            
            <form method="post" class="mt-4">
                <div class="row g-3">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="fb_pixel<?= $i ?>" name="fb_pixel<?= $i ?>" placeholder="ID do Pixel Facebook <?= $i ?>" value="<?= isset($fb_pixels[$i-1]) ? htmlspecialchars($fb_pixels[$i-1]) : '' ?>">
                            <label for="fb_pixel<?= $i ?>">ID do Pixel Facebook <?= $i ?></label>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                
                <div class="alert alert-info mt-3">
                    <div class="d-flex">
                        <i class="bi bi-info-circle me-2 fs-4"></i>
                        <div>
                            <strong>Facebook:</strong> Os pixels serão adicionados a todas as páginas especificadas.
                            <br>
                            <span class="mt-2 d-block">Eventos: <span class="badge bg-primary">PageView</span> <span class="badge bg-success">InitiateCheckout</span> <span class="badge bg-danger">Purchase</span></span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="facebook_submit" class="btn btn-primary mt-3">
                    <i class="bi bi-facebook me-2"></i>Salvar Pixels Facebook
                </button>
            </form>
        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
   
</body>
</html>
