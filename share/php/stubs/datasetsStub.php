<?php

function getProjectIdFromUdi($dbh, $udi) {
    switch ($udi) {
        case 'R1.x100.001:0001':
            return 100;
        case 'R1.x200.002:0001':
            return 200;
        case 'R1.x300.003:0001':
            return 300;
    }
    return null;
}
