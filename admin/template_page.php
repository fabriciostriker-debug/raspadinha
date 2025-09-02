<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>V2 RASPA - Título da Página</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Sidebar CSS -->
    <link href="css/sidebar.css" rel="stylesheet">
    <!-- Admin Main CSS -->
    <link href="css/admin-main.css" rel="stylesheet">
</head>
<body>
    <?php include('includes/sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header fade-in">
            <h1 class="page-title">
                <i class="bi bi-gear"></i>
                Título da Página
            </h1>
            <p class="page-subtitle">Descrição da página e suas funcionalidades</p>
        </div>

        <!-- Content Section -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Main Card -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-card-text"></i>
                            Conteúdo Principal
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Este é um exemplo de como usar o novo sistema de design do painel administrativo V2 RASPA.</p>
                        
                        <!-- Example Form -->
                        <form data-validate>
                            <div class="form-group">
                                <label class="form-label">Nome do Campo</label>
                                <input type="text" class="form-control" placeholder="Digite aqui..." required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Seleção</label>
                                <select class="form-control">
                                    <option>Opção 1</option>
                                    <option>Opção 2</option>
                                    <option>Opção 3</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-control" data-mask="phone" placeholder="(11) 99999-9999">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">CPF</label>
                                <input type="text" class="form-control" data-mask="cpf" placeholder="000.000.000-00">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Valor</label>
                                <input type="text" class="form-control" data-mask="currency" placeholder="R$ 0,00">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Arquivo</label>
                                <input type="file" class="form-control">
                                <div class="file-preview mt-2"></div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i>
                                    Salvar
                                </button>
                                <button type="button" class="btn btn-outline">
                                    <i class="bi bi-x"></i>
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Example -->
                <div class="card fade-in mt-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-table"></i>
                            Exemplo de Tabela
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Search Bar -->
                        <div class="mb-3">
                            <input type="text" class="form-control table-search" placeholder="Buscar na tabela...">
                        </div>
                        
                        <div class="table-container">
                            <table class="table sortable-table">
                                <thead>
                                    <tr>
                                        <th data-sort="name">Nome</th>
                                        <th data-sort="email">Email</th>
                                        <th data-sort="status">Status</th>
                                        <th data-sort="date">Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td data-name="João Silva">João Silva</td>
                                        <td data-email="joao@email.com">joao@email.com</td>
                                        <td data-status="Ativo">
                                            <span class="badge badge-success">Ativo</span>
                                        </td>
                                        <td data-date="2024-01-15">15/01/2024</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td data-name="Maria Santos">Maria Santos</td>
                                        <td data-email="maria@email.com">maria@email.com</td>
                                        <td data-status="Inativo">
                                            <span class="badge badge-warning">Inativo</span>
                                        </td>
                                        <td data-date="2024-01-14">14/01/2024</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Stats Card -->
                <div class="stat-card fade-in">
                    <div class="stat-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="stat-value">1,234</div>
                    <div class="stat-label">Total de Registros</div>
                    <div class="stat-change positive">
                        <i class="bi bi-arrow-up"></i>
                        <span>+15% este mês</span>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card fade-in mt-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-info-circle"></i>
                            Informações
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-lightbulb"></i>
                            Dica: Use as máscaras de input para melhorar a experiência do usuário.
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            O sistema está funcionando perfeitamente!
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" onclick="AdminPanel.showNotification('Operação realizada com sucesso!', 'success')">
                                <i class="bi bi-check"></i>
                                Testar Notificação
                            </button>
                            
                            <button class="btn btn-warning" onclick="AdminPanel.confirmAction('Tem certeza que deseja continuar?', () => alert('Ação confirmada!'))">
                                <i class="bi bi-question-circle"></i>
                                Testar Confirmação
                            </button>
                            
                            <button class="btn btn-outline" onclick="AdminPanel.exportData()">
                                <i class="bi bi-download"></i>
                                Exportar Dados
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin Main JS -->
    <script src="js/admin-main.js"></script>
</body>
</html>
