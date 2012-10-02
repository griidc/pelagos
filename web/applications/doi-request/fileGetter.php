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
elseif ($file != '')
{
    header("HTTP/1.0 403 Not Found");
    flush();
    ob_clean();
    exit;
}

if ($_GET) 
{
    if (isset($_GET['getfile'])) 
    {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $file = getcwd() .'/includes/'. $_GET['getfile'];
        $file = str_replace('//','/',$file);
        $file = str_replace('../','',$file);
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
}

if (substr($_SERVER['REQUEST_URI'],-1) != "/")
{
    $newLocation = 'Location:'.$_SERVER['REQUEST_URI']. '/';
    header($newLocation);
}

?>
