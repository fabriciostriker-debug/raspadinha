<?php
function get_kwai_pixels() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT pixel_id FROM kwai_pixels");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $pixels = array();
        while ($row = $result->fetch_assoc()) {
            $pixels[] = $row['pixel_id'];
        }
        
        return $pixels;
    } catch (Exception $e) {
        error_log("Erro ao buscar pixels Kwai: " . $e->getMessage());
        return array();
    }
}

function generate_kwai_pixel_code($event = 'contentView', $data = null) {
    $pixels = get_kwai_pixels();
    
    if (empty($pixels)) {
        return '';
    }
    
    $code = '';
    
    // Incluir o código base do Kwai apenas uma vez
    static $base_loaded = false;
    if (!$base_loaded) {
        $code .= '<script>
!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.install=t():e.install=t()}(window||self,function(){return function(e){var t={};function n(o){if(t[o])return t[o].exports;var r=t[o]={i:o,l:!1,exports:{}};return e[o].call(r.exports,r,r.exports,n),r.l=!0,r.exports}return n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(o,r,function(t){return e[t]}.bind(null,r));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=72)}({72:function(e,t,n){"use strict";var o=this&&this.__spreadArray||function(e,t,n){if(n||2===arguments.length)for(var o,r=0,i=t.length;r<i;r++)!o&&r in t||(o||(o=Array.prototype.slice.call(t,0,r)),o[r]=t[r]);return e.concat(o||Array.prototype.slice.call(t))};Object.defineProperty(t,"__esModule",{value:!0});var r=function(e,t,n){var o,i=e.createElement("script");i.type="text/javascript",i.async=!0,i.src=t,n&&(i.onerror=function(){r(e,n)});var a=e.getElementsByTagName("script")[0];null===(o=a.parentNode)||void 0===o||o.insertBefore(i,a)};!function(e,t,n){e.KwaiAnalyticsObject=n;var i=e[n]=e[n]||[];i.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];var a=function(e,t){e[t]=function(){for(var n=[],r=0;r<arguments.length;r++)n[r]=arguments[r];var i=o([t],n,!0);e.push(i)}};i.methods.forEach(function(e){a(i,e)}),i.instance=function(e){var t,n=(null===(t=i._i)||void 0===t?void 0:t[e])||[];return i.methods.forEach(function(e){a(n,e)}),n},i.load=function(e,o){var a="https://s21-def.ap4r.com/kos/s101/nlav112572/pixel/events.js";i._i=i._i||{},i._i[e]=[],i._i[e]._u=a,i._t=i._t||{},i._t[e]=+new Date,i._o=i._o||{},i._o[e]=o||{};var c="?sdkid="+e+"&lib="+n;r(t,a+c,"https://s21-def.ks-la.net/kos/s101/nlav112572/pixel/events.js"+c)}}(window,document,"kwaiq")}})}());
</script>' . "\n";
        $base_loaded = true;
    }
    
    // Carregar cada pixel
    foreach ($pixels as $pixel_id) {
        $code .= '<script>' . "\n";
        $code .= "kwaiq.load('" . htmlspecialchars($pixel_id, ENT_QUOTES, 'UTF-8') . "');" . "\n";
        
        // Disparar evento específico
        if ($event === 'contentView') {
            $code .= "kwaiq.page();" . "\n";
        } else {
            // Corrigido o formato para evitar erros de sintaxe
            if ($data) {
                // Garantir que os dados são um objeto JSON válido
                $json_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $code .= "kwaiq.track('" . $event . "', " . $json_data . ");" . "\n";
            } else {
                $code .= "kwaiq.track('" . $event . "');" . "\n";
            }
        }
        
        $code .= '</script>' . "\n";
    }
    
    return $code;
}
?>
