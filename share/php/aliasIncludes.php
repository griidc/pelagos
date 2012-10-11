<?php

$file = preg_replace('/^\/[^\/]+$/','',$_SERVER['REQUEST_URI']);
$file = preg_replace('/^\/[^\/]+\//','',$file);
$file = str_replace('//','/',$file);
$file = str_replace('../','',$file);
if (preg_match('/^includes\//',$file))
{
    $info = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($info, $file);

    if ($mime === false)
    {
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

$URI = preg_split('/\?/',$_SERVER['REQUEST_URI']);

if (!preg_match('/\/$/',$URI[0]))
{
    $newLocation = "$URI[0]/";
    if (isset($URI[1]) and !empty($URI[1])) $newLocation .= "?$URI[1]";
    header("Location: $newLocation");
}

?>
