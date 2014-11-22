<?php

function getDMsFromUser($uid)
{
    if (!function_exists('getRCsFromUser')) {
        require 'ResearchConsortia.php';
    }
    if (!function_exists('getDMsFromRC')) {
        require 'rpis.php';
    }
    $dms = array();
    foreach (getRCsFromUser($uid) as $rc) {
        $dms = array_merge($dms, getDMsFromRC($rc));
    }
    return $dms;
}

function getDMsFromUDI($udi)
{
    if (!function_exists('getRCFromUDI')) {
        require 'ResearchConsortia.php';
    }
    if (!function_exists('getDMsFromRC')) {
        require 'rpis.php';
    }
    return getDMsFromRC(getRCFromUDI($udi));
}
