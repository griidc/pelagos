<?php

function bytes2filesize($bytes,$precision = 0) {
    $units = array('B','KB','MB','GB','TB');
    for ($e = count($units)-1; $e > 0; $e--) {
        $one = pow(1024,$e);
        if ($bytes >= $one) {
            return round($bytes/$one,$precision) . ' ' . $units[$e];
        }
    }
    return "$bytes $units[0]";
}

?>
