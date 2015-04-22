<?php

if (preg_match("!^$_SERVER[SCRIPT_NAME]/([^\?]*)!", $_SERVER['REQUEST_URI'], $matches)) {
    $_SERVER['REQUEST_URI'] = preg_replace("!$matches[1]!", '', $_SERVER['REQUEST_URI']);
    switch($matches[1]) {
        case 'getTaskDetails.php':
            require 'getTaskDetails.php';
            exit;
            // don't need to break because we are exiting
        case 'getPeopleDetails.php':
            require 'getPeopleDetails.php';
            exit;
            // don't need to break because we are exiting
    }
}

$GLOBALS['pelagos']['title'] = 'RIS Web Service';

print '<p><a href="' . $GLOBALS['pelagos']['component_path'] . '/getTaskDetails.php">getTaskDetails.php</a></p>';
print '<p><a href="' . $GLOBALS['pelagos']['component_path'] . '/getPeopleDetails.php">getPeopleDetails.php</a></p>';
