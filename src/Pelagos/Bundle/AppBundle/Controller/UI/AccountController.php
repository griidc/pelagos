<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Security\EntityProperty;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class AccountController extends UIController
{
    /**
     * The Funding Org action.
     *
     * @Route("/Account/")
     * @Method("GET")
     *
     * @return Response A Response instance.
     */
    public function showAction()
    {
        return $this->render('PelagosAppBundle:template:Account.html.twig');
    }
    
    /**
     * Function the post for e-mail verification, and token emailing.
     *
     * @param Request $request The Symonfy Request Object.
     *
     * @Route("/Account/VerifyEmail")
     * @Method("POST")
     *
     * @return Response A Response instance.
     */
    public function verifyEmail(Request $request)
    {
        $emailAddress = $request->request->get('emailAddress');
        
        //$this->setTitle('Account Request Result');
        
        $entity = $this->entityHandler->getBy('Pelagos:Person', array('emailAddress' => $emailAddress));
        
        if (count($entity) === 0) {
            throw new \Pelagos\Exception\NotFoundException('e-mail not found!');
        }
        
        foreach ($entity as $person) {
            // Get personToken
            $personToken = $person->getToken();
            
            if ($person->getAccount() instanceof \Pelagos\Entity\Account) {
                throw new \Pelagos\Exception\AccountExistsException('You already have an account!');
            }
            
            // if $person has Token, remove Token
            if ($personToken instanceof \Pelagos\Entity\personToken) {
                $personToken->getPerson()->setToken(null);
                $this->entityHandler->delete($personToken);
            }
            
            $dateInterval = new \DateInterval('P7D');
            
            // Create new personToken
            $personToken = new \Pelagos\Entity\PersonToken($person, 'CREATE_ACCOUNT', $dateInterval);
            
            // Persist PersonToken
            $personToken->setPerson($person);
            $personToken->setCreator($person);
            $personToken = $this->entityHandler->create($personToken);
            
            $mailData = array(
                'Person' => $person,
                'PersonToken' => $personToken,
            );
            
            $twig = $this->get('twig');
            
            $template = $twig->loadTemplate('PelagosAppBundle:template:AccountConfirmation.email.html.twig');
            
            $email = array(
                'toEmail'  => $person->getEmailAddress(),
                'toName'   => $person->getFirstName() . ' ' . $person->getLastName(),
                'subject'  => $template->renderBlock('subject', $mailData),
                'bodyHTML' => $template->renderBlock('body_html', $mailData),
                'bodyText' => $template->renderBlock('body_text', $mailData),
            );
            
            $this->sendMail($email);
        }
        
        return $this->render('PelagosAppBundle:template:EmailFound.html.twig');
    }
    
    /**
     * The Funding Org action.
     *
     * @Route("/Account/Verify/{tokenText}")
     * @Method("GET")
     *
     * @return Response A Response instance.
     */
    public function verifyTokenAction($tokenText)
    {
        $entity = $this->entityHandler->getBy('Pelagos:PersonToken', array('tokenText' => $tokenText));

        if (count($entity) === 0) {
            throw new \Pelagos\Exception\NotFoundException('Token not found!');
        }

        foreach ($entity as $personToken) {
            if (!$personToken->isValid()) {
                throw new \Pelagos\Exception\InvalidTokenException('Invalid Token!');
            }

            $person = $personToken->getPerson();

            if ($person->getAccount() !== null) {
                throw new \Pelagos\Exception\AccountExistsException('You already have an account!');
            }
        }

        $twigData = array(
            'tokenText' => $tokenText
        );

        return $this->render('PelagosAppBundle:template:setPassword.html.twig', $twigData);
    }
    
    /**
     * The Funding Org action.
        *
     * @Route("/Account/Create/")
     * @Method("POST")
        *
     * @return Response A Response instance.
     */
    public function createAccountAction(Request $request)
    {
        $tokenText = $request->request->get('tokenText');
        
        $entity = $this->entityHandler->getBy('Pelagos:PersonToken', array('tokenText' => $tokenText));
        
        if (count($entity) === 0) {
            throw new \Pelagos\Exception\NotFoundException('Token not found!');
        }
        
        foreach ($entity as $personToken) {
            if (!$personToken->isValid()) {
                throw new \Pelagos\Exception\InvalidTokenException('Invalid Token!');
            }
            $person = $personToken->getPerson();
            
            if ($person->getAccount() !== null) {
                throw new \Pelagos\Exception\AccountExistsException('You already have an account!');
            }
            
            if ($request->request->get('password') !== $request->request->get('verify_password')) {
                throw new \Exception('Password do not match!');
            }
            
            $userId = \Pelagos\Factory\UserIdFactory::generateUniqueUserId($person, $this->entityHandler);
            
            $account = new \Pelagos\Entity\Account($person, $userId, $request->request->get('password'));
            
            $account->setCreator($userId);
            
            $account = $this->entityHandler->create($account);
            
            $twigData = array(
                'Account' => $account,
            );
            
            return $this->render('PelagosAppBundle:template:accountCreated.html.twig', $twigData);
        }
    }
    
    /**
     * A swift mailer function to send e-mail.
     *
     * @param array $email An array of parameters used to send e-mail.
     *
     * @access private
     *
     * @return integer The number of successful recipients.
     */
    private function sendMail(array $email)
    {
        // Hooray a Transport, we're saved!
        $transport = \Swift_MailTransport::newInstance();
        
        // Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($transport);
        
        // Create a message
        $message = \Swift_Message::newInstance()
        ->setFrom(array('griidc@gomri.org' => 'GRIIDC'))
        ->setTo(array($email['toEmail'] => $email['toName']))
        ->setSubject($email['subject'])
        ->setBody($email['bodyText'], 'text/plain')
        ->addPart($email['bodyHTML'], 'text/html');
        
        // Send the message
        return $mailer->send($message);
    }
}
