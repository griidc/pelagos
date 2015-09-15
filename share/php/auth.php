<?php
// @codingStandardsIgnoreFile

function user_is_logged_in_somehow() {
    $drupal_login = user_is_logged_in();
    $guest_login = (isset($_SESSION['guestAuth']) and $_SESSION['guestAuth']);
    if ($drupal_login or $guest_login ) {
        return true;
    }
    return false;
}

function get_auth_info() {
    global $user;
    $auth_info = NULL;
    if (isset($user) and is_object($user) and property_exists($user,'name')) {
        $auth_info['username'] = $user->name;
        if (preg_match('/cas_name\|s:\d+:"([^"]+)";/',$user->session,$matches)) {
            $auth_info['type'] = 'cas';
            $auth_info['cas_name'] = $matches[1];
        }
        else {
            $auth_info['type'] = 'drupal';
        }
    }
    elseif (isset($_SESSION['guestAuth']) and $_SESSION['guestAuth']) {
        if (array_key_exists('guestAuthUser',$_SESSION)) $auth_info['username'] = $_SESSION['guestAuthUser'];
        if (array_key_exists('guestAuthType',$_SESSION)) $auth_info['type'] = $_SESSION['guestAuthType'];
        if (array_key_exists('guestAuthProvider',$_SESSION)) $auth_info['provider'] = $_SESSION['guestAuthProvider'];
    }
    return $auth_info;
}

?>
