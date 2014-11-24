<?php

function getRCFromUDI($UDI)
{
    switch ($UDI) {
        case 'R1.x134.115:0002':
            return 134;
        case 'R1.x135.120:0002':
            return 135;
    }
    return null;
}

function getRCsFromUser($userID)
{
    switch ($userID) {
        case 'schen':
            return array(134);
        case 'dhastings':
            return array(135,138);
    }
    return array();
}
