<?php
class BSPayConfig {
    private static $client_id = null;
    private static $client_secret = null;
    private static $webhook_url = null;
    
    private static function getConfigFromDB($key) {
        global $conn;
        if (!isset($conn)) {
            require_once dirname(__FILE__) . '/db.php';
        }
        
        $stmt = $conn->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['valor'];
        }
        
        return null;
    }
    
    public static function getClientId() {
        if (self::$client_id === null) {
            self::$client_id = self::getConfigFromDB('bspay_client_id');
        }
        return self::$client_id;
    }
    
    public static function getClientSecret() {
        if (self::$client_secret === null) {
            self::$client_secret = self::getConfigFromDB('bspay_client_secret');
        }
        return self::$client_secret;
    }
    
    public static function getWebhookUrl() {
        if (self::$webhook_url === null) {
            self::$webhook_url = self::getConfigFromDB('bspay_webhook_url');
        }
        return self::$webhook_url;
    }
}
?>
