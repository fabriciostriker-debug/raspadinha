<?php
// Incluir rastreamento de afiliados no início da página
require_once dirname(__DIR__) . '/affiliate_tracker.php';
require_once __DIR__ . '/site_functions.php';

// Verificar se o usuário está logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioLogado = isset($_SESSION['usuario_id']);
$saldo = 0;
$nomeUsuario = '';

// Buscar configuração de confirmação de senha
$require_password_confirmation = '1'; // Padrão ativado

    if ($usuarioLogado) {
        require __DIR__ . '/db.php';
        $userId = $_SESSION['usuario_id'];
        $stmt = $conn->prepare("SELECT name, balance, bonus_balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $nomeUsuario = $user['name'];
            $saldo = $user['balance'];
            $bonus_saldo = $user['bonus_balance'] ?? 0;
            $saldo_total = $saldo + $bonus_saldo;
        }
} else {
    require __DIR__ . '/db.php';
}

// Buscar configuração de confirmação de senha
$password_config_query = $conn->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'require_password_confirmation'");
$password_config_query->execute();
$password_config_result = $password_config_query->get_result();

if ($password_config_result && $password_config_result->num_rows > 0) {
    $require_password_confirmation = $password_config_result->fetch_assoc()['setting_value'];
}

if ($usuarioLogado) {
    
    // Buscar valor mínimo de depósito das configurações globais
    $min_deposit_query = $conn->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'min_deposit_amount'");
    $min_deposit_query->execute();
    $min_deposit_result = $min_deposit_query->get_result();
    $min_deposit = 1.00; // Valor padrão caso não encontre a configuração
    
    if ($min_deposit_result && $min_deposit_result->num_rows > 0) {
        $min_deposit = floatval($min_deposit_result->fetch_assoc()['setting_value']);
    }
    
    // Buscar configurações de suporte
    $suporte_tipo = 'telegram';
    $telegram_usuario = 'Suportefun777';
    $whatsapp_numero = '';
    
    $suporte_query = $conn->prepare("SELECT chave, valor FROM configuracoes WHERE chave IN ('suporte_tipo', 'suporte_telegram_usuario', 'suporte_whatsapp_numero')");
    $suporte_query->execute();
    $suporte_result = $suporte_query->get_result();
    
    while ($suporte_row = $suporte_result->fetch_assoc()) {
        switch ($suporte_row['chave']) {
            case 'suporte_tipo':
                $suporte_tipo = $suporte_row['valor'];
                break;
            case 'suporte_telegram_usuario':
                $telegram_usuario = $suporte_row['valor'];
                break;
            case 'suporte_whatsapp_numero':
                $whatsapp_numero = $suporte_row['valor'];
                break;
        }
    }
    
    // Construir URL de suporte com base no tipo
    $suporte_url = '';
    if ($suporte_tipo === 'telegram') {
        $suporte_url = 'https://t.me/' . $telegram_usuario;
    } else {
        $suporte_url = 'https://api.whatsapp.com/send/?phone=' . $whatsapp_numero . '&text&type=phone_number&app_absent=0';
    }
}


?>
<?php
// Incluir o pixel do Facebook
require_once __DIR__ . '/facebook_pixel.php';
$pixel_code = generate_pixel_code('PageView');

// Incluir o pixel do Kwai
require_once __DIR__ . '/kwai_pixel.php';
$kwai_pixel_code = generate_kwai_pixel_code('contentView');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raspadinha Virtual - <?php echo get_site_name(); ?></title>
    
    <!-- Facebook Pixel Code -->
    <?php echo $pixel_code; ?>
    <!-- End Facebook Pixel Code -->
    
    <!-- Kwai Pixel Code -->
    <?php echo $kwai_pixel_code; ?>
    <!-- End Kwai Pixel Code -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/header-original-modern.js"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { 'inter': ['Inter', 'sans-serif'] },
                colors: {
                    'neon-green': '#00ff88',
                    'dark-bg': '#0a0a0f',
                    'card-bg': '#1a1a2e',
                    'accent-gold': '#ffd700',
                    'accent-purple': '#8b5cf6',
                }
            }
        }
    }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/inicio.css">
    <link rel="stylesheet" href="css/melhorias-ux.css">
    <link rel="stylesheet" href="css/mobile_nav.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/header-original-modern.css">

 
</head>
<body class="text-white min-h-screen flex flex-col inicio-page">
    <!-- Desktop Navigation -->
    <nav class="desktop-nav">
        <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
            <!-- Logo Section -->
            <div class="flex items-center space-x-2">
                <?php 
                // Buscar logo principal das configurações
                $logo_principal_query = $conn->prepare("SELECT valor FROM configuracoes WHERE chave = 'logo_principal'");
                $logo_principal_query->execute();
                $logo_result = $logo_principal_query->get_result();
                $logo_path = '/img/logo.webp'; // Valor padrão
                
                if ($logo_result && $logo_result->num_rows > 0) {
                    $logo_path = $logo_result->fetch_assoc()['valor'];
                }
                ?>
                <img src="<?php echo $logo_path; ?>" alt="<?php echo get_site_name(); ?> Logo" class="logo-image">
            </div>
            
            <br>
            <!-- Desktop User Section -->
            <?php if ($usuarioLogado): ?>
                <div class="flex gap-3 items-center desktop-user-section">
                    <span id="saldoDesktop" class="bg-green-500 text-white px-3 py-1 rounded text-sm font-semibold">
                        R$ <?= number_format($saldo_total, 2, ',', '.') ?>
                    </span>
                    <button onclick="abrirDeposito()" class="bg-green-500 hover:bg-emerald-600 px-3 py-1 rounded text-sm font-semibold transition-all flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 19h14a2 2 0 002-2v-2H3v2a2 2 0 002 2z"/>
                        </svg>
                        
                    </button>
                    <div class="relative group">
                        <button class="flex items-center gap-1 text-sm font-medium hover:text-purple-300 transition-colors" style="opacity: 1; transform: translateY(-2px) scale(1.02); transition: 0.6s; box-shadow: rgba(34, 197, 94, 0.4) 0px 8px 25px;">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" style="opacity: 1; transform: translateY(0px); transition: 0.6s;">
                                <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z" style="opacity: 1; transform: translateY(0px); transition: 0.6s;"></path>
                            </svg>
                        </button>
                        <div class="absolute hidden group-hover:block bg-gray-700 mt-1 rounded shadow-md w-40 right-0">
                            <a href="perfil.php" class="block px-4 py-2 hover:bg-gray-600 transition-colors">Perfil</a>
                            <a href="perfil.php" class="block px-4 py-2 hover:bg-gray-600 transition-colors">Sacar</a>
                            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-600 transition-colors">Sair</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex gap-3 desktop-guest-section">
                    <button onclick="abrirModal('login')" class="text-sm text-white hover:text-green-300 px-4 py-2 rounded-lg border border-slate-600 hover:border-green-400 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Entrar
                    </button>
                    <button onclick="abrirModal('register')" class="btn-primary text-white text-sm px-6 py-2 rounded-lg font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Registrar
                    </button>
                </div>
            <?php endif; ?>
            

        </div>
        <br>
    </nav>
