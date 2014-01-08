<?php

include_once '/usr/local/share/GRIIDC/php/ldap.php';
include_once '/usr/local/share/GRIIDC/php/drupal.php';

class griidcMailer
{
	public $currentUserEmail;
	public $currentUserLastName;
	public $currentUserFirstName;
	public $mailSubject;
	public $mailMessage;
	
	private $toUsers;
	
	public $donotBCC;
	
	public function __construct($useCurrentUser)
	{
		$this->donotBCC = false;
		$userId = getDrupalUserName();
		$ldap = connectLDAP('triton.tamucc.edu');
		$userDN = getDNs($ldap,"dc=griidc,dc=org", "(uid=$userId)");
		$userDN = $userDN[0]['dn'];
		$attributes = array('givenName','sn','mail');
		$entries = getAttributes($ldap,$userDN,$attributes);
		if (count($entries)>0)
		{
			$this->currentUserFirstName = $entries['givenName'][0];
			$this->currentUserLastName = $entries['sn'][0];
			$this->currentUserEmail = $entries['mail'][0];
			
			if ($useCurrentUser)
			{
				$this->addToUser($this->currentUserFirstName,$this->currentUserLastName,$this->currentUserEmail);
			}
		}

		return true;
	}
	
	public function addToUser($firstName, $lastName, $eMail)
	{
		$newUser = array('userFirstName' => $firstName, 'userLastName' => $lastName, 'userEmail' => $eMail);
		
		$this->toUsers[] = $newUser;
	}
	
	public function sendMail()
	{
		$subject = $this->mailSubject;
		$message = $this->mailMessage;
		$to      = '';

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "To: ";
		foreach ($this->toUsers as $toUser) {
			$userLastName = $toUser['userLastName'];
			$userFirstName = $toUser['userFirstName'];
			$userEmail = $toUser['userEmail'];
			$headers .= "\"$userLastName, $userFirstName\" <$userEmail>, ";
			//$to .= $userEmail . ', '; ;
		}
		$headers .= "\r\n";
		
		if (!$this->donotBCC)
		{
			$headers .= "Bcc: griidc@gomri.org" . "\r\n";
		}
		
		$headers .= "From: \"GRIIDC\" <griidc@gomri.org>" . "\r\n";
		$headers .= 'X-Mailer: PHP/' . phpversion();
		$parameters = '-ODeliveryMode=d'; 
				
		return mail($to, $subject, $message, $headers, $parameters);
	}
}

?>