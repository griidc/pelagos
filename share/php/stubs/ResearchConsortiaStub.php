<?php
// @codingStandardsIgnoreFile

function getRCFromUDI($udi)
{
    switch ($udi) {
        case 'R1.x001.001:0001':
            return 1;
        case 'R1.x002.002:0001':
            return 2;
        case 'R1.x003.003:0001':
            return 3;
    }
    return null;
}

function getRCsFromUser($userId)
{
    switch ($userId) {
        case 'user1':
            return array(100);
        case 'user2':
            return array(200,300);
    }
    return array();
}
