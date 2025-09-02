<?php
/**
 * Gera o código do Facebook Pixel para inclusão nas páginas
 */

function get_facebook_pixels() {
    global $conn;
    
    $pixels = [];
    
    // Verificar se a conexão está disponível
    if (!isset($conn)) {
        require_once __DIR__ . '/db.php';
    }
    
    // Buscar pixels cadastrados
    $result = $conn->query("SELECT pixel_id FROM facebook_pixels");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pixels[] = $row['pixel_id'];
        }
    }
    
    return $pixels;
}

function generate_pixel_code($event = 'PageView', $data = null) {
    $pixels = get_facebook_pixels();
    
    // Se não há pixels configurados, retorna vazio
    if (empty($pixels)) {
        return '';
    }
    
    ob_start();
    ?>
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');

<?php foreach ($pixels as $pixel_id): ?>
fbq('init', '<?php echo $pixel_id; ?>');
<?php endforeach; ?>

fbq('track', "<?php echo $event; ?>"<?php echo ($data ? ', ' . json_encode($data) : ''); ?>);
</script>
<noscript>
<?php foreach ($pixels as $pixel_id): ?>
<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo $pixel_id; ?>&ev=<?php echo $event; ?>&noscript=1"/>
<?php endforeach; ?>
</noscript>
<!-- End Facebook Pixel Code -->
<?php
    return ob_get_clean();
}
?>
