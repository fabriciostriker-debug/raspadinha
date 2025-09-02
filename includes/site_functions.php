<?php
/**
 * Funções relacionadas às configurações do site
 */

/**
 * Obtém o nome do site das configurações globais
 * @return string Nome do site ou "Raspa Sorte" como padrão se não estiver configurado
 */
function get_site_name() {
    global $conn;
    
    // Verificar se a conexão com o banco já está estabelecida
    if (!isset($conn) || !$conn instanceof mysqli) {
        require_once __DIR__ . '/db.php';
    }
    
    // Buscar o nome do site das configurações globais
    $stmt = $conn->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'site_name'");
    if (!$stmt) {
        return "Raspa Sorte"; // Valor padrão caso ocorra erro na preparação
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $site_name = $result->fetch_assoc()['setting_value'];
        // Se o nome do site estiver vazio no banco, retornar o valor padrão
        return !empty($site_name) ? $site_name : "Raspa Sorte";
    }
    
    return "Raspa Sorte"; // Valor padrão caso não encontre a configuração
}
?>
