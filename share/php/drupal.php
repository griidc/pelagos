<?php

if (!function_exists('drupal_set_message')) {
    function drupal_set_message($message = NULL, $type = 'status', $repeat = TRUE) {
        echo "<p><strong>[$type]: $message</strong></p>";
    }
}

function getDrupalUserName() {
    global $user;
    if (isset($user) and is_object($user) and property_exists($user,'name')) return $user->name;
    else return NULL;
}

?>