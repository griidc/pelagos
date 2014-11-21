<?php

function getDMsFromUser($uid)
{
    $dms = array();
    foreach (getRCsFromUser($uid) as $rc) {
        $dms = array_merge($dms, getDMsFromRC($rc));
    }
    return $dms;
}

function getDMsFromUDI($udi)
{
    return getDMsFromRC(getRCFromUDI($udi));
}
