<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/affiliate_functions.php';
require_once 'includes/site_functions.php';

// Buscar configurações de suporte
$suporte_tipo = 'telegram'; // Valor padrão
$telegram_usuario = 'Suportefun777'; // Valor padrão
$whatsapp_numero = ''; // Valor padrão

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
    // Formata o número para remover caracteres não numéricos
    $whatsapp_numero_limpo = preg_replace('/\D/', '', $whatsapp_numero );
    $suporte_url = 'https://api.whatsapp.com/send/?phone=' . $whatsapp_numero_limpo . '&text&type=phone_number&app_absent=0';
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['usuario_id'];

// Buscar informações do usuário
$stmt = $conn->prepare("SELECT name, email, affiliate_status, affiliate_balance, is_agent FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: login.php');
    exit();
}

// Verificar se é afiliado
$stmt = $conn->prepare("SELECT a.affiliate_code FROM affiliates a WHERE a.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$affiliate = $result->fetch_assoc();

if (!$affiliate) {
    header('Location: inicio.php');
    exit();
}

$affiliate_code = $affiliate['affiliate_code'];

// Verificar se é agente
$is_agent = $user['is_agent'] == 1;

// Buscar estatísticas do afiliado
$stats = getAffiliateStats($conn, $user_id);

// Buscar informações detalhadas do afiliado
$stmt = $conn->prepare("SELECT * FROM affiliates WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$affiliate_info = $stmt->get_result()->fetch_assoc();

// Buscar taxa de comissão do agente se for agente
$agent_commission_rate = null;
if ($is_agent) {
    $agent_commission_rate = $affiliate_info['agent_commission_rate'] ?? 15.00;
}

// Processar ações POST para agentes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'request_payout':
            $amount = (float)$_POST['amount'];
            
            if ($amount > 0 && $amount <= $user['affiliate_balance']) {
                // Iniciar transação
                $conn->begin_transaction();

                try {
                    // Inserir solicitação de saque
                    $stmt = $conn->prepare("INSERT INTO payouts (affiliate_id, amount) VALUES (?, ?)");
                    $stmt->bind_param("id", $affiliate_info['id'], $amount);
                    $stmt->execute();

                    // Atualizar saldo do afiliado
                    $stmt = $conn->prepare("UPDATE users SET affiliate_balance = affiliate_balance - ? WHERE id = ?");
                    $stmt->bind_param("di", $amount, $user_id);
                    $stmt->execute();

                    // Confirmar transação
                    $conn->commit();
                    $success_message = "Solicitação de saque enviada com sucesso!";

                } catch (Exception $e) {
                    // Reverter transação em caso de erro
                    $conn->rollback();
                    error_log("Erro ao processar saque de afiliado: " . $e->getMessage());
                    $error_message = "Erro ao processar solicitação de saque.";
                }
            } else {
                $error_message = "Valor inválido para saque.";
            }
            break;

        case 'make_influencer':
            if ($is_agent) {
                $influenced_user_id = intval($_POST['user_id']);
                
                // Verificar se o usuário é um dos indicados do agente
                $stmt = $conn->prepare("
                    SELECT r.referred_id 
                    FROM referrals r 
                    WHERE r.referrer_id = ? AND r.referred_id = ? AND r.level = 1
                ");
                $stmt->bind_param("ii", $user_id, $influenced_user_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $conn->begin_transaction();
                    try {
                        // Gerar código único para o afiliado
                        $new_affiliate_code = uniqid('inf_');
                        
                        // Criar afiliado com taxa padrão
                        $stmt = $conn->prepare("
                            INSERT INTO affiliates (user_id, affiliate_code, revshare_commission_rate, agent_id) 
                            VALUES (?, ?, 5.00, ?)
                        ");
                        $stmt->bind_param("isi", $influenced_user_id, $new_affiliate_code, $user_id);
                        $stmt->execute();
                        
                        // Atualizar status de afiliado do usuário
                        $stmt = $conn->prepare("UPDATE users SET affiliate_status = 1 WHERE id = ?");
                        $stmt->bind_param("i", $influenced_user_id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $success_message = "Usuário transformado em influencer com sucesso!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Erro ao transformar usuário em influencer: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Usuário não encontrado entre seus indicados diretos.";
                }
            } else {
                $error_message = "Apenas agentes podem realizar esta ação.";
            }
            break;

                        case 'update_influencer_rate':
                            if ($is_agent) {
                                $influenced_user_id = intval($_POST['user_id']);
                                $new_rate = floatval($_POST['rate']);
                                
                                // Verificar se o usuário é um influencer deste agente
                                $stmt = $conn->prepare("
                                    SELECT a.id 
                                    FROM affiliates a 
                                    WHERE a.user_id = ? AND a.agent_id = ?
                                ");
                                $stmt->bind_param("ii", $influenced_user_id, $user_id);
                                $stmt->execute();
                                if ($stmt->get_result()->num_rows > 0) {
                                    // Calcular taxa máxima permitida (100% - 8% taxa - 20% casa - % agente)
                                    $max_rate = 100 - 8 - 20 - $agent_commission_rate;
                                    
                                    if ($new_rate >= 0 && $new_rate <= $max_rate) {
                                        $conn->begin_transaction();
                                        try {
                                            // Atualizar taxa REAL do influencer (para cálculos)
                                            $stmt = $conn->prepare("
                                                UPDATE affiliates 
                                                SET revshare_commission_rate_admin = ?, revshare_commission_rate = ? 
                                                WHERE user_id = ? AND agent_id = ?
                                            ");
                                            $stmt->bind_param("ddii", $new_rate, $new_rate, $influenced_user_id, $user_id);
                                            $stmt->execute();
                                            
                                            // Registrar alteração
                                            $stmt = $conn->prepare("
                                                INSERT INTO agent_rate_changes (agent_id, affiliate_id, old_rate, new_rate)
                                                SELECT ?, a.id, ?, ?
                                                FROM affiliates a
                                                WHERE a.user_id = ?
                                            ");
                                            $old_rate = $new_rate; // Para simplificar, usar o novo valor
                                            $stmt->bind_param("iddi", $user_id, $old_rate, $new_rate, $influenced_user_id);
                                            $stmt->execute();
                                            
                                            $conn->commit();
                                            $success_message = "Taxa do afiliado atualizada com sucesso!";
                                        } catch (Exception $e) {
                                            $conn->rollback();
                                            $error_message = "Erro ao atualizar taxa do afiliado: " . $e->getMessage();
                                        }
                                    } else {
                                        $error_message = "Taxa inválida. Deve estar entre 0 e " . $max_rate . "%.";
                                    }
                                } else {
                                    $error_message = "Afiliado não encontrado ou não pertence a você.";
                                }
                            } else {
                                $error_message = "Apenas agentes podem realizar esta ação.";
                            }
                            break;

        case 'disable_influencer':
            if ($is_agent) {
                $influenced_user_id = intval($_POST['user_id']);
                
                // Verificar se o usuário é um influencer deste agente
                $stmt = $conn->prepare("
                    SELECT a.id 
                    FROM affiliates a 
                    WHERE a.user_id = ? AND a.agent_id = ?
                ");
                $stmt->bind_param("ii", $influenced_user_id, $user_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $conn->begin_transaction();
                    try {
                        // Desativar o afiliado
                        $stmt = $conn->prepare("
                            UPDATE affiliates 
                            SET is_active = 0 
                            WHERE user_id = ? AND agent_id = ?
                        ");
                        $stmt->bind_param("ii", $influenced_user_id, $user_id);
                        $stmt->execute();
                        
                        // Atualizar status de afiliado do usuário
                        $stmt = $conn->prepare("UPDATE users SET affiliate_status = 0 WHERE id = ?");
                        $stmt->bind_param("i", $influenced_user_id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $success_message = "Afiliado desabilitado com sucesso!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Erro ao desabilitar afiliado: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Afiliado não encontrado ou não pertence a você.";
                }
            } else {
                $error_message = "Apenas agentes podem realizar esta ação.";
            }
            break;

        case 'toggle_influence_mode':
            if ($is_agent) {
                $target_user_id = intval($_POST['user_id']);
                $new_state = intval($_POST['state']);
                
                // Verificar se o usuário é um dos indicados do agente
                $stmt = $conn->prepare("
                    SELECT r.referred_id 
                    FROM referrals r 
                    WHERE r.referrer_id = ? AND r.referred_id = ? AND r.level = 1
                ");
                $stmt->bind_param("ii", $user_id, $target_user_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $conn->begin_transaction();
                    try {
                        // Atualizar ou inserir configuração de modo influência
                        $stmt = $conn->prepare("
                            INSERT INTO user_settings (user_id, influence_mode_enabled) 
                            VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE influence_mode_enabled = VALUES(influence_mode_enabled)
                        ");
                        $stmt->bind_param("ii", $target_user_id, $new_state);
                        $stmt->execute();
                        
                        $conn->commit();
                        $success_message = "Modo influência " . ($new_state ? "ativado" : "desativado") . " com sucesso!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Erro ao alterar modo influência: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Usuário não encontrado entre seus indicados diretos.";
                }
            } else {
                $error_message = "Apenas agentes podem realizar esta ação.";
            }
            break;
    }
}

// Buscar histórico de comissões - filtrar canceladas se configurado para ocultar descontos
$commissions_query = "
    SELECT c.* 
    FROM commissions c 
    WHERE c.affiliate_id = ?";

if (!$stats['show_deductions']) {
    $commissions_query .= " AND c.status != 'cancelled'";
}

$commissions_query .= " ORDER BY c.created_at DESC LIMIT 20";

$stmt = $conn->prepare($commissions_query);
$stmt->bind_param("i", $affiliate_info['id']);
$stmt->execute();
$commissions_history = $stmt->get_result();

// Buscar solicitações de saque
$stmt = $conn->prepare("SELECT * FROM payouts WHERE affiliate_id = ? ORDER BY request_date DESC LIMIT 10");
$stmt->bind_param("i", $affiliate_info['id']);
$stmt->execute();
$payouts_history = $stmt->get_result();

// Função para mascarar email
function maskEmail($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return str_repeat('*', 10);
    
    $name = $parts[0];
    $domain = $parts[1];
    
    $maskedName = substr($name, 0, 1) . str_repeat('*', strlen($name) - 1);
    return $maskedName . '@' . $domain;
}

// Função para mascarar nome
function maskName($name) {
    $length = strlen($name);
    if ($length <= 2) return str_repeat('*', 5);
    
    return substr($name, 0, 1) . str_repeat('*', $length - 2) . substr($name, -1);
}

// Buscar indicações diretas
if ($is_agent) {
    // Para agentes, buscar todos os indicados
    $stmt = $conn->prepare("
        SELECT u.name, u.email, r.created_at, r.referred_id, 
               a.id as affiliate_id, a.revshare_commission_rate,
               CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END as is_influencer,
               COALESCE(us.influence_mode_enabled, 0) as influence_mode_enabled
        FROM referrals r 
        JOIN users u ON r.referred_id = u.id 
        LEFT JOIN affiliates a ON u.id = a.user_id AND a.agent_id = ?
        LEFT JOIN user_settings us ON u.id = us.user_id
        WHERE r.referrer_id = ? AND r.level = 1 
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
} else {
    // Para afiliados normais, buscar apenas indicados básicos
    $stmt = $conn->prepare("
        SELECT u.name, u.email, r.created_at, r.referred_id
        FROM referrals r 
        JOIN users u ON r.referred_id = u.id 
        WHERE r.referrer_id = ? AND r.level = 1 
        ORDER BY r.created_at DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$direct_referrals = $stmt->get_result();

// Gerar link de afiliado
$affiliate_link = "http://" . $_SERVER['HTTP_HOST'] . "/inicio.php?ref=" . $affiliate_code;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Afiliado - <?php echo get_site_name(); ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #7257b4;
            --secondary-color: #6876df;
            --success-color: #10b981;
            --warning-color: #fbbf24;
            --danger-color: #ef4444;
            --dark-color: #202c3e;
            --light-color: #f8fafc;
        }

        body {
            background: linear-gradient(135deg, var(--dark-color) 0%, #1e293b 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .main-container {
            padding: 2rem 0;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(114, 87, 180, 0.3);
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            color: white;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(114, 87, 180, 0.4);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .affiliate-link-card {
            background: linear-gradient(135deg, var(--success-color), #059669);
            border-radius: 1rem;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
        }

        .link-input {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 0.5rem;
            color: white;
            padding: 0.75rem;
            width: 100%;
            margin: 1rem 0;
        }

        .link-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .copy-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .table-dark {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .table-dark th {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            font-weight: 600;
        }

        .table-dark td {
            border-color: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.9);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(114, 87, 180, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #059669);
            border: none;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color), #f59e0b);
            border: none;
            color: #1f2937;
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
        }

        .modal-content {
            background: var(--dark-color);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 0.5rem;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(114, 87, 180, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
        }

        .commission-card {
            background: linear-gradient(135deg, var(--warning-color), #f59e0b);
            border-radius: 1rem;
            padding: 1.5rem;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .level-indicator {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            text-align: center;
            line-height: 2rem;
            font-weight: 700;
            margin-right: 0.5rem;
        }

        .agent-card {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border-radius: 1rem;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
        }

        .influencer-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .rate-input {
            width: 80px;
            text-align: center;
        }

        .performance-stats {
            padding: 1rem;
        }

        .stat-item {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            text-align: center;
        }

        .stat-item h6 {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .dashboard-card {
                padding: 1.5rem;
            }
            
            .stat-value {
                font-size: 2rem;
            }
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
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="inicio.php">
                <i class="bi bi-coins"></i> <?php echo get_site_name(); ?> - Afiliados
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="perfil.php">
                    <i class="bi bi-person"></i> Perfil
                </a>
                <a class="nav-link text-white" href="<?php echo htmlspecialchars($suporte_url); ?>" target="_blank">
    <i class="bi bi-telegram"></i> Suporte
</a>

                <a class="nav-link text-white" href="raspadinhas">
                    <i class="bi bi-box-arrow-right"></i> Inicio 
                </a>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <!-- Mensagens -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Boas-vindas -->
        <div class="dashboard-card fade-in">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">Bem-vindo, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                    <p class="mb-0 opacity-75">Gerencie suas indicações e acompanhe seus ganhos como afiliado</p>
                    <?php if ($is_agent): ?>
                        <div class="mt-2">
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-star-fill"></i> AGENTE
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-end">
                    <div class="badge bg-success fs-6">
                        <i class="bi bi-check-circle"></i> Afiliado Ativo
                    </div>
                </div>
            </div>
        </div>

        <!-- Link de Afiliado -->
        <div class="affiliate-link-card fade-in">
            <h4 class="mb-3">
                <i class="bi bi-link-45deg"></i> Seu Link de Afiliado
            </h4>
            <p class="mb-3">Compartilhe este link para ganhar comissões por cada pessoa que se cadastrar:</p>
            <div class="row align-items-center">
                <div class="col-md-9">
                    <input type="text" class="link-input" id="affiliateLink" value="<?php echo $affiliate_link; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <button class="copy-btn w-100" onclick="copyAffiliateLink()">
                        <i class="bi bi-clipboard"></i> Copiar
                    </button>
                </div>
            </div>
            <small class="opacity-75">
                <i class="bi bi-info-circle"></i> 
                Seu código único: <strong><?php echo $affiliate_code; ?></strong>
            </small>
        </div>

        <!-- Estatísticas -->
        <div class="row fade-in">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['clicks']); ?></div>
                    <div class="stat-label">Cliques</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['signups']); ?></div>
                    <div class="stat-label">Cadastros</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['deposits']); ?></div>
                    <div class="stat-label">Depósitos</div>
                </div>
            </div>
        </div>

        <!-- Estatísticas Financeiras -->
        <div class="row fade-in mt-4">
            <div class="<?php echo $stats['show_deductions'] ? 'col-md-4' : 'col-md-6'; ?>">
                <div class="stat-card bg-success">
                    <div class="stat-value">R$ <?php echo number_format($stats['total_commission'], 2, ',', '.'); ?></div>
                    <div class="stat-label">Total de Comissões</div>
                </div>
            </div>
            <?php if ($stats['show_deductions']): ?>
            <div class="col-md-4">
                <div class="stat-card bg-danger">
                    <div class="stat-value">R$ <?php echo number_format($stats['total_deductions'], 2, ',', '.'); ?></div>
                    <div class="stat-label">Total de Descontos</div>
                </div>
            </div>
            <?php endif; ?>
            <div class="<?php echo $stats['show_deductions'] ? 'col-md-4' : 'col-md-6'; ?>">
                <div class="stat-card">
                    <div class="stat-value">R$ <?php echo number_format($stats['balance'], 2, ',', '.'); ?></div>
                    <div class="stat-label">Saldo Disponível</div>
                </div>
            </div>
        </div>

        <!-- Comissões por Tipo -->
        <div class="row fade-in">
            <div class="col-md-12">
                <div class="commission-card">
                    <h5 class="mb-2">
                        <i class="bi bi-arrow-repeat"></i> Comissão RevShare
                    </h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0">R$ <?php echo number_format($stats["revshare_commission"], 2, ",", "."); ?></div>
                            <small>Por depósitos realizados</small>
                        </div>
                        <div class="text-end">
                            <div class="h6 mb-0"><?php echo number_format($affiliate_info["revshare_commission_rate"], 1); ?>%</div>
                            <small>Taxa atual</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($is_agent): ?>
        <!-- Painel do Agente -->
        <div class="agent-card fade-in">
            <h4 class="mb-3">
                <i class="bi bi-star-fill"></i> Painel do Agente
            </h4>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Taxa de Comissão do Agente:</strong> <?php echo number_format($agent_commission_rate, 1); ?>%</p>
                    <p class="mb-2"><strong>Taxa Máxima para Influencers:</strong> <?php echo number_format(100 - 8 - 20 - $agent_commission_rate, 1); ?>%</p>
                </div>
                <div class="col-md-6">
                    <div class="text-end">
                        <small>Distribuição:</small><br>
                        <small>• 8% Taxa de Pagamento</small><br>
                        <small>• 20% Casa</small><br>
                        <small>• <?php echo number_format($agent_commission_rate, 1); ?>% Agente</small><br>
                        <small>• Restante: Influencer + Distribuição</small>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ações Rápidas -->
        <div class="dashboard-card fade-in">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-3">Ações Rápidas</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#payoutModal">
                                <i class="bi bi-cash-coin"></i> Solicitar Saque
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button class="btn btn-success w-100" onclick="shareOnWhatsApp()">
                                <i class="bi bi-whatsapp"></i> Compartilhar no WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h5>Total de Comissões</h5>
                        <div class="h2 text-success">R$ <?php echo number_format($stats["revshare_commission"], 2, ",", "."); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Histórico de Comissões -->
        <div class="dashboard-card fade-in">
            <h4 class="mb-3">
                <i class="bi bi-clock-history"></i> Histórico de Comissões
            </h4>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Nível</th>
                            <th>Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($commissions_history->num_rows > 0): ?>
                            <?php while ($commission = $commissions_history->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($commission['created_at'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $commission['type'] == 'CPA' ? 'bg-primary' : 'bg-warning'; ?>">
                                        <?php echo $commission['type']; ?>
                                    </span>
                                </td>
                                <td><?php echo $commission['level']; ?></td>
                                <td class="text-success">R$ <?php echo number_format($commission['amount'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-success">Aprovada</span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-inbox"></i> Nenhuma comissão encontrada
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Histórico de Saques -->
        <div class="dashboard-card fade-in">
            <h4 class="mb-3">
                <i class="bi bi-cash-stack"></i> Histórico de Saques
            </h4>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Data da Solicitação</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data do Pagamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($payouts_history->num_rows > 0): ?>
                            <?php while ($payout = $payouts_history->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($payout['request_date'])); ?></td>
                                <td class="text-success">R$ <?php echo number_format($payout['amount'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $payout['status'] == 'paid' ? 'bg-success' : 
                                             ($payout['status'] == 'pending' ? 'bg-warning' : 'bg-danger'); 
                                    ?>">
                                        <?php 
                                        echo $payout['status'] == 'paid' ? 'Pago' : 
                                             ($payout['status'] == 'pending' ? 'Pendente' : 'Cancelado'); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    echo isset($payout['paid_date']) && $payout['paid_date'] ? date('d/m/Y H:i', strtotime($payout['paid_date'])) : '-'; 
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="bi bi-inbox"></i> Nenhum saque solicitado ainda
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Indicações Diretas -->
        <div class="dashboard-card fade-in">
            <h4 class="mb-3">
                <i class="bi bi-people"></i> Suas Indicações Diretas
                <?php if ($is_agent): ?>
                    <small class="text-muted">(Como Agente)</small>
                <?php endif; ?>
            </h4>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Data do Cadastro</th>
                            <?php if ($is_agent): ?>
                                <th>Status</th>
                                <th>Taxa (%)</th>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($direct_referrals->num_rows > 0): ?>
                            <?php while ($referral = $direct_referrals->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo maskName(htmlspecialchars($referral['name'])); ?></td>
                                <td><?php echo maskEmail(htmlspecialchars($referral['email'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($referral['created_at'])); ?></td>
                                <?php if ($is_agent): ?>
                                    <td>
                                        <?php if ($referral['is_influencer']): ?>
                                            <span class="badge bg-primary">
                                                <i class="bi bi-star-fill"></i> Influencer
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Usuário Normal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($referral['is_influencer']): ?>
                                            <span class="text-warning fw-bold">
                                                <?php echo number_format($referral['revshare_commission_rate'], 1); ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$referral['is_influencer']): ?>
                                            <!-- Usuário ainda não é afiliado - apenas botão para tornar afiliado -->
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="make_influencer">
                                                <input type="hidden" name="user_id" value="<?php echo $referral['referred_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-primary w-100" onclick="return confirm('Transformar este usuário em afiliado?')">
                                                    <i class="bi bi-star"></i> Tornar afiliado
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Usuário já é afiliado - controles completos -->
                                            <div class="d-flex flex-column gap-1">
                                                <!-- Botão para ativar/desativar modo influência -->
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle_influence_mode">
                                                    <input type="hidden" name="user_id" value="<?php echo $referral['referred_id']; ?>">
                                                    <input type="hidden" name="state" value="<?php echo $referral['influence_mode_enabled'] ? '0' : '1'; ?>">
                                                    <button type="submit" class="btn btn-sm <?php echo $referral['influence_mode_enabled'] ? 'btn-warning' : 'btn-info'; ?> w-100">
                                                        <i class="bi bi-<?php echo $referral['influence_mode_enabled'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                                        <?php echo $referral['influence_mode_enabled'] ? 'Desativar' : 'Ativar'; ?> Influência
                                                    </button>
                                                </form>
                                                
                                                <!-- Campo para ajustar taxa de comissão -->
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="update_influencer_rate">
                                                    <input type="hidden" name="user_id" value="<?php echo $referral['referred_id']; ?>">
                                                    <div class="input-group input-group-sm" style="width: 120px;">
                                                        <input type="number" class="form-control rate-input" name="rate" 
                                                               value="<?php echo $referral['revshare_commission_rate']; ?>" 
                                                               min="0" max="<?php echo 100 - 8 - 20 - $agent_commission_rate; ?>" step="0.1">
                                                        <button type="submit" class="btn btn-outline-warning btn-sm">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                                
                                                <!-- Botão para desabilitar afiliado -->
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="disable_influencer">
                                                    <input type="hidden" name="user_id" value="<?php echo $referral['referred_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Tem certeza que deseja desabilitar este afiliado? Esta ação removerá o status de afiliado do usuário.')">
                                                        <i class="bi bi-x-circle"></i> Desabilitar Afiliado
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $is_agent ? '6' : '3'; ?>" class="text-center py-4">
                                    <i class="bi bi-person-plus"></i> Nenhuma indicação ainda
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($is_agent): ?>
                <div class="mt-3">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Informações para Agentes:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Taxa máxima para influencers: <?php echo number_format(100 - 8 - 20 - $agent_commission_rate, 1); ?>%</li>
                            <li>Cálculo: 100% - 8% (taxa) - 20% (casa) - <?php echo number_format($agent_commission_rate, 1); ?>% (agente) = <?php echo number_format(100 - 8 - 20 - $agent_commission_rate, 1); ?>% máximo</li>
                            <li>O restante vai para distribuição (afeta chance de vitória dos usuários do influencer)</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Saque -->
    <div class="modal fade" id="payoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-cash-coin"></i> Solicitar Saque
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="request_payout">
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Saldo Disponível</label>
                            <div class="h4 text-success">R$ <?php echo number_format($stats['balance'], 2, ',', '.'); ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Valor do Saque</label>
                            <input type="number" class="form-control" name="amount" step="0.01" min="10" max="<?php echo $stats['balance']; ?>" placeholder="Valor mínimo: R$ 10,00" required>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Informações importantes:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Valor mínimo para saque: R$ 10,00</li>
                                <li>Processamento em até 2 dias úteis</li>
                                <li>Pagamento via PIX</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Solicitar Saque</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function copyAffiliateLink() {
            const linkInput = document.getElementById('affiliateLink');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999);
            document.execCommand('copy');
            
            // Feedback visual
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check"></i> Copiado!';
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
            }, 2000);
        }

        function shareOnWhatsApp() {
            const link = document.getElementById('affiliateLink').value;
            const message = `🎯 Venha jogar raspadinha online e ganhar dinheiro de verdade!\n\n💰 Cadastre-se pelo meu link e comece a ganhar:\n${link}\n\n🎮 Raspadinhas virtuais com prêmios em PIX!\n✅ Pagamento instantâneo\n🔒 100% seguro`;
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }

        // Animação de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
