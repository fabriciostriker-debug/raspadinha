<?php
// Incluir funções do site
require_once __DIR__ . '/site_functions.php';

// Variáveis padrão do footer, caso não venham da página
$logo_path = $logo_path ?? 'img/logo.webp';
$suporte_tipo = $suporte_tipo ?? 'telegram';
$telegram_usuario = $telegram_usuario ?? 'Suportefun777';
$whatsapp_numero = $whatsapp_numero ?? '';

// Tenta carregar configurações do banco se a conexão existir
if (isset($conn) && $conn) {
    // Logo principal
    try {
        $logo_stmt = $conn->prepare("SELECT valor FROM configuracoes WHERE chave = 'logo_principal'");
        if ($logo_stmt && $logo_stmt->execute()) {
            $logo_result = $logo_stmt->get_result();
            if ($logo_result && $logo_result->num_rows > 0) {
                $row = $logo_result->fetch_assoc();
                if (!empty($row['valor'])) {
                    $logo_path = $row['valor'];
                }
            }
        }
    } catch (Throwable $e) {
        // Silenciar erros do footer
    }

    // Suporte (telegram/whatsapp)
    try {
        $suporte_query = $conn->prepare("SELECT chave, valor FROM configuracoes WHERE chave IN ('suporte_tipo', 'suporte_telegram_usuario', 'suporte_whatsapp_numero')");
        if ($suporte_query && $suporte_query->execute()) {
            $suporte_result = $suporte_query->get_result();
            while ($suporte_row = $suporte_result->fetch_assoc()) {
                switch ($suporte_row['chave']) {
                    case 'suporte_tipo':
                        $suporte_tipo = $suporte_row['valor'] ?: $suporte_tipo;
                        break;
                    case 'suporte_telegram_usuario':
                        $telegram_usuario = $suporte_row['valor'] ?: $telegram_usuario;
                        break;
                    case 'suporte_whatsapp_numero':
                        $whatsapp_numero = $suporte_row['valor'] ?: $whatsapp_numero;
                        break;
                }
            }
        }
    } catch (Throwable $e) {
        // Silenciar erros do footer
    }
}

$suporte_url = ($suporte_tipo === 'telegram')
    ? ('https://t.me/' . $telegram_usuario)
    : ('https://api.whatsapp.com/send/?phone=' . $whatsapp_numero . '&text&type=phone_number&app_absent=0');
?>

<footer class="site-footer bg-gradient-to-r from-dark-bg via-card-bg to-dark-bg border-t border-neon-green/20 mt-16 py-12 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <!-- Logo e Descrição -->
            <div class="md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="<?php echo get_site_name(); ?>" class="h-10 w-auto">
                    <div class="text-2xl font-bold bg-gradient-to-r from-neon-green to-accent-purple bg-clip-text text-transparent">
                        <?php echo get_site_name(); ?>
                    </div>
                </div>
                <p class="text-gray-300 leading-relaxed mb-4">
                    🎮 A melhor plataforma de raspadinhas virtuais do Brasil! 
                    Diversão garantida com prêmios reais e saques instantâneos via PIX.
                </p>
                <div class="flex gap-4">
                    <a href="<?php echo htmlspecialchars($suporte_url); ?>" target="_blank" class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 px-4 py-2 rounded-lg transition-all hover:scale-105 flex items-center gap-2">
                        <i class="fab fa-<?php echo $suporte_tipo === 'telegram' ? 'telegram-plane' : 'whatsapp';?>"></i>
                        Suporte
                    </a>
                    <button onclick="abrirDeposito()" class="bg-neon-green/20 hover:bg-neon-green/30 text-neon-green px-4 py-2 rounded-lg transition-all hover:scale-105 flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        Depositar
                    </button>
                </div>
            </div>

            <!-- Links Rápidos -->
            <div>
                <h4 class="text-white font-semibold text-lg mb-4 flex items-center gap-2">
                    <i class="fas fa-link text-neon-green"></i>
                    Links Rápidos
                </h4>
                <ul class="space-y-3">
                    <li><a href="inicio.php" class="text-gray-300 hover:text-neon-green transition-colors flex items-center gap-2">
                        <i class="fas fa-home text-sm"></i>Início
                    </a></li>
                    <li><a href="inicio.php#raspadinhas" class="text-gray-300 hover:text-neon-green transition-colors flex items-center gap-2">
                        <i class="fas fa-gamepad text-sm"></i>Raspadinhas
                    </a></li>
                    <li><a href="premios" class="text-gray-300 hover:text-neon-green transition-colors flex items-center gap-2">
                        <i class="fas fa-trophy text-sm"></i>Ganhadores
                    </a></li>
                    <li><a href="perfil.php" class="text-gray-300 hover:text-neon-green transition-colors flex items-center gap-2">
                        <i class="fas fa-user text-sm"></i>Meu Perfil
                    </a></li>
                </ul>
            </div>

            <!-- Informações -->
            <div>
                <h4 class="text-white font-semibold text-lg mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-accent-gold"></i>
                    Informações
                </h4>
                <ul class="space-y-3">
                    <li class="text-gray-300 flex items-center gap-2">
                        <i class="fas fa-shield-alt text-neon-green text-sm"></i>
                        100% Seguro
                    </li>
                    <li class="text-gray-300 flex items-center gap-2">
                        <i class="fas fa-bolt text-yellow-400 text-sm"></i>
                        Saques Instantâneos
                    </li>
                    <li class="text-gray-300 flex items-center gap-2">
                        <i class="fas fa-mobile-alt text-blue-400 text-sm"></i>
                        Mobile Friendly
                    </li>
                    <li class="text-gray-300 flex items-center gap-2">
                        <i class="fas fa-headset text-purple-400 text-sm"></i>
                        Suporte 24/7
                    </li>
                </ul>
            </div>
        </div>

        <!-- Separador -->
        <div class="border-t border-neon-green/20 mb-6"></div>

        <!-- Estatísticas -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="text-center bg-gradient-to-br from-neon-green/10 to-transparent p-4 rounded-xl border border-neon-green/20">
                <div class="text-2xl font-bold text-neon-green mb-1">10K+</div>
                <div class="text-sm text-gray-400">Jogadores Ativos</div>
            </div>
            <div class="text-center bg-gradient-to-br from-accent-gold/10 to-transparent p-4 rounded-xl border border-accent-gold/20">
                <div class="text-2xl font-bold text-accent-gold mb-1">R$ 50M+</div>
                <div class="text-sm text-gray-400">Prêmios Pagos</div>
            </div>
            <div class="text-center bg-gradient-to-br from-purple-500/10 to-transparent p-4 rounded-xl border border-purple-500/20">
                <div class="text-2xl font-bold text-purple-400 mb-1">99.9%</div>
                <div class="text-sm text-gray-400">Uptime</div>
            </div>
            <div class="text-center bg-gradient-to-br from-blue-500/10 to-transparent p-4 rounded-xl border border-blue-500/20">
                <div class="text-2xl font-bold text-blue-400 mb-1">5 min</div>
                <div class="text-sm text-gray-400">Saque Médio</div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="text-center">
            <div class="bg-gradient-to-r from-transparent via-card-bg to-transparent p-6 rounded-2xl border border-neon-green/10">
                <p class="text-gray-400 text-sm mb-2">
                    © 2024 <?php echo get_site_name(); ?>. Todos os direitos reservados.
                </p>
                <p class="text-xs text-gray-500">
                    🔞 Jogo responsável. Maiores de 18 anos. 
                    <span class="text-neon-green">Jogue com responsabilidade!</span>
                </p>
                <div class="flex justify-center items-center gap-4 mt-4">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <i class="fas fa-clock text-neon-green"></i>
                        Online: <span id="uptime" class="text-neon-green font-mono">99.9%</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <i class="fas fa-users text-accent-gold"></i>
                        Jogadores: <span id="online-count" class="text-accent-gold font-mono">1,247</span>
                    </div>
                </div>
            </div>
        </div>
		<br>
    </div>
	<br>
	<br>
	<br>
</footer>

<script>
// Animação de números no footer
(function animateNumbers() {
    const uptime = document.getElementById('uptime');
    const onlineCount = document.getElementById('online-count');
    if (!uptime || !onlineCount) return;

    setInterval(() => {
        const variation = (Math.random() * 0.2 - 0.1).toFixed(1);
        const currentUptime = (99.9 + parseFloat(variation)).toFixed(1);
        uptime.textContent = Math.min(99.9, Math.max(99.0, currentUptime)) + '%';
    }, 5000);

    setInterval(() => {
        const variation = Math.floor(Math.random() * 100 - 50);
        const currentCount = 1247 + variation;
        onlineCount.textContent = Math.max(1000, currentCount).toLocaleString('pt-BR');
    }, 3000);
})();
</script>
