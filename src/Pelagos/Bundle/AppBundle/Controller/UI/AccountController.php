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
     * @Route("/")
     * @Method("GET")
     *
     * @return Response A Symfony Response instance.
     */
    public function defaultAction()
    {
        return $this->render('PelagosAppBundle:template:Account.html.twig');
    }

    /**
     * Password Reset action.
     *
     * @Route("/password-reset")
     * @Method("GET")
     *
     * @return Response A Symfony Response instance.
     */
    public function passwordResetAction()
    {
        return $this->render('PelagosAppBundle:template:PasswordReset.html.twig');
    }

    /**
     * Post handler to verify the email address by sending a link with a Person Token.
     *
     * @param Request $request The Symfony Request object.
     *
     * @throws \Exception When more than one Person is found for an email address.
     *
     * @Route("/")
     * @Method("POST")
     *
     * @return Response A Symfony Response instance.
     */
    public function sendVerificationEmailAction(Request $request)
    {
        $emailAddress = $request->request->get('emailAddress');
        $reset = ($request->request->get('reset') == 'reset') ? true : false;

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

        if ($person->getAccount() instanceof Account and !$reset) {
            return $this->render('PelagosAppBundle:template:AccountExists.html.twig');
        }

        $dateInterval = new \DateInterval('P7D');

        // Get TWIG instance
        $twig = $this->get('twig');

        if ($reset === true) {
            // Create new personToken
            $personToken = new PersonToken($person, 'PASSWORD_RESET', $dateInterval);
            // Load email template
            $template = $twig->loadTemplate('PelagosAppBundle:template:PasswordReset.email.html.twig');
        } else {
            // Create new personToken
            $personToken = new PersonToken($person, 'CREATE_ACCOUNT', $dateInterval);
            // Load email template
            $template = $twig->loadTemplate('PelagosAppBundle:template:AccountConfirmation.email.html.twig');
        }

        $user = $this->getUser();
        // If user is authenticated.
        if ($user instanceof Account) {
            // Get the authenticated person.
            $creator = $user->getPerson();
        } else {
            // Get the anonymous person.
            $creator = $this->entityHandler->get('Pelagos:Person', -1);
        }
        $personToken->setCreator($creator);

        // Persist PersonToken
        $personToken = $this->entityHandler->create($personToken);

        $mailData = array(
            'person' => $person,
            'personToken' => $personToken,
        );

        // Create a message
        $message = \Swift_Message::newInstance()
            ->setFrom(array('griidc@gomri.org' => 'GRIIDC'))
            ->setTo(array($person->getEmailAddress() => $person->getFirstName() . ' ' . $person->getLastName()))
            ->setSubject($template->renderBlock('subject', $mailData))
            ->setBody($template->renderBlock('body_text', $mailData), 'text/plain')
            ->addPart($template->renderBlock('body_html', $mailData), 'text/html');

        // Send the message
        $this->get('mailer')->send($message);

        return $this->render(
            'PelagosAppBundle:template:EmailFound.html.twig',
            array(
                'reset' => $reset,
            )
        );
    }

    /**
     * The target of the email verification link.
     *
     * This verifies that the token has authenticated the user and that the user does not already have an account.
     * It then provides the user with a screen to establish a password.
     *
     * @Route("/verify-email")
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

        $reset = false;
        if ($this->getUser()->getPerson()->getToken() instanceof PersonToken) {
            $reset = ($this->getUser()->getPerson()->getToken()->getUse() === 'PASSWORD_RESET') ? true : false;
        }

        // If a password has been set.
        if ($this->getUser()->getPassword() !== null and $reset === false) {
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
     * @Route("/create")
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

        $reset = false;
        if ($this->getUser()->getPerson()->getToken() instanceof PersonToken) {
            $reset = ($this->getUser()->getPerson()->getToken()->getUse() === 'PASSWORD_RESET') ? true : false;
        }

        // If a password has been set.
        if ($this->getUser()->getPassword() !== null and $reset === false) {
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

        if ($reset === true) {
            $account = $person->getAccount();
            $account->setPassword($request->request->get('password'));

        } else {
            // Generate a unique User ID for this account.
            $userId = UserIdFactory::generateUniqueUserId($person, $this->entityHandler);

            // Create a new account.
            $account = new Account($person, $userId, $request->request->get('password'));

            // Set the creator.
            $account->setCreator($person);

            // Save the account.
            $account = $this->entityHandler->create($account);
        }

        // Delete the person token.
        $this->entityHandler->delete($person->getToken());
        $person->setToken(null);

        if ($reset === true) {
            return $this->render('PelagosAppBundle:template:AccountReset.html.twig');
        } else {
            return $this->render(
                'PelagosAppBundle:template:AccountCreated.html.twig',
                array(
                    'Account' => $account,
                )
            );
        }
    }
}
