<?php

function dumpIncludesFile($file) {
    if (!preg_match('/^includes\//',$file)) {
        $file = "includes/$file";
    }
    if (preg_match('/\.css$/',$file)) {
        $mime = 'text/css';
    }
    else {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($info, $file);
    }

    if ($mime === false) {
        header("HTTP/1.0 403 Not Found");
        flush();
        ob_clean();
        exit;
    }
    header('Content-Length: ' . filesize($file));
    header('Content-Disposition: inline; filename=' . basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Content-type: '.$mime);
    flush();
    ob_clean();
    readfile ($file);
    exit;
}

?>
