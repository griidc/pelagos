<?php

namespace App\Controller\UI;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Account;
use App\Entity\Entity;
use App\Entity\Password;
use App\Entity\Person;
use App\Entity\PersonToken;
use App\Exception\PasswordException;
use App\Exception\UidNumberInUseInLDAPException;
use App\Event\EntityEventDispatcher;
use App\Handler\EntityHandler;
use App\Util\Factory\UserIdFactory;
use App\Util\Ldap\Ldap;
use App\Util\MailSender;

/**
 * The account controller.
 */
class AccountController extends AbstractController
{
    /**
     * Protected entityHandler value instance of entityHandler.
     *
     * @var entityHandler
     */
    protected $entityHandler;

    /**
     * Protected validator value instance of Symfony Validator.
     *
     * @var validator
     */
    protected $validator;

    /**
     * Boolean value for account_less_strict_password_rules.
     *
     * @var boolean
     */
    protected $passwordRules;

    /**
     * The index action.
     *
     * @Route("/account", methods={"GET"}, name="pelagos_app_ui_account_default")
     *
     * @return Response
     */
    public function index()
    {
        return $this->render('Account/Account.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    /**
     * Password Reset action.
     *
     * @Route("/account/reset-password", name="pelagos_app_ui_account_passwordreset")
     *
     * @return Response A Symfony Response instance.
     */
    public function passwordReset()
    {
        // If the user is authenticated.
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // Redirect to change password.
            return $this->redirectToRoute('pelagos_app_ui_account_changepassword');
        }
        return $this->render('Account/PasswordReset.html.twig');
    }

    /**
     * Post handler to verify the email address by sending a link with a Person Token.
     *
     * @param Request    $request The Symfony Request object.
     * @param MailSender $mailer  The custom swift mailer utility class.
     *
     * @throws \Exception When more than one Person is found for an email address.
     *
     * @Route("/account", methods={"POST"}, name="pelagos_app_ui_account_sendverificationemail")
     *
     * @return Response A Symfony Response instance.
     */
    public function sendVerificationEmail(Request $request, MailSender $mailer)
    {
        $emailAddress = $request->request->get('emailAddress');
        $reset = ($request->request->get('reset') == 'reset') ? true : false;

        $people = $this->entityHandler->getBy(Person::class, array('emailAddress' => $emailAddress));

        if (count($people) === 0) {
            return $this->render('Account/EmailNotFound.html.twig');
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

        $hasAccount = $person->getAccount() instanceof Account;

        if ($hasAccount and !$reset) {
            return $this->render('Account/AccountExists.html.twig');
        } elseif (!$hasAccount and $reset) {
            return $this->render('Account/NoAccount.html.twig');
        }

        $dateInterval = new \DateInterval('P7D');

        // Get TWIG instance
        $twig = $this->get('twig');

        if ($reset === true) {
            // Create new personToken
            $personToken = new PersonToken($person, 'PASSWORD_RESET', $dateInterval);
            // Load email template
            $template = $twig->load('Account/PasswordReset.email.twig');
        } else {
            // Create new personToken
            $personToken = new PersonToken($person, 'CREATE_ACCOUNT', $dateInterval);
            // Load email template
            $template = $twig->load('Account/AccountConfirmation.email.twig');
        }

        // Persist and Validate PersonToken
        $this->validateEntity($personToken);
        $personToken = $this->entityHandler->create($personToken);

        $mailData = array(
            'person' => $person,
            'personToken' => $personToken,
        );

        $mailData['recipient'] = $person;
        $mailer->sendEmailMessage(
            $template,
            $mailData,
            array(new Address($person->getEmailAddress(), $person->getFirstName() . ' ' . $person->getLastName()))
        );

        return $this->render(
            'Account/EmailFound.html.twig',
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
     * @Route("/account/verify-email", methods={"GET"}, name="pelagos_app_ui_account_verifyemail")
     *
     * @return Response A Response instance.
     */
    public function verifyEmailAction()
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // The token must be bad or missing.
            return $this->render('template/InvalidToken.html.twig');
        }

        $reset = false;
        if ($this->getUser()->getPerson()->getToken() instanceof PersonToken) {
            $reset = ($this->getUser()->getPerson()->getToken()->getUse() === 'PASSWORD_RESET') ? true : false;
        }

        // If a password has been set.
        if ($this->getUser()->getPassword() !== null and $reset === false) {
            // The user already has an account.
            return $this->render('Account/AccountExists.html.twig');
        }

        // Send back the set password screen.
        return $this->render(
            'Account/setPassword.html.twig',
            array(
                'personToken' => $this->getUser()->getPerson()->getToken(),
            )
        );
    }

    /**
     * The page for changing the password when it's expired.
     *
     * @Route("/account/password-expired", methods={"GET"}, name="pelagos_app_ui_account_passwordexpired")
     *
     * @return Response A Response instance.
     */
    public function passwordExpiredAction()
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // The token must be bad or missing.
            return $this->render('template/InvalidToken.html.twig');
        }

        // Send back the set password screen.
        return $this->render(
            'Account/setExpiredPassword.html.twig',
            array(
                'personToken' => $this->getUser()->getPerson()->getToken(),
            )
        );
    }

    /**
     * Redirect GET sent to this route.
     *
     * @Route("/account/create", methods={"GET"}, name="pelagos_app_ui_account_redirect")
     *
     * @return Response A Symfony Response instance.
     */
    public function redirectAction()
    {
        $redirectResponse = new RedirectResponse('/', 303);
        return $redirectResponse;
    }

    /**
     * Post handler to create an account.
     *
     * @param Request $request The Symfony Request object.
     * @param Ldap    $ldap    The Ldap Utility.
     *
     * @throws \Exception When password do not match.
     *
     * @Route("/account/create", methods={"POST"}, name="pelagos_app_ui_account_create")
     *
     * @return Response A Symfony Response instance.
     */
    public function createAction(Request $request, Ldap $ldap, LoggerInterface $logger)
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // The token must be bad or missing.
            return $this->render('template/InvalidToken.html.twig');
        }

        $reset = false;
        if ($this->getUser()->getPerson()->getToken() instanceof PersonToken) {
            $reset = ($this->getUser()->getPerson()->getToken()->getUse() === 'PASSWORD_RESET') ? true : false;
        }

        // If a password has been set.
        if ($this->getUser()->getPassword() !== null and $reset === false) {
            // The user already has an account.
            return $this->render('Account/AccountExists.html.twig');
        }

        // If the supplied passwords don't match.
        if ($request->request->get('password') !== $request->request->get('verify_password')) {
            // Throw an exception.
            throw new \Exception('Passwords do not match!');
        }

        // Get the authenticated Person.
        $person = $this->getUser()->getPerson();

        // Create new Password
        $password = new Password($request->request->get('password'));

        // Set the creator for password.
        $password->setCreator($person);

        if ($reset === true) {
            $account = $person->getAccount();

            try {
                $account->setPassword(
                    $password,
                    $this->passwordRules
                );
            } catch (PasswordException $e) {
                return $this->render(
                    'template/ErrorMessage.html.twig',
                    array('errormessage' => $e->getMessage())
                );
            }

            // Validate the entities.
            $this->validateEntity($password);
            $this->validateEntity($account);

            // Persist Account
            $account = $this->entityHandler->update($account);

            try {
                $ldap->updatePerson($person);
            } catch (exception $e) {
                $logger->error('LDAP error: ' . $e->getMessage());
            }
        } else {
            // Generate a unique User ID for this account.
            $userId = UserIdFactory::generateUniqueUserId($person, $this->entityHandler);

            // Create a new account.
            try {
                $account = new Account($person, $userId, $password);
            } catch (PasswordException $e) {
                return $this->render(
                    'template/ErrorMessage.html.twig',
                    array('errormessage' => $e->getMessage())
                );
            }

            // Validate the entities.
            $this->validateEntity($password);
            $this->validateEntity($account);

            // Persist Account
            $account = $this->entityHandler->create($account);

        // Delete the person token.
        $this->entityHandler->delete($person->getToken());
        $person->setToken(null);

        if ($reset === true) {
            return $this->render('Account/AccountReset.html.twig');
        } else {
            return $this->render(
                'Account/AccountCreated.html.twig',
                array(
                    'Account' => $account,
                )
            );
        }
    }

    /**
     * The action to change your password, will show password change dialog.
     *
     * @Route("/change-password", methods={"GET"}, name="pelagos_app_ui_account_changepassword")
     *
     * @return Response A Response instance.
     */
    public function changePasswordAction()
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // The token must be bad or missing.
            return $this->redirectToRoute('pelagos_app_ui_account_passwordreset');
        }

        // Send back the set password screen.
        return $this->render('Account/changePassword.html.twig');
    }

    /**
     * Post handler to change password.
     *
     * @param Request $request The Symfony Request object.
     * @param Ldap    $ldap    The Ldap Utility.
     *
     * @throws \Exception When password do not match.
     *
     * @Route("/change-password", methods={"POST"}, name="pelagos_app_ui_account_changepasswordpost")
     *
     * @return Response A Symfony Response instance.
     */
    public function changePasswordPostAction(Request $request, Ldap $ldap, LoggerInterface $logger)
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // User is not logged in, or doesn't have a token.
            return $this->render('template/NotLoggedIn.html.twig');
        }

        // If the supplied passwords don't match.
        if ($request->request->get('password') !== $request->request->get('verify_password')) {
            // Throw an exception.
            throw new \Exception('Passwords do not match!');
        }

        // Get the authenticated Person.
        $person = $this->getUser()->getPerson();

        // Get their Account
        $account = $person->getAccount();

        // Create a new Password Entity.
        $password = new Password($request->request->get('password'));

        // Set the creator for password.
        $password->setCreator($person);

        // Attach the password to the account.
        try {
            $account->setPassword(
                $password,
                $this->passwordRules
            );
        } catch (PasswordException $e) {
            return $this->render(
                'template/ErrorMessage.html.twig',
                array('errormessage' => $e->getMessage())
            );
        }

        // Validate both Password and Account
        $this->validateEntity($password);
        $this->validateEntity($account);

        $account = $this->entityHandler->update($account);

        // Update LDAP
        try {
            // Try to add the person to LDAP, incase it needs to re-create.
            $ldap->addPerson($person);
        } catch (UidNumberInUseInLDAPException $exception) {
            // If that fails, try to update the person in LDAP.
            $ldap->updatePerson($person);
        } catch (UidNumberInUseInLDAPException $exception) {
            $logger->error('LDAP Error: ' . $exception->getMessage());
        }

        return $this->render('Account/AccountReset.html.twig');
    }

    /**
     * Forgot username for users.
     *
     * @param Request               $request               The Symfony request object.
     * @param EntityEventDispatcher $entityEventDispatcher The Entity Event Dispatcher.
     *
     * @Route("/account/forgot-username", methods={"GET"}, name="pelagos_app_ui_account_forgotusername")
     *
     * @return Response A Response instance.
     */
    public function forgotUsernameAction(Request $request, EntityEventDispatcher $entityEventDispatcher)
    {
        // If the user is already authenticated.
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('template/AlreadyLoggedIn.html.twig');
        }

        $userEmailAddr = $request->query->get('emailAddress');

        if ($userEmailAddr) {
            $person = $this->entityHandler->getBy(Person::class, array('emailAddress' => $userEmailAddr));

            if (!empty($person[0])) {
                $entityEventDispatcher->dispatch(
                    $person[0]->getAccount(),
                    'forgot_username'
                );
            }
        }

        return $this->render(
            'Account/forgotUsername.html.twig',
            array(
                'emailId' => $userEmailAddr
            )
        );
    }

    /**
     * The action will redirect you to the correct password page.
     *
     * Either change password if logged in, otherwise reset password.
     *
     * @Route("/password", methods={"GET"}, name="pelagos_app_ui_account_password"))
     *
     * @return RedirectResponse A Redirect Response.
     */
    public function passwordAction()
    {
        // If the user is not authenticated.
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('pelagos_app_ui_account_changepassword');
        } else {
            return $this->redirectToRoute('pelagos_app_ui_account_passwordreset');
        }
    }

    /**
     * Validates the Entity prior to persisting it.
     *
     * @param Entity $entity The Entity (and it's extentions).
     *
     * @throws BadRequestHttpException When invalid data is submitted.
     *
     * @return void
     */
    public function validateEntity(Entity $entity)
    {
        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            throw new BadRequestHttpException(
                (string) $errors
            );
        }
    }
}
