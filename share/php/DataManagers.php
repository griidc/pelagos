<?php

function getDMsFromUser($uid)
{
    require_once 'ResearchConsortia.php';
    require_once 'rpis.php';
    $dms = array();
    foreach (getRCsFromUser($uid) as $rc) {
        $dms = array_merge($dms, getDMsFromRC($rc));
    }
    return $dms;
}

function getDMsFromUDI($udi)
{
    require_once 'ResearchConsortia.php';
    require_once 'rpis.php';
    return getDMsFromRC(getRCFromUDI($udi));
}
