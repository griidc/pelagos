<?php

namespace App\Controller\UI;

use App\Entity\Account;
use App\Entity\Entity;
use App\Entity\Password;
use App\Entity\Person;
use App\Entity\PersonToken;
use App\Event\EntityEventDispatcher;
use App\Exception\PasswordException;
use App\Handler\EntityHandler;
use App\Repository\PersonRepository;
use App\Util\Factory\UserIdFactory;
use App\Util\Ldap\Ldap;
use App\Util\MailSender;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment as TwigEnvironment;

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
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Boolean value for account_less_strict_password_rules.
     *
     * @var boolean
     */
    protected $passwordRules;

    /**
     * Logger Interface for account related logging.
     *
     * @var LoggerInterface
     */
    protected $accountLogger;

    /**
     * Protected entityManager value instance of EntityManagerInterface.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param boolean            $passwordRules Boolean value for account_less_strict_password_rules.
     *
     * @return void
     */
    public function __construct(EntityHandler $entityHandler, EntityManagerInterface $entityManager, ValidatorInterface $validator, LoggerInterface $accountLogger, bool $passwordRules)
    {
        $this->entityHandler = $entityHandler;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->passwordRules = $passwordRules;
        $this->accountLogger = $accountLogger;
    }

    /**
     * The index action.
     *
     *
     * @return Response
     */
    #[Route(path: '/account', methods: ['GET'], name: 'pelagos_app_ui_account_default')]
    public function index()
    {
        return $this->render('Account/Account.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    /**
     * Password Reset action.
     *
     *
     * @return Response A Symfony Response instance.
     */
    #[Route(path: '/account/reset-password', name: 'pelagos_app_ui_account_passwordreset')]
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
     *
     * @return Response A Symfony Response instance.
     */
    #[Route(path: '/account', methods: ['POST'], name: 'pelagos_app_ui_account_sendverificationemail')]
    public function sendVerificationEmail(Request $request, MailSender $mailer, PersonRepository $personRepository, TwigEnvironment $twigEnvironment)
    {
        $emailAddress = $request->request->get('emailAddress') ?? null;
        $reset = ($request->request->get('reset') == 'reset') ? true : false;

        $people = $personRepository->findBy(['emailAddress' => $emailAddress]);

        $this->accountLogger->info('Account request for ' . (string) $emailAddress, ['reset' => $reset, 'people_found' => count($people)]);

        if (count($people) === 0) {
            $this->accountLogger->info('No person found for email address during password reset attempt for: ' . (string) $emailAddress);
            return $this->render('Account/EmailFound.html.twig', array(
                'reset' => $reset,
            ));
        }

        if (count($people) > 1) {
            throw new \Exception("More than one Person found for email address: $emailAddress");
        }

        $person = $people[0];

        $this->accountLogger->info('Account request matched to person ID ' . $person->getFullName(), ['personId' => $person->getId()]);

        // Get personToken
        $personToken = $person->getToken();

        // if $person has Token, remove Token
        if ($personToken instanceof PersonToken) {
            $personToken->getPerson()->setToken(null);
            $this->entityHandler->delete($personToken);
        }

        $hasAccount = $person->getAccount() instanceof Account;

        $this->accountLogger->info('Person has account: ' . ($hasAccount ? 'yes' : 'no'), ['personId' => $person->getId()]);

        if ($hasAccount and !$reset) {
            return $this->render('Account/AccountExists.html.twig');
        } elseif (!$hasAccount and $reset) {
            return $this->render('Account/NoAccount.html.twig');
        }

        $dateInterval = new \DateInterval('P7D');

        if ($reset === true) {
            // Create new personToken
            $personToken = new PersonToken($person, 'PASSWORD_RESET', $dateInterval);
            // Load email template
            $template = $twigEnvironment->load('Account/PasswordReset.email.twig');
        } else {
            // Create new personToken
            $personToken = new PersonToken($person, 'CREATE_ACCOUNT', $dateInterval);
            // Load email template
            $template = $twigEnvironment->load('Account/AccountConfirmation.email.twig');
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

        $this->accountLogger->info('Account email sent to ' . (string) $emailAddress, ['reset' => $reset, 'personId' => $person->getId()]);

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
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/account/verify-email', methods: ['GET'], name: 'pelagos_app_ui_account_verifyemail')]
    public function verifyEmailAction()
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // The token must be bad or missing.
            return $this->render('template/InvalidToken.html.twig');
        }

        $reset = false;

        /** @var Account $account */
        $account = $this->getUser();
        $person = $account->getPerson();

        if ($person->getToken() instanceof PersonToken) {
            $reset = ($person->getToken()->getUse() === 'PASSWORD_RESET') ? true : false;
        }

        $this->accountLogger->info('Email verification for person ' . $person->getFullName(), [
            'reset' => $reset,
            'personId' => $person->getId(),
            'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        // If a password has been set.
        if ($account->getPassword() !== null and $reset === false) {
            // The user already has an account.
            return $this->render('Account/AccountExists.html.twig');
        }

        // Send back the set password screen.
        return $this->render(
            'Account/setPassword.html.twig',
            array(
                'personToken' => $person->getToken(),
            )
        );
    }

    /**
     * The page for changing the password when it's expired.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/account/password-expired', methods: ['GET'], name: 'pelagos_app_ui_account_passwordexpired')]
    public function passwordExpiredAction()
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // The token must be bad or missing.
            return $this->render('template/InvalidToken.html.twig');
        }

        /** @var Account $account */
        $account = $this->getUser();
        $person = $account->getPerson();

        $this->accountLogger->info('Password expired change for person ' . $person->getFullName(), ['personId' => $person->getId()]);

        // Send back the set password screen.
        return $this->render(
            'Account/setExpiredPassword.html.twig',
            array(
                'personToken' => $person->getToken(),
            )
        );
    }

    /**
     * Redirect GET sent to this route.
     */
    #[Route(path: '/account/create', methods: ['GET'], name: 'pelagos_app_ui_account_redirect')]
    public function redirectAction(): Response
    {
        return new RedirectResponse('/', 303);
    }

    /**
     * Post handler to create an account.
     *
     * @param Request $request The Symfony Request object.
     * @param Ldap    $ldap    The Ldap Utility.
     *
     * @throws \Exception When password do not match.
     *
     *
     * @return Response A Symfony Response instance.
     */
    #[Route(path: '/account/create', methods: ['POST'], name: 'pelagos_app_ui_account_create')]
    public function createAction(Request $request, Ldap $ldap)
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // The token must be bad or missing.
            return $this->render('template/InvalidToken.html.twig');
        }

        // Get the authenticated Person.
        /** @var Account $account */
        $account = $this->getUser();
        $person = $account->getPerson();

        $reset = false;
        if ($person->getToken() instanceof PersonToken) {
            $reset = ($person->getToken()->getUse() === 'PASSWORD_RESET') ? true : false;
        }

        $this->accountLogger->info('Account creation for person ' . $person->getFullName(), ['reset' => $reset, 'personId' => $person->getId()]);

        // If a password has been set.
        if ($account->getPassword() !== null and $reset === false) {
            // The user already has an account.
            return $this->render('Account/AccountExists.html.twig');
        }

        // If the supplied passwords don't match.
        if ($request->request->get('password') !== $request->request->get('verify_password')) {
            // Throw an exception.
            throw new \Exception('Passwords do not match!');
        }

        // Create new Password
        $password = new Password((string) $request->request->get('password'));

        // Set the creator for password.
        $password->setCreator($person);

        if ($reset === true) {
            // $account = $person->getAccount();

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

            $ldap->updatePerson($person);
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

            try {
                // Try to add the person to LDAP.
                $ldap->addPerson($person);
            } catch (LdapException $exception) {
                // If that fails, try to update the person in LDAP.
                $ldap->updatePerson($person);
            }
        }

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
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/change-password', methods: ['GET'], name: 'pelagos_app_ui_account_changepassword')]
    public function changePasswordAction()
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // The token must be bad or missing.
            return $this->redirectToRoute('pelagos_app_ui_account_passwordreset');
        }

        $this->accountLogger->info('Change password requested page loaded.', [
            'username' => $this->getUser()->getUsername(),
            'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
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
     *
     * @return Response A Symfony Response instance.
     */
    #[Route(path: '/change-password', methods: ['POST'], name: 'pelagos_app_ui_account_changepasswordpost')]
    public function changePasswordPostAction(Request $request, Ldap $ldap)
    {
        // If the user is not authenticated.
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // User is not logged in, or doesn't have a token.
            $this->accountLogger->info('Password change failed.', [
                'username' => $this->getUser()->getUsername(),
                'reason' => 'Not authenticated on POST.',
                'ipAddress' => $request->getClientIp() ?? 'unknown'
            ]);
            return $this->render('template/NotLoggedIn.html.twig');
        }

        // If the supplied passwords don't match.
        if ($request->request->get('password') !== $request->request->get('verify_password')) {
            $this->accountLogger->info('Password change failed.', [
                'username' => $this->getUser()->getUsername(),
                'reason' => 'Passwords do not match.',
                'ipAddress' => $request->getClientIp() ?? 'unknown'
            ]);
            // Throw an exception.
            throw new \Exception('Passwords do not match!');
        }

        // Get the authenticated Person.
        /** @var Account $user */
        $user = $this->getUser();
        $person = $user->getPerson();

        // Get their Account
        $account = $person->getAccount();

        // Create a new Password Entity.
        $password = new Password((string) $request->request->get('password'));

        // Set the creator for password.
        $password->setCreator($person);

        // Attach the password to the account.
        try {
            $account?->setPassword(
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

        $this->entityHandler->update($account);

        // Update LDAP
        try {
            // Try to add the person to LDAP, incase it needs to re-create.
            $ldap->addPerson($person);
        } catch (\Exception $exception) {
            // If that fails, try to update the person in LDAP.
            $ldap->updatePerson($person);
        }

        $this->accountLogger->info('Password successfully changed.', ['username' => $this->getUser()->getUsername(), 'ipAddress' => $request->getClientIp() ?? 'unknown']);
        return $this->render('Account/AccountReset.html.twig');
    }

    /**
     * Forgot username for users.
     *
     * @param Request               $request               The Symfony request object.
     * @param EntityEventDispatcher $entityEventDispatcher The Entity Event Dispatcher.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/forgot-username', methods: ['GET'], name: 'pelagos_app_ui_account_forgotusername')]
    public function forgotUsernameAction(Request $request, EntityEventDispatcher $entityEventDispatcher, PersonRepository $personRepository)
    {

        // If the user is already authenticated.
        if ($this->isGranted('ROLE_USER')) {
            /** @var Account $account */
            $account = $this->getUser();

            $this->accountLogger->info('The "forgot username" lookup was attempted by logged in user:' . $account->getUsername() . '.');
            return $this->render('template/AlreadyLoggedIn.html.twig');
        }

        $userEmailAddr = $request->query->get('emailAddress') ?? null;

        $person = $personRepository->findOneBy(['emailAddress' => $userEmailAddr]);

        $usernameFound = null;
        if ($person instanceof Person) {
            $entityEventDispatcher->dispatch(
                $person,
                'forgot_username'
            );
            $usernameFound = $person->getAccount()?->getUsername();
        }

        $this->accountLogger->info('Username lookup made using email ' . ($userEmailAddr ?? '') . ' returning ' . ($usernameFound ?? 'not found'));
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
     *
     * @return RedirectResponse A Redirect Response.
     */
    #[Route(path: '/password', methods: ['GET'], name: 'pelagos_app_ui_account_password')]
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
