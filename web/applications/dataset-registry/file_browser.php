<?php

require_once '/usr/local/share/GRIIDC/php/drupal.php';
require_once '/usr/local/share/GRIIDC/php/dif-registry.php';

if (array_key_exists('dir',$_GET)) {

    $user = getUID();

    if ($user) {

        $homeDir = NULL;
        $gidNumber = NULL;
        $groupName = NULL;

        $ldap = ldap_connect('ldap://triton.tamucc.edu');

        $userResult = ldap_search($ldap, "ou=people,dc=griidc,dc=org", "(&(uid=$user)(objectClass=posixAccount))", array("gidNumber","homeDirectory"));

        if (ldap_count_entries($ldap, $userResult) > 0) {
            $userEntries = ldap_get_entries($ldap, $userResult);
            $userEntry = $userEntries[0];
            if (array_key_exists('homedirectory',$userEntry)) {
                $homeDir = preg_replace('/^\//','',$userEntry['homedirectory'][0]);
            }
            if (array_key_exists('gidnumber',$userEntry)) {
                $gidNumber = $userEntry['gidnumber'][0];
            }

            $groupResult = ldap_search($ldap, "ou=posixGroups,dc=griidc,dc=org", "(objectClass=posixGroup)", array("cn","gidNumber","memberUid"));

            if (ldap_count_entries($ldap, $groupResult) > 0) {
                $groupEntries = ldap_get_entries($ldap, $groupResult);
                foreach ($groupEntries as $group) {
                    if (is_array($group)) {
                        if (!is_null($gidNumber) and array_key_exists('gidnumber',$group) and $group['gidnumber'][0] == $gidNumber) {
                            $groupName = $group['cn'][0];
                            break;
                        }
                        if (array_key_exists('memberuid',$group) and is_array($group['memberuid'])) {
                            foreach ($group['memberuid'] as $uid) {
                                if ($uid == $user) {
                                    $groupName = $group['cn'][0];
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!is_null($groupName) and $groupName == 'external-users') {
            $chrootDir = "/home/incoming/chroot/$user";
        }
        elseif (isset($homeDir)) {
            $chrootDir = "/$homeDir";
        }
        else {
            echo <<<EOT
                <div style="padding:10px; color:red;">
                Your account has not been configured for SFTP access.<br>
                <br>
                If you wish to use SFTP, please contact <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> to request SFTP access.
                <br>
                <br>
                <input type="button" value="Cancel" onclick="hideFileBrowser();">
                </div>
EOT;
            exit;
        }


        if (array_key_exists('dir',$_GET) and !preg_match('/^[\.\/]/',$_GET['dir'])) {
            $browseDir = $_GET['dir'];
            if ($browseDir == '%home%') {
                if (!is_null($homeDir) and is_dir("$chrootDir/$homeDir")) {
                    $browseDir = $homeDir;
                }
                else {
                    $browseDir = '';
                }
            }
            $browseDirs = preg_split('/\//',$browseDir);
            preg_match('/(.*)\//',$browseDir,$matches);
            $parent_dir = $matches[1];
        }

        if (!is_dir($chrootDir)) {
            echo <<<EOT
                <div style="padding:10px; color:red;">
                Your SFTP directory has not been set up.<br>
                <br>
                Please contact <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for assistance.
                <br>
                <br>
                <input type="button" value="Cancel" onclick="hideFileBrowser();">
                </div>
EOT;
            exit;
        }

        $type = 'data';
        if (array_key_exists('type',$_GET)) {
            $type = $_GET['type'];
        }

        $dir = opendir("$chrootDir/$browseDir");

        echo <<<EOT
            <style media="all">
                div#middlecontainer a.dir {
                    font-weight: bold;
                    color: blue;
                }
                div#middlecontainer a.dir:hover {
                    color: red;
                    text-decoration: none;
                }
                #fileBox {
                    overflow-x:hidden;
                    overflow-y:auto;
                    height:285px;
                    border:1px solid black;
                    padding-top: 21px;
                    top: 0px;
                }
                #fileBox > div {
                    color: black;
                    cursor: default;
                    padding-left: 2px;
                    border: 1px solid transparent;
                }
                #fileBox > div:hover {
                    border: 1px solid #b8d6fb;
                    background-color: #f2f7fd;
                }
                #fileBox > div.header {
                    color: #4c6091;
                    position: absolute;
                    top: 51px;
                    height: 18px;
                    background-color: white;
                }
                #fileBox > div.header:hover {
                    border: 1px solid transparent;
                    background-color: white;
                }
                #fileBox > div.dir {
                    font-weight: bold;
                }
                #fileBox > div > div {
                    display: inline-block;
                    overflow-x: hidden;
                }
                #fileBox div.name {
                    width: 340px;
                }
                #fileBox div.mod {
                    width: 130px;
                }
                #fileBox div.size {
                    width: 70px;
                    text-align: right;
                }
            </style>
            <div style="padding:10px; height:100%">
                <div style="height:20px; overflow:hidden; font-weight:bold; font-size:120%;">
                    Select $type file:
                </div>
                <div style="height:20px; overflow:hidden;">
                    <strong>Directory:</strong> <a href="javascript:fileBrowser('$type','')" class="dir">$chrootDir
EOT;

        if (!preg_match('/\/$/',$chrootDir)) { echo '/'; }

        echo "</a>";

        if ($browseDirs and is_array($browseDirs) and count($browseDirs) > 0) {
            $linkDir = '';
            foreach ($browseDirs as $currDir) {
                if ($currDir == '') { continue; }
                $linkDir .= "$currDir/";
                echo "<a href=\"javascript:fileBrowser('$type','$linkDir')\" class=\"dir\">$currDir/</a>";
            }
        }

        echo <<<EOT
                </div>
                <div id="fileBox">
                    <div class="header">
                        <div class="name">Name</div>
                        <div class="mod">Date modified</div>
                        <div class="size">Size</div>
                    </div>
EOT;

        $dirs = array();
        $files = array();

        while (false !== ($entry = readdir($dir))) {
            if (preg_match('/^\./',$entry)) {
                continue;
            }
            if (is_dir("$chrootDir/$browseDir/$entry")) {
                $dirs[] = $entry;
            }
            else {
                $files[] = $entry;
            }
        }

        if ($browseDir != '') { $dirs[] = '..'; }

        sort($dirs);
        sort($files);

        foreach ($dirs as $dir) {
            if ($dir == '..') {
                echo "<div onclick=\"javascript:showFileBrowser('$type','$parent_dir')\" class='dir'><div class='name'>$dir</div></div>";
            }
            else {
                if ($browseDir != '') { $linkDir = "$browseDir/$dir"; }
                else { $linkDir = $dir; }
                echo "<div onclick=\"javascript:showFileBrowser('$type','$linkDir')\" class='dir'><div class='name'>$dir</div></div>";
            }
        }

        foreach ($files as $file) {
            $path = $chrootDir;
            if ($browseDir != '') {
                if (!preg_match('/\/$/',$path)) { $path .= '/'; }
                $path .= "$browseDir";
            }
            if (!preg_match('/\/$/',$path)) { $path .= '/'; }
            $path .= $file;
            $mod_time = date("Y-m-d h:i A", filemtime($path));
            $size_bytes = filesize($path);
            $size_kb = $size_bytes / 1000;
            $size = sprintf("%.1f KB",$size_kb);
            if ($size_kb >= 1000) {
                $size_mb = $size_kb / 1000;
                $size = sprintf("%.1f MB",$size_mb);
                if ($size_mb >= 1000) {
                    $size_gb = $size_mb / 1000;
                    $size = sprintf("%.1f GB",$size_gb);
                }
                    if ($size_gb >= 1000) {
                        $size_tb = $size_gb / 1000;
                        $size = "$size_tb TB";
                    }
            }
            echo <<<EOT
                    <div onclick="setPath('$type','$path'); hideFileBrowser();" class="file">
                        <div class="name">$file</div>
                        <div class="mod">$mod_time</div>
                        <div class="size">$size</div>
                    </div>
EOT;
        }

        echo <<<EOT
                </div>
                <div style="height:40px; margin-top: 10px; overflow:hidden;">
                    <input type="button" value="Cancel" onclick="hideFileBrowser();">
                </div>
            </div>
EOT;

        exit;

    }
}

?>
