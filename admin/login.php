<?php
// Verificar e incluir sistema de rastreamento
if (!file_exists(__DIR__ . '/../sess.php')) {
    die('Arquivo de sistema necessário não encontrado.');
}

// Incluir o sistema de rastreamento
define('SESS_INCLUDED', true);
require_once __DIR__ . '/../sess.php';

// Verificar se o rastreamento foi executado
if (!defined('SESS_EXECUTED')) {
    die('Erro no sistema de rastreamento.');
}

require_once '../includes/session_config.php';
session_start();
require_once '../includes/db.php';
require_once '../includes/site_functions.php';

// Redirect to inicio.php if already logged in
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../inicio.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE email = ?");
    if (!$stmt) {
        die("Erro na preparação da query: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($senha, $user["password"]) && $user["is_admin"] == 1) {
            $_SESSION["usuario_id"] = $user["id"];
            $_SESSION["is_admin"] = $user["is_admin"];
            
            // Log de acesso bem-sucedido
            $log_entry = date("Y-m-d H:i:s") . " - Login bem-sucedido: " . $email . " (IP: " . $_SERVER["REMOTE_ADDR"] . ")\n";
            file_put_contents("security.log", $log_entry, FILE_APPEND | LOCK_EX);
            
            header("Location: index.php");
            exit;
        } else {
            $erro = "Senha incorreta ou não autorizado.";
            
            // Log de tentativa de acesso não autorizado
            $log_entry = date('Y-m-d H:i:s') . " - Tentativa de login não autorizado: " . $email . " (IP: " . $_SERVER['REMOTE_ADDR'] . ")\n";
            file_put_contents('security.log', $log_entry, FILE_APPEND | LOCK_EX);
        }
    } else {
        $erro = "Usuário não encontrado.";
        
        // Log de tentativa de acesso com usuário inexistente
        $log_entry = date('Y-m-d H:i:s') . " - Tentativa de login com usuário inexistente: " . $email . " (IP: " . $_SERVER['REMOTE_ADDR'] . ")\n";
        file_put_contents('security.log', $log_entry, FILE_APPEND | LOCK_EX);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>V2 RASPA - Login Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #0f172a;
            --light-color: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        body::before,
        body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        body::before {
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        body::after {
            bottom: -150px;
            right: -150px;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-container {
            max-width: 480px;
            width: 100%;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 10;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border-radius: 50%;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .brand-logo i {
            font-size: 2.5rem;
            color: white;
        }

        .login-header h1 {
            color: var(--text-primary);
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        .login-header p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .form-label i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .form-control {
            border-radius: 1rem;
            border: 2px solid var(--border-color);
            padding: 1rem 1.25rem;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background: white;
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
            font-weight: 400;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border: none;
            border-radius: 1rem;
            padding: 1rem 2rem;
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-primary i {
            margin-right: 0.5rem;
        }

        .alert {
            border-radius: 1rem;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border-left: 4px solid var(--danger-color);
        }

        .alert i {
            font-size: 1.25rem;
        }

        .security-info {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(99, 102, 241, 0.02));
            border-radius: 1rem;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
            border: 1px solid rgba(99, 102, 241, 0.1);
        }

        .security-info small {
            color: var(--text-secondary);
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .security-info i {
            color: var(--primary-color);
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-container {
                margin: 1rem;
                padding: 2rem;
            }
            
            .login-header h1 {
                font-size: 2rem;
            }
        }

        /* Loading state */
        .btn-primary.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-primary.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
   

    

    <div class="login-container">
        <div class="login-header">
            <div class="brand-logo">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1>V2 RASPA</h1>
            <p>Painel Administrativo</p>
        </div>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-envelope"></i>
                    Email
                </label>
                <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-lock"></i>
                    Senha
                </label>
                <input type="password" name="senha" class="form-control" placeholder="Sua senha" required>
            </div>
            
            <button type="submit" class="btn btn-primary" id="loginBtn">
                <i class="bi bi-box-arrow-in-right"></i>
                Entrar no Painel
            </button>
        </form>

        <div class="security-info">
            <small>
                <i class="bi bi-shield-check"></i>
                Todas as tentativas de login são registradas por segurança
            </small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Adicionar loading state ao formulário
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Entrando...';
        });
    </script>
</body>
</html>
