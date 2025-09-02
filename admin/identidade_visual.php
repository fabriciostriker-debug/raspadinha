<?php
session_start();
require_once '../includes/db.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Criar tabela carousel_banners se não existir
$check_table = $conn->query("SHOW TABLES LIKE 'carousel_banners'");
if ($check_table->num_rows == 0) {
    $sql = "CREATE TABLE carousel_banners (
        id INT(11) NOT NULL AUTO_INCREMENT,
        image_url VARCHAR(255) NOT NULL,
        position INT(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$conn->query($sql)) {
        $message = "Erro ao criar tabela: " . $conn->error;
    }
}

// Verificar se o admin tem 2FA configurado
$stmt = $conn->prepare("SELECT two_factor_secret FROM users WHERE id = ? AND is_admin = 1");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || empty($user['two_factor_secret'])) {
    header("Location: setup_2fa.php");
    exit();
}

$message = '';

// Processar formulário de upload de logo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // Upload ou atualização de banner do carousel
    if ($_POST['action'] == 'upload_carousel_banner') {
        if (isset($_FILES['carousel_banner_file']) && $_FILES['carousel_banner_file']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file = $_FILES['carousel_banner_file'];
            
            if (in_array($file['type'], $allowed_types)) {
                $upload_dir = '../img/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid('carousel_') . '.' . $file_extension;
                $file_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    $caminho = '/img/' . $new_filename;
                    $position = intval($_POST['banner_position']);
                    
                    // Verificar se já existe um banner nesta posição
                    $check = $conn->prepare("SELECT id FROM carousel_banners WHERE position = ?");
                    $check->bind_param("i", $position);
                    $check->execute();
                    $result = $check->get_result();
                    
                    if ($result->num_rows > 0) {
                        // Atualizar banner existente
                        $banner_id = $result->fetch_assoc()['id'];
                        
                        // Obter URL da imagem antiga para excluir
                        $get_old = $conn->prepare("SELECT image_url FROM carousel_banners WHERE id = ?");
                        $get_old->bind_param("i", $banner_id);
                        $get_old->execute();
                        $old_banner = $get_old->get_result()->fetch_assoc();
                        
                        // Excluir arquivo antigo se existir
                        if ($old_banner && !empty($old_banner['image_url']) && file_exists('../' . $old_banner['image_url'])) {
                            unlink('../' . $old_banner['image_url']);
                        }
                        
                        $stmt = $conn->prepare("UPDATE carousel_banners SET image_url = ? WHERE id = ?");
                        $stmt->bind_param("si", $caminho, $banner_id);
                    } else {
                        // Inserir novo banner
                        $stmt = $conn->prepare("INSERT INTO carousel_banners (image_url, position) VALUES (?, ?)");
                        $stmt->bind_param("si", $caminho, $position);
                    }
                    
                    $stmt->execute();
                    $message = "Banner do carousel atualizado com sucesso!";
                } else {
                    $message = "Erro ao fazer upload do arquivo.";
                }
            } else {
                $message = "Tipo de arquivo não permitido. Use apenas JPG, PNG, GIF ou WebP.";
            }
        } else {
            $message = "Selecione um arquivo para upload.";
        }
    }
    
    // Excluir banner do carousel
    if ($_POST['action'] == 'delete_carousel_banner') {
        $banner_id = $_POST['carousel_banner_id'];
        
        // Obter URL da imagem para excluir arquivo
        $stmt = $conn->prepare("SELECT image_url FROM carousel_banners WHERE id = ?");
        $stmt->bind_param("i", $banner_id);
        $stmt->execute();
        $banner = $stmt->get_result()->fetch_assoc();
        
        // Excluir o arquivo físico se existir
        if ($banner && !empty($banner['image_url']) && file_exists('../' . $banner['image_url'])) {
            unlink('../' . $banner['image_url']);
        }
        
        // Excluir do banco de dados
        $stmt = $conn->prepare("DELETE FROM carousel_banners WHERE id = ?");
        $stmt->bind_param("i", $banner_id);
        $stmt->execute();
        
        $message = "Banner do carousel excluído com sucesso!";
    }
    
    // Upload de logo principal ou rodapé
    if ($_POST['action'] == 'upload_logo') {
        $tipo_logo = $_POST['tipo_logo'];
        
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file = $_FILES['logo_file'];
            
            if (in_array($file['type'], $allowed_types)) {
                $upload_dir = '../img/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = 'logo' . ($tipo_logo == 'rodape' ? '_rodape' : '') . '.' . $file_extension;
                $file_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    // Salvar caminho no banco de dados (tabela configuracoes)
                    $chave = 'logo_' . $tipo_logo;
                    $caminho = '/img/' . $new_filename;
                    
                    // Verificar se o registro já existe
                    $check = $conn->prepare("SELECT id FROM configuracoes WHERE chave = ?");
                    $check->bind_param("s", $chave);
                    $check->execute();
                    $result = $check->get_result();
                    
                    if ($result->num_rows > 0) {
                        // Atualizar registro existente
                        $stmt = $conn->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ?");
                        $stmt->bind_param("ss", $caminho, $chave);
                    } else {
                        // Inserir novo registro
                        $stmt = $conn->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?)");
                        $stmt->bind_param("ss", $chave, $caminho);
                    }
                    
                    $stmt->execute();
                    $message = "Logo " . ($tipo_logo == 'rodape' ? 'do rodapé' : 'principal') . " atualizado com sucesso!";
                } else {
                    $message = "Erro ao fazer upload do arquivo.";
                }
            } else {
                $message = "Tipo de arquivo não permitido. Use apenas JPG, PNG, GIF ou WebP.";
            }
        } else {
            $message = "Selecione um arquivo para upload.";
        }
    }
    
    // Upload de banner
    if ($_POST['action'] == 'upload_banner') {
        if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file = $_FILES['banner_file'];
            
            if (in_array($file['type'], $allowed_types)) {
                $upload_dir = '../img/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid('banner_') . '.' . $file_extension;
                $file_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    $nome = $_POST['banner_name'];
                    $descricao = $_POST['banner_description'];
                    $caminho = '/img/' . $new_filename;
                    $position = isset($_POST['banner_position']) ? $_POST['banner_position'] : 'header';
                    $is_active = isset($_POST['is_active']) ? 1 : 0;
                    $sort_order = isset($_POST['banner_order']) ? intval($_POST['banner_order']) : 0;
                    
                    $stmt = $conn->prepare("
                        INSERT INTO banners (name, description, file_path, position, is_active, sort_order) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("ssssii", $nome, $descricao, $caminho, $position, $is_active, $sort_order);
                    $stmt->execute();
                    
                    $message = "Banner enviado com sucesso!";
                } else {
                    $message = "Erro ao fazer upload do arquivo.";
                }
            } else {
                $message = "Tipo de arquivo não permitido. Use apenas JPG, PNG, GIF ou WebP.";
            }
        } else {
            $message = "Selecione um arquivo para upload.";
        }
    }
    
    // Atualizar banner
    if ($_POST['action'] == 'update_banner') {
        $banner_id = $_POST['banner_id'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("
            UPDATE banners 
            SET name = ?, description = ?, position = ?, sort_order = ?, is_active = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("sssiii", 
            $_POST['banner_name'], 
            $_POST['banner_description'], 
            $_POST['banner_position'], 
            $_POST['banner_order'], 
            $is_active, 
            $banner_id
        );
        $stmt->execute();
        
        $message = "Banner atualizado com sucesso!";
    }
    
    // Excluir banner
    if ($_POST['action'] == 'delete_banner') {
        $banner_id = $_POST['banner_id'];
        
        $stmt = $conn->prepare("SELECT file_path FROM banners WHERE id = ?");
        $stmt->bind_param("i", $banner_id);
        $stmt->execute();
        $banner = $stmt->get_result()->fetch_assoc();
        
        // A exclusão do arquivo físico é opcional, mantendo apenas para referência
        if ($banner && file_exists('../' . $banner['file_path'])) {
            unlink('../' . $banner['file_path']);
        }
        
        $stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->bind_param("i", $banner_id);
        $stmt->execute();
        
        $message = "Banner excluído com sucesso!";
    }
}

// Buscar configurações dos logos
function getLogo($tipo) {
    global $conn;
    $chave = 'logo_' . $tipo;
    
    $stmt = $conn->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
    $stmt->bind_param("s", $chave);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['valor'];
    }
    
    return '/img/logo.webp'; // Logo padrão se não encontrar
}

$logo_principal = getLogo('principal');
$logo_rodape = getLogo('rodape');

// Buscar banners
$banners_result = $conn->query("SELECT * FROM banners ORDER BY position, sort_order");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identidade Visual - Painel Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
			padding-left: var(--sidebar-width);
            min-height: 100vh;
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

        @media (min-width: 768px) {
            .settings-card {
                padding: 2rem;
            }
        }

        .setting-group {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
        }

        @media (min-width: 768px) {
            .setting-group {
                padding: 1.5rem;
            }
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
            padding: 0.75rem 1.5rem;
        }

        @media (min-width: 768px) {
            .btn-primary {
                padding: 0.75rem 2rem;
            }
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #059669);
            border: none;
            border-radius: 0.5rem;
        }

        .setting-description {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 0.25rem;
        }

        .danger-zone {
            border-left-color: var(--danger-color);
            background: rgba(239, 68, 68, 0.1);
        }

        .warning-zone {
            border-left-color: var(--warning-color);
            background: rgba(251, 191, 36, 0.1);
        }

        .user-settings-zone {
            border-left-color: var(--success-color);
            background: rgba(16, 185, 129, 0.1);
        }

        .nav-tabs .nav-link {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.8);
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }

        @media (min-width: 768px) {
            .nav-tabs .nav-link {
                margin-right: 0.5rem;
                margin-bottom: 0;
                border-radius: 0.5rem 0.5rem 0 0;
                font-size: 1rem;
                padding: 0.75rem 1.5rem;
            }
        }

        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .tab-content {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            padding: 1rem;
        }

        @media (min-width: 768px) {
            .tab-content {
                border-radius: 0 0.75rem 0.75rem 0.75rem;
                padding: 2rem;
            }
        }

        .table-dark {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
        }

        .table-dark th,
        .table-dark td {
            border-color: rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
        }

        @media (min-width: 768px) {
            .table-dark th,
            .table-dark td {
                font-size: 1rem;
            }
        }

        .modal-content {
            background: var(--dark-color);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .banner-preview, .logo-preview {
            max-width: 200px;
            max-height: 100px;
            object-fit: contain;
            border-radius: 0.5rem;
            background: rgba(0, 0, 0, 0.2);
            padding: 0.5rem;
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .logo-preview {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="main-content">
    <?php include 'includes/sidebar.php'; ?>

    <div class="container-fluid main-container">
        <!-- Mensagens -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi bi-brush"></i> Identidade Visual
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Dashboard</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Identidade Visual</li>
                </ol>
            </nav>
        </div>

        <!-- Tabs de Navegação -->
        <ul class="nav nav-tabs mb-4 flex-wrap" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="logos-tab" data-bs-toggle="tab" data-bs-target="#logos" type="button" role="tab">
                    <i class="bi bi-image"></i> <span class="d-none d-sm-inline">Logos</span><span class="d-sm-none">Logos</span>
                </button>
            </li>
            <li class="nav-item" role="presentation" style="display: none;">
                <button class="nav-link" id="banners-tab" data-bs-toggle="tab" data-bs-target="#banners" type="button" role="tab">
                    <i class="bi bi-images"></i> <span class="d-none d-sm-inline">Banners</span><span class="d-sm-none">Banners</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="carousel-banners-tab" data-bs-toggle="tab" data-bs-target="#carousel-banners" type="button" role="tab">
                    <i class="bi bi-card-image"></i> <span class="d-none d-sm-inline">Banners Carousel</span><span class="d-sm-none">Carousel</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="configTabsContent">
            <!-- Tab Logos -->
            <div class="tab-pane fade show active" id="logos" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="setting-group">
                            <h4 class="mb-3">
                                <i class="bi bi-image"></i> Logos Atuais
                            </h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="logo-container">
                                        <h5>Logo Principal</h5>
                                        <img src="<?= $logo_principal ?>" alt="Logo Principal" class="logo-preview">
                                        <p class="text-muted small">Usado no cabeçalho do site</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="logo-container">
                                        <h5>Logo do Rodapé</h5>
                                        <img src="<?= $logo_rodape ?>" alt="Logo do Rodapé" class="logo-preview">
                                        <p class="text-muted small">Usado no rodapé do site</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="settings-card">
                            <h5 class="mb-3">
                                <i class="bi bi-upload"></i> Atualizar Logo
                            </h5>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="upload_logo">
                                
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Logo</label>
                                    <select class="form-select" name="tipo_logo" required>
                                        <option value="principal">Logo Principal (Cabeçalho)</option>
                                        <option value="rodape">Logo do Rodapé</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Selecionar Arquivo</label>
                                    <input type="file" class="form-control" name="logo_file" accept="image/*" required>
                                    <div class="form-text text-white-50">
                                        Formatos aceitos: JPG, PNG, GIF, WebP<br>
                                        Recomendado: formato WebP transparente
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-upload"></i> Enviar Logo
                                </button>
                            </form>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle"></i> <strong>Dica:</strong> 
                                Use imagens com fundo transparente (PNG ou WebP) para melhor resultado visual.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Banners -->
            <div class="tab-pane fade" id="banners" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="setting-group">
                            <h4 class="mb-3">
                                <i class="bi bi-images"></i> Banners Cadastrados
                            </h4>
                            
                            <div class="table-responsive">
                                <table class="table table-dark table-striped" id="bannersTable">
                                    <thead>
                                        <tr>
                                            <th>Preview</th>
                                            <th>Nome</th>
                                            <th>Posição</th>
                                            <th>Status</th>
                                            <th>Ordem</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($banner = $banners_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($banner['file_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($banner['name']); ?>" 
                                                     class="banner-preview">
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($banner['name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($banner['description']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo ucfirst($banner['position']); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($banner['is_active']): ?>
                                                    <span class="badge bg-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?php echo $banner['sort_order']; ?></td>
                                            <td>
                                                <div class="btn-group-vertical btn-group-sm">
                                                    <button type="button" class="btn btn-primary btn-sm mb-1" 
                                                            onclick="editBanner(<?php echo htmlspecialchars(json_encode($banner)); ?>)">
                                                        <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Editar</span>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            onclick="deleteBanner(<?php echo $banner['id']; ?>, '<?php echo htmlspecialchars($banner['name']); ?>')">
                                                        <i class="bi bi-trash"></i> <span class="d-none d-lg-inline">Excluir</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="settings-card">
                            <h5 class="mb-3">
                                <i class="bi bi-plus-circle"></i> Adicionar Novo Banner
                            </h5>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="upload_banner">
                                
                                <div class="mb-3">
                                    <label class="form-label">Nome do Banner</label>
                                    <input type="text" class="form-control" name="banner_name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Descrição</label>
                                    <textarea class="form-control" name="banner_description" rows="2"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Posição</label>
                                    <select class="form-select" name="banner_position" required>
                                        <option value="header">Header</option>
                                        <option value="sidebar">Sidebar</option>
                                        <option value="footer">Footer</option>
                                        <option value="content">Conteúdo</option>
                                        <option value="carousel">Carousel Principal</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Ordem de Exibição</label>
                                    <input type="number" class="form-control" name="banner_order" value="0" min="0">
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="banner_active" name="is_active" checked>
                                    <label class="form-check-label" for="banner_active">Banner ativo</label>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Arquivo da Imagem</label>
                                    <input type="file" class="form-control" name="banner_file" 
                                           accept="image/jpeg,image/png,image/gif,image/webp" required>
                                    <div class="form-text text-white-50">
                                        Formatos aceitos: JPG, PNG, GIF, WebP<br>
                                        Tamanho máximo: 5MB
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-upload"></i> Enviar Banner
                                </button>
                            </form>
                        </div>
                        
                        <div class="settings-card">
                            <h5 class="mb-3">
                                <i class="bi bi-info-circle"></i> Dicas para Banners
                            </h5>
                            
                            <div class="alert alert-info">
                                <strong>Dimensões Recomendadas:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Carousel Principal:</strong> 1200x300px</li>
                                    <li><strong>Header:</strong> 1200x300px</li>
                                    <li><strong>Sidebar:</strong> 300x600px</li>
                                    <li><strong>Footer:</strong> 1200x200px</li>
                                    <li><strong>Conteúdo:</strong> 800x400px</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning">
                                <strong>Otimização:</strong> Use imagens otimizadas para web para melhor performance.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Banners Carousel -->
            <div class="tab-pane fade" id="carousel-banners" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="setting-group">
                            <h4 class="mb-3">
                                <i class="bi bi-images"></i> Banners do Carousel Principal
                            </h4>
                            
                            <?php
                            // Buscar banners do carousel
                            $carousel_banners_result = $conn->query("SELECT * FROM carousel_banners ORDER BY position ASC LIMIT 2");
                            $carousel_banners = [];
                            while ($banner = $carousel_banners_result->fetch_assoc()) {
                                $carousel_banners[] = $banner;
                            }
                            ?>
                            
                            <div class="row">
                                <?php 
                                $banner_positions = array(1 => "Primeiro Banner", 2 => "Segundo Banner");
                                $banner_count = count($carousel_banners);
                                
                                for ($i = 0; $i < 2; $i++): 
                                    $has_banner = $i < $banner_count;
                                    $banner = $has_banner ? $carousel_banners[$i] : null;
                                    $position = $i + 1;
                                ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card bg-dark">
                                        <div class="card-header bg-primary">
                                            <h5 class="mb-0"><?php echo $banner_positions[$position]; ?></h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <?php if ($has_banner): ?>
                                                <img src="<?php echo htmlspecialchars($banner['image_url']); ?>" 
                                                     alt="Banner <?php echo $position; ?>" 
                                                     class="img-fluid rounded mb-3" style="max-height: 200px;">
                                                
                                                <div class="btn-group w-100 mt-2">
                                                    <button type="button" class="btn btn-warning btn-sm" 
                                                            onclick="editCarouselBanner(<?php echo $banner['id']; ?>, <?php echo $position; ?>)">
                                                        <i class="bi bi-pencil"></i> Alterar
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            onclick="deleteCarouselBanner(<?php echo $banner['id']; ?>, <?php echo $position; ?>)">
                                                        <i class="bi bi-trash"></i> Remover
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center p-4 bg-secondary bg-opacity-25 rounded mb-3">
                                                    <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                                                    <p class="mt-3 text-muted">Nenhum banner configurado</p>
                                                </div>
                                                
                                                <button type="button" class="btn btn-primary btn-sm w-100" 
                                                        onclick="addCarouselBanner(<?php echo $position; ?>)">
                                                    <i class="bi bi-plus-circle"></i> Adicionar Banner
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle"></i> <strong>Importante:</strong> 
                                Estes banners serão exibidos no carousel principal da página inicial.
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="settings-card">
                            <h5 class="mb-3">
                                <i class="bi bi-upload"></i> Upload de Banner para o Carousel
                            </h5>
                            
                            <form method="POST" enctype="multipart/form-data" id="carouselBannerForm">
                                <input type="hidden" name="action" value="upload_carousel_banner">
                                <input type="hidden" name="banner_position" id="carousel_banner_position" value="1">
                                <input type="hidden" name="banner_id" id="carousel_banner_id" value="">
                                
                                <div class="mb-3">
                                    <label class="form-label">Posição do Banner</label>
                                    <input type="text" class="form-control" id="carousel_position_display" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Arquivo da Imagem</label>
                                    <input type="file" class="form-control" name="carousel_banner_file" 
                                           accept="image/jpeg,image/png,image/gif,image/webp" required>
                                    <div class="form-text text-white-50">
                                        Formatos aceitos: JPG, PNG, GIF, WebP<br>
                                        Dimensão recomendada: 1200x300px
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-upload"></i> Salvar Banner
                                </button>
                            </form>
                        </div>
                        
                        <div class="settings-card">
                            <h5 class="mb-3">
                                <i class="bi bi-info-circle"></i> Instruções
                            </h5>
                            
                            <div class="alert alert-warning">
                                <strong>Atenção:</strong> Os banners do carousel são os que aparecem no topo da página inicial, com animação de transição.
                            </div>
                            
                            <ul class="text-white-50 ps-3">
                                <li>É possível configurar até 2 banners no carousel.</li>
                                <li>Utilize imagens de alta qualidade, preferencialmente no formato WebP.</li>
                                <li>Mantenha as dimensões recomendadas para melhor aparência visual.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Confirmar Exclusão de Banner do Carousel -->
    <div class="modal fade" id="deleteCarouselBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir este banner do carousel?</p>
                    <p class="text-warning">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_carousel_banner">
                        <input type="hidden" name="carousel_banner_id" id="delete_carousel_banner_id">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Excluir Banner
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Banner -->
    <div class="modal fade" id="editBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-image"></i> Editar Banner
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_banner">
                    <input type="hidden" name="banner_id" id="edit_banner_id">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nome do Banner</label>
                            <input type="text" class="form-control" name="banner_name" id="edit_banner_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="banner_description" id="edit_banner_description" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Posição</label>
                            <select class="form-select" name="banner_position" id="edit_banner_position" required>
                                <option value="header">Header</option>
                                <option value="sidebar">Sidebar</option>
                                <option value="footer">Footer</option>
                                <option value="content">Conteúdo</option>
                                <option value="carousel">Carousel Principal</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Ordem de Exibição</label>
                            <input type="number" class="form-control" name="banner_order" id="edit_banner_order" min="0">
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_banner_active" value="1">
                            <label class="form-check-label" for="edit_banner_active">
                                Banner ativo
                            </label>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Confirmar Exclusão de Banner -->
    <div class="modal fade" id="deleteBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o banner <strong id="delete_banner_name"></strong>?</p>
                    <p class="text-warning">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_banner">
                        <input type="hidden" name="banner_id" id="delete_banner_id">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Excluir Banner
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Inicializar DataTables
        $(document).ready(function() {
            $('#bannersTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                responsive: true,
                pageLength: 10,
                order: [[2, 'asc'], [4, 'asc']], // Ordenar por posição e ordem
                columnDefs: [
                    { targets: [0], orderable: false },
                    { targets: [4], className: 'text-center' },
                    { targets: [5], orderable: false }
                ]
            });
        });
        
        function editBanner(banner) {
            document.getElementById('edit_banner_id').value = banner.id;
            document.getElementById('edit_banner_name').value = banner.name;
            document.getElementById('edit_banner_description').value = banner.description || '';
            document.getElementById('edit_banner_position').value = banner.position;
            document.getElementById('edit_banner_order').value = banner.sort_order;
            document.getElementById('edit_banner_active').checked = banner.is_active == 1;
            
            new bootstrap.Modal(document.getElementById('editBannerModal')).show();
        }
        
        function deleteBanner(bannerId, bannerName) {
            document.getElementById('delete_banner_id').value = bannerId;
            document.getElementById('delete_banner_name').textContent = bannerName;
            
            new bootstrap.Modal(document.getElementById('deleteBannerModal')).show();
        }
        
        // Funções para gerenciamento dos banners do carousel
        function addCarouselBanner(position) {
            document.getElementById('carousel_banner_position').value = position;
            document.getElementById('carousel_banner_id').value = '';
            document.getElementById('carousel_position_display').value = position === 1 ? 'Primeiro Banner' : 'Segundo Banner';
            
            // Rola a página até o formulário
            document.getElementById('carouselBannerForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        function editCarouselBanner(id, position) {
            document.getElementById('carousel_banner_position').value = position;
            document.getElementById('carousel_banner_id').value = id;
            document.getElementById('carousel_position_display').value = position === 1 ? 'Primeiro Banner' : 'Segundo Banner';
            
            // Rola a página até o formulário
            document.getElementById('carouselBannerForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        function deleteCarouselBanner(id, position) {
            document.getElementById('delete_carousel_banner_id').value = id;
            new bootstrap.Modal(document.getElementById('deleteCarouselBannerModal')).show();
        }
    </script>
    </div>
</body>
</html>
