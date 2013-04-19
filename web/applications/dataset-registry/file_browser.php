<?php

require_once '/usr/local/share/GRIIDC/php/drupal.php';

if (array_key_exists('dir',$_GET)) {

    $user = getDrupalUserName();

    if ($user) {

        $homeDir = NULL;
        $gidNumber = NULL;
        $sftpGroup = NULL;
        
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
        
            $groupResult = ldap_search($ldap, "ou=SFTP,ou=applications,dc=griidc,dc=org", "(objectClass=posixGroup)", array("cn","gidNumber","memberUid"));
        
            if (ldap_count_entries($ldap, $groupResult) > 0) {
                $groupEntries = ldap_get_entries($ldap, $groupResult);
                foreach ($groupEntries as $group) {
                    if (is_array($group)) {
                        if (!is_null($gidNumber) and array_key_exists('gidnumber',$group) and $group['gidnumber'][0] == $gidNumber) {
                            $sftpGroup = $group['cn'][0];
                            break;
                        }
                        if (array_key_exists('memberuid',$group) and is_array($group['memberuid'])) {
                            foreach ($group['memberuid'] as $uid) {
                                if ($uid == $user) {
                                    $sftpGroup = $group['cn'][0];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if (is_null($sftpGroup) or !preg_match('/^(?:(.*)-)?sftp-users$/',$sftpGroup,$matches)) {
            echo <<<EOT
                <div style="padding:10px;">
                You are not an SFTP user.<br>
                <br>
                Please contact <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> to request SFTP access.
                <br>
                <br>
                <input type="button" value="Cancel" onclick="jQuery('#fileBrowser').hide();">
                </div>
EOT;
            exit;
        }
        
        $chrootDir = "/sftp/chroot";
        if ($matches[1] != '') { $chrootDir .= "/$matches[1]"; }
        $chrootDir .= "/$user";
        
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
                <div style="padding:10px;">
                Your SFTP directory has not been set up.<br>
                <br>
                Please contact <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for assistance.
                <br>
                <br>
                <input type="button" value="Cancel" onclick="jQuery('#fileBrowser').hide();">
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
                div#middlecontainer a.file {
                    color: blue;
                }
                div#middlecontainer a.file:hover {
                    color: red;
                    text-decoration: none;
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
                <div style="overflow:auto; height:300px; border:1px solid black; padding:2px;">
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
                echo "<a href=\"javascript:fileBrowser('$type','$parent_dir')\" class='dir'>$dir</a>";
            }
            else {
                if ($browseDir != '') { $linkDir = "$browseDir/$dir"; }
                else { $linkDir = $dir; }
                echo "<a href=\"javascript:fileBrowser('$type','$linkDir')\" class='dir'>$dir</a>";
            }
            echo "<br>";
        }
        
        foreach ($files as $file) {
            $path = $chrootDir;
            if ($browseDir != '') {
                if (!preg_match('/\/$/',$path)) { $path .= '/'; }
                $path .= "$browseDir";
            }
            if (!preg_match('/\/$/',$path)) { $path .= '/'; }
            $path .= $file;
            echo "<a href=\"javascript:setPath('$type','$path');jQuery('#fileBrowser').hide();\" class='file'>$file</a><br>";
        }
        
        echo <<<EOT
               </div>
                <div style="height:40px; margin-top: 10px; overflow:hidden;">
                    <input type="button" value="Cancel" onclick="jQuery('#fileBrowser').hide();">
                </div>
            </div>
EOT;
        
        exit;

    }
}

?>
