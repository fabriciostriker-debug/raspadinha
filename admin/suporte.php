<?php
session_start();
require_once '../includes/db.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$message = '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $suporte_tipo = $_POST['suporte_tipo'] ?? 'telegram';
    $telegram_usuario = $_POST['telegram_usuario'] ?? '';
    $whatsapp_numero = $_POST['whatsapp_numero'] ?? '';
    
    // Sanitizar dados
    $telegram_usuario = trim($telegram_usuario);
    $whatsapp_numero = preg_replace('/[^0-9]/', '', $whatsapp_numero);
    
    // Atualizar configurações no banco de dados
    $stmt = $conn->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
    
    // Salvar tipo de suporte
    $chave = 'suporte_tipo';
    $stmt->bind_param("sss", $chave, $suporte_tipo, $suporte_tipo);
    $stmt->execute();
    
    // Salvar usuário do Telegram
    $chave = 'suporte_telegram_usuario';
    $stmt->bind_param("sss", $chave, $telegram_usuario, $telegram_usuario);
    $stmt->execute();
    
    // Salvar número do WhatsApp
    $chave = 'suporte_whatsapp_numero';
    $stmt->bind_param("sss", $chave, $whatsapp_numero, $whatsapp_numero);
    $stmt->execute();
    
    $message = 'Configurações de suporte atualizadas com sucesso!';
}

// Buscar configurações atuais
$suporte_tipo = 'telegram';
$telegram_usuario = 'Suportefun777';
$whatsapp_numero = '';

$stmt = $conn->prepare("SELECT chave, valor FROM configuracoes WHERE chave IN ('suporte_tipo', 'suporte_telegram_usuario', 'suporte_whatsapp_numero')");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    switch ($row['chave']) {
        case 'suporte_tipo':
            $suporte_tipo = $row['valor'];
            break;
        case 'suporte_telegram_usuario':
            $telegram_usuario = $row['valor'];
            break;
        case 'suporte_whatsapp_numero':
            $whatsapp_numero = $row['valor'];
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações de Suporte - Painel Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
                <link href="css/sidebar.css" rel="stylesheet">

    
    <style>
        :root {
    --primary-color: #7257b4;
    --secondary-color: #6876df;
}


        body {
            background: linear-gradient(135deg, var(--dark-color) 0%, #1e293b 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			padding-left: var(--sidebar-width);
            color: white;
        }

        .main-container {
            padding: 2rem;
        }

        .settings-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
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

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
        }

        .preview-box {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }

        .preview-title {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.5rem;
        }

        .preview-content {
            font-family: monospace;
            word-break: break-all;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
    <div class="container-fluid">


        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1 class="mt-5 mb-4"><i class="bi bi-headset"></i> Configurações de Suporte</h1>

                
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="settings-card">
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label">Tipo de Suporte</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="suporte_tipo" id="tipo_telegram" value="telegram" <?php echo $suporte_tipo == 'telegram' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="tipo_telegram">
                                    <i class="bi bi-telegram"></i> Telegram
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="suporte_tipo" id="tipo_whatsapp" value="whatsapp" <?php echo $suporte_tipo == 'whatsapp' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="tipo_whatsapp">
                                    <i class="bi bi-whatsapp"></i> WhatsApp
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4" id="telegram_section">
                            <label for="telegram_usuario" class="form-label">Usuário do Telegram</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" id="telegram_usuario" name="telegram_usuario" value="<?php echo htmlspecialchars($telegram_usuario); ?>" placeholder="Usuário sem o @">
                            </div>
                            <div class="form-text text-white-50">Digite o nome de usuário sem o "@". Ex: "Suportefun777"</div>
                            
                            <div class="preview-box">
                                <div class="preview-title">Link de Suporte:</div>
                                <div class="preview-content" id="telegram_preview">https://t.me/<?php echo htmlspecialchars($telegram_usuario); ?></div>
                            </div>
                        </div>
                        
                        <div class="mb-4" id="whatsapp_section" style="display: none;">
                            <label for="whatsapp_numero" class="form-label">Número do WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text">+</span>
                                <input type="text" class="form-control" id="whatsapp_numero" name="whatsapp_numero" value="<?php echo htmlspecialchars($whatsapp_numero); ?>" placeholder="551199999999">
                            </div>
                            <div class="form-text text-white-50">Digite o número completo com código do país e DDD, sem símbolos. Ex: "551199999999"</div>
                            
                            <div class="preview-box">
                                <div class="preview-title">Link de Suporte:</div>
                                <div class="preview-content" id="whatsapp_preview">https://api.whatsapp.com/send/?phone=<?php echo htmlspecialchars($whatsapp_numero); ?>&text&type=phone_number&app_absent=0</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Configurações
                        </button>
                    </form>
                </div>
                
                <div class="settings-card">
                    <h4 class="mb-3"><i class="bi bi-info-circle"></i> Instruções</h4>
                    <p>Esta configuração afeta os links de suporte em todo o site, incluindo:</p>
                    <ul>
                        <li>Menu de navegação principal</li>
                        <li>Botão flutuante de suporte</li>
                        <li>Seção de ajuda no rodapé</li>
                    </ul>
                    <p>Escolha apenas um tipo de suporte por vez (Telegram ou WhatsApp).</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Alternar entre as seções de Telegram e WhatsApp
        document.addEventListener('DOMContentLoaded', function() {
            const tipoTelegram = document.getElementById('tipo_telegram');
            const tipoWhatsapp = document.getElementById('tipo_whatsapp');
            const telegramSection = document.getElementById('telegram_section');
            const whatsappSection = document.getElementById('whatsapp_section');
            
            // Mostrar seção inicial com base na seleção
            toggleSections();
            
            // Atualizar visualização ao mudar a seleção
            tipoTelegram.addEventListener('change', toggleSections);
            tipoWhatsapp.addEventListener('change', toggleSections);
            
            function toggleSections() {
                if (tipoTelegram.checked) {
                    telegramSection.style.display = 'block';
                    whatsappSection.style.display = 'none';
                } else {
                    telegramSection.style.display = 'none';
                    whatsappSection.style.display = 'block';
                }
            }
            
            // Atualizar previews em tempo real
            const telegramUsuario = document.getElementById('telegram_usuario');
            const whatsappNumero = document.getElementById('whatsapp_numero');
            const telegramPreview = document.getElementById('telegram_preview');
            const whatsappPreview = document.getElementById('whatsapp_preview');
            
            telegramUsuario.addEventListener('input', function() {
                telegramPreview.textContent = 'https://t.me/' + this.value;
            });
            
            whatsappNumero.addEventListener('input', function() {
                whatsappPreview.textContent = 'https://api.whatsapp.com/send/?phone=' + this.value + '&text&type=phone_number&app_absent=0';
            });
        });
    </script>
        </div> <!-- Fecha o container-fluid -->
</div> <!-- Fecha o main-content -->

</body>
</html>
