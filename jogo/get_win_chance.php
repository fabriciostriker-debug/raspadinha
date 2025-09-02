<?php
session_start();
require 'includes/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401);
    echo json_encode(["erro" => "Usuário não autenticado"]);
    exit;
}

$userId = $_SESSION["usuario_id"];

try {
    // Buscar se o usuário veio de uma cadeia agente→influencer
    $stmt = $conn->prepare("
        SELECT 
            urc.referrer_id,
            urc.level,
            a_agent.user_id as agent_user_id,
            a_agent.agent_commission_rate,
            a_influencer.user_id as influencer_user_id,
            a_influencer.revshare_commission_rate as influencer_rate
        FROM user_referral_chain urc
        LEFT JOIN affiliates a_agent ON urc.referrer_id = a_agent.user_id AND urc.level = 2
        LEFT JOIN affiliates a_influencer ON urc.referrer_id = a_influencer.user_id AND urc.level = 1
        WHERE urc.user_id = ? 
        ORDER BY urc.level ASC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $referral_chain = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $is_special_chain = false;
    $win_chance = 0.3; // Chance padrão (30%)
    
    // Verificar se há uma cadeia agente→influencer
    $agent_rate = null;
    $influencer_rate = null;
    
    foreach ($referral_chain as $chain) {
        if ($chain['level'] == 1 && $chain['influencer_user_id']) {
            // Usuário veio diretamente de um influencer
            $influencer_rate = $chain['influencer_rate'];
            
            // Verificar se o influencer tem um agente
            $stmt = $conn->prepare("
                SELECT agent_commission_rate 
                FROM affiliates 
                WHERE user_id = ? AND agent_id IS NOT NULL
            ");
            $stmt->bind_param("i", $chain['influencer_user_id']);
            $stmt->execute();
            $agent_result = $stmt->get_result()->fetch_assoc();
            
            if ($agent_result) {
                $agent_rate = $agent_result['agent_commission_rate'];
                $is_special_chain = true;
            }
            break;
        }
    }
    
    // Se é uma cadeia especial, calcular a chance baseada na distribuição
    if ($is_special_chain && $agent_rate !== null && $influencer_rate !== null) {
        // Cálculo: 100% - 8% (taxa) - 20% (casa) - % agente - % influencer = % distribuição
        $distribution_percentage = 100 - 8 - 20 - $agent_rate - $influencer_rate;
        
        // A chance de vitória é a porcentagem de distribuição / 100
        $win_chance = max(0, min(1, $distribution_percentage / 100));
        
        // Adicionar um pouco de randomização para não ser muito previsível
        $win_chance += (rand(-5, 5) / 100); // ±5%
        $win_chance = max(0.01, min(0.99, $win_chance)); // Garantir que fica entre 1% e 99%
    } else {
        // Usuário normal - usar controle padrão do admin
        $stmt = $conn->prepare("
            SELECT config_value 
            FROM admin_config 
            WHERE config_key = 'chance_vitoria' 
            LIMIT 1
        ");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $win_chance = floatval($result['config_value']);
        }
    }
    
    echo json_encode([
        "sucesso" => true,
        "win_chance" => $win_chance,
        "is_special_chain" => $is_special_chain,
        "agent_rate" => $agent_rate,
        "influencer_rate" => $influencer_rate,
        "distribution_percentage" => $is_special_chain ? (100 - 8 - 20 - $agent_rate - $influencer_rate) : null
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro interno do servidor",
        "detalhes" => $e->getMessage()
    ]);
}
?>
