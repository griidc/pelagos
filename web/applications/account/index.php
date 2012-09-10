<?php

require_once '/usr/local/share/Slim/Slim/Slim.php';
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';

require_once 'lib/constants.php';
require_once 'lib/account.php';
require_once 'config.php';

$GLOBALS['LDAP'] = ldap_connect('ldap://'.LDAP_HOST);

$app = new Slim(array(
                        'view' => new TwigView,
                        'debug' => true,
                        'log.level' => Slim_Log::DEBUG,
                        'log.enabled' => true
                     ));

$GLOBALS['BASE'] = '';
if (preg_match('/^(\/[^\/]+)/',$app->request()->getResourceUri(),$matches)) {
    $GLOBALS['BASE'] = $matches[1];
}

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

$app->get("$GLOBALS[BASE]/", function () use ($app) {
    $stash['BASE'] = $GLOBALS['BASE'];
    return $app->render('index.html',$stash);
});

$app->get("$GLOBALS[BASE]/new", function () use ($app) {
    $stash['BASE'] = $GLOBALS['BASE'];
    return $app->render('verify_form.html',$stash);
});

$app->post("$GLOBALS[BASE]/new", function () use ($app) {
    $email = $app->request()->post('email');
    if (empty($email)) {
        drupal_set_message("You must enter an email address.",'error');
        $stash['BASE'] = $GLOBALS['BASE'];
        return $app->render('verify_form.html',$stash);
    }
    $personsResult = ldap_search($GLOBALS['LDAP'], "dc=griidc,dc=org", "(mail=$email)", array("uid"));
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
                drupal_set_message("The email address \"$email\" is not authorized for account creation.",'error');
                echo "<p>Please email: <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a> to request authorization for a GRIIDC account.</p>";
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
        $message = "Please visit the following link to verify your identity and complete your GRIIDC account request: https://$GLOBALS[HOST]$GLOBALS[BASE]/request?email=$email&hash=$person[hash]";

        mail($email,$subject,$message,"From: $from\nTo: $to");

        return $app->render('verification_email_sent.html',$person['Person']);
    }
});

$app->get("$GLOBALS[BASE]/request", function () use ($app) {
    $email = $app->request()->get('email');
    $hash = $app->request()->get('hash');
    $verify = verify_email($email,$hash);

    if (!$verify['verified']) {
        return $app->render('verification_fail.html');
    }
    else {
        $person = $verify['person']['Person'];
        $stash['person']['givenName']['value'] = $person['FirstName'];
        $stash['person']['sn']['value'] = $person['LastName'];
        $stash['person']['cn']['value'] = '';
        if (isset($person['Title'][0]) and $person['Title'][0] != '') $stash['person']['cn']['value'] .= $person['Title'][0] . ' ';
        $stash['person']['cn']['value'] .= $person['FirstName'];
        if (isset($person['MiddleName']) and $person['MiddleName'] != '') $stash['person']['cn']['value'] .= ' ' . $person['MiddleName'];
        $stash['person']['cn']['value'] .= ' ' .$person['LastName'];
        if (isset($person['Suffix']) and $person['Suffix'] != '') $stash['person']['cn']['value'] .= ', ' . $person['Suffix'];
        $stash['person']['mail']['value'] = $email;
        $stash['person']['telephoneNumber']['value'] = $person['PhoneNum'];
        if (isset($person['JobTitle']) and $person['JobTitle'] != '') $stash['person']['title']['value'] = $person['JobTitle'];

        $stash['person']['sshPublicKey']['value'] = PASTE_PUB_KEY;
        $stash['affiliations'] = get_affiliations(null);
        $stash['hash'] = $hash;
        $stash['checked'] = array('Drupal' => 1, 'pkiNone' => 1);
        $stash['PASTE_PUB_KEY'] = PASTE_PUB_KEY;
        $stash['BASE'] = $GLOBALS['BASE'];

        drupal_set_message("Thank you, $email has been verified.",'status');
        drupal_add_css("$GLOBALS[BASE]/css/account-form.css",'external');
        return $app->render('request_form.html',$stash);
    }
});

$app->post("$GLOBALS[BASE]/request", function () use ($app) {
    $email = $app->request()->get('email');
    $hash = $app->request()->get('hash');
    $verify = verify_email($email,$hash);

    if (!$verify['verified']) {
        return $app->render('not_verified.html');
    }
    else {
        $stash = check_person($app,'u');
        $stash['person']['mail']['value'] = $email;

        $confirmPassword = $app->request()->post('confirmPassword');
        $stash['person']['confirmPassword']['value'] = $confirmPassword;

        if (!is_null($stash['person']['userPassword']['value']) and $stash['person']['userPassword']['value'] != '' and strlen($stash['person']['userPassword']['value']) < 8) {
            $stash['err'][] = "password is too short (must be at least 8 characters)";
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

            foreach (array_merge($GLOBALS['APPLICATIONS'],array('Shell')) as $application) {
                $stash['checked'][$application] = $app->request()->post($application) ? 1 : 0;
            }

            foreach (array('pkiNone','pkiGenerate','pkiProvide') as $pubKeyActionOption) {
                $stash['checked'][$pubKeyActionOption] = $pubKeyActionOption == $pubKeyAction ? 1 : 0;
            }

            $stash['affiliations'] = get_affiliations($app->request()->post('affiliation'));
            $stash['hash'] = $hash;
            $stash['PASTE_PUB_KEY'] = PASTE_PUB_KEY;
            $stash['BASE'] = $GLOBALS['BASE'];

            drupal_add_css("$GLOBALS[BASE]/css/account-form.css",'external');
            return $app->render('request_form.html',$stash);
        }
        else {
            $uid = find_unique_uid($app->request()->post('givenName'),$app->request()->post('sn'));

            $stash['person']['uid']['value'] = $uid;
            $stash['pkiGenerate'] = 0;

            $objectClasses = array('top','person','inetOrgPerson','organizationalPerson');

            if ($app->request()->post('Shell')) {
                $objectClasses[] = 'posixAccount';
                $stash['person'] = add_posix_fields($stash['person']);
            }

            if ($pubKeyAction == 'pkiGenerate') {
                $stash['pkiGenerate'] = 1;
                $stash['pki'] = generate_pki($uid,$app->request()->post('userPassword'));
                $stash['person']['sshPublicKey']['value'] = $stash['pki']['pubKey'];
                $objectClasses[] = 'ldapPublicKey';
            }
            elseif ($pubKeyAction == 'pkiProvide' and $sshPublicKey != PASTE_PUB_KEY) {
                $stash['person']['sshPublicKey']['value'] = $sshPublicKey;
                $objectClasses[] = 'ldapPublicKey';
            }

            $stash['objectClasses'] = $objectClasses;

            $stash['applications'] = array();
            foreach ($GLOBALS['APPLICATIONS'] as $application) {
                if ($app->request()->post($application)) {
                    $stash['applications'][$application] = 'users';
                }
            }

            $stash['affiliation'] = $app->request()->post('affiliation');

            $stash['person']['userPassword']['value'] = make_ssha_password($stash['person']['userPassword']['value']);

            $ldifFile = SPOOL_DIR . "/incoming/$uid.ldif";

            write_ldif($ldifFile,$stash);

            $notify_to = array();
            $adminsResult = ldap_search($GLOBALS['LDAP'], "cn=ldapadmins,ou=groups,dc=griidc,dc=org", '(objectClass=*)', array("member"));
            $admins = ldap_get_entries($GLOBALS['LDAP'], $adminsResult);
            for ($i=0;$i<$admins[0]['member']['count'];$i++) {
                $adminEmailResult = ldap_search($GLOBALS['LDAP'], $admins[0]['member'][$i], '(objectClass=*)', array("mail"));
                $adminEmail = ldap_get_entries($GLOBALS['LDAP'], $adminEmailResult);
                $notify_to[] = $adminEmail[0]['mail'][0];
            }

            $fromAddress = 'GRIIDC Account Request <griidc@gomri.org>';
            $subject = "GRIIDC Account Request: $uid";
            $message = "An account request has been submitted.\n\nTo review and approve this request, please visit: https://$GLOBALS[HOST]$GLOBALS[BASE]/approve?uid=$uid";

            foreach ($notify_to as $toAddress) {
                mail($toAddress,$subject,$message,"From: $fromAddress");
            }

            $stash['BASE'] = $GLOBALS['BASE'];
            return $app->render('request_submitted.html',$stash);
        }

    }
});

$app->get("$GLOBALS[BASE]/approve", $GLOBALS['AUTH_FOR_ROLE']('admin'), function () use ($app) {
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
            $stash['BASE'] = $GLOBALS['BASE'];
            $stash['uid'] = $uid;
            $stash['affiliations'] = get_affiliations($stash['affiliation']);
            $stash['checked']['Shell'] = in_array('posixAccount',$stash['objectClasses']);
            foreach ($GLOBALS['APPLICATIONS'] as $application) {
                $stash['checked'][$application] = isset($stash['applications'][$application]);
            }
            drupal_add_css("$GLOBALS[BASE]/css/account-form.css",'external');
            return $app->render('approve_form.html',$stash);
        }
    }
});

$app->post("$GLOBALS[BASE]/approve", $GLOBALS['AUTH_FOR_ROLE']('admin'), function () use ($app) {
    $env = $app->environment();
    if (isset($env['authorized']) and $env['authorized']) {
        $stash = array();
        $uid = $app->request()->get('uid');

        $stash = check_person($app,'a');
        if (count($stash['err']) > 0) {
            output_errors($stash['err']);
        }
        else {
            $ldifFile = SPOOL_DIR . "/incoming/$uid.ldif";
            drupal_set_message("Account request $uid updated.",'status');
        }

        $stash['BASE'] = $GLOBALS['BASE'];
        $stash['uid'] = $uid;
        $stash['affiliations'] = get_affiliations($app->request()->post('affiliation'));
        foreach (array_merge($GLOBALS['APPLICATIONS'],array('Shell')) as $application) {
            $stash['checked'][$application] = $app->request()->post($application) ? 1 : 0;
        }
        drupal_add_css("$GLOBALS[BASE]/css/account-form.css",'external');
        return $app->render('approve_form.html',$stash);
    }
});

$app->post("$GLOBALS[BASE]/approve/create", $GLOBALS['AUTH_FOR_ROLE']('admin'), function () use ($app) {
    $env = $app->environment();
    if  (isset($env['authorized']) and $env['authorized']) {
        $uid = $app->request()->get('uid');
        $ldifFile = SPOOL_DIR . "/incoming/$uid.ldif";
        $ldif = read_ldif($ldifFile);
        $return_val = 0;
        $cmd = sprintf('/usr/bin/ldapadd -h "%s" -D "%s" -w "%s" -f "%s" 2>&1',LDAP_HOST,LDAP_BIND_DN,LDAP_BIND_PW,$ldifFile);
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
            $message = "Your account request has been approved.\n\nYour username is: $uid\n\nYou may now use this username and the password you provided to log in to GRIIDC services.";

            mail($toAddress,$subject,$message,"From: $fromAddress");
        }
    }
});

$app->post("$GLOBALS[BASE]/approve/delete", $GLOBALS['AUTH_FOR_ROLE']('admin'), function () use ($app) {
    $env = $app->environment();
    if  (isset($env['authorized']) and $env['authorized']) {
        $uid = $app->request()->get('uid');
        $ldifFile = SPOOL_DIR . "/incoming/$uid.ldif";
        rename($ldifFile,SPOOL_DIR . "/trash/$uid.ldif");
        drupal_set_message("Account request $uid deleted.",'status');
    }
});

$app->post("$GLOBALS[BASE]/dlkey", function () use ($app) {
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

$app->run();

?>
