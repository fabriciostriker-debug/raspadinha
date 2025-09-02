<?php
// Exibir erros PHP para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'includes/db.php';
require 'includes/auth.php';
require '../includes/site_functions.php';

// Verificar se o usuário está logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioLogado = isset($_SESSION['usuario_id']);
$saldo = 0;
$nomeUsuario = '';

if ($usuarioLogado) {
    $userId = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("SELECT name, balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $nomeUsuario = $user['name'];
        $saldo = $user['balance'];
    }
}

// Pega os parâmetros da URL
$tipo = $_GET['raspadinha'] ?? 'esperanca';
$valorAposta = isset($_GET['valor']) ? floatval($_GET['valor']) : 1.00;

// Define configurações baseadas no valor da aposta
switch ($valorAposta) {
  case 1.00:
    $premioMaximo = 1000.00;
    $chance = 0.01;
    $nomeRaspadinha = 'Sonho de Consumo';
    break;
  case 5.00:
    $premioMaximo = 5000.00;
    $chance = 0.0012;
    $nomeRaspadinha = 'Raspe da Emoção';
    break;
  case 10.00:
    $premioMaximo = 6300.00;
    $chance = 0.0010;
    $nomeRaspadinha = 'Me mimei';
    break;
  case 20.00:
    $premioMaximo = 7500.00;
    $chance = 0.0009;
    $nomeRaspadinha = 'Super Prêmios';
    break;
  case 50.00:
    $premioMaximo = 11000.00;
    $chance = 0.0008;
    $nomeRaspadinha = 'Sonho de Consumo';
    break;
  case 100.00:
    $premioMaximo = 14000.00;
    $chance = 0.0007;
    $nomeRaspadinha = 'Premium VIP';
    break;
  default:
    $premioMaximo = 50.00;
    $chance = 0.01;
    $nomeRaspadinha = 'Esperança';
    break;
}

$userId = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$saldo = $usuario['balance'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎮 Raspadinha Virtual - <?php echo get_site_name(); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/inicio.css">
    <link rel="stylesheet" href="../css/header-original-modern.css">
    
    <script>
    function abrirDeposito() {
        window.location.href = '../inicio#deposito';
    }
    
    // Configuração do Tailwind para cores customizadas
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    'inter': ['Inter', 'sans-serif'],
                },
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
    
    <link rel="stylesheet" href="../css/mobile_nav.css">
    <link rel="stylesheet" href="../css/footer.css">
    <style>
        /* Reset e configurações base */
        * {
        }

        /* Variáveis CSS para cores customizadas */
        :root {
            --neon-green: #00ff88;
            --dark-bg: #0a0a0f;
            --card-bg: #1a1a2e;
            --accent-gold: #ffd700;
            --accent-purple: #8b5cf6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(ellipse at center, #1a1a2e 0%, #16213e 35%, #0a0a0f 100%);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }


   

        /* Container principal do jogo */
        .game-main-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 32px 24px;
            position: relative;
        }

        /* Card do jogo */
        .game-card {
            background: linear-gradient(145deg, rgba(26, 26, 46, 0.95) 0%, rgba(22, 33, 62, 0.95) 100%);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(0, 255, 136, 0.2);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            padding: 32px;
            position: relative;
            overflow: hidden;
        }

        .game-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 136, 0.1), transparent);
            animation: cardShimmer 3s infinite;
        }

        @keyframes cardShimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Header do card */
        .game-card-header {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
            z-index: 2;
        }

        .game-title {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #00ff88 0%, #8b5cf6 50%, #ffd700 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .game-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Info cards */
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .info-card {
            background: linear-gradient(135deg, rgba(0, 255, 136, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 255, 136, 0.2);
            border-color: rgba(0, 255, 136, 0.4);
        }

        .info-card-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #00ff88;
            margin-bottom: 4px;
        }

        .info-card-label {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
        }

        /* Container da raspadinha */
        .scratch-game-container {
            position: relative;
            margin-bottom: 32px;
        }

        .scratch-container {
            position: relative;
            aspect-ratio: 1;
            max-width: 400px;
            margin: 0 auto;
            border-radius: 24px;
            background: linear-gradient(145deg, #1a1a2e 0%, #16213e 100%);
            border: 2px solid rgba(0, 255, 136, 0.3);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                inset 0 0 60px rgba(0, 255, 136, 0.1);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .scratch-container::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 24px;
            background: linear-gradient(45deg, #00ff88, #8b5cf6, #ffd700, #00ff88);
            background-size: 400% 400%;
            z-index: -1;
            animation: borderGlow 4s ease-in-out infinite;
        }

        @keyframes borderGlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .scratch-container:hover {
            box-shadow: 
                0 30px 60px rgba(0, 0, 0, 0.5),
                inset 0 0 80px rgba(0, 255, 136, 0.2);
        }

        /* Grid das áreas de raspagem */
        .scratch-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            padding: 24px;
            height: 100%;
            position: relative;
            z-index: 1;
        }

        .scratch-area {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
            border: 2px solid rgba(0, 255, 136, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 
                0 8px 16px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .scratch-area::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            transition: all 0.3s ease;
        }

        .scratch-area:hover::before {
            left: 100%;
        }

        .scratch-area:hover {
            transform: scale(1.05);
            border-color: rgba(0, 255, 136, 0.5);
            box-shadow: 
                0 12px 24px rgba(0, 255, 136, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }

        .scratch-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 14px;
            transition: all 0.3s ease;
        }

        .scratch-area.revealed {
            animation: revealPulse 1s ease-out;
            border-color: #ffd700;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
        }

        @keyframes revealPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); box-shadow: 0 0 30px rgba(255, 215, 0, 0.6); }
            100% { transform: scale(1); }
        }

        .scratch-area.winner {
            animation: winnerGlow 2s ease-in-out infinite;
            border-color: #ff4444;
            background: linear-gradient(135deg, #fecaca, #f87171);
        }

        @keyframes winnerGlow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(255, 68, 68, 0.4);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 40px rgba(255, 68, 68, 0.8);
                transform: scale(1.03);
            }
        }

        /* Canvas de raspagem */
        .scratch-canvas {
            position: absolute;
            top: 24px;
            left: 24px;
            width: calc(100% - 48px);
            height: calc(100% - 48px);
            border-radius: 16px;
            cursor: crosshair;
            z-index: 10;
            touch-action: none;
        }

        /* Mensagens e controles */
        .game-message {
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 24px;
            min-height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .play-again-button {
            background: linear-gradient(135deg, #00ff88 0%, #00cc6a 100%);
            color: #000;
            padding: 16px 32px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: none;
            margin: 0 auto;
            box-shadow: 
                0 8px 16px rgba(0, 255, 136, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(0, 255, 136, 0.4);
        }

        .play-again-button:hover {
            background: linear-gradient(135deg, #00cc6a 0%, #00aa55 100%);
            transform: translateY(-2px);
            box-shadow: 
                0 12px 24px rgba(0, 255, 136, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .play-again-button:active {
            transform: translateY(0);
        }

        .play-again-button.visible {
            display: block;
            animation: buttonSlideUp 0.6s ease-out;
        }

        @keyframes buttonSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Carrossel de ganhadores aprimorado */
        .winners-carousel-container {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.8) 0%, rgba(22, 33, 62, 0.8) 100%);
            padding: 24px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
        }

        .winners-carousel-title {
            text-align: center;
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #00ff88 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .winners-carousel {
            overflow: hidden;
            position: relative;
            border-radius: 16px;
            mask: linear-gradient(to right, transparent, black 40px, black calc(100% - 40px), transparent);
        }

        .carousel-track {
            display: flex;
            gap: 16px;
            animation: scroll-horizontal 40s linear infinite;
            width: max-content;
        }

        .winner-card {
            background: linear-gradient(135deg, rgba(0, 255, 136, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 16px;
            padding: 16px;
            min-width: 200px;
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .winner-card:hover {
            transform: scale(1.05);
            border-color: rgba(0, 255, 136, 0.4);
            box-shadow: 0 8px 16px rgba(0, 255, 136, 0.2);
        }

        .winner-card img {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
        }

        .winner-info {
            flex: 1;
            min-width: 0;
        }

        .winner-name {
            color: #ffd700;
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .winner-prize {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            margin: 2px 0;
        }

        .winner-amount {
            color: #00ff88;
            font-weight: 700;
            font-size: 0.9rem;
        }

        /* Navegação mobile: regras extraídas para ../css/mobile_nav.css */

        /* Botão de suporte aprimorado */
        .suporte-btn {
            position: fixed;
            bottom: 100px;
            right: 20px;
            background: linear-gradient(135deg, #0088cc 0%, #0066aa 100%);
            color: white;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 
                0 8px 16px rgba(0, 136, 204, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            z-index: 1000;
            border: 2px solid rgba(0, 136, 204, 0.3);
        }

        .suporte-btn:hover {
            background: linear-gradient(135deg, #0066aa 0%, #004488 100%);
            transform: scale(1.1) translateY(-2px);
            box-shadow: 
                0 12px 24px rgba(0, 136, 204, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        /* Responsividade */
        @media (max-width: 640px) {
            .game-main-container {
                padding: 16px 12px;
            }
            
            .game-card {
                padding: 24px 20px;
            }
            
            .game-title {
                font-size: 1.75rem;
            }
            
            .scratch-container {
                max-width: 320px;
            }
            
            .scratch-grid {
                padding: 16px;
                gap: 8px;
            }
            
            .scratch-area {
                font-size: 0.95rem;
            }
            
            .info-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            

            
            .winner-card {
                min-width: 180px;
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .desktop-nav {
            }
            
            .game-title {
                font-size: 1.5rem;
            }
            
            .scratch-container {
                max-width: 280px;
            }
            
            .scratch-grid {
                padding: 12px;
                gap: 6px;
            }
        }

        @media (max-width: 767px) {
            .game-header {
                display: none !important;
            }
        }
        
        @media (min-width: 768px) {
            .game-header, .header-visible {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                position: relative !important;
                z-index: 1000 !important;
            }
        }
        
        /* Ocultação da barra mobile em telas >= md movida para ../css/mobile_nav.css */

        /* Animações de entrada */
        .fade-in {
            animation: fadeIn 0.8s ease-out;
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

        .slide-up {
            animation: slideUp 0.6s ease-out;
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

        /* Efeitos de partículas flutuantes (otimizados) */
        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            /* Limitar a área de renderização */
            overflow: hidden;
        }

        .particle {
            position: absolute;
            /* Simplificado para círculo sólido em vez de gradiente para melhor desempenho */
            background-color: rgba(0, 255, 136, 0.4);
            border-radius: 50%;
            animation: float 20s infinite linear;
            /* Impedir que o browser renderize as partículas em camadas separadas */
            will-change: transform;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-10vh);
                opacity: 0;
            }
        }

        /* Pause animações quando necessário */
        .winners-carousel:hover .carousel-track {
            animation-play-state: paused;
        }
        
        /* Animações para cards vencedores */
        @keyframes winningPulse {
            0% { 
                box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
                transform: scale(1.03); 
            }
            50% { 
                box-shadow: 0 0 25px rgba(255, 215, 0, 0.9);
                transform: scale(1.05);
            }
            100% { 
                box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
                transform: scale(1.03);
            }
        }

        @keyframes winningBorderGlow {
            0% { border-color: #ffd700; }
            25% { border-color: #ffec8b; }
            50% { border-color: #ffd700; }
            75% { border-color: #f0e68c; }
            100% { border-color: #ffd700; }
        }

        .winning-card {
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
            border: 4px solid #ffd700 !important;
            animation: winningPulse 2s infinite;
            z-index: 200;
            position: relative;
            transform: scale(1.03);
            /* Removido transition para reduzir carga */
            /* Simplificado filter para melhorar performance */
        }

        /* Simplificado para melhorar performance em dispositivos móveis */
        .winning-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 215, 0, 0.15);
            z-index: 190;
            pointer-events: none;
            border-radius: 8px;
        }

        .winning-card::after {
            position: absolute;
            top: -15px;
            right: -10px;
            font-size: 24px;
            /* Removida animação do emoji para reduzir carga de processamento */
            z-index: 201;
        }
    </style>
</head>
<body class="text-white min-h-screen">
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
                $logo_path = '../img/logo.webp'; // Valor padrão
                
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
                        R$ <?= number_format($saldo, 2, ',', '.') ?>
                    </span>
                    <button onclick="abrirDeposito()" class="bg-green-500 hover:bg-emerald-600 px-3 py-1 rounded text-sm font-semibold transition-all flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 19h14a2 2 0 002-2v-2H3v2a2 2 0 002 2z"/>
                        </svg>
                        Depositar
                    </button>
                    <div class="relative group">
                        <button class="flex items-center gap-1 text-sm font-medium hover:text-purple-300 transition-colors" style="opacity: 1; transform: translateY(-2px) scale(1.02); transition: 0.6s; box-shadow: rgba(34, 197, 94, 0.4) 0px 8px 25px;">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" style="opacity: 1; transform: translateY(0px); transition: 0.6s;">
                                <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z" style="opacity: 1; transform: translateY(0px); transition: 0.6s;"></path>
                            </svg>
                        </button>
                        <div class="absolute hidden group-hover:block bg-gray-700 mt-1 rounded shadow-md w-40 right-0">
                            <a href="../perfil.php" class="block px-4 py-2 hover:bg-gray-600 transition-colors">Perfil</a>
                            <a href="../perfil.php" class="block px-4 py-2 hover:bg-gray-600 transition-colors">Sacar</a>
                            <a href="../logout.php" class="block px-4 py-2 hover:bg-gray-600 transition-colors">Sair</a>
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

    <!-- Partículas flutuantes -->
    <div class="floating-particles" id="particles"></div>


    

    <!-- Main Game Container -->
    <div class="game-main-container fade-in">
        <div class="game-card">
            <!-- Game Header -->
            <div class="game-card-header">
                <h1 class="game-title">🎮 Raspadinha <?= htmlspecialchars($nomeRaspadinha) ?></h1>
                <p class="game-subtitle">Raspe e descubra se você ganhou!</p>
            </div>

            <!-- Info Cards -->
            <div class="info-cards">
                
                <div class="info-card">
                    <div class="info-card-value">R$ <?= number_format($valorAposta, 2, ',', '.') ?></div>
                    <div class="info-card-label">🎯 Aposta</div>
                </div>
                <div class="info-card">
                    <div class="info-card-value">R$ <?= number_format($premioMaximo, 2, ',', '.') ?></div>
                    <div class="info-card-label">🏆 Prêmio Máx</div>
                </div>
            </div>

            <!-- Scratch Game -->
            <div class="scratch-game-container">
                <div class="scratch-container">
                    <div class="scratch-grid">
                        <div class="scratch-area" data-index="0"></div>
                        <div class="scratch-area" data-index="1"></div>
                        <div class="scratch-area" data-index="2"></div>
                        <div class="scratch-area" data-index="3"></div>
                        <div class="scratch-area" data-index="4"></div>
                        <div class="scratch-area" data-index="5"></div>
                        <div class="scratch-area" data-index="6"></div>
                        <div class="scratch-area" data-index="7"></div>
                        <div class="scratch-area" data-index="8"></div>
                    </div>
                </div>
            </div>

            <!-- Game Controls -->
            <div class="text-center">
                <div class="game-message message">✨ Passe o dedo ou mouse para raspar ✨</div>
                <button class="play-again-button">
                    <i class="fas fa-redo mr-2"></i>
                    🎮 Jogar Novamente
                </button>
                <div class="mt-4">
                    <button onclick="history.back()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full font-medium transition-all hover:scale-105">
                        <i class="fas fa-arrow-left mr-2"></i>
                        ← Voltar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <?php include __DIR__ . '/../includes/mobile_nav.php'; ?>

    <!-- Support Button -->
    <?php
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
    
    $suporte_url = '';
    if ($suporte_tipo === 'telegram') {
        $suporte_url = 'https://t.me/' . $telegram_usuario;
    } else {
        $suporte_url = 'https://api.whatsapp.com/send/?phone=' . $whatsapp_numero . '&text&type=phone_number&app_absent=0';
    }
    ?>
    <a href="<?php echo $suporte_url; ?>" target="_blank" class="suporte-btn">
        <i class="fab fa-<?= $suporte_tipo === 'telegram' ? 'telegram-plane' : 'whatsapp' ?> text-xl"></i>
    </a>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Incluir o script de raspagem -->
    <script src="./scratch.js"></script>
    
    <!-- Script para partículas flutuantes -->
    <script>
        // Criar partículas flutuantes (reduzidas para otimização)
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            // Reduzindo de 15 para 6 partículas para melhorar desempenho
            const particleCount = 6;
            
            // Criar todas as partículas de uma vez com DocumentFragment para melhor desempenho
            const fragment = document.createDocumentFragment();
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Tamanho aleatório
                const size = Math.random() * 4 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                
                // Posição horizontal aleatória
                particle.style.left = Math.random() * 100 + '%';
                
                // Delay aleatório para iniciar a animação
                particle.style.animationDelay = Math.random() * 15 + 's';
                
                // Duração aleatória da animação - aumentada para reduzir a carga de processamento
                particle.style.animationDuration = (Math.random() * 15 + 15) + 's';
                
                fragment.appendChild(particle);
            }
            
            particlesContainer.appendChild(fragment);
        }

        // Inicializar partículas quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            // Adicionar classes de animação com delay
            setTimeout(() => {
                document.querySelector('.game-main-container').classList.add('fade-in');
            }, 200);
            
            setTimeout(() => {
                document.querySelector('.winners-carousel-container').classList.add('slide-up');
            }, 400);
        });

        // Atualizar saldo em tempo real - FUNÇÃO GLOBAL ACESSÍVEL DE QUALQUER LUGAR
        window.updateBalance = function() {
            fetch('../get_balance.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const saldoFormatted = 'R$ ' + parseFloat(data.balance).toLocaleString('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        
                        // Atualizar TODOS os elementos de saldo
                        // 1. Saldo no card do jogo
                        const saldoElements = document.querySelectorAll('.info-card-value');
                        if (saldoElements[0]) {
                            saldoElements[0].textContent = saldoFormatted;
                        }
                        
                        // 2. Saldo no header desktop
                        const saldoDesktop = document.getElementById('saldoDesktop');
                        if (saldoDesktop) {
                            saldoDesktop.textContent = saldoFormatted;
                        }
                        
                        // 3. Saldo no menu mobile
                        const saldoMobile = document.getElementById('saldoMobile');
                        if (saldoMobile) {
                            saldoMobile.textContent = saldoFormatted;
                        }
                        
                        console.log("Saldo atualizado para: " + saldoFormatted);
                    }
                })
                .catch(error => console.error('Erro ao atualizar saldo:', error));
        };

        // Atualizar saldo automaticamente a cada 5 segundos (reduzido para maior precisão)
        setInterval(window.updateBalance, 5000);
        
        // Atualizar o saldo imediatamente quando a página carregar
        window.updateBalance();

        // Efeito de hover para cards
        document.querySelectorAll('.info-card, .winner-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Adicionar efeitos sonoros (opcional)
        function playSound(type) {
            const audio = new Audio();
            switch(type) {
                case 'scratch':
                    audio.src = '../assets/audio/raspar.mp3';
                    break;
                case 'win':
                    audio.src = '../assets/audio/ganhou.mp3';
                    break;
                case 'lose':
                    audio.src = '../assets/audio/perdeu.mp3';
                    break;
            }
            audio.volume = 0.3;
            audio.play().catch(e => console.log('Audio play prevented:', e));
        }

        // Adicionar feedback tátil para dispositivos mobile
        function addHapticFeedback() {
            if ('vibrate' in navigator) {
                navigator.vibrate(50);
            }
        }

        // Animações do footer agora são carregadas dentro do include do footer
    </script>
</body>
</html>
