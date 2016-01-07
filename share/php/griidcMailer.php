<?php
// @codingStandardsIgnoreFile

if (!class_exists('griidcMailer')) {
    class griidcMailer
    {
        public $currentUserEmail;
        public $currentUserLastName;
        public $currentUserFirstName;
        public $mailSubject;
        public $mailMessage;

        private $toUsers;
        private $ccUsers;
        private $bccUsers;

        public $donotBCC;

        public function __construct($useCurrentUser)
        {
            $config = parse_ini_file('/etc/opt/pelagos.ini', true);
            $config = array_merge($config, parse_ini_file($config['paths']['conf'].'/ldap.ini', true));

            if (!function_exists('getDrupalUserName')) {
                include_once $config['paths']['share'].'/php/drupal.php';
            }

            $this->donotBCC = false;
            $userId = getDrupalUserName();
            if (isset($userId)) {
                if (!function_exists('connectLDAP')) {
                    include_once $config['paths']['share'].'/php/ldap.php';
                }
                $ldap = connectLDAP($config['ldap']['server']);
                $userDN = getDNs($ldap, "dc=griidc,dc=org", "(uid=$userId)");
                $userDN = $userDN[0]['dn'];
                $attributes = array('givenName','sn','mail');
                $entries = getAttributes($ldap, $userDN, $attributes);
                if (count($entries)>0) {
                    $this->currentUserFirstName = $entries['givenName'][0];
                    $this->currentUserLastName = $entries['sn'][0];
                    $this->currentUserEmail = $entries['mail'][0];

                    if ($useCurrentUser) {
                        $this->addToUser(
                            $this->currentUserFirstName,
                            $this->currentUserLastName,
                            $this->currentUserEmail
                        );
                    }
                }
            }

            return true;
        }

        public function addToUser($firstName, $lastName, $eMail)
        {
            $newUser = array('userFirstName' => $firstName, 'userLastName' => $lastName, 'userEmail' => $eMail);
            $this->toUsers[] = $newUser;
        }

        public function addCCUser($firstName, $lastName, $eMail)
        {
            $newUser = array('userFirstName' => $firstName, 'userLastName' => $lastName, 'userEmail' => $eMail);
            $this->ccUsers[] = $newUser;
        }

        public function addBCCUser($firstName, $lastName, $eMail)
        {
            $newUser = array('userFirstName' => $firstName, 'userLastName' => $lastName, 'userEmail' => $eMail);
            $this->bccUsers[] = $newUser;
        }

        public function sendMail()
        {
            $subject = $this->mailSubject;
            $message = $this->mailMessage;
            $to      = '';

            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

            //TO:
            $headers .= "To: ";
            foreach ($this->toUsers as $User) {
                $userLastName = $User['userLastName'];
                $userFirstName = $User['userFirstName'];
                $userEmail = $User['userEmail'];
                if ($userFirstName <> "" and $userLastName <> "") {
                    $headers .= "\"$userLastName, $userFirstName\" <$userEmail>, ";
                } else {
                    $headers .= " $userEmail, ";
                }
                //$to .= $userEmail . ', '; ;
            }
            $headers .= "\r\n";

            if (sizeof($this->ccUsers) > 1) {
                $headers .= "CC: ";
                foreach ($this->ccUsers as $User) {
                    $userLastName = $User['userLastName'];
                    $userFirstName = $User['userFirstName'];
                    $userEmail = $User['userEmail'];
                    $headers .= "\"$userLastName, $userFirstName\" <$userEmail>, ";
                    //$to .= $userEmail . ', '; ;
                }
                $headers .= "\r\n";
            }

            if (sizeof($this->bccUsers) > 1) {
                $headers .= "BCC: ";
                foreach ($this->bccUsers as $User) {
                    $userLastName = $User['userLastName'];
                    $userFirstName = $User['userFirstName'];
                    $userEmail = $User['userEmail'];
                    if ($userFirstName <> "" and $userLastName <> "") {
                        $headers .= "\"$userLastName, $userFirstName\" <$userEmail>, ";
                    } else {
                        $headers .= " $userEmail, ";
                    }
                }
                $headers .= "\r\n";
            }

            if (!$this->donotBCC) {
                $headers .= "Bcc: griidc@gomri.org" . "\r\n";
            }

            $headers .= "From: \"GRIIDC\" <griidc@gomri.org>" . "\r\n";
            $headers .= 'X-Mailer: PHP/' . phpversion();
            $parameters = '-ODeliveryMode=d';

            return mail($to, $subject, $message, $headers, $parameters);
        }
    }
}
