<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        if (isset($_POST['editar_saldo'])) {
            $id = intval($_POST['id']);
            $novoSaldo = floatval($_POST['saldo']);
            $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->bind_param("di", $novoSaldo, $id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Saldo atualizado com sucesso!';
                $response['new_balance'] = number_format($novoSaldo, 2, ',', '.');
            } else {
                $response['message'] = 'Erro ao atualizar saldo.';
            }
        }

        if (isset($_POST['promover'])) {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Usuário promovido a administrador com sucesso!';
                $response['action'] = 'promote';
            } else {
                $response['message'] = 'Erro ao promover usuário.';
            }
        }

        if (isset($_POST['rebaixar'])) {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE users SET is_admin = 0 WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Usuário rebaixado com sucesso!';
                $response['action'] = 'demote';
            } else {
                $response['message'] = 'Erro ao rebaixar usuário.';
            }
        }

        if (isset($_POST['resetar_saldos'])) {
            if ($conn->query("UPDATE users SET balance = 0")) {
                $response['success'] = true;
                $response['message'] = 'Todos os saldos foram resetados com sucesso!';
                $response['action'] = 'reset_all';
            } else {
                $response['message'] = 'Erro ao resetar saldos.';
            }
        }

        if (isset($_POST['toggle_affiliate'])) {
            $user_id = intval($_POST['user_id']);
            $action = $_POST['action']; // 'enable' or 'disable'
            
            if ($action === 'enable') {
                $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                
                require_once '../includes/affiliate_functions.php';
                $affiliate_code = createAffiliate($conn, $user_id, $user['name']);
                
                if ($affiliate_code) {
                    $response['success'] = true;
                    $response['message'] = 'Usuário habilitado como afiliado com sucesso!';
                    $response['action'] = 'enable_affiliate';
                } else {
                    $response['message'] = 'Erro ao habilitar usuário como afiliado.';
                }
            } else {
                $conn->begin_transaction();
                try {
                    $stmt = $conn->prepare("DELETE FROM affiliates WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("UPDATE users SET affiliate_status = 0, is_agent = 0 WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    
                    $conn->commit();
                    $response['success'] = true;
                    $response['message'] = 'Usuário removido do programa de afiliados com sucesso!';
                    $response['action'] = 'disable_affiliate';
                } catch (Exception $e) {
                    $conn->rollback();
                    $response['message'] = 'Erro ao remover usuário do programa de afiliados.';
                }
            }
        }

        if (isset($_POST['toggle_agent'])) {
            $user_id = intval($_POST['user_id']);
            $action = $_POST['action']; 
            
            $stmt = $conn->prepare("SELECT id FROM affiliates WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $is_affiliate = $stmt->get_result()->num_rows > 0;
            
            if (!$is_affiliate) {
                $response['message'] = 'Usuário precisa ser afiliado para se tornar agente.';
            } else {
                if ($action === 'enable') {
                    $conn->begin_transaction();
                    try {
                        $stmt = $conn->prepare("UPDATE users SET is_agent = 1 WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        
                        $stmt = $conn->prepare("UPDATE affiliates SET agent_commission_rate = 15.00 WHERE user_id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $response['success'] = true;
                        $response['message'] = 'Usuário definido como agente com sucesso!';
                        $response['action'] = 'enable_agent';
                    } catch (Exception $e) {
                        $conn->rollback();
                        $response['message'] = 'Erro ao definir usuário como agente.';
                    }
                } else {
                    $conn->begin_transaction();
                    try {
                        $stmt = $conn->prepare("UPDATE users SET is_agent = 0 WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        
                        $stmt = $conn->prepare("UPDATE affiliates SET agent_commission_rate = NULL WHERE user_id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $response['success'] = true;
                        $response['message'] = 'Status de agente removido com sucesso!';
                        $response['action'] = 'disable_agent';
                    } catch (Exception $e) {
                        $conn->rollback();
                        $response['message'] = 'Erro ao remover status de agente.';
                    }
                }
            }
        }

        if (isset($_POST['update_agent_rate'])) {
            $user_id = intval($_POST['user_id']);
            $rate = floatval($_POST['rate']);
            
            if ($rate < 0 || $rate > 100) {
                $response['message'] = 'Taxa inválida. Deve estar entre 0 e 100.';
            } else {
                $stmt = $conn->prepare("UPDATE affiliates SET agent_commission_rate = ? WHERE user_id = ?");
                $stmt->bind_param("di", $rate, $user_id);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Taxa do agente atualizada com sucesso!';
                } else {
                    $response['message'] = 'Erro ao atualizar taxa do agente.';
                }
            }
        }

        if (isset($_POST['criar_usuario'])) {
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                $response['message'] = 'Este email já está cadastrado.';
            } else {
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, balance, is_admin) VALUES (?, ?, ?, 0, 0)");
                $stmt->bind_param("sss", $nome, $email, $senha);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Usuário criado com sucesso!';
                    $response['action'] = 'create_user';
                } else {
                    $response['message'] = 'Erro ao criar usuário.';
                }
            }
        }
    
        if (isset($_POST['action']) && $_POST['action'] == 'toggle_influence_mode') {
    $user_id = intval($_POST['user_id']);
    $influence_mode = isset($_POST['influence_mode_enabled']) && $_POST['influence_mode_enabled'] == '1' ? 1 : 0;

    $conn->begin_transaction();

    try {
        $stmt1 = $conn->prepare("UPDATE users SET influence_mode_enabled = ? WHERE id = ?");
        $stmt1->bind_param("ii", $influence_mode, $user_id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("
            INSERT INTO user_settings (user_id, influence_mode_enabled) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE influence_mode_enabled = VALUES(influence_mode_enabled)
        ");
        $stmt2->bind_param("ii", $user_id, $influence_mode);
        $stmt2->execute();

        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Modo Influência atualizado com sucesso!';

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Erro ao atualizar o Modo Influência: ' . $e->getMessage();
    }
}

        
    } catch (Exception $e) {
        $response['message'] = 'Erro interno: ' . $e->getMessage();
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
    if ($response['success']) {
        header("Location: usuarios.php?success=" . urlencode($response['message']));
    } else {
        header("Location: usuarios.php?error=" . urlencode($response['message']));
    }
    exit();
}

$result = $conn->query("SELECT id, name, email, balance, is_admin FROM users WHERE email != 'root12377@gmail.com' ORDER BY id ASC");

$stats = $conn->query("SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN is_admin = 1 THEN 1 END) as total_admins,
    SUM(balance) as total_balance,
    AVG(balance) as avg_balance
    FROM users WHERE email != 'root12377@gmail.com'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Painel de Administração</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 para mensagens bonitas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sidebar CSS -->
    <link href="css/sidebar.css" rel="stylesheet">
    <style>
    :root {
        --sidebar-width: 260px;
        --header-height: 60px;
        --primary-color: #667eea;
        --secondary-color: #764ba2;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --dark-color: #1f2937;
        --light-color: #f8fafc;
        --border-color: #e5e7eb;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    * {
        box-sizing: border-box;
    }

    html {
        font-size: 16px;
    }

    body {
        background: var(--light-color);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }
    


@media (max-width: 768px) {
    
}


    

    

    .header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header h1 {
        font-size: 1.75rem;
        margin: 0;
    }
    .header p {
        margin: 0.25rem 0 0;
        opacity: 0.9;
    }

   

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        flex-shrink: 0;
    }
    .stat-icon.users { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .stat-icon.admins { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .stat-icon.balance { background: linear-gradient(135deg, #10b981, #059669); }
    .stat-icon.average { background: linear-gradient(135deg, #f59e0b, #d97706); }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }
    .stat-label {
        color: #6b7280;
        font-size: 0.875rem;
        margin: 0;
    }

    .content-section {
        padding: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .create-user-form {
        background: #f9fafb;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
    }
    
   .main-container {
    transition: margin-left 0.3s ease;
    margin-left: var(--sidebar-width); 
}

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid var(--border-color);
        padding: 0.75rem;
        width: 100%;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        outline: none;
    }

    .btn {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        white-space: nowrap;
    }
    .btn-primary { background: var(--primary-color); color: white; }
    .btn-danger { background: var(--danger-color); color: white; }
    .btn-success { background: var(--success-color); color: white; }
    .btn-warning { background: var(--warning-color); color: white; }
    .btn-info { background: #06b6d4; color: white; }
    .btn-sm { padding: 0.5rem 0.75rem; font-size: 0.8rem; }

    .table-container {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        min-width: 800px;
        border-collapse: collapse;
    }

    .table thead th {
        background: #f9fafb;
        padding: 0.75rem 1rem;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: left;
        border-bottom: 2px solid var(--border-color);
    }

    .table tbody td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .user-info { display: flex; align-items: center; gap: 0.75rem; }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        flex-shrink: 0;
    }
    .user-details h6 { margin: 0; font-weight: 600; }
    .user-details small { color: #6b7280; }

    .admin-badge, .user-badge {
        padding: 0.25rem 0.6rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .admin-badge { background: var(--success-color); color: white; }
    .user-badge { background: #e5e7eb; color: #374151; }

    .balance-form .input-group { min-width: 150px; }

    .loading-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    
</style>

</head>
<body>
    <?php include('includes/sidebar.php'); ?>
  
    <div class="main-container">
        <div class="header">
            <div class="header-content">
                <div class="header-text">
                    <h1><i class="fas fa-cogs me-2"></i>Painel de Administração</h1>
                    <p>Gerencie usuários, saldos e permissões do sistema</p>
                </div>
                
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="stat-value" id="total-users"><?= number_format($stats['total_users']) ?></h3>
                <p class="stat-label">Total de Usuários</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon admins">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3 class="stat-value" id="total-admins"><?= number_format($stats['total_admins']) ?></h3>
                <p class="stat-label">Administradores</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon balance">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 class="stat-value" id="total-balance">R$ <?= number_format($stats['total_balance'], 2, ',', '.') ?></h3>
                <p class="stat-label">Saldo Total</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon average">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="stat-value" id="avg-balance">R$ <?= number_format($stats['avg_balance'], 2, ',', '.') ?></h3>
                <p class="stat-label">Saldo Médio</p>
            </div>
        </div>

        <div class="content-section">
            <div class="reset-button">
                <button id="reset-all-btn" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-2"></i>Resetar Todos os Saldos
                </button>
            </div>

            <h4 class="section-title">
                <i class="fas fa-user-plus"></i>
                Criar Novo Usuário
            </h4>
            <div class="create-user-form">
                <form id="create-user-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nome</label>
                            <input name="nome" class="form-control" placeholder="Digite o nome" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input name="email" class="form-control" placeholder="Digite o email" type="email" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Senha</label>
                            <input name="senha" class="form-control" placeholder="Digite a senha" type="password" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Criar Usuário
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <h4 class="section-title">
                <i class="fas fa-list"></i>
                Lista de Usuários
            </h4>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Saldo</th>
                                <th>Tipo</th>
                                <th>Afiliado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr data-user-id="<?= $row['id'] ?>">
                                <td><strong>#<?= $row['id'] ?></strong></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                        </div>
                                        <div class="user-details">
                                            <h6><?= htmlspecialchars($row['name']) ?></h6>
                                            <small><?= htmlspecialchars($row['email']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <form class="balance-form d-flex align-items-center" data-user-id="<?= $row['id'] ?>">
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="number" step="0.01" min="0" name="saldo" value="<?= $row['balance'] ?>" class="form-control balance-input">
                                            <button class="btn btn-success btn-sm" type="submit">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </div>
                                    </form>
                                </td>
                                <td class="user-type-cell">
                                    <?php if ($row['is_admin']): ?>
                                        <span class="admin-badge">
                                            <i class="fas fa-crown me-1"></i>Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="user-badge">
                                            <i class="fas fa-user me-1"></i>Usuário
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $stmt = $conn->prepare("SELECT id FROM affiliates WHERE user_id = ?");
                                    $stmt->bind_param("i", $row['id']);
                                    $stmt->execute();
                                    $is_affiliate = $stmt->get_result()->num_rows > 0;

                                    $stmt = $conn->prepare("SELECT is_agent FROM users WHERE id = ? AND is_agent = 1");
                                    $stmt->bind_param("i", $row['id']);
                                    $stmt->execute();
                                    $is_agent = $stmt->get_result()->num_rows > 0;
                                    ?>
                                    <div class="d-flex gap-2">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="toggle_affiliate" value="1">
                                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="action" value="<?= $is_affiliate ? 'disable' : 'enable' ?>">
                                            <button type="submit" class="btn <?= $is_affiliate ? 'btn-danger' : 'btn-success' ?> btn-sm">
                                                <?php if ($is_affiliate): ?>
                                                    <i class="fas fa-user-minus"></i>
                                                    <span class="d-none d-md-inline ms-1">Desabilitar Afiliado</span>
                                                <?php else: ?>
                                                    <i class="fas fa-user-plus"></i>
                                                    <span class="d-none d-md-inline ms-1">Tornar Afiliado</span>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                        <?php if ($is_affiliate): ?>
                                        <div class="d-flex gap-2">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="toggle_agent" value="1">
                                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="action" value="<?= $is_agent ? 'disable' : 'enable' ?>">
                                                <button type="submit" class="btn <?= $is_agent ? 'btn-warning' : 'btn-info' ?> btn-sm">
                                                    <?php if ($is_agent): ?>
                                                        <i class="fas fa-user-shield"></i>
                                                        <span class="d-none d-md-inline ms-1">Remover Agente</span>
                                                    <?php else: ?>
                                                        <i class="fas fa-user-shield"></i>
                                                        <span class="d-none d-md-inline ms-1">Tornar Agente</span>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                            <?php if ($is_agent): ?>
                                            <?php
                                            $stmt = $conn->prepare("SELECT agent_commission_rate FROM affiliates WHERE user_id = ?");
                                            $stmt->bind_param("i", $row['id']);
                                            $stmt->execute();
                                            $agent_rate = $stmt->get_result()->fetch_assoc()['agent_commission_rate'] ?? 15.00;
                                            ?>
                                            <button class="btn btn-primary btn-sm" onclick="editAgentRate(<?= $row['id'] ?>, <?= $agent_rate ?>)" data-bs-toggle="modal" data-bs-target="#agentRateModal">
                                                <i class="fas fa-percentage"></i>
                                                <span class="d-none d-md-inline ms-1">Taxa do Agente (<?= number_format($agent_rate, 2) ?>%)</span>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                                                   <td>
                                    <div class="action-buttons d-flex gap-2">
                                        
                                        <?php if ($row['is_admin']): ?>
                                            <button class="btn btn-warning btn-sm demote-btn" data-user-id="<?= $row['id'] ?>" title="Rebaixar para usuário">
                                                <i class="fas fa-arrow-down"></i>
                                                <span class="d-none d-md-inline ms-1">Remover admin</span>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-info btn-sm promote-btn" data-user-id="<?= $row['id'] ?>" title="Promover para admin">
                                                <i class="fas fa-arrow-up"></i>
                                                <span class="d-none d-md-inline ms-1">Adicionar admin</span>
                                            </button>
                                        <?php endif; ?>

                                        <?php
$stmt_check = $conn->prepare("
    SELECT 
        u.influence_mode_enabled AS status_in_users,
        us.influence_mode_enabled AS status_in_user_settings
    FROM users u
    LEFT JOIN user_settings us ON u.id = us.user_id
    WHERE u.id = ?
");
$stmt_check->bind_param("i", $row['id']);
$stmt_check->execute();
$status_result = $stmt_check->get_result()->fetch_assoc();

$is_influence_enabled = ($status_result['status_in_users'] == 1 && $status_result['status_in_user_settings'] == 1);

$stmt_check->close();
?>

                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_influence_mode">
                                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="influence_mode_enabled" value="<?= $is_influence_enabled ? '0' : '1' ?>">
                                            
                                            <button type="submit" class="btn <?= $is_influence_enabled ? 'btn-danger' : 'btn-success' ?> btn-sm" title="<?= $is_influence_enabled ? 'Desativar Modo Influência' : 'Ativar Modo Influência' ?>">
                                                <i class="fas fa-star"></i>
                                                <span class="d-none d-md-inline ms-1"><?= $is_influence_enabled ? 'Desativar Conta demo' : 'Ativar Conta demo' ?></span>
                                            </button>
                                        </form>

                                    </div>
                                </td>



                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
        </div>

    <div class="modal fade" id="agentRateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Taxa do Agente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="agentRateForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="update_agent_rate" value="1">
                        <input type="hidden" name="user_id" id="agent_user_id">
                        <div class="mb-3">
                            <label class="form-label">Taxa de Comissão (%)</label>
                            <input type="number" name="rate" id="agent_rate" class="form-control" step="0.01" min="0" max="100" required>
                            <small class="text-muted">Defina a porcentagem que este agente irá receber das comissões.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        async function makeAjaxRequest(url, formData) {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Erro na requisição');
                }
                
                return await response.json();
            } catch (error) {
                console.error('Erro:', error);
                throw error;
            }
        }

        function showMessage(success, message) {
            if (success) {
                Toast.fire({
                    icon: 'success',
                    title: message
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: message
                });
            }
        }

        async function updateStats() {
            try {
                const response = await fetch(window.location.href);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                document.getElementById('total-users').textContent = doc.getElementById('total-users').textContent;
                document.getElementById('total-admins').textContent = doc.getElementById('total-admins').textContent;
                document.getElementById('total-balance').textContent = doc.getElementById('total-balance').textContent;
                document.getElementById('avg-balance').textContent = doc.getElementById('avg-balance').textContent;
            } catch (error) {
                console.error('Erro ao atualizar estatísticas:', error);
            }
        }

        function updateUserType(userId, isAdmin) {
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            if (!row) return;

            const typeCell = row.querySelector('.user-type-cell');
            const actionCell = row.querySelector('.action-buttons');

            if (isAdmin) {
                typeCell.innerHTML = '<span class="admin-badge"><i class="fas fa-crown me-1"></i>Admin</span>';
                actionCell.innerHTML = `<button class="btn btn-warning btn-sm demote-btn" data-user-id="${userId}" title="Rebaixar para usuário"><i class="fas fa-arrow-down"></i><span class="d-none d-md-inline ms-1">Rebaixar</span></button>`;
            } else {
                typeCell.innerHTML = '<span class="user-badge"><i class="fas fa-user me-1"></i>Usuário</span>';
                actionCell.innerHTML = `<button class="btn btn-info btn-sm promote-btn" data-user-id="${userId}" title="Promover para admin"><i class="fas fa-arrow-up"></i><span class="d-none d-md-inline ms-1">Promover</span></button>`;
            }

            attachActionButtonListeners();
        }

        function attachActionButtonListeners() {
            document.querySelectorAll('.promote-btn').forEach(btn => {
                btn.replaceWith(btn.cloneNode(true));
            });
            
            document.querySelectorAll('.promote-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const userId = this.dataset.userId;
                    const originalContent = this.innerHTML;
                    
                    try {
                        this.disabled = true;
                        this.innerHTML = '<span class="loading-spinner"></span>';
                        
                        const formData = new FormData();
                        formData.append('promover', '1');
                        formData.append('id', userId);
                        
                        const result = await makeAjaxRequest(window.location.href, formData);
                        showMessage(result.success, result.message);
                        
                        if (result.success) {
                            updateUserType(userId, true);
                            updateStats();
                        }
                    } catch (error) {
                        showMessage(false, 'Erro ao promover usuário');
                    } finally {
                        this.disabled = false;
                        this.innerHTML = originalContent;
                    }
                });
            });

            document.querySelectorAll('.demote-btn').forEach(btn => {
                btn.replaceWith(btn.cloneNode(true)); 
            });
            
            document.querySelectorAll('.demote-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const userId = this.dataset.userId;
                    const originalContent = this.innerHTML;
                    
                    try {
                        this.disabled = true;
                        this.innerHTML = '<span class="loading-spinner"></span>';
                        
                        const formData = new FormData();
                        formData.append('rebaixar', '1');
                        formData.append('id', userId);
                        
                        const result = await makeAjaxRequest(window.location.href, formData);
                        showMessage(result.success, result.message);
                        
                        if (result.success) {
                            updateUserType(userId, false);
                            updateStats();
                        }
                    } catch (error) {
                        showMessage(false, 'Erro ao rebaixar usuário');
                    } finally {
                        this.disabled = false;
                        this.innerHTML = originalContent;
                    }
                });
            });
        }

        function editAgentRate(userId, currentRate) {
            document.getElementById('agent_user_id').value = userId;
            document.getElementById('agent_rate').value = currentRate;
        }

        document.getElementById('agentRateForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            
            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading-spinner"></span> Salvando...';
                
                const formData = new FormData(this);
                const result = await makeAjaxRequest(window.location.href, formData);
                showMessage(result.success, result.message);
                
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('agentRateModal')).hide();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } catch (error) {
                showMessage(false, 'Erro ao atualizar taxa do agente');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('keydown', function(e) {
                if (e.altKey && e.key === 'Backspace') {
                    e.preventDefault();
                    history.back();
                }
            });

            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            document.getElementById('create-user-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalContent = submitBtn.innerHTML;
                
                try {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="loading-spinner"></span> Criando...';
                    
                    const formData = new FormData(this);
                    formData.append('criar_usuario', '1');
                    
                    const result = await makeAjaxRequest(window.location.href, formData);
                    showMessage(result.success, result.message);
                    
                    if (result.success) {
                        this.reset();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } catch (error) {
                    showMessage(false, 'Erro ao criar usuário');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                }
            });

            document.querySelectorAll('.balance-form').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const userId = this.dataset.userId;
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalContent = submitBtn.innerHTML;
                    
                    try {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="loading-spinner"></span>';
                        
                        const formData = new FormData(this);
                        formData.append('editar_saldo', '1');
                        formData.append('id', userId);
                        
                        const result = await makeAjaxRequest(window.location.href, formData);
                        showMessage(result.success, result.message);
                        
                        if (result.success) {
                            updateStats();
                        }
                    } catch (error) {
                        showMessage(false, 'Erro ao atualizar saldo');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                    }
                });
            });

            document.getElementById('reset-all-btn').addEventListener('click', async function() {
                const result = await Swal.fire({
                    title: 'Tem certeza?',
                    text: 'Esta ação irá zerar todos os saldos dos usuários!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sim, resetar!',
                    cancelButtonText: 'Cancelar'
                });

                if (result.isConfirmed) {
                    const originalContent = this.innerHTML;
                    
                    try {
                        this.disabled = true;
                        this.innerHTML = '<span class="loading-spinner"></span> Resetando...';
                        
                        const formData = new FormData();
                        formData.append('resetar_saldos', '1');
                        
                        const response = await makeAjaxRequest(window.location.href, formData);
                        showMessage(response.success, response.message);
                        
                        if (response.success) {
                            document.querySelectorAll('input[name="saldo"]').forEach(input => {
                                input.value = '0.00';
                            });
                            updateStats();
                        }
                    } catch (error) {
                        showMessage(false, 'Erro ao resetar saldos');
                    } finally {
                        this.disabled = false;
                        this.innerHTML = originalContent;
                    }
                }
            });

            attachActionButtonListeners();

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                showMessage(true, urlParams.get('success'));
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            if (urlParams.has('error')) {
                showMessage(false, urlParams.get('error'));
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });

        function voltarComConfirmacao() {
            const forms = document.querySelectorAll('form');
            let hasChanges = false;

            forms.forEach(form => {
                const formData = new FormData(form);
                for (let [key, value] of formData.entries()) {
                    if (value.trim() !== '') {
                        hasChanges = true;
                        break;
                    }
                }
            });

            if (hasChanges) {
                Swal.fire({
                    title: 'Alterações não salvas',
                    text: 'Você tem alterações não salvas. Deseja realmente sair?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sim, sair',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        history.back();
                    }
                });
            } else {
                history.back();
            }
        }
    </script>
</body>
</div>
</html>
