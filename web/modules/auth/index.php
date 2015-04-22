<?php

$GLOBALS['pelagos']['title'] = 'Authentication';

$GLOBALS['griidc'] = parse_ini_file('/etc/opt/pelagos.ini',true);
$GLOBALS['config'] = parse_ini_file('config.ini',true);
$GLOBALS['libraries'] = parse_ini_file($GLOBALS['griidc']['paths']['conf'].'/libraries.ini',true);

require_once $GLOBALS['libraries']['Slim2']['include'];
\Slim\Slim::registerAutoloader();
require_once $GLOBALS['libraries']['Slim-Views']['include_Twig'];
# load Twig
require_once 'Twig/Autoloader.php';
require_once $GLOBALS['libraries']['LightOpenID']['include'];

# add pelagos/share/php to the include path
set_include_path('../../../share/php' . PATH_SEPARATOR . get_include_path());

require_once 'drupal.php';
require_once 'dumpIncludesFile.php';
require_once 'rpis.php';
require_once 'auth.php';

$GLOBALS['auth_types'] = array(
    'cas' => array(
        'name' => 'CAS'
    ),
    'openid' => array(
        'name' => 'OpenID',
        'providers' => array(
            'symantec' => array(
                'name' => 'Symantec',
                'identity' => 'https://pip.verisignlabs.com/login.do',
                'logout' => 'https://pip.verisignlabs.com/logout.do'
            )
        )
    ),
    'oauth2' => array(
        'name' => 'OAuth2',
        'providers' => array(
            'google' => array(
                'name' => 'google',
                'client_id' => $GLOBALS['config']['google']['client_id'],
                'logout' => 'https://accounts.google.com/logout'
            )
        )
    )
);

$app = new \Slim\Slim(array(
                        'view' => new \Slim\Views\Twig(),
                        'debug' => true,
                        'log.level' => \Slim\Log::DEBUG,
                        'log.enabled' => true
                     ));

$env = $app->environment();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$app->baseUrl = "$protocol$env[SERVER_NAME]$env[SCRIPT_NAME]";

$app->view->parserDirectory = $GLOBALS['libraries']['Twig']['directory'];

$app->hook('slim.before', function () use ($app) {
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
                 || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]$env[SCRIPT_NAME]"));
    $app->view()->appendData(array('serverUrl' => "$protocol$env[SERVER_NAME]"));
});

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/js/:name.js', function ($name) use ($app) {
    $stash['funds'] = getFundingSources(getDBH('RPIS'));
    $stash['projects'] = getProjectDetails(getDBH('RPIS'),array("fundsrc=7"));
    header('Content-type: text/javascript');
    $app->render("js/$name.js",$stash);
    exit;
});

$app->get('/css/:name.css', function ($name) use ($app) {
    header('Content-type: text/css');
    $app->render("css/$name.css");
    exit;
});

$app->get('/', function () use ($app) {
    drupal_add_css("$_SERVER[SCRIPT_NAME]/includes/css/auth.css",array('type'=>'external'));
    $stash['auth'] = get_auth_info();
    $stash['auth_types'] = $GLOBALS['auth_types'];
    return $app->render('html/index.html',$stash);
});

$app->get('/:auth_type', function ($auth_type) use ($app) {
    drupal_add_css("$_SERVER[SCRIPT_NAME]/includes/css/auth.css",array('type'=>'external'));
    $stash['auth'] = get_auth_info();
    if (!user_is_logged_in_somehow() and $auth_type == 'cas') {
        drupal_goto('cas',array('query' => array('destination' => preg_replace('/^\/+/','',$app->request->get('dest')))));
    }
    if (!is_null($app->request->get('dest'))) {
        drupal_goto($app->request->get('dest'));
    }
    $stash['auth_type_key'] = $auth_type;
    $stash['auth_type'] = $GLOBALS['auth_types'][$auth_type];
    return $app->render('html/auth_type.html',$stash);
})->conditions(array('auth_type' => join('|',array_keys($GLOBALS['auth_types']))));

// this route will provide a redirect to the oauth2 provider's login URL
$app->get('/oauth2/:provider(/)', function ($provider) use ($app) {
    global $pelagos;
    switch($provider) {
        case "google":
            $url = "https://accounts.google.com/o/oauth2/auth";
            $params = array(
                             "response_type" => "code",
                             "client_id" => $GLOBALS['config']['google']['client_id'],
                             "redirect_uri" => "$pelagos[component_url]/oauth2callback",
                             "scope" => "https://www.googleapis.com/auth/plus.me https://www.googleapis.com/auth/plus.profile.emails.read"
            );
            $request_to = $url . '?' . http_build_query($params);
            header("Location: " . $request_to);
            drupal_exit();
        break;
    }
})->conditions(array('provider' => join('|',array_keys($GLOBALS['auth_types']['oauth2']['providers']))));

$app->get('/oauth2callback', function () use ($app) {
    global $pelagos;
    $token = $app->request->get('code');
    $url = 'https://accounts.google.com/o/oauth2/token';
    $params = array(
        "code" => $token,
        "client_id" => $GLOBALS['config']['google']['client_id'],
        "client_secret" => $GLOBALS['config']['google']['client_secret'],
        "redirect_uri" => "$pelagos[component_url]/oauth2callback",
        "grant_type" => "authorization_code"
    );
    $request = new HttpRequest($url, HttpRequest::METH_POST);
    $request->setPostFields($params);
    $request->send();
    $responseObj = json_decode($request->getResponseBody());
    if((isset($responseObj->access_token)) and (!isset($responseObj->error))) {

        preg_match("/^([^\.]+)\.([^\.]+)/",$responseObj->id_token,$jwt);
        $google_user_info = json_decode(base64_decode($jwt[2]));

        $_SESSION['guestAuth'] = true;
        $_SESSION['guestAuthType'] = 'oauth2';
        $_SESSION['guestAuthProvider'] = 'google';
        $_SESSION['guestAuthUser'] = $google_user_info->email;

        $dest = $app->request->get('dest');
        if (substr($dest,0,1) != '/') {
            $dest = "/$dest";
        }
        header("Location: $dest");
        drupal_exit();
    }
})->conditions(array('provider' => join('|',array_keys($GLOBALS['auth_types']['oauth2']['providers']))));

$app->get('/openid/:provider', function ($provider) use ($app) {
    try {
        $env = $app->environment();
        $openid = new LightOpenID($env["SERVER_NAME"]);
        if (!$openid->mode) {
            if (isset($_GET['login'])) {
                $openid->identity = $GLOBALS['auth_types']['openid']['providers'][$provider]['identity'];
                header('Location: ' . $openid->authUrl());
            }
            $openid->identity = $GLOBALS['auth_types']['openid']['providers'][$provider]['identity'];
            $openid->required = array('contact/email', 'contact/country/home', 'namePerson/first', 'namePerson/last');
            drupal_goto($openid->authUrl());
        }
        else {
            $openid->validate();
            $info = $openid->getAttributes();
            $_SESSION['guestAuth'] = true;
            $_SESSION['guestAuthType'] = 'openid';
            $_SESSION['guestAuthProvider'] = $provider;
            $_SESSION['guestAuthUser'] = $info['contact/email'];
            $dest = $app->request->get('dest');
            if (substr($dest,0,1) != '/') $dest = "/$dest";
            header("Location: $dest");
            drupal_exit();
        }
    }
    catch(ErrorException $e) {
        drupal_set_message($e->getMessage(),'error');
    }
})->conditions(array('provider' => join('|',array_keys($GLOBALS['auth_types']['openid']['providers']))));

$app->get('/logout', function () use ($app) {
    $auth_info = get_auth_info();
    if (isset($auth_info)) {
        if ($auth_info['type'] == 'cas') {
            cas_logout();
        }
        if ($auth_info['type'] == 'openid' or $auth_info['type'] == 'oauth2') {
            try {
                if (array_key_exists('guestAuthUser',$_SESSION)) {
                    $user = $_SESSION['guestAuthUser'];
                    unset($_SESSION['guestAuthUser']);
                }
                if (array_key_exists('guestAuthProvider',$_SESSION)) unset($_SESSION['guestAuthProvider']);
                if (array_key_exists('guestAuthType',$_SESSION)) unset($_SESSION['guestAuthType']);
                if (array_key_exists('guestAuth',$_SESSION)) unset($_SESSION['guestAuth']);
                if (array_key_exists('logout',$GLOBALS['auth_types'][$auth_info['type']]['providers'][$auth_info['provider']]))
                    drupal_goto($GLOBALS['auth_types'][$auth_info['type']]['providers'][$auth_info['provider']]['logout']);
                drupal_set_message("Guess access for $user has been logged out.",'status');
                drupal_goto($app->baseUrl);
            }
            catch(ErrorException $e) {
                drupal_set_message($e->getMessage(),'error');
            }
        }
    }
    else {
        print "Not logged in!";
    }
});

$app->run();
