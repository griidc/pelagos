<?php

$GLOBALS['pelagos'] = array();
$GLOBALS['pelagos']['title'] = 'Dataset Registry';

# make sure current working directory is the directory that this file lives in
$GLOBALS['orig_cwd'] = getcwd();
chdir(realpath(dirname(__FILE__)));

require_once 'registry.php';

chdir($GLOBALS['orig_cwd']);
