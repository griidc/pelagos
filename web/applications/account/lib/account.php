<?php

function find_unique_uid($firstName,$lastName) {
    $uid = strtolower(substr($firstName,0,1) . $lastName);
    $uid = preg_replace('/[^A-Za-z]/','',$uid);

    $num = 1;
    $foundUnique = 0;

    # search ldap
    while (!$foundUnique) {
        $personsResult = ldap_search($GLOBALS['LDAP'], 'dc=griidc,dc=org', "(uid=$uid" . ($num > 1 ? $num : '') . ')', array('uid'));
        $persons = ldap_get_entries($GLOBALS['LDAP'], $personsResult);
        if ($persons['count'] > 0) {
            $num++;
        }
        else {
            $foundUnique = 1;
        }
    }

    # search pending account requests
    $ldifs = scandir(SPOOL_DIR.'/incoming');
    foreach ($ldifs as $ldifFile) {
        if (!preg_match('/\.ldif$/',$ldifFile)) continue;
        $ldif = read_ldif(SPOOL_DIR . "/incoming/$ldifFile");
        if (isset($ldif['person']['uid']['value']) and $ldif['person']['uid']['value'] == $uid . ($num > 1 ? $num : '')) {
            $num++;
        }
    }

    return $uid . ($num > 1 ? $num : '');
}

function find_free_uidNumber() {
    # search ldap
    $accountsResult = ldap_search($GLOBALS['LDAP'], "dc=griidc,dc=org", '(objectClass=posixAccount)', array("uidNumber"));
    $accounts = ldap_get_entries($GLOBALS['LDAP'], $accountsResult);
    $maxUidNumber = 999;
    foreach ($accounts as $account) {
        $foundUidNumber = $account['uidnumber'][0];
        if ($foundUidNumber > $maxUidNumber) {
            $maxUidNumber = $foundUidNumber;
        }
    }

    # search pending account requests
    $ldifs = scandir(SPOOL_DIR.'/incoming');
    foreach ($ldifs as $ldifFile) {
        if (!preg_match('/\.ldif$/',$ldifFile)) continue;
        $ldif = read_ldif(SPOOL_DIR . "/incoming/$ldifFile");
        if (isset($ldif['person']['uidNumber']['value']) and $ldif['person']['uidNumber']['value'] > $maxUidNumber) {
            $maxUidNumber = $ldif['person']['uidNumber']['value'];
        }
    }

    return $maxUidNumber + 1;
}

function make_ssha_password($password) {
    mt_srand((double)microtime()*1000000);
    $salt = pack("CCCC", mt_rand(), mt_rand(), mt_rand(), mt_rand());
    $hash = "{SSHA}" . base64_encode(pack("H*", sha1($password . $salt)) . $salt);
    return $hash;
}

function query_RPIS($email) {
    $respStr = file_get_contents("http://griidc.tamucc.edu/services/RPIS/getPeopleDetails.php?email=$email");
    $respArr = json_decode(json_encode((array) simplexml_load_string($respStr)),1);

    $retval['exception'] = false;

    if (isset($respArr['Exception'])) {
        $retval['exception'] = true;
        if (isset($respArr['Exception']['@attributes']) and isset($respArr['Exception']['@attributes']['exceptionCode'])) {
            $retval['exceptionCode'] = $respArr['Exception']['@attributes']['exceptionCode'];
        }
        if (isset($respArr['Exception']['ExceptionText'])) {
            $retval['ExceptionText'] = $respArr['Exception']['ExceptionText'];
        }
    }
    elseif (!isset($respArr['Count'])) {
        $retval['exception'] = true;
        $retval['ExceptionText'] = 'Bad response: Count element not found.';
    }
    elseif ($respArr['Count'] < 1) {
        $retval['exception'] = true;
        $retval['ExceptionText'] = 'Bad response: Count returned less than one.';
    }
    elseif ($respArr['Count'] > 1) {
        $retval['exception'] = true;
        $retval['ExceptionText'] = 'More than one person found with that email address.';
    }
    else {
        $retval['Person'] = $respArr['Person'];
        $retval['hash'] = md5($respStr);
    }

    return $retval;
}

function query_griidc_people($email) {
    $connString = sprintf('host=%s port=5432 dbname=%s user=%s password=%s',GRIIDC_PEOPLE_HOST,GRIIDC_PEOPLE_DBNAME,GRIIDC_PEOPLE_USER,GRIIDC_PEOPLE_PASSWORD);
    $dbconn = pg_connect($connString) or die("Couldn't Connect " . pg_last_error());
    $returnds = pg_query($dbconn, "select first_name,middle_name,last_name,business_phone,job_title,suffix,email from people where upper(email) = '" . strtoupper($email) . "'");
    $retval['found'] = false;
    while ($row = pg_fetch_row($returnds)){
        $retval['Person']['FirstName'] = $row[0];
        $retval['Person']['MiddleName'] = $row[1];
        $retval['Person']['LastName'] = $row[2];
        $retval['Person']['PhoneNum'] = $row[3];
        $retval['Person']['JobTitle'] = $row[4];
        $retval['Person']['Suffix'] = $row[5];
        $retval['Person']['Email'] = $row[6];
        $retval['found'] = true;
    }
    if ($retval['found']) {
        $str = '';
        foreach ($retval['Person'] as $field) {
            $str .= $field;
        }
        $retval['hash'] = md5($str);
    }
    return $retval;
}

function check_person($app,$type='u',$ldif=null) {
    if (is_null($ldif)) $retval = array();
    else $retval = $ldif;
    $retval['err'] = array();
    foreach ($GLOBALS['PERSON_FIELDS'] as $field => $details) {
        $value = $app->request()->post($field);
        if (in_array($type,$details['attrs'])) {
            $retval['person'][$field]['value'] = $value;
            if (in_array('r',$details['attrs']) and (is_null($value) or $value == '')) {
                $retval['err'][] = "\"$details[name]\" is a required field";
                $retval['person'][$field]['class'] = 'account_errorfield';
            }
        }
    }
    $affiliation = $app->request()->post('affiliation');
    if (is_null($affiliation) or $affiliation == 'select' or $affiliation == '') {
        $retval['err'][] = 'you must select an affiliation (select "Other:" and enter your affiliation if yours is not listed)';
    }
    elseif ($affiliation == 'other' and $retval['person']['o']['value'] == '') {
        $retval['err'][] = 'you must specify your affiliation if you select "Other:"';
        $retval['person']['o']['class'] = 'account_errorfield';
    }
    if (!array_key_exists('objectClasses',$retval))
        $retval['objectClasses'] = array('top','person','inetOrgPerson','organizationalPerson');
    if ($app->request()->post('Shell')) {
        if (!in_array('posixAccount',$retval['objectClasses']))
            $retval['objectClasses'][] = 'posixAccount';
    }
    elseif (in_array('posixAccount',$retval['objectClasses']))
        $retval['objectClasses'] = array_diff($retval['objectClasses'],array('posixAccount'));
    return $retval;
}

function add_posix_fields($person) {
    $person['uidNumber']['value'] = find_free_uidNumber();
    $person['gidNumber']['value'] = '1000';
    $person['gecos']['value'] = preg_replace('/,/','',$person['cn']['value']);
    $person['homeDirectory']['value'] = '/home/users/' . $person['uid']['value'];
    $person['loginShell']['value'] = '/bin/bash';
    return $person;
}

function read_ldif($ldifFile) {
    $ldif = array();
    $ldif['objectClasses'] = array();
    $ldif['applications'] = array();
    $contents = file_get_contents($ldifFile);
    $ldif['raw'] = $contents;
    $lines = explode("\n", $contents);
    $person = true;
    foreach ($lines as $line) {
        if ($line == '') $person = false;
        if ($person) {
            if (preg_match('/^(\w+): (.*)/',$line,$matches)) {
                if ($matches[1] == 'objectClass') {
                    $ldif['objectClasses'][] = $matches[2];
                    continue;
                }
                $ldif['person'][$matches[1]]['value'] = $matches[2];
            }
        }
        else {
            if (preg_match('/^dn: (.*,ou=groups,dc=griidc,dc=org)/',$line,$matches)) {
                $ldif['affiliation'] = $matches[1];
            }
            if (preg_match('/^dn: cn=([^,]+),ou=([^,]+),ou=applications,dc=griidc,dc=org/',$line,$matches)) {
                $ldif['applications'][$matches[2]] = $matches[1];
            }
        }
    }
    if (!isset($ldif['affiliation'])) $ldif['affiliation'] = 'other';
    return $ldif;
}

function write_ldif($ldifFile,$ldif) {
    $ldif['person']['dn']['value'] = 'uid=' . $ldif['person']['uid']['value'] . ',ou=members,ou=people,dc=griidc,dc=org';
    $contents = 'dn: ' . $ldif['person']['dn']['value'];

    # add person fields
    foreach ($GLOBALS['PERSON_FIELDS'] as $field => $details) {
        if (isset($ldif['person'][$field]['value']) and $ldif['person'][$field]['value'] != '') {
            $contents .= "\n$field: ". $ldif['person'][$field]['value'];
        }
    }

    # add sshPublicKey if ldapPublicKey objectClass found
    if (in_array('ldapPublicKey',$ldif['objectClasses'])) {
        if (isset($ldif['person']['sshPublicKey']['value']) and 
            $ldif['person']['sshPublicKey']['value'] != '' and
            $ldif['person']['sshPublicKey']['value'] != PASTE_PUB_KEY) {
            $contents .= "\nsshPublicKey: ". $ldif['person']['sshPublicKey']['value'];
        }
    }

    # add posixAccount fields if posixAccount objectClass found
    if (in_array('posixAccount',$ldif['objectClasses'])) {
        foreach ($GLOBALS['POSIX_ACCOUNT_FIELDS'] as $field) {
            if (isset($ldif['person'][$field]['value']) and $ldif['person'][$field]['value'] != '') {
                $contents .= "\n$field: ". $ldif['person'][$field]['value'];
            }
        }
    }

    foreach ($ldif['objectClasses'] as $objectClass) {
        $contents .= "\nobjectClass: $objectClass";
    }

    if (isset($ldif['affiliation']) and $ldif['affiliation'] != '' and $ldif['affiliation'] != 'other' and $ldif['affiliation'] != 'select') {
        $contents .= "\n\ndn: $ldif[affiliation]";
        $contents .= "\nchangetype: modify";
        $result = ldap_read($GLOBALS['LDAP'],$ldif['affiliation'],'(objectClass=*)',array('objectClass'));
        $entry = ldap_get_entries($GLOBALS['LDAP'], $result);
        if ($entry[0]['objectclass'][0] == 'posixGroup') {
            $contents .= "\nadd: memberUid";
            $contents .= "\nmemberUid: " . $ldif['person']['uid']['value'];
        }
        else {
            $contents .= "\nadd: member";
            $contents .= "\nmember: " . $ldif['person']['dn']['value'];
        }
    }

    foreach ($ldif['applications'] as $application => $group) {
        $contents .= "\n\ndn: cn=$group,ou=$application,ou=applications,dc=griidc,dc=org";
        $contents .= "\nchangetype: modify";
        $contents .= "\nadd: member";
        $contents .= "\nmember: " . $ldif['person']['dn']['value'];
    }

    umask(0066);
    file_put_contents($ldifFile,$contents);
    $ldif['raw'] = $contents;
    return $ldif;
}

function verify_email ($email,$hash) {
    $retval['verified'] = false;
    if (is_null($email) or $email == '') {
        $retval['error_message'] = 'missing email';
    }
    elseif (is_null($hash) or $hash == '') {
        $retval['error_message'] = 'missing hash';
    }
    else {
        $found = false;

        $griidc_person = query_griidc_people($email);

        if ($griidc_person['found']) {
            $found = true;
            $retval['person'] = $griidc_person;
        }
        else {
            $RPIS = query_RPIS($email);

            if ($RPIS['exception']) {
                $retval['error_message'] = 'RPIS exception';
            }
            else {
                $found = true;
                $retval['person'] = $RPIS;
            }
        }

        if (!$found) {
            $retval['error_message'] = 'email not found';
        }
        elseif ($hash != $retval['person']['hash']) {
            $retval['error_message'] = 'hash does not match';
        }
        else {
            $retval['verified'] = true;
        }
    }
    if (!$retval['verified']) {
        drupal_set_message("Email verification failed: $retval[error_message]",'error');
    }
    return $retval;
}

function get_affiliations($affiliation) {
    $affiliations = array();

    foreach ($GLOBALS['LDAP_AFFILIATIONS'] as $la) {
        $name_attr = $GLOBALS['NAME_ATTRS'][$la['objectClass']];
        $result = ldap_list($GLOBALS['LDAP'], "ou=$la[ou],ou=groups,dc=griidc,dc=org","(objectClass=$la[objectClass])",array($name_attr,'dn'));
        $entries = ldap_get_entries($GLOBALS['LDAP'], $result);
        sort($entries);
        foreach ($entries as $entry) {
            if (empty($entry['dn'])) continue;
            $dn = $entry['dn'];
            if ($la['objectClass'] == 'organizationalUnit') {
                $defaultGroup = $la['defaultGroup'];
                $defaultGroup = preg_replace("/\\\$$name_attr/",strtolower($entry[$name_attr][0]),$defaultGroup);
                $dn = "cn=$defaultGroup,$dn";
            }
            $name = $la['ou'];
            if ($entry[$name_attr][0] != 'members') $name .= ': ' . $entry[$name_attr][0];
            $affiliations[$la['ou']][] = array('name' => $name, 'dn' => $dn, 'selected' => $dn == $affiliation);
        }
    }

    $affiliations['other'] = $affiliation == 'other';
    return $affiliations;
}

function output_errors($err) {
    $err_message = '<div style="position:relative; left:-5px">Please correct the following errors (fields hilighted in red below):</div><ul>';
    foreach ($err as $e) {
        $err_message .= "<li>$e</li>";
    }
    $err_message .= '</ul>';
    drupal_set_message($err_message,'error');
}

function generate_pki($uid,$passphrase) {
    $retval = array();
    $pemfile = "/tmp/$uid.pem";
    $ppkfile = "/tmp/$uid.ppk";
    $pubfile = "/tmp/$uid.pem.pub";

    # escape user-provided data to prevent bad things
    $passphrase_arg = escapeshellarg($passphrase);
    $pemfile_arg = escapeshellarg($pemfile);
    $ppkfile_arg = escapeshellarg($ppkfile);

    # generate keypair
    exec("/usr/bin/ssh-keygen -q -b 2048 -t rsa -N $passphrase_arg -f $pemfile_arg");

    # build ppk from private key
    exec("echo $passphrase_arg | /usr/bin/puttygen $pemfile_arg -o $ppkfile_arg");

    # slurp in keys from temp files
    $retval['privKey'] = file_get_contents($pemfile);
    $retval['ppk'] = file_get_contents($ppkfile);
    $pubKey = file_get_contents($pubfile);

    # replace apache@... with uid
    $pubKey = preg_replace("/\S+\n?$/",$uid,$pubKey);
    $retval['pubKey'] = $pubKey;

    # clean up temporary files
    unlink($pemfile);
    unlink($ppkfile);
    unlink($pubfile);
    return $retval;
}

function get_notify_to() {
    $notify_to = array();
    $adminsResult = ldap_search($GLOBALS['LDAP'], "cn=ldapadmins,ou=groups,dc=griidc,dc=org", '(objectClass=*)', array("member"));
    $admins = ldap_get_entries($GLOBALS['LDAP'], $adminsResult);
    for ($i=0;$i<$admins[0]['member']['count'];$i++) {
        $adminEmailResult = ldap_search($GLOBALS['LDAP'], $admins[0]['member'][$i], '(objectClass=*)', array("mail"));
        $adminEmail = ldap_get_entries($GLOBALS['LDAP'], $adminEmailResult);
        $notify_to[] = $adminEmail[0]['mail'][0];
    }
    return $notify_to;
}

?>
