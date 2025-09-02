<?php
// Forçar exibição de todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir cabeçalho para não cachear
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

echo "<h1>Página de Depuração PHP</h1>";
echo "<p>Esta página exibe todos os erros e detalhes para depuração.</p>";

// Informações do PHP
echo "<h2>Informações do PHP</h2>";
echo "<ul>";
echo "<li>Versão do PHP: " . phpversion() . "</li>";
echo "<li>display_errors: " . ini_get('display_errors') . "</li>";
echo "<li>error_reporting: " . ini_get('error_reporting') . "</li>";
echo "</ul>";

// Verificar conexão com o banco de dados
echo "<h2>Teste de Conexão com Banco de Dados</h2>";
try {
    require_once 'includes/db.php';
    echo "<p style='color: green;'>✓ Conexão com o banco de dados bem sucedida!</p>";
    
    // Verificar se a tabela user_referral_chain existe
    $result = $conn->query("SHOW TABLES LIKE 'user_referral_chain'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Tabela user_referral_chain existe!</p>";
        
        // Mostrar estrutura da tabela
        $result = $conn->query("DESCRIBE user_referral_chain");
        echo "<h3>Estrutura da tabela user_referral_chain:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ Tabela user_referral_chain NÃO existe!</p>";
        echo "<pre style='background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
        echo "CREATE TABLE user_referral_chain (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    influencer_id INT,
    agent_id INT,
    influencer_rate DECIMAL(10,2),
    agent_rate DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (influencer_id) REFERENCES users(id),
    FOREIGN KEY (agent_id) REFERENCES users(id)
);";
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro na conexão com o banco de dados: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre style='background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
    echo $e->getTraceAsString();
    echo "</pre>";
}

// Verificar função calculateWinChanceFromReferralChain
echo "<h2>Teste da função calculateWinChanceFromReferralChain</h2>";
try {
    require_once 'includes/affiliate_functions.php';
    if (function_exists('calculateWinChanceFromReferralChain')) {
        echo "<p style='color: green;'>✓ Função calculateWinChanceFromReferralChain existe!</p>";
        
        // Mostrar o código da função
        $func = new ReflectionFunction('calculateWinChanceFromReferralChain');
        $filename = $func->getFileName();
        $start_line = $func->getStartLine() - 1;
        $end_line = $func->getEndLine();
        $length = $end_line - $start_line;
        
        $source = file($filename);
        $body = implode("", array_slice($source, $start_line, $length));
        
        echo "<h3>Código da função:</h3>";
        echo "<pre style='background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
        echo htmlspecialchars($body);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Função calculateWinChanceFromReferralChain NÃO existe!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro ao verificar função: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre style='background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
    echo $e->getTraceAsString();
    echo "</pre>";
}

// Testar cálculo de porcentagem
echo "<h2>Teste de Cálculo de Porcentagem</h2>";
try {
    $taxaPagamento = 8;
    $taxaCasa = 20;
    $taxaAgente = 15;
    $taxaInfluencer = 50;
    
    $chance = (100 - $taxaPagamento - $taxaCasa - $taxaAgente - $taxaInfluencer) / 100;
    echo "<p>Taxas: Pagamento (8%), Casa (20%), Agente (15%), Influencer (50%)</p>";
    echo "<p>Chance = (100% - 8% - 20% - 15% - 50%) / 100 = <strong>" . $chance . "</strong></p>";
    
    if ($chance === 0.07) {
        echo "<p style='color: green;'>✓ Cálculo correto!</p>";
    } else {
        echo "<p style='color: red;'>✗ Cálculo incorreto! Deveria ser 0.07</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro no cálculo: " . $e->getMessage() . "</p>";
}

// Testes da página affiliate_dashboard.php
echo "<h2>Teste da página affiliate_dashboard.php</h2>";
echo "<p>Para testar a página affiliate_dashboard.php, clique <a href='affiliate_dashboard.php' target='_blank'>aqui</a>.</p>";
echo "<p>Se a página retornar erro 500, volte aqui para verificar os logs.</p>";

// Mostrar último erro registrado
echo "<h2>Último Erro Registrado</h2>";
$error = error_get_last();
if ($error) {
    echo "<pre style='background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
    print_r($error);
    echo "</pre>";
} else {
    echo "<p>Nenhum erro registrado.</p>";
}

// Adicionar botão para gerar erro propositalmente (para testar exibição de erro)
echo "<h2>Gerar Erro Propositalmente</h2>";
echo "<p>Clique no botão abaixo para gerar um erro propositalmente e verificar se está sendo exibido.</p>";
echo "<form method='get'>";
echo "<input type='hidden' name='trigger_error' value='1'>";
echo "<button type='submit'>Gerar Erro</button>";
echo "</form>";

// Se o parâmetro trigger_error estiver presente, gera um erro
if (isset($_GET['trigger_error'])) {
    // Forçar um erro de divisão por zero
    $a = 10;
    $b = 0;
    $c = $a / $b; // Isso gerará um erro
}
?>
