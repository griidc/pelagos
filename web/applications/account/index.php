<?php
// @codingStandardsIgnoreFile

require_once __DIR__.'/../../../vendor/autoload.php';

$GLOBALS['pelagos']['title'] = 'Account Management';

# load global pelagos config
$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

# load Common library from global share
require_once($GLOBALS['config']['paths']['share'].'/php/Common.php');

# check for local config file
if (file_exists('config.ini')) {
    # merge local config with global config
    $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
}

# load library info
$GLOBALS['libraries'] = parse_ini_file($GLOBALS['config']['paths']['conf'] . '/libraries.ini', true);

# add pelagos/share/php to the include path
set_include_path(get_include_path() . PATH_SEPARATOR . $GLOBALS['config']['paths']['share'] . '/php');

require_once 'db-utils.lib.php';
require_once 'drupal.php';
require_once 'EventHandler.php';

require_once 'lib/constants.php';
require_once 'lib/account.php';
require_once 'config.php';

$GLOBALS['DB'] = parse_ini_file($GLOBALS['config']['paths']['conf'].'/db.ini',true);

$GLOBALS['LDAP'] = ldap_connect('ldap://'.LDAP_HOST);

# initialize Slim
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

# add custom Twig extensions
$app->view->parserExtensions = array(
    new \Pelagos\TwigExtensions()
);

$app->hook('slim.before', function () use ($app) {
    $env = $app->environment();
    $app->view()->appendData(array('baseUrl' => $env['SCRIPT_NAME']));
});

$GLOBALS['HOST'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

$GLOBALS['AUTH_FOR_ROLE'] = function ($role = 'user') use ($app) {
    return function () use ($role,$app) {
        global $user;
        $env = $app->environment();
        $current_role = '';

        if ($user->uid) {
            $env['uid'] = $user->name;
            $current_role = 'user';
            $logged_in_uid = $user->name;
            $adminsResult = ldap_search($GLOBALS['LDAP'], "cn=ldapadmins,ou=groups,dc=griidc,dc=org", '(objectClass=*)', array("member"));
            $admins = ldap_get_entries($GLOBALS['LDAP'], $adminsResult);
            for ($i=0;$i<$admins[0]['member']['count'];$i++) {
                if ("uid=$logged_in_uid,ou=members,ou=people,dc=griidc,dc=org" == $admins[0]['member'][$i]) {
                    $current_role = 'admin';
                }
            }
            if ($current_role != $role ) {
                drupal_set_message("You are not authorized for this action!",'error');
                $env['authorized'] = false;
            }
            else {
                $env['authorized'] = true;
            }
        }
        else {
            $currentpage = urlencode(preg_replace('/^\//','',$_SERVER['REQUEST_URI']));
            drupal_set_message("You must be logged in to perform this action!<p><a href='/cas?destination=$currentpage' style='font-weight:bold;'>Log In</a></p>",'error');
            $env['authorized'] = false;
        }
    };
};

$app->get('/', function () use ($app) {
    global $user;
    $env = $app->environment();
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/index.css",'external');
    $stash = array();
    if (isset($user) and property_exists($user,'name') and $user->name) {
        $uid = $user->name;
        $stash['username'] = $uid;

        if ($GLOBALS['AUTH_FOR_ROLE']('admin')) {
            if ($app->request()->get('as_user')) {
                $uid = $app->request()->get('as_user');

                if (preg_match('/^\d+$/',$uid)) {
                    $ris_id = $uid;
                    $result = ldap_search($GLOBALS['LDAP'], "ou=people,dc=griidc,dc=org", "(employeeNumber=$uid)", array('uid'));
                    $entries = ldap_get_entries($GLOBALS['LDAP'], $result);
                    if ($entries['count'] and array_key_exists('uid',$entries[0])) {
                        $uid = $entries[0]['uid'][0];
                        $stash['effective_user'] = $uid;
                    }
                    else {
                        $stash['effective_user'] = '<no GRIIDC account>';
                    }
                }
                else {
                    $stash['effective_user'] = $uid;
                }

            }
        }

        if (!isset($ris_id)) {
            $result = ldap_search($GLOBALS['LDAP'], "ou=people,dc=griidc,dc=org", "(uid=$uid)", array('employeeNumber'));
            $entries = ldap_get_entries($GLOBALS['LDAP'], $result);
            if ($entries['count'] and array_key_exists('employeenumber',$entries[0])) {
                $ris_id = $entries[0]['employeenumber'][0];
            }
        }

        if (isset($ris_id)) {
            $stash['ris_id'] = $ris_id;
            $ris_dbh = OpenDB('RIS_RO');

            $ris_sth = $ris_dbh->prepare('SELECT People_Firstname AS first_name, People_Lastname AS last_name
                                          FROM People
                                          WHERE People.People_ID = ?');
            $ris_sth->execute(array($ris_id));
            $stash['person'] = $ris_sth->fetch();

            $ris_sth = $ris_dbh->prepare('SELECT DISTINCT FundingSource.Fund_Name AS name, FundingSource.Fund_ID AS ID, FundingSource.Fund_sort AS sort
                                          FROM FundingSource
                                          JOIN Programs
                                              ON Programs.Program_FundSrc = FundingSource.Fund_ID
                                          JOIN ProjPeople
                                              ON Programs.Program_ID = ProjPeople.Program_ID
                                          WHERE ProjPeople.People_ID = ?
                                          ORDER BY sort DESC');
            $ris_sth->execute(array($ris_id));
            $funds = $ris_sth->fetchAll();

            for ($f=0; $f<count($funds); $f++) {

                $ris_sth = $ris_dbh->prepare('SELECT DISTINCT Program_Title AS title, Programs.Program_ID AS ID
                                              FROM Programs
                                              JOIN ProjPeople
                                                  ON Programs.Program_ID = ProjPeople.Program_ID
                                              WHERE Programs.Program_FundSrc = ? AND
                                                  ProjPeople.People_ID = ?
                                              ORDER BY title');
                $ris_sth->execute(array($funds[$f]['ID'],$ris_id));
                $funds[$f]['projects'] = $ris_sth->fetchAll();

                for ($i=0; $i<count($funds[$f]['projects']); $i++) {

                    $ris_sth = $ris_dbh->prepare('SELECT DISTINCT Role_Name AS name, Roles.Role_ID AS ID
                                                  FROM Roles
                                                  JOIN ProjPeople
                                                      ON Roles.Role_ID = ProjPeople.Role_ID
                                                  WHERE ProjPeople.People_ID = ? AND
                                                        ProjPeople.Program_ID = ? AND
                                                        ProjPeople.Project_ID = 0
                                                  ORDER BY name');
                    $ris_sth->execute(array($ris_id,$funds[$f]['projects'][$i]['ID']));
                    $funds[$f]['projects'][$i]['roles'] = $ris_sth->fetchAll();

                    $ris_sth = $ris_dbh->prepare('SELECT DISTINCT Project_Title AS title, Projects.Project_ID AS ID, Project_SubTaskNum AS num
                                                  FROM Projects
                                                  JOIN ProjPeople
                                                      ON Projects.Project_ID = ProjPeople.Project_ID
                                                  WHERE ProjPeople.People_ID = ? AND
                                                        ProjPeople.Program_ID = ?
                                                  ORDER BY num');
                    $ris_sth->execute(array($ris_id,$funds[$f]['projects'][$i]['ID']));
                    $funds[$f]['projects'][$i]['tasks'] = $ris_sth->fetchAll();
                    for ($j=0; $j<count($funds[$f]['projects'][$i]['tasks']); $j++) {
                        $ris_sth = $ris_dbh->prepare('SELECT DISTINCT Role_Name as name, Roles.Role_ID AS ID
                                                      FROM Roles
                                                      JOIN ProjPeople
                                                          ON Roles.Role_ID = ProjPeople.Role_ID
                                                      WHERE ProjPeople.People_ID = ? AND
                                                            ProjPeople.Program_ID = ? AND
                                                            ProjPeople.Project_ID = ?
                                                      ORDER BY name');
                        $ris_sth->execute(array($ris_id,$funds[$f]['projects'][$i]['ID'],$funds[$f]['projects'][$i]['tasks'][$j]['ID']));
                        $funds[$f]['projects'][$i]['tasks'][$j]['roles'] = $ris_sth->fetchAll();
                    }
                }
            }
            $stash['funds'] = $funds;
        }

    }
    return $app->render('index.html',$stash);
});

$app->get('/new', function () use ($app) {
    return $app->render('verify_form.html');
});

$app->post('/new', function () use ($app) {
    $email = substr($app->request()->post('email'),0,100);
    if (empty($email)) {
        drupal_set_message("You must enter an email address.",'error');
        return $app->render('verify_form.html');
    }
    $email_safe = preg_replace('/([()*\\\\])/','\\\\$1',$email);
    $personsResult = ldap_search($GLOBALS['LDAP'], "dc=griidc,dc=org", "(mail=$email_safe)", array("uid"));
    $persons = ldap_get_entries($GLOBALS['LDAP'], $personsResult);
    if ($persons['count'] > 0) {
        drupal_set_message("An account already exists for the email address \"$email\".",'error');
        echo "<p>If you are having trouble logging in, please contact: <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for help.";
        return;
    }
    $found = false;
    $griidc_people = query_griidc_people($email);
    if ($griidc_people['found']) {
        $found = true;
        $person = $griidc_people;
    }
    else {
        $RPIS = query_RPIS($email);
        if ($RPIS['exception']) {
            if (isset($RPIS['exceptionCode']) and $RPIS['exceptionCode'] == 'NoDataAvailable') {
                $stash{'email'} = $email;
                return $app->render('email_not_found.html',$stash);
            }
            elseif (isset($RPIS['ExceptionText'])) {
                drupal_set_message("An error occurred: $RPIS[ExceptionText]",'error');
                echo "<p>Please contact <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for help.";
            }
            else {
                drupal_set_message('An unknown error occurred.','error');
                echo "<p>Please contact <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for help.";
            }
        }
        else {
            $found = true;
            $person = $RPIS;
        }
    }

    if ($found) {
        $from = 'GRIIDC Account Management Portal <griidc@gomri.org>';
        $to = $person['Person']['FirstName'] . ' ' . $person['Person']['LastName'] . " <$email>";
        $subject = "GRIIDC Account Request";
        $env = $app->environment();
        $message = "Please visit the following link to verify your identity and complete your GRIIDC account request: https://$GLOBALS[HOST]$env[SCRIPT_NAME]/request?email=$email&hash=$person[hash]";

        mail($email,$subject,$message,"From: $from\nTo: $to",'-f griidc@gomri.org');

        return $app->render('verification_email_sent.html',$person['Person']);
    }
});

$app->get('/request', function () use ($app) {
    $email = $app->request()->get('email');
    $hash = $app->request()->get('hash');
    $verify = verify_email($email,$hash);

    if (!$verify['verified']) {
        return $app->render('verification_fail.html');
    }
    else {
        $person = $verify['person']['Person'];
        foreach ($GLOBALS['PERSON_FIELDS'] as $field => $details) {
            if (isset($details['rpis'])) {
                $rpisval = '';
                foreach ($details['rpis'] as $rpis) {
                    $separator = '';
                    if (preg_match('/{([^}]+)}/',$rpis,$matches)) {
                        $separator = $matches[1];
                        $rpis = preg_replace('/{[^}]+}/','',$rpis);
                    }
                    if (isset($person[$rpis]) and !is_array($person[$rpis]) and $person[$rpis] != '') {
                        if (!empty($rpisval)) $rpisval .= "$separator ";
                        $rpisval .= $person[$rpis];
                    }
                }
                if (!empty($rpisval)) $stash['person'][$field]['value'] = $rpisval;
            }
        }

        $stash['person']['sshPublicKey']['value'] = PASTE_PUB_KEY;
        $stash['hash'] = $hash;
        $stash['checked'] = array('Drupal' => 1, 'pkiNone' => 1);
        $stash['PASTE_PUB_KEY'] = PASTE_PUB_KEY;

        drupal_set_message("Thank you, $email has been verified.",'status');
        $env = $app->environment();
        drupal_add_css("$env[SCRIPT_NAME]/includes/css/account-form.css",'external');
        return $app->render('request_form.html',$stash);
    }
});

$app->post('/request', function () use ($app) {
    $email = $app->request()->get('email');
    $hash = $app->request()->get('hash');
    $verify = verify_email($email,$hash);

    if (!$verify['verified']) {
        return $app->render('not_verified.html');
    }
    else {
        $stash = check_person($app,'u');
        $stash['person']['mail']['value'] = $email;
        $person = $verify['person']['Person'];
        if (isset($person['@attributes']['ID']) and !is_array($person['@attributes']['ID']) and $person['@attributes']['ID'] != '') $stash['person']['employeeNumber']['value'] = $person['@attributes']['ID'];

        $confirmPassword = $app->request()->post('confirmPassword');
        $stash['person']['confirmPassword']['value'] = $confirmPassword;

        if (!is_null($stash['person']['userPassword']['value']) and $stash['person']['userPassword']['value'] != '' and strlen($stash['person']['userPassword']['value']) < 8) {
            $stash['err'][] = "password is too short (must be at least 8 characters)";
            $stash['person']['userPassword']['class'] = 'account_errorfield';
            $stash['person']['confirmPassword']['class'] = 'account_errorfield';
        }

        if (!is_null($stash['person']['userPassword']['value']) and $stash['person']['userPassword']['value'] != '' and !password_complex_enough($stash['person']['userPassword']['value'])) {
            $stash['err'][] = 'password does not meet complexity requirements';
            $stash['person']['userPassword']['class'] = 'account_errorfield';
            $stash['person']['confirmPassword']['class'] = 'account_errorfield';
        }

        if ($stash['person']['userPassword']['value'] != $stash['person']['confirmPassword']['value']) {
            $stash['err'][] = "passwords do not match";
            $stash['person']['confirmPassword']['class'] = 'account_errorfield';
        }

        $pubKeyAction = $app->request()->post('pubKeyAction');
        if ($app->request()->post('Mercurial') and (is_null($pubKeyAction) or $pubKeyAction == '' or $pubKeyAction == 'none')) {
            $stash['err'][] = "SSH Public Key required for source code repository push access.";
        }

        $sshPublicKey = $app->request()->post('sshPublicKey');
        if ($pubKeyAction == 'pkiProvide' and (is_null($sshPublicKey) or $sshPublicKey == '' or $sshPublicKey == PASTE_PUB_KEY)) {
            $stash['err'][] = "you opted to provide an SSH Public Key, but did not enter one";
            $stash['person']['sshPublicKey']['class'] = 'account_errorfield';
        }

        if (count($stash['err']) > 0) {
            output_errors($stash['err']);

            foreach (array_merge($GLOBALS['APPLICATIONS'],array('Shell'=>'users')) as $application => $default_group) {
                $stash['checked'][$application] = $app->request()->post($application) ? 1 : 0;
            }

            foreach (array('pkiNone','pkiGenerate','pkiProvide') as $pubKeyActionOption) {
                $stash['checked'][$pubKeyActionOption] = $pubKeyActionOption == $pubKeyAction ? 1 : 0;
            }

            $stash['hash'] = $hash;
            $stash['PASTE_PUB_KEY'] = PASTE_PUB_KEY;

            $env = $app->environment();
            drupal_add_css("$env[SCRIPT_NAME]/includes/css/account-form.css",'external');

            return $app->render('request_form.html',$stash);
        }
        else {
            $uid = find_unique_uid($app->request()->post('givenName'),$app->request()->post('sn'));

            $stash['person']['uid']['value'] = $uid;
            $stash['pkiGenerate'] = 0;

            if ($pubKeyAction == 'pkiGenerate') {
                $stash['pkiGenerate'] = 1;
                $stash['pki'] = generate_pki($uid,$app->request()->post('userPassword'));
                $stash['person']['sshPublicKey']['value'] = $stash['pki']['pubKey'];
                $stash['objectClasses'][] = 'ldapPublicKey';
            }
            elseif ($pubKeyAction == 'pkiProvide' and $sshPublicKey != PASTE_PUB_KEY) {
                $stash['person']['sshPublicKey']['value'] = $sshPublicKey;
                $stash['objectClasses'][] = 'ldapPublicKey';
            }

            $stash['applications'] = array();
            foreach ($GLOBALS['APPLICATIONS'] as $application => $default_group) {
                if ($app->request()->post($application)) {
                    $stash['applications'][$application] = $default_group;
                }
            }

            $stash['person']['userPassword']['value'] = make_ssha_password($stash['person']['userPassword']['value']);

            $ldifFile = SPOOL_DIR . "/incoming/$uid.ldif";

            $ldif = write_ldif($ldifFile,$stash);

            $fromAddress = 'GRIIDC Account Request <griidc@gomri.org>';
            $subject = "GRIIDC Account Request: $uid";
            $env = $app->environment();
            $message = "An account request has been submitted.\n\nTo review and approve this request, please visit: https://$GLOBALS[HOST]$env[SCRIPT_NAME]/approve?uid=$uid";
            $message .= "\n\n\nRequest details:\n\n" . ldif_to_message($ldif);

            foreach (get_notify_to() as $toAddress) {
                mail($toAddress,$subject,$message,"From: $fromAddress",'-f griidc@gomri.org');
            }

            $risUserId = null;
            if (array_key_exists('employeeNumber', $stash['person']) and
                array_key_exists('value', $stash['person']['employeeNumber']) and
                isset($stash['person']['employeeNumber']['value'])) {
                $risUserId = $stash['person']['employeeNumber']['value'];
            }

            # pass event to notification event handler, along with username of
            # user who successfully initiated an account request.
            eventHappened(
                'account_requested',
                array(
                    'risUserId' => $risUserId,
                    'user' => array(
                        'firstName' => $stash['person']['givenName']['value'],
                        'lastName' => $stash['person']['sn']['value'],
                        'email' => $stash['person']['mail']['value']
                    )
                )
            );

            return $app->render('request_submitted.html',$stash);
        }

    }
});

$app->get('/approve', $GLOBALS['AUTH_FOR_ROLE']('admin'), function () use ($app) {
    $env = $app->environment();
    if  (isset($env['authorized']) and $env['authorized']) {
        $uid = $app->request()->get('uid');
        $ldifFile = SPOOL_DIR . "/incoming/$uid.ldif";
        if (is_null($uid) or $uid =='') {
            drupal_set_message("You must supply a uid.",'error');
        }
        elseif (!file_exists($ldifFile)) {
            drupal_set_message("Account request for $uid not found.",'error');
        }
        else {
            $stash = read_ldif($ldifFile);
            $stash['uid'] = $uid;
            $stash['checked']['Shell'] = in_array('posixAccount',$stash['objectClasses']);
            foreach ($GLOBALS['APPLICATIONS'] as $application => $default_group) {
                $stash['checked'][$application] = isset($stash['applications'][$application]);
            }
            $env = $app->environment();
            drupal_add_css("$env[SCRIPT_NAME]/includes/css/account-form.css",'external');
            return $app->render('approve_form.html',$stash);
        }
    }
});

$app->post('/approve', $GLOBALS['AUTH_FOR_ROLE']('admin'), function () use ($app) {
    global $user;
    $env = $app->environment();
    if (isset($env['authorized']) and $env['authorized']) {
        $uid = $app->request()->get('uid');
        $ldifFile = SPOOL_DIR . "/incoming/$uid.ldif";
        $ldif = read_ldif($ldifFile);
        $stash = check_person($app,'a',$ldif);
        if (count($stash['err']) > 0) {
            output_errors($stash['err']);
        }
        else {
            drupal_set_message("Account request $uid updated.",'status');
        }

        $stash['uid'] = $uid;

        $stash['applications'] = array();
        foreach ($GLOBALS['APPLICATIONS'] as $application => $default_group) {
            $stash['checked'][$application] = $app->request()->post($application) ? 1 : 0;
            if ($app->request()->post($application)) {
                $stash['applications'][$application] = $default_group;
            }
        }
        $stash['checked']['Shell'] = $app->request()->post('Shell') ? 1 : 0;

        write_ldif($ldifFile,$stash);

        $env = $app->environment();
        drupal_add_css("$env[SCRIPT_NAME]/includes/css/account-form.css",'external');
        return $app->render('approve_form.html',$stash);
    }
});

$app->post('/approve/create', $GLOBALS['AUTH_FOR_ROLE']('admin'), function () use ($app) {
    global $user;
    $env = $app->environment();
    if  (isset($env['authorized']) and $env['authorized']) {
        $uid = $app->request()->get('uid');
        $ldifFile = SPOOL_DIR . "/incoming/$uid.ldif";
        $ldif = read_ldif($ldifFile);
        $ldif = check_person($app,'a',$ldif);

        $ldif['applications'] = array();
        foreach ($GLOBALS['APPLICATIONS'] as $application => $default_group) {
            if ($app->request()->post($application)) {
                $ldif['applications'][$application] = $default_group;
            }
        }

        if (in_array('posixAccount',$ldif['objectClasses'])) {
            $ldif['person'] = add_posix_fields($ldif['person']);
        }
        $ldif = write_ldif($ldifFile,$ldif);
        $return_val = 0;
        $cmd = sprintf("/usr/bin/ldapadd -h '%s' -D '%s' -w '%s' -f '%s' 2>&1",LDAP_HOST,LDAP_BIND_DN,LDAP_BIND_PW,$ldifFile);
        exec($cmd,$output,$return_val);
        if ($return_val) {
            drupal_set_message("An error occurred creating account $uid.",'error');
            echo "Error details:";
            echo "<pre>";
            foreach ($output as $line) {
                echo "$line";
            }
            echo "</pre>";
        }
        else {
            rename($ldifFile,SPOOL_DIR . "/approved/$uid.ldif");
            drupal_set_message("Account $uid created.",'status');

            $toAddress = $ldif['person']['mail']['value'];
            $fromAddress = 'GRIIDC Account Request <griidc@gomri.org>';
            $subject = "GRIIDC Account Request Approved: $uid";
            $message = "Your account request has been approved.";
            $message .= "\n\nYour username is: $uid";
            $message .= "\n\nYou may now use this username and the password you provided to log in to GRIIDC services.";
            $message .= "\n\nNote: You must change your password every 180 days or it will expire. You will begin receiving password expiration warnings one week before your password expires.";

            mail($toAddress,$subject,$message,"From: $fromAddress",'-f griidc@gomri.org');

            $subject = "GRIIDC Account Request: $uid";
            $message = "The account request for $uid has been approved by " . $user->name . ".";
            $message .= "\n\n\nRequest details:\n\n" . ldif_to_message($ldif);

            foreach (get_notify_to() as $toAddress) {
                mail($toAddress,$subject,$message,"From: $fromAddress",'-f griidc@gomri.org');
            }

            # pass event to notification event handler, along with username of
            # user who's account request was approved
            eventHappened(
                'account_request_approved',
                array(
                    'userId' => $uid,
                    'user' => array(
                        'firstName' => $ldif['person']['givenName']['value'],
                        'lastName' => $ldif['person']['sn']['value'],
                        'email' => $ldif['person']['mail']['value']
                    )
                )
            );
        }
    }
});

$app->post('/approve/delete', $GLOBALS['AUTH_FOR_ROLE']('admin'), function () use ($app) {
    global $user;
    $env = $app->environment();
    if  (isset($env['authorized']) and $env['authorized']) {
        $uid = $app->request()->get('uid');
        $ldifFile = SPOOL_DIR . "/incoming/$uid.ldif";
        $ldif = read_ldif($ldifFile);
        rename($ldifFile,SPOOL_DIR . "/trash/$uid.ldif");
        drupal_set_message("Account request $uid deleted.",'status');

        $fromAddress = 'GRIIDC Account Request <griidc@gomri.org>';
        $subject = "GRIIDC Account Request: $uid";
        $message = "The account request for $uid has been deleted by " . $user->name . ".";
        $message .= "\n\n\nRequest details:\n\n" . ldif_to_message($ldif);

        foreach (get_notify_to() as $toAddress) {
            mail($toAddress,$subject,$message,"From: $fromAddress",'-f griidc@gomri.org');
        }
    }
});

$app->post('/dlkey', function () use ($app) {
    header('Content-type: text/plain');
    $uid = $app->request()->post('uid');
    $type = $app->request()->post('type');
    if ($type == 'private') {
        $filename = "$uid.pem";
    }
    elseif ($type == 'ppk') {
        $filename = "$uid.ppk";
    }
    elseif ($type == 'public') {
        $filename = "$uid.pub";
    }
    header("Content-Disposition: attachment; filename=\"$filename\"");
    echo $app->request()->post('key');
    exit;
});

$app->get('/includes/:file', function ($file) use ($app) {
    $file = "includes/$file";
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
})->conditions(array('file' => '.+'));

$app->get('/password', function () use ($app) {
    global $user;
    if (isset($user->name)) {
        $env = $app->environment();
        $app->redirect("$env[SCRIPT_NAME]/password/change");
    }
    return $app->render('password_form.html');
});

$app->post('/password', function () use ($app) {
    global $user;
    if (isset($user->name)) {
        $env = $app->environment();
        $app->redirect("$env[SCRIPT_NAME]/password/reset");
    }
    $email = substr($app->request()->post('email'),0,100);
    if (empty($email)) {
        drupal_set_message("You must enter an email address.",'error');
        return $app->render('password_form.html');
    }

    # attempt to bind to LDAP
    if (!ldap_bind($GLOBALS['LDAP'], LDAP_BIND_DN, LDAP_BIND_PW)) {
        drupal_set_message("Error binding to LDAP.",'error');
        echo "<p>Please contact: <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for help.";
        return;
    }

    $person = get_ldap_user("mail=$email");

    if (is_null($person)) {
        echo '<p>Please make sure you enter the email address associated with your account. If you need assistance, please contact: <a href="mailto:griidc@gomri.org">griidc@gomri.org</a> for help.</p>';
        return $app->render('password_form.html');
    }

    $uid = $person['uid'][0];

    $from = 'GRIIDC Account Management Portal <griidc@gomri.org>';
    $to = $person['givenname'][0] . ' ' . $person['sn'][0] . " <$email>";
    $subject = "GRIIDC Password Reset";
    $env = $app->environment();
    $message = "Please visit the following link to verify your identity and complete your GRIIDC account request: https://$GLOBALS[HOST]$env[SCRIPT_NAME]/password/reset?uid=$uid&hash=$person[hash]";

    mail($email,$subject,$message,"From: $from\nTo: $to",'-f griidc@gomri.org');

    $stash['email'] = $email;
    return $app->render('password_reset_email_sent.html',$stash);
});

$app->get('/password/:action', function ($action) use ($app) {
    # attempt to bind to LDAP
    if (!ldap_bind($GLOBALS['LDAP'], LDAP_BIND_DN, LDAP_BIND_PW)) {
        drupal_set_message("Error binding to LDAP.",'error');
        echo "<p>Please contact: <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for help.";
        return;
    }

    # verify user and get user info
    $person = get_verified_user($app);

    # if we can't verify the user, ask them to log in
    if (is_null($person)) {
        echo "<p>Please <a href='/auth/cas?dest=$_SERVER[SCRIPT_NAME]/password/reset'>login</a> first to change your password.</p>";
        echo "<p>If you have forgotten your password or it has expired, you may <a href='/account/password'>reset it</a>.</p>";
        return;
    }

    # create stash and put relevant info in it
    $stash = array();
    $stash['uid'] = $person['uid'][0];
    $stash['hash'] = $person['hash'];
    $stash['action'] = $action;

    # get password policy
    $ppolicy = get_password_policy();

    # check to make sure minimum password age has been met
    if (!password_old_enough($ppolicy,$person)) {
        if (array_key_exists('pwdminage', $ppolicy)) {
            $pwdMinAge = $ppolicy['pwdminage'][0];
        } else {
            $pwdMinAge = 86400;
        }
        $pwdMinAgeHours = round($pwdMinAge/60/60);
        drupal_set_message("You can only change your password once every $pwdMinAgeHours hours.",'error');
        return;
    }

    return $app->render('password_reset_form.html',$stash);
})->conditions(array('action' => '(reset|change)'));

$app->post('/password/:action', function ($action) use ($app) {
    # attempt to bind to LDAP
    if (!ldap_bind($GLOBALS['LDAP'], LDAP_BIND_DN, LDAP_BIND_PW)) {
        drupal_set_message("Error binding to LDAP.",'error');
        echo "<p>Please contact: <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for help.";
        return;
    }

    # verify user and get user info
    $person = get_verified_user($app);

    # create stash and put relevant info in it
    $stash = array();
    $stash['uid'] = $person['uid'][0];
    $stash['hash'] = $person['hash'];
    $stash['action'] = $action;

    # make sure we have authenticated (either by being logged in or using a password reset link)
    if (is_null($person)) {
        echo "<p>Please make sure you copied the entire link correcly from the password reset email. If you need assistance, please contact: <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for help.";
        return;
    }

    $password = $app->request()->post('password');

    # make sure the user has entered a password
    if (empty($password)) {
        drupal_set_message("You must enter a password.",'error');
        return $app->render('password_reset_form.html',$stash);
    }

    # make sure passwords match
    if ($app->request()->post('verify_password') != $password) {
        drupal_set_message("Passwords do not match.",'error');
        return $app->render('password_reset_form.html',$stash);
    }

    # get password policy
    $ppolicy = get_password_policy();

    # check to make sure minimum password age has been met
    if (!password_old_enough($ppolicy,$person)) {
        drupal_set_message("You tried to change your password again too soon.",'error');
        return $app->render('password_reset_form.html',$stash);
    }

    # check to make sure user is not just using the same password
    if (count($person['userpassword']) > 0 and preg_match('/{SSHA}(.*)$/',$person['userpassword'][0],$matches)) {
        $hash_and_salt = base64_decode($matches[1]);
        $curr_hash = join('',unpack('H*',substr($hash_and_salt,0,20)));
        $salt = substr($hash_and_salt,20,4);
        $new_hash = sha1($password.$salt);
        if ($new_hash == $curr_hash ) {
            drupal_set_message("Your new password must be different from your current password.",'error');
            return $app->render('password_reset_form.html',$stash);
        }
    }

    # check to make sure user is not using a recently used password
    $pwd_in_history = false;
    if (array_key_exists('pwdhistory',$person)) {
        foreach ($person['pwdhistory'] as $pwd) {
            if (preg_match('/{SSHA}(.*)$/',$pwd,$matches)) {
                $hash_and_salt = base64_decode($matches[1]);
                $old_hash = join('',unpack('H*',substr($hash_and_salt,0,20)));
                $salt = substr($hash_and_salt,20,4);
                $new_hash = sha1($password.$salt);
                if ($new_hash == $old_hash ) {
                    $pwd_in_history = true;
                }
            }
        }
    }
    if ($pwd_in_history) {
        drupal_set_message("Password used recently.",'error');
        return $app->render('password_reset_form.html',$stash);
    }

    # check to make sure password is long enough
    if (strlen($password) < 8) {
        drupal_set_message('Password must be at least 8 characters long.','error');
        return $app->render('password_reset_form.html',$stash);
    }

    # check to make sure password is complex enough
    if (!password_complex_enough($password)) {
        drupal_set_message('Password does not meet complexity requirements.','error');
        return $app->render('password_reset_form.html',$stash);
    }

    # change password
    if (!ldap_mod_replace ($GLOBALS['LDAP'], $person['dn'], array('userpassword' => $password))) {
        drupal_set_message("Error updating password.",'error');
        echo "<p>Please contact: <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for help.";
        return;
    }

    drupal_set_message('Password changed.','status');
    echo "<p>Your password has been updated. Please use this new password to log in to GRIIDC systems. If you need assistance, please contact: <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> for help.</p>";
})->conditions(array('action' => '(reset|change)'));

$app->run();
