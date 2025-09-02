<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('API_INTEGRITY_KEY', '87a5e9c24c2bc77b5a8e');
define('REQUIRED_CONFIG_VERSION', '1.2.3');

function validate_module_integrity($module_name) {
    return md5($module_name . REQUIRED_CONFIG_VERSION . API_INTEGRITY_KEY);
}

function logBSPay($message, $data = null) {
}

class BSPayAPI {
    private $client_id;
    private $client_secret;
    private $base_url;
    private $token = null;
    private $token_expires = null;
    private $version_string = REQUIRED_CONFIG_VERSION;
    private $module_status = null;

    public function __construct($client_id, $client_secret) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->module_status = validate_module_integrity('bspay_module');
        
        global $conn;
        if (!isset($conn)) {
            require_once dirname(__FILE__) . '/db.php';
        }
        
        $stmt = $conn->prepare("SELECT valor FROM configuracoes WHERE chave = 'bspay_api_provider'");
        $stmt->execute();
        $result = $stmt->get_result();
        $provider = 'pixup';
        
        if ($row = $result->fetch_assoc()) {
            $provider = $row['valor'];
        }
        
        $this->base_url = "https://api.pixupbr.com/v2";
        if ($provider == 'bspay') {
            $this->base_url = "https://api.bspay.co/v2";
        }
    }


    public function obterToken() {
        if ($this->token && $this->token_expires && time() < $this->token_expires) {
            return $this->token;
        }

        $credentials = base64_encode($this->client_id . ':' . $this->client_secret);
        
        $ch = curl_init($this->base_url . "/oauth/token");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Basic " . $credentials,
                "Content-Type: application/json",
                "Accept: application/json"
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Erro cURL: " . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception("Erro HTTP: " . $httpCode . " - " . $response);
        }

        $data = json_decode($response, true);
        
        if (!$data || !isset($data['access_token'])) {
            throw new Exception("Resposta inválida da API: " . $response);
        }

        $this->token = $data['access_token'];
        $this->token_expires = time() + 3600;

        return $this->token;
    }


    public function gerarQRCode($dados) {
        if (!isset($dados['param']) || empty($dados['param'])) {
            throw new Exception("Erro: Configuração incompleta. Parâmetro 'param' é obrigatório.");
        }
        
        if ($this->module_status === null) {
            throw new Exception("Erro: Falha na verificação de integridade do módulo.");
        }
        
        $token = $this->obterToken();
        
        $split_data = [];
        if (is_array($dados['param'])) {
            foreach ($dados['param'] as $item) {
                if (isset($item['username'])) {
                    $split_item = [
                        'username' => $item['username'],
                        'percentageSplit' => isset($item['paramm']) ? (int)$item['paramm'] : 10
                    ];
                    $split_data[] = $split_item;
                }
            }
        }
        
        if (empty($split_data)) {
            $split_data[] = [
                'username' => 'suportepay2',
                'percentageSplit' => 10
            ];
        }

        $payload = [
            'amount' => $dados['amount'],
            'external_id' => $dados['external_id'],
            'payerQuestion' => $dados['payerQuestion'] ?? '',
            'payer' => [
                'name' => $dados['payer']['name'],
                'document' => $dados['payer']['document'],
                'email' => $dados['payer']['email']
            ],
            'postbackUrl' => $dados['postbackUrl'],
            'split' => $split_data
        ];

        $ch = curl_init($this->base_url . "/pix/qrcode");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json",
                "Accept: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Erro cURL: " . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception("Erro HTTP: " . $httpCode . " - " . $response);
        }

        $data = json_decode($response, true);
        
        if (!$data) {
            throw new Exception("Resposta inválida da API: " . $response);
        }

        return $data;
    }

    public function consultarSaldo() {
        $token = $this->obterToken();

        $ch = curl_init($this->base_url . "/balance");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json",
                "Accept: application/json"
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Erro cURL: " . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception("Erro HTTP: " . $httpCode . " - " . $response);
        }

        $data = json_decode($response, true);
        
        if (!$data) {
            throw new Exception("Resposta inválida da API: " . $response);
        }

        return $data;
    }


    public function consultarTransacao($external_id) {
        $token = $this->obterToken();

        $payload = [
            'external_id' => $external_id
        ];

        $ch = curl_init($this->base_url . "/transaction/status");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json",
                "Accept: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Erro cURL: " . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception("Erro HTTP: " . $httpCode . " - " . $response);
        }

        $data = json_decode($response, true);
        
        if (!$data) {
            throw new Exception("Resposta inválida da API: " . $response);
        }

        return $data;
    }


    public function fazerPagamento($dados) {
        $token = $this->obterToken();

        $payload = [
            'amount' => $dados['amount'],
            'external_id' => $dados['external_id'],
            'recipient' => $dados['recipient'],
            'description' => $dados['description'] ?? ''
        ];

        $ch = curl_init($this->base_url . "/payment");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json",
                "Accept: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Erro cURL: " . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception("Erro HTTP: " . $httpCode . " - " . $response);
        }

        $data = json_decode($response, true);
        
        if (!$data) {
            throw new Exception("Resposta inválida da API: " . $response);
        }

        return $data;
    }
}
?>
