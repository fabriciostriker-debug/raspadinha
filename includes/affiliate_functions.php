<?php
/**
 * Funções auxiliares para o sistema de afiliados
 */

/**
 * Calcula a chance de vitória baseada na cadeia de referência do usuário
 */
function calculateWinChanceFromReferralChain($conn, $user_id) {
    try {
        // Buscar informações da cadeia de referência
        $stmt = $conn->prepare("
            SELECT 
                influencer_rate,
                agent_rate
            FROM user_referral_chain 
            WHERE user_id = ? AND influencer_id IS NOT NULL AND agent_id IS NOT NULL
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($chain = $result->fetch_assoc()) {
            // Calcular chance baseada nas taxas
            $taxaPagamento = 8; // 8% taxa de pagamento
            $taxaCasa = 20; // 20% taxa da casa
            $taxaAgente = floatval($chain['agent_rate']);
            $taxaInfluencer = floatval($chain['influencer_rate']);
            
            // Chance é o que sobra após todas as taxas
            $chance = (100 - $taxaPagamento - $taxaCasa - $taxaAgente - $taxaInfluencer) / 100;
            return max(0, min(1, $chance)); // Garantir que fique entre 0 e 1
        }
        
        return null; // Retorna null se não encontrar cadeia de referência
    } catch (Exception $e) {
        error_log("Erro ao calcular chance de vitória: " . $e->getMessage());
        return null;
    }
}


/**
 * Gera um código de afiliado único baseado no username e user_id
 */
function generateAffiliateCode($username, $user_id) {
    // Remove espaços e caracteres especiais do username
    $clean_username = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($username));
    
    // Se o username limpo for muito curto, usa apenas o user_id
    if (strlen($clean_username) < 3) {
        return 'user' . $user_id;
    }
    
    // Combina username limpo com user_id
    return $clean_username . $user_id;
}

/**
 * Registra um clique de afiliado
 */
function registerAffiliateClick($conn, $affiliate_code) {
    try {
        // Buscar o ID do afiliado pelo código
        $stmt = $conn->prepare("SELECT id FROM affiliates WHERE affiliate_code = ? AND is_active = 1");
        $stmt->bind_param("s", $affiliate_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($affiliate = $result->fetch_assoc()) {
            $affiliate_id = $affiliate['id'];
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            
            // Registrar o clique
            $stmt = $conn->prepare("INSERT INTO affiliate_clicks (affiliate_id, ip_address, user_agent) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $affiliate_id, $ip_address, $user_agent);
            $stmt->execute();
            
            return true;
        }
    } catch (Exception $e) {
        error_log("Erro ao registrar clique de afiliado: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Registra uma conversão de afiliado
 */
function registerAffiliateConversion($conn, $affiliate_id, $converted_user_id, $conversion_type, $amount = 0) {
    try {
        $stmt = $conn->prepare("INSERT INTO affiliate_conversions (affiliate_id, converted_user_id, conversion_type, amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisd", $affiliate_id, $converted_user_id, $conversion_type, $amount);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Erro ao registrar conversão de afiliado: " . $e->getMessage());
        return false;
    }
}

/**
 * Calcula e registra comissões recursivamente para até 4 níveis
 */
function calculateAndRegisterCommissions($conn, $converted_user_id, $conversion_type, $conversion_amount, $current_user_id, $level = 1) {
    if ($level > 4) {
        return;
    }
    
    try {
        // PRIMEIRO: Verificar se existe cadeia agente/influencer para este usuário
        if ($level == 1) {
            $stmt = $conn->prepare("
                SELECT influencer_id, agent_id, agent_rate
                FROM user_referral_chain 
                WHERE user_id = ? AND influencer_id IS NOT NULL AND agent_id IS NOT NULL
            ");
            $stmt->bind_param("i", $converted_user_id);
            $stmt->execute();
            $chain_result = $stmt->get_result();
            
            if ($chain = $chain_result->fetch_assoc()) {
                // EXISTE CADEIA AGENTE/INFLUENCER - Aplicar lógica específica
                $influencer_id = $chain['influencer_id'];
                $agent_id = $chain['agent_id'];
                $agent_rate = $chain['agent_rate'];
                
                if ($conversion_type == 'deposit') {
                    // 1. COMISSÃO DO INFLUENCER
                    $stmt = $conn->prepare("
                        SELECT a.id as affiliate_id, a.revshare_commission_rate_admin
                        FROM affiliates a 
                        WHERE a.user_id = ? AND a.is_active = 1
                    ");
                    $stmt->bind_param("i", $influencer_id);
                    $stmt->execute();
                    $influencer_data = $stmt->get_result()->fetch_assoc();
                    
                    if ($influencer_data) {
                        $influencer_commission_rate = $influencer_data['revshare_commission_rate_admin'] / 100;
                        $influencer_commission = $conversion_amount * $influencer_commission_rate;
                        
                        // Registrar comissão do influencer
                        $stmt = $conn->prepare("INSERT INTO commissions (affiliate_id, referred_user_id, type, amount, level) VALUES (?, ?, 'RevShare', ?, 1)");
                        $stmt->bind_param("iid", $influencer_data['affiliate_id'], $converted_user_id, $influencer_commission);
                        $stmt->execute();
                        
                        // Atualizar saldo do influencer
                        if ($influencer_commission > 0) {
                            $stmt = $conn->prepare("UPDATE users SET affiliate_balance = affiliate_balance + ? WHERE id = ?");
                            $stmt->bind_param("di", $influencer_commission, $influencer_id);
                            $stmt->execute();
                        }
                    }
                    
                    // 2. COMISSÃO DO AGENTE
                    $stmt = $conn->prepare("
                        SELECT a.id as affiliate_id
                        FROM affiliates a 
                        WHERE a.user_id = ? AND a.is_active = 1
                    ");
                    $stmt->bind_param("i", $agent_id);
                    $stmt->execute();
                    $agent_data = $stmt->get_result()->fetch_assoc();
                    
                    if ($agent_data) {
                        $agent_commission_rate = $agent_rate / 100;
                        $agent_commission = $conversion_amount * $agent_commission_rate;
                        
                        // Registrar comissão do agente
                        $stmt = $conn->prepare("INSERT INTO commissions (affiliate_id, referred_user_id, type, amount, level) VALUES (?, ?, 'RevShare', ?, 2)");
                        $stmt->bind_param("iid", $agent_data['affiliate_id'], $converted_user_id, $agent_commission);
                        $stmt->execute();
                        
                        // Atualizar saldo do agente
                        if ($agent_commission > 0) {
                            $stmt = $conn->prepare("UPDATE users SET affiliate_balance = affiliate_balance + ? WHERE id = ?");
                            $stmt->bind_param("di", $agent_commission, $agent_id);
                            $stmt->execute();
                        }
                    }
                }
                
                // Para cadeia agente/influencer, não continuar processamento recursivo
                return;
            }
        }
        
        // LÓGICA NORMAL DE AFILIADO (quando não há cadeia agente/influencer)
        $stmt = $conn->prepare("
            SELECT a.id as affiliate_id, a.cpa_commission_rate, a.revshare_commission_rate, 
                   a.cpa_commission_rate_admin, a.revshare_commission_rate_admin,
                   a.fixed_commission_per_signup, a.allow_sub_affiliate_earnings 
            FROM affiliates a 
            WHERE a.user_id = ? AND a.is_active = 1
        ");
        $stmt->bind_param("i", $current_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($affiliate_data = $result->fetch_assoc()) {
            $affiliate_id = $affiliate_data['affiliate_id'];
            $commission_amount = 0;
            
            // Se for nível 1 ou se sub-afiliados são permitidos para este afiliado
            if ($level == 1 || $affiliate_data['allow_sub_affiliate_earnings']) {
                if ($conversion_type == 'signup') { 
                    $commission_amount = 0; 
                } else if ($conversion_type == 'deposit') {
                    // Usar a taxa real de comissão para cálculo
                    $commission_rate = $affiliate_data['revshare_commission_rate_admin'] / 100;
                    $commission_amount = $conversion_amount * $commission_rate;
                }
                
                // Registrar a comissão mesmo se for 0 para manter histórico
                $commission_type = ($conversion_type == 'signup') ? 'CPA' : 'RevShare';
                $stmt = $conn->prepare("INSERT INTO commissions (affiliate_id, referred_user_id, type, amount, level) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisdi", $affiliate_id, $converted_user_id, $commission_type, $commission_amount, $level);
                $stmt->execute();
                
                // Atualizar saldo do afiliado se houver comissão
                if ($commission_amount > 0) {
                    $stmt = $conn->prepare("UPDATE users SET affiliate_balance = affiliate_balance + ? WHERE id = ?");
                    $stmt->bind_param("di", $commission_amount, $current_user_id);
                    $stmt->execute();
                }
            }
            
            // Buscar o próximo afiliado na cadeia
            $stmt = $conn->prepare("SELECT referrer_id FROM users WHERE id = ?");
            $stmt->bind_param("i", $current_user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($next_referrer = $result->fetch_assoc()) {
                if ($next_referrer['referrer_id']) {
                    calculateAndRegisterCommissions($conn, $converted_user_id, $conversion_type, $conversion_amount, $next_referrer['referrer_id'], $level + 1);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao calcular comissões: " . $e->getMessage());
    }
}

/**
 * Registra indicações em cascata (até 4 níveis)
 */
function registerReferralChain($conn, $new_user_id, $referrer_id) {
    try {
        $current_referrer_id = $referrer_id;
        $level = 1;
        
        while ($level <= 4 && $current_referrer_id) {
            // Registrar a indicação
            $stmt = $conn->prepare("INSERT INTO referrals (referrer_id, referred_id, level) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $current_referrer_id, $new_user_id, $level);
            $stmt->execute();
            
            // Buscar o próximo referrer na cadeia
            $stmt = $conn->prepare("SELECT referrer_id FROM users WHERE id = ?");
            $stmt->bind_param("i", $current_referrer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($parent = $result->fetch_assoc()) {
                $current_referrer_id = $parent['referrer_id'];
                $level++;
            } else {
                break;
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Erro ao registrar cadeia de indicações: " . $e->getMessage());
        return false;
    }
}

/**
 * Cria um novo afiliado (apenas para uso administrativo)
 */
function createAffiliate($conn, $user_id, $username) {
    try {
        // Verificar se já é afiliado
        $stmt = $conn->prepare("SELECT id FROM affiliates WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false; // Usuário já é afiliado
        }
        
        // Gerar código de afiliado
        $affiliate_code = generateAffiliateCode($username, $user_id);
        
        // Verificar se o código já existe
        $stmt = $conn->prepare("SELECT id FROM affiliates WHERE affiliate_code = ?");
        $stmt->bind_param("s", $affiliate_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $affiliate_code = $affiliate_code . time();
        }
        
        // Iniciar transação
        $conn->begin_transaction();
        
        try {
            // Criar registro de afiliado
            $stmt = $conn->prepare("INSERT INTO affiliates (user_id, affiliate_code, cpa_commission_rate, revshare_commission_rate) VALUES (?, ?, 10.00, 5.00)");
            $stmt->bind_param("is", $user_id, $affiliate_code);
            $stmt->execute();
            
            // Atualizar status de afiliado do usuário
            $stmt = $conn->prepare("UPDATE users SET affiliate_status = 1 WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            $conn->commit();
            return $affiliate_code;
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Erro ao criar afiliado: " . $e->getMessage());
            return false;
        }
    } catch (Exception $e) {
        error_log("Erro ao criar afiliado: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém estatísticas do afiliado
 */
function getAffiliateStats($conn, $user_id) {
    try {
        $stats = [
            'clicks' => 0,
            'signups' => 0,
            'deposits' => 0,
            'total_commission' => 0,
            'cpa_commission' => 0,
            'revshare_commission' => 0,
            'balance' => 0,
            'total_deductions' => 0,
            'show_deductions' => 0
        ];
        
        // Buscar ID do afiliado e configuração de exibição de descontos
        $stmt = $conn->prepare("SELECT id, show_deductions FROM affiliates WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($affiliate = $result->fetch_assoc()) {
            $affiliate_id = $affiliate['id'];
            $stats['show_deductions'] = $affiliate['show_deductions'];
            
            // Cliques
            $stmt = $conn->prepare("SELECT COUNT(*) as clicks FROM affiliate_clicks WHERE affiliate_id = ?");
            $stmt->bind_param("i", $affiliate_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['clicks'] = $result->fetch_assoc()['clicks'];
            
            // Cadastros
            $stmt = $conn->prepare("SELECT COUNT(*) as signups FROM affiliate_conversions WHERE affiliate_id = ? AND conversion_type = 'signup'");
            $stmt->bind_param("i", $affiliate_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['signups'] = $result->fetch_assoc()['signups'];
            
            // Depósitos
            $stmt = $conn->prepare("SELECT COUNT(*) as deposits FROM affiliate_conversions WHERE affiliate_id = ? AND conversion_type = 'deposit'");
            $stmt->bind_param("i", $affiliate_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['deposits'] = $result->fetch_assoc()['deposits'];
            
            // Comissões CPA
            $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as cpa_commission FROM commissions WHERE affiliate_id = ? AND type = 'CPA'");
            $stmt->bind_param("i", $affiliate_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['cpa_commission'] = $result->fetch_assoc()['cpa_commission'];
            
            // Comissões RevShare (excluindo canceladas)
            $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as revshare_commission FROM commissions WHERE affiliate_id = ? AND type = 'RevShare' AND status != 'cancelled'");
            $stmt->bind_param("i", $affiliate_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['revshare_commission'] = $result->fetch_assoc()['revshare_commission'];
            
            // Descontos (comissões canceladas) - só calcular se permitir exibir
            if ($stats['show_deductions']) {
                $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total_deductions FROM commissions WHERE affiliate_id = ? AND status = 'cancelled'");
                $stmt->bind_param("i", $affiliate_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $stats['total_deductions'] = $result->fetch_assoc()['total_deductions'];
            }
            
            $stats['total_commission'] = $stats['cpa_commission'] + $stats['revshare_commission'];
        }
        
        // Saldo do usuário
        $stmt = $conn->prepare("SELECT affiliate_balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stats['balance'] = $user['affiliate_balance'] ?? 0;
        
        return $stats;
    } catch (Exception $e) {
        error_log("Erro ao obter estatísticas do afiliado: " . $e->getMessage());
        return false;
    }
}
?>
