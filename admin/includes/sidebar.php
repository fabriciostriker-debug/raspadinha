<?php
// Determina qual página está ativa com base no nome do arquivo atual
$current_page = basename($_SERVER['PHP_SELF']);

// Função simples para verificar se o link atual está ativo
function is_active($page_name) {
    global $current_page;
    return ($current_page == $page_name) ? 'active' : '';
}
?>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-brand">
            <i class="bi bi-speedometer2"></i>
            V2 RASPA
        </a>
    </div>
    
    <div class="sidebar-nav">
        <div class="nav-item">
            <a href="index.php" class="nav-link <?php echo is_active('index.php'); ?>">
                <i class="bi bi-house-door"></i>
                Dashboard
            </a>
        </div>
        <div class="nav-item">
            <a href="usuarios.php" class="nav-link <?php echo is_active('usuarios.php'); ?>">
                <i class="bi bi-people"></i>
                Gerenciar Usuários
            </a>
        </div>
        <div class="nav-item">
            <a href="global_settings.php" class="nav-link <?php echo is_active('global_settings.php'); ?>">
                <i class="bi bi-gear-wide-connected"></i>
                Configurações Globais
            </a>
        </div>
        <div class="nav-item">
            <a href="notificacoes.php" class="nav-link <?php echo is_active('notificacoes.php'); ?>">
                <i class="bi bi-bell"></i>
                Notificações iOS/Android
            </a>
        </div>
        <div class="nav-item">
            <a href="affiliates.php" class="nav-link <?php echo is_active('affiliates.php'); ?>">
                <i class="bi bi-share"></i>
                Gestão de Afiliados
            </a>
        </div>
        <div class="nav-item">
            <a href="payout_management.php" class="nav-link <?php echo is_active('payout_management.php'); ?>">
                <i class="bi bi-cash-coin"></i>
                Saque de Afiliados
            </a>
        </div>
        <div class="nav-item">
            <a href="affiliate_levels.php" class="nav-link <?php echo is_active('affiliate_levels.php'); ?>">
                <i class="bi bi-diagram-3"></i>
                Níveis de Afiliados
            </a>
        </div>
        <div class="nav-item">
            <a href="depositos.php" class="nav-link <?php echo is_active('depositos.php'); ?>">
                <i class="bi bi-wallet2"></i>
                Ver Depósitos
            </a>
        </div>
        <div class="nav-item">
            <a href="controle_raspadinha.php" class="nav-link <?php echo is_active('controle_raspadinha.php'); ?>">
                <i class="bi bi-dice-6"></i>
                Controle de Raspadinha
            </a>
        </div>
        <div class="nav-item">
            <a href="configuracoes.php" class="nav-link <?php echo is_active('configuracoes.php'); ?>">
                <i class="bi bi-credit-card"></i>
                Configuração de gateway
            </a>
        </div>
        <div class="nav-item">
            <a href="saques_pix.php" class="nav-link <?php echo is_active('saques_pix.php'); ?>">
                <i class="bi bi-cash-stack"></i>
                Saques
            </a>
        </div>
        <div class="nav-item">
            <a href="rtp.php" class="nav-link <?php echo is_active('rtp.php'); ?>">
                <i class="bi bi-gear"></i>
                Teste de RTP
            </a>
        </div>
        <div class="nav-item">
            <a href="identidade_visual.php" class="nav-link <?php echo is_active('identidade_visual.php'); ?>">
                <i class="bi bi-brush"></i>
                Identidade Visual
            </a>
        </div>
        <div class="nav-item">
            <a href="suporte.php" class="nav-link <?php echo is_active('suporte.php'); ?>">
                <i class="bi bi-headset"></i>
                Suporte - Telegram/WhatsApp
            </a>
        </div>
        <div class="nav-item">
            <a href="relatorio.php" class="nav-link <?php echo is_active('relatorio.php'); ?>">
                <i class="bi bi-graph-up"></i>
                Relatórios
            </a>
        </div>
        <div class="nav-item">
            <a href="pixel.php" class="nav-link <?php echo is_active('pixel.php'); ?>">
                <i class="bi bi-facebook"></i>
                Pixel do Facebook
            </a>
        </div>
        <div class="nav-item">
            <a href="kwai_pixel.php" class="nav-link <?php echo is_active('kwai_pixel.php'); ?>">
                <i class="bi bi-play-circle"></i>
                Pixel do Kwai
            </a>
        </div>

        <hr style="border-color: rgba(255, 255, 255, 0.1); margin: 1rem;">
        <div class="nav-item">
            <a href="logout.php" class="nav-link logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                Sair
            </a>
        </div>
    </div>
</nav>

<!-- Mobile Header -->
<header class="mobile-header">
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="bi bi-list"></i>
    </button>
    <a href="index.php" class="mobile-brand">
        <i class="bi bi-speedometer2 me-2"></i>
        V2 RASPA
    </a>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success">Online</span>
    </div>
</header>
