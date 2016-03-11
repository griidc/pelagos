<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Pelagos\Bundle\AppBundle\Factory\UserIdFactory;
use Pelagos\Entity\Account;
use Pelagos\Entity\PersonToken;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class AccountController extends UIController
{
    /**
     * The default action.
     *
     * @Route("/Account")
     * @Method("GET")
     *
     * @return Response A Symfony Response instance.
     */
    public function defaultAction()
    {
        return $this->render('PelagosAppBundle:template:Account.html.twig');
    }

    /**
     * Post handler to verify the email address by sending a link with a Person Token.
     *
     * @param Request $request The Symfony Request object.
     *
     * @throws \Exception When more than one Person is found for an email address.
     *
     * @Route("/Account")
     * @Method("POST")
     *
     * @return Response A Symfony Response instance.
     */
    public function sendVerificationEmailAction(Request $request)
    {
        $emailAddress = $request->request->get('emailAddress');

        $people = $this->entityHandler->getBy('Pelagos:Person', array('emailAddress' => $emailAddress));

        if (count($people) === 0) {
            return $this->render('PelagosAppBundle:template:EmailNotFound.html.twig');
        }

        if (count($people) > 1) {
            throw new \Exception("More than one Person found for email address: $emailAddress");
        }

        $person = $people[0];

        // Get personToken
        $personToken = $person->getToken();

        // if $person has Token, remove Token
        if ($personToken instanceof PersonToken) {
            $personToken->getPerson()->setToken(null);
            $this->entityHandler->delete($personToken);
        }

        if ($person->getAccount() instanceof Account) {
            return $this->render('PelagosAppBundle:template:AccountExists.html.twig');
        }

        $dateInterval = new \DateInterval('P7D');

        // Create new personToken
        $personToken = new PersonToken($person, 'CREATE_ACCOUNT', $dateInterval);

        $personToken->setCreator($person);

        // Persist PersonToken
        $personToken = $this->entityHandler->create($personToken);

        $mailData = array(
            'person' => $person,
            'personToken' => $personToken,
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

        return $this->render('PelagosAppBundle:template:EmailFound.html.twig');
    }

    /**
     * The target of the email verification link.
     *
     * This verifies that the token has authenticated the user and that the user does not already have an account.
     * It then provides the user with a screen to establish a password.
     *
     * @Route("/Account/VerifyEmail")
     * @Method("GET")
     *
     * @return Response A Response instance.
     */
    public function verifyEmailAction()
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // The token must be bad or missing.
            return $this->render('PelagosAppBundle:template:InvalidToken.html.twig');
        }

        // If a password has been set.
        if ($this->getUser()->getPassword() !== null) {
            // The user already has an account.
            return $this->render('PelagosAppBundle:template:AccountExists.html.twig');
        }

        // Send back the set password screen.
        return $this->render(
            'PelagosAppBundle:template:setPassword.html.twig',
            array(
                'personToken' => $this->getUser()->getPerson()->getToken(),
            )
        );
    }

    /**
     * Post handler to create an account.
     *
     * @param Request $request The Symfony Request object.
     *
     * @throws \Exception When password do not match.
     *
     * @Route("/Account/Create")
     * @Method("POST")
     *
     * @return Response A Symfony Response instance.
     */
    public function createAction(Request $request)
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // The token must be bad or missing.
            return $this->render('PelagosAppBundle:template:InvalidToken.html.twig');
        }

        // If a password has been set.
        if ($this->getUser()->getPassword() !== null) {
            // The user already has an account.
            return $this->render('PelagosAppBundle:template:AccountExists.html.twig');
        }

        // If the supplied passwords don't match.
        if ($request->request->get('password') !== $request->request->get('verify_password')) {
            // Throw an exception.
            throw new \Exception('Passwords do not match!');
        }

        // Get the authenticated Person.
        $person = $this->getUser()->getPerson();

        // Generate a unique User ID for this account.
        $userId = UserIdFactory::generateUniqueUserId($person, $this->entityHandler);

        // Create a new account.
        $account = new Account($person, $userId, $request->request->get('password'));

        // Set the creator.
        $account->setCreator($userId);

        // Save the account.
        $account = $this->entityHandler->create($account);

        // Delete the person token.
        $this->entityHandler->delete($person->getToken());
        $person->setToken(null);

        return $this->render(
            'PelagosAppBundle:template:AccountCreated.html.twig',
            array(
                'Account' => $account,
            )
        );
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
