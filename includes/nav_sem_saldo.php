<?php
// Incluir funções do site
require_once __DIR__ . '/site_functions.php';

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
?>

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
            $logo_path = 'img/logo.webp'; // Valor padrão
            
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
                <!-- Removido o mostrador de saldo aqui -->
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
