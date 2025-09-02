<?php
session_start();
require_once '../includes/db.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

$message = '';
$message_type = '';

// Verificar se as configurações já existem e criar se necessário
$check = $conn->query("SELECT COUNT(*) as count FROM global_settings WHERE setting_key = 'pushover_api_token'");
$row = $check->fetch_assoc();

if ($row['count'] == 0) {
    // Inserir configurações padrão para o Pushover
    $conn->query("INSERT INTO global_settings (setting_key, setting_value, description) 
                 VALUES 
                 ('pushover_api_token', '', 'API Token para notificações Pushover'),
                 ('pushover_user_key', '', 'User Key para notificações Pushover'),
                 ('pushover_enabled', '0', 'Ativar notificações Pushover (1=Sim, 0=Não)'),
                 ('pushover_notify_pix_generated', '0', 'Notificar quando PIX for gerado (1=Sim, 0=Não)'),
                 ('pushover_notify_pix_paid', '1', 'Notificar quando PIX for pago (1=Sim, 0=Não)')");
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_settings'])) {
        // Atualizar configurações
        $api_token = $conn->real_escape_string($_POST['pushover_api_token']);
        $user_key = $conn->real_escape_string($_POST['pushover_user_key']);
        $enabled = isset($_POST['pushover_enabled']) ? '1' : '0';
        $notify_generated = isset($_POST['pushover_notify_pix_generated']) ? '1' : '0';
        $notify_paid = isset($_POST['pushover_notify_pix_paid']) ? '1' : '0';
        
        $conn->query("UPDATE global_settings SET setting_value = '$api_token' WHERE setting_key = 'pushover_api_token'");
        $conn->query("UPDATE global_settings SET setting_value = '$user_key' WHERE setting_key = 'pushover_user_key'");
        $conn->query("UPDATE global_settings SET setting_value = '$enabled' WHERE setting_key = 'pushover_enabled'");
        $conn->query("UPDATE global_settings SET setting_value = '$notify_generated' WHERE setting_key = 'pushover_notify_pix_generated'");
        $conn->query("UPDATE global_settings SET setting_value = '$notify_paid' WHERE setting_key = 'pushover_notify_pix_paid'");
        
        $message = "Configurações salvas com sucesso!";
        $message_type = "success";
    }
    
    // Testar envio de notificação
    if (isset($_POST['test_notification'])) {
        $api_token = $conn->real_escape_string($_POST['pushover_api_token']);
        $user_key = $conn->real_escape_string($_POST['pushover_user_key']);
        
        if (!empty($api_token) && !empty($user_key)) {
            // Enviar notificação de teste
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => "https://api.pushover.net/1/messages.json",
                CURLOPT_POSTFIELDS => array(
                    "token" => $api_token,
                    "user" => $user_key,
                    "message" => "Teste de notificação ✅ Sistema de raspadinha",
                ),
                CURLOPT_SAFE_UPLOAD => true,
                CURLOPT_RETURNTRANSFER => true,
            ));
            
            $response = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($status == 200) {
                $message = "Notificação de teste enviada com sucesso!";
                $message_type = "success";
            } else {
                $response_data = json_decode($response, true);
                $message = "Erro ao enviar notificação: " . ($response_data['errors'][0] ?? 'Erro desconhecido');
                $message_type = "danger";
            }
        } else {
            $message = "API Token e User Key são obrigatórios para enviar uma notificação de teste.";
            $message_type = "warning";
        }
    }
}

// Buscar configurações atuais
$result = $conn->query("SELECT setting_key, setting_value FROM global_settings 
                        WHERE setting_key IN ('pushover_api_token', 'pushover_user_key', 'pushover_enabled', 
                        'pushover_notify_pix_generated', 'pushover_notify_pix_paid')");

$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações iOS/Android - Painel Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/sidebar.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #7257b4;
            --secondary-color: #6876df;
            --success-color: #10b981;
            --warning-color: #fbbf24;
            --danger-color: #ef4444;
            --dark-color: #202c3e;
        }

        body {
            background: linear-gradient(135deg, var(--dark-color) 0%, #1e293b 100%);
            min-height: 100vh;
			 padding-left: var(--sidebar-width);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .main-container {
            padding: 1rem;
        }

        @media (min-width: 768px) {
            .main-container {
                padding: 2rem;
            }
        }

        .settings-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .setting-group {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 0.5rem;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(114, 87, 180, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 0.5rem;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #059669);
            border: none;
            border-radius: 0.5rem;
        }
        
        .tutorial-steps li {
            margin-bottom: 0.75rem;
        }
        
        .app-store-badge {
            height: 40px;
            margin: 0.5rem 0.5rem 0.5rem 0;
        }
    </style>
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>

    <div class="container main-container main-content">
        <!-- Cabeçalho da Página -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-bell"></i> Notificações iOS/Android</h2>
        </div>
        
        <!-- Mensagens de Feedback -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Configurações do Pushover -->
            <div class="col-lg-6">
                <div class="settings-card">
                    <h4 class="mb-4"><i class="bi bi-bell-fill"></i> Configurações do Pushover</h4>
                    
                    <form method="POST" action="">
                        <div class="setting-group">
                            <div class="mb-3">
                                <label for="pushover_api_token" class="form-label">API Token/Key</label>
                                <input type="text" class="form-control" id="pushover_api_token" name="pushover_api_token" 
                                       value="<?php echo htmlspecialchars($settings['pushover_api_token'] ?? ''); ?>" 
                                       placeholder="Ex: a2iujytref45h5cdwue7npua">
                                <div class="form-text text-light">API Token da sua aplicação no Pushover.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="pushover_user_key" class="form-label">User Key</label>
                                <input type="text" class="form-control" id="pushover_user_key" name="pushover_user_key" 
                                       value="<?php echo htmlspecialchars($settings['pushover_user_key'] ?? ''); ?>" 
                                       placeholder="Ex: uws42trgefdasdjhtuehakp4w3ce2m">
                                <div class="form-text text-light">Chave de usuário da sua conta Pushover.</div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="pushover_enabled" name="pushover_enabled" 
                                       <?php echo ($settings['pushover_enabled'] ?? '') == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="pushover_enabled">
                                    Ativar notificações Pushover
                                </label>
                            </div>
                        </div>
                        
                        <div class="setting-group">
                            <h5 class="mb-3">Tipos de Notificações</h5>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="pushover_notify_pix_generated" name="pushover_notify_pix_generated" 
                                       <?php echo ($settings['pushover_notify_pix_generated'] ?? '') == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="pushover_notify_pix_generated">
                                    Notificar quando PIX for gerado
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="pushover_notify_pix_paid" name="pushover_notify_pix_paid" 
                                       <?php echo ($settings['pushover_notify_pix_paid'] ?? '') == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="pushover_notify_pix_paid">
                                    Notificar quando PIX for pago
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" name="save_settings" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Configurações
                            </button>
                            <button type="submit" name="test_notification" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Testar Notificação
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tutorial do Pushover -->
            <div class="col-lg-6">
                <div class="settings-card">
                    <h4 class="mb-4"><i class="bi bi-info-circle"></i> Como Configurar o Pushover</h4>
                    
                    <div class="alert alert-info">
                        <strong>Importante:</strong> O Pushover custa $4.99 (pagamento único) e permite receber notificações em tempo real nos seus dispositivos iOS e Android.
                    </div>
                    
                    <ol class="tutorial-steps">
                        <li>Baixe o app Pushover:
                            <div>
                                <a href="https://play.google.com/store/apps/details?id=net.superblock.pushover" target="_blank" class="me-2">
                                    <img src="https://play.google.com/intl/en_us/badges/static/images/badges/pt-br_badge_web_generic.png" alt="Google Play" class="app-store-badge" style="height: 45px;">
                                </a>
                                <a href="https://apps.apple.com/us/app/pushover-notifications/id506088175" target="_blank">
                                    <img src="https://tools.applemediaservices.com/api/badges/download-on-the-app-store/black/pt-br?size=250x83" alt="App Store" class="app-store-badge" style="height: 35px;">
                                </a>
                            </div>
                        </li>
                        <li>Instale o aplicativo e crie uma conta</li>
                        <li>Confirme seu email clicando no link recebido com o assunto: "[Pushover] Welcome to Pushover!"</li>
                        <li>Acesse sua conta e anote sua <strong>User Key</strong></li>
                        <li>Acesse <a href="https://pushover.net/apps/build" target="_blank" class="text-light">https://pushover.net/apps/build</a> para criar uma aplicação</li>
                        <li>No campo "Name", digite "PIX"</li>
                        <li>Escolha um ícone relacionado a dinheiro</li>
                        <li>Marque a opção "By checking this box, you agree that you have read our Terms of Service..."</li>
                        <li>Clique em "Create Application"</li>
                        <li>Copie o <strong>API Token/Key</strong> gerado</li>
                        <li>Insira o API Token e User Key nos campos ao lado</li>
                        <li>Salve as configurações e teste o envio de notificação</li>
                    </ol>
                    
                    <div class="mt-4">
                        <div class="alert alert-light">
                            <strong>Exemplo de User Key:</strong> 
                            <code>uws4uythtrg5h5g43fg84urfeidm</code>
                        </div>
                        <div class="alert alert-light">
                            <strong>Exemplo de API Token:</strong> 
                            <code>a2cznaz79wdfgh2cmczfcdwue7npua</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
