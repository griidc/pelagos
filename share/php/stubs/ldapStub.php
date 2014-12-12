<?php

function getEmployeeNumberFromUID($userId) {
    switch ($userId) {
        case 'user1':
            return 1;
        case 'user2':
            return 2;
    }
    return null;
}
