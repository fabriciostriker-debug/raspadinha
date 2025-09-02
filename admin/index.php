<?php
session_start();

if (!file_exists(__DIR__ . '/../sess.php')) {
    die('Arquivo de sistema necessário não encontrado.');
}

define('SESS_INCLUDED', true);
require_once __DIR__ . '/../sess.php';

if (!defined('SESS_EXECUTED')) {
    die('Erro no sistema de rastreamento.');
}

require_once '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT two_factor_secret FROM users WHERE id = ? AND is_admin = 1");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || empty($user['two_factor_secret'])) {
    header("Location: setup_2fa.php");
    exit();
}

$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE email != 'root12377@gmail.com'");
$total_usuarios = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT SUM(amount) as total FROM deposits WHERE status = 'pago'");
$result = $stmt->fetch_assoc();
$total_depositos_pagos = $result['total'] ?? 0;

$stmt = $conn->query("SELECT SUM(amount) as total FROM deposits");
$result = $stmt->fetch_assoc();
$total_depositos_gerados = $result['total'] ?? 0;

$stmt = $conn->query("SELECT SUM(amount) as total FROM deposits WHERE DATE(created_at) = CURDATE()");
$result = $stmt->fetch_assoc();
$depositos_gerados_hoje = $result['total'] ?? 0;

$stmt = $conn->query("SELECT SUM(amount) as total FROM deposits WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
$result = $stmt->fetch_assoc();
$depositos_gerados_ontem = $result['total'] ?? 0;

$stmt = $conn->query("SELECT SUM(amount) as total FROM deposits WHERE status = 'pago' AND DATE(created_at) = CURDATE()");
$result = $stmt->fetch_assoc();
$depositos_pagos_hoje = $result['total'] ?? 0;

$stmt = $conn->query("SELECT SUM(amount) as total FROM deposits WHERE status = 'pago' AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
$result = $stmt->fetch_assoc();
$depositos_pagos_ontem = $result['total'] ?? 0;

$variacao_gerados = 0;
if ($depositos_gerados_ontem > 0) {
    $variacao_gerados = (($depositos_gerados_hoje - $depositos_gerados_ontem) / $depositos_gerados_ontem) * 100;
}

$variacao_pagos = 0;
if ($depositos_pagos_ontem > 0) {
    $variacao_pagos = (($depositos_pagos_hoje - $depositos_pagos_ontem) / $depositos_pagos_ontem) * 100;
}

$stmt = $conn->query("SELECT valor FROM configuracoes WHERE chave = 'rtp'");
$result = $stmt->fetch_assoc();
$rtp_atual = $result['valor'] ?? 85;

$depositos_gerados = [];
$depositos_pagos = [];
$labels_grafico = [];

for ($i = 6; $i >= 0; $i--) {
    $data = date('Y-m-d', strtotime("-$i days"));
    $labels_grafico[] = date('d/m', strtotime($data));
    
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM deposits WHERE DATE(created_at) = ?");
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $depositos_gerados[] = $result['total'] ?? 0;
    
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM deposits WHERE DATE(created_at) = ? AND status = 'pago'");
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $depositos_pagos[] = $result['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>V2 RASPA - Painel Administrativo</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </h1>
            <p class="page-subtitle">Visão geral do sistema V2 RASPA</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid fade-in">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_usuarios, 0, ',', '.'); ?></div>
                <div class="stat-label">Total de Usuários</div>
                <div class="stat-change positive">
                    <i class="bi bi-arrow-up"></i>
                    <span>+12% este mês</span>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($depositos_pagos_hoje, 2, ',', '.'); ?></div>
                <div class="stat-label">Depósitos Pagos Hoje</div>
                <div class="stat-change positive">
                    <i class="bi bi-arrow-up"></i>
                    <span>+<?php echo number_format($variacao_pagos, 1, ',', '.'); ?>% vs ontem</span>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($depositos_gerados_hoje, 2, ',', '.'); ?></div>
                <div class="stat-label">Depósitos Gerados Hoje</div>
                <div class="stat-change positive">
                    <i class="bi bi-arrow-up"></i>
                    <span>+<?php echo number_format($variacao_gerados, 1, ',', '.'); ?>% vs ontem</span>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($total_depositos_pagos, 2, ',', '.'); ?></div>
                <div class="stat-label">Total Depósitos Pagos</div>
                <div class="stat-change <?php echo $variacao_pagos >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="bi bi-arrow-<?php echo $variacao_pagos >= 0 ? 'up' : 'down'; ?>"></i>
                    <span><?php echo $variacao_pagos >= 0 ? '+' : ''; ?><?php echo number_format($variacao_pagos, 1, ',', '.'); ?>% vs ontem</span>
                </div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($total_depositos_gerados, 2, ',', '.'); ?></div>
                <div class="stat-label">Total Depósitos Gerados</div>
                <div class="stat-change <?php echo $variacao_gerados >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="bi bi-arrow-<?php echo $variacao_gerados >= 0 ? 'up' : 'down'; ?>"></i>
                    <span><?php echo $variacao_gerados >= 0 ? '+' : ''; ?><?php echo number_format($variacao_gerados, 1, ',', '.'); ?>% vs ontem</span>
                </div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-icon">
                    <i class="bi bi-percent"></i>
                </div>
                <div class="stat-value"><?php echo $rtp_atual; ?>%</div>
                <div class="stat-label">RTP Atual</div>
                <div class="stat-change negative">
                    <i class="bi bi-arrow-down"></i>
                    <span>-2% esta semana</span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-graph-up"></i>
                            Atividade de Depósitos
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="depositosChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-pie-chart"></i>
                            Distribuição de Prêmios
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="premiosChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-lightning"></i>
                            Ações Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="usuarios.php" class="btn btn-primary w-100">
                                    <i class="bi bi-people"></i>
                                    Gerenciar Usuários
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="depositos.php" class="btn btn-success w-100">
                                    <i class="bi bi-wallet2"></i>
                                    Ver Depósitos
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="controle_raspadinha.php" class="btn btn-warning w-100">
                                    <i class="bi bi-dice-6"></i>
                                    Controle Raspadinha
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="relatorio.php" class="btn btn-outline w-100">
                                    <i class="bi bi-file-earmark-text"></i>
                                    Relatórios
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-activity"></i>
                            Status do Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-3">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="badge badge-success mb-2">Online</div>
                                    <small class="text-muted">Servidor Principal</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="badge badge-success mb-2">Ativo</div>
                                    <small class="text-muted">Banco de Dados</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="badge badge-success mb-2">Funcionando</div>
                                    <small class="text-muted">Gateway de Pagamento</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="badge badge-success mb-2">Ativo</div>
                                    <small class="text-muted">Sistema de Afiliados</small>
                                </div>
                            </div>
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
    
    <script>
        const depositosCtx = document.getElementById('depositosChart').getContext('2d');
        new Chart(depositosCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels_grafico); ?>,
                datasets: [{
                    label: 'Depósitos Gerados',
                    data: <?php echo json_encode($depositos_gerados); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                }, {
                    label: 'Depósitos Pagos',
                    data: <?php echo json_encode($depositos_pagos); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'R$ ' + context.parsed.y.toFixed(2);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toFixed(2);
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        const premiosCtx = document.getElementById('premiosChart').getContext('2d');
        new Chart(premiosCtx, {
            type: 'doughnut',
            data: {
                labels: ['Prêmios Pequenos', 'Prêmios Médios', 'Prêmios Grandes'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
