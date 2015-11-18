<?php

namespace Pelagos\Component\EntityApplication;

use \Pelagos\Service\EntityService;

/**
 * Class for the person application class.
 */
class AccountApplication extends \Pelagos\Component\EntityApplication
{
    /**
     * The instance of \Twig used by this application service.
     *
     * @var \Twig $twig
     *
     * @access private
     */
    private $twig;

    /**
     * The template that hold all Account verbiage.
     *
     * @var \Twig_Template $accountTemplate
     *
     * @access private
     */
    private $accountTemplate;

    /**
     * Constructor for AccountApplication.
     *
     * @param \Slim\Slim $slim The instance of \Slim\Slim used by this application service.
     *
     * @access public
     */
    public function __construct(\Slim\Slim $slim)
    {
        parent::__construct($slim);

        $this->setTitle('Account Creation');

        $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem('./templates'));

        $this->accountTemplate = $this->twig->loadTemplate('Account.html.twig');

        $this->addJS(
            array(
                'static/js/account.js',
                '//cdnjs.cloudflare.com/ajax/libs/xregexp/2.0.0/xregexp-all-min.js',
            )
        );
    }

    /**
     * Method to handle Account Creation, returns template/form.
     *
     * @param string $entityType The type of entity to create.
     *
     * @access public
     *
     * @return void
     */
    public function handleEntity($entityType)
    {
        try {
            echo $this->accountTemplate->renderBlock('accountRequest', array('path' => $this->path));
        } catch (\Exception $e) {
            $this->slim->render('error.html', array('errorMessage' => $e->getMessage()));
            return;
        }
    }

    /**
     * Function to handle entities and id or value.
     *
     * @param string $entityType The type of entity to handle (account).
     * @param string $entityId   The hash verification value of the account.
     *
     * @access public
     *
     * @return void
     */
    public function handleEntityInstance($entityType, $entityId)
    {
        try {
            $this->setPassword($entityId);
        } catch (\Pelagos\Exception\AccountExistsException $e) {
            echo $this->accountTemplate->renderBlock('accountExists', array());
            return;
        } catch (\Pelagos\Exception\NotFoundException $e) {
            echo $this->accountTemplate->renderBlock('invalidToken', array());
            return;
        } catch (\Pelagos\Exception\InvalidTokenException $e) {
            echo $this->accountTemplate->renderBlock('expiredToken', array('uri' => $this->uri));
            return;
        } catch (\Exception $e) {
            $this->slim->render('error.html', array('errorMessage' => $e->getMessage()));
            return;
        }
    }

    /**
     * Function the post for e-mail verification, and token emailing.
     *
     * @param string $entityType The type of entity (account).
     * @param string $entityId   The value of the entity (email address).
     *
     * @access public
     *
     * @return void
     */
    public function handlePost($entityType, $entityId)
    {
        $postValues = $this->slim->request->params();

        if (array_key_exists('action', $postValues)) {
            try {
                if ($postValues['action'] === 'accountrequest') {
                    $this->verifyEmail($postValues['emailAddress']);
                }
                if ($postValues['action'] === 'establishaccount') {
                    $this->createAccount($postValues, $entityId);
                }
            } catch (\Pelagos\Exception\AccountExistsException $e) {
                echo $this->accountTemplate->renderBlock('accountExists', array());
                return;
            } catch (\Pelagos\Exception\NotFoundException $e) {
                echo $this->accountTemplate->renderBlock('emailNotFound', array());
                return;
            } catch (\Exception $e) {
                $this->slim->render('error.html', array('errorMessage' => $e->getMessage()));
                return;
            }
        }
    }

    /**
     * Function that checks your email.
     *
     * @param string $emailAddress Email address.
     *
     * @access private
     *
     * @throws \Pelagos\Exception\NotFoundException      When the email is not found.
     * @throws \Pelagos\Exception\AccountExistsException When you already have an account.
     *
     * @return void
     */
    private function verifyEmail($emailAddress)
    {
        $this->setTitle('Account Request Result');

        $entityService = new EntityService($this->getEntityManager());

        $entity = $entityService->getBy('Person', array('emailAddress' => $emailAddress));

        if (count($entity) === 0) {
            throw new \Pelagos\Exception\NotFoundException('e-mail not found!');
        }

        foreach ($entity as $person) {
            // Get personToken
            $personToken = $person->getToken();

            if ($person->getAccount() !== null) {
                throw new \Pelagos\Exception\AccountExistsException('You already have an account!');
            }

            // if $person has Token, remove Token
            if ($personToken instanceof \Pelagos\Entity\personToken) {
                $personToken->getPerson()->setToken(null);
                $entityService->delete($personToken);
            }

            $dateInterval = new \DateInterval('P7D');

            // Create new personToken
            $personToken = new \Pelagos\Entity\PersonToken($person, 'CREATE_ACCOUNT', $dateInterval);

            // Persist PersonToken
            $personToken->setPerson($person);
            $personToken = $entityService->persist($personToken);

            $mailData = array(
                'Person' => $person,
                'PersonToken' => $personToken,
                'uri' => $this->uri,
            );

            $template = $this->twig->loadTemplate('AccountConfirmation.email.html.twig');

            $email = array(
                'toEmail'  => $person->getEmailAddress(),
                'toName'   => $person->getFirstName() . ' ' . $person->getLastName(),
                'subject'  => $template->renderBlock('subject', $mailData),
                'bodyHTML' => $template->renderBlock('body_html', $mailData),
                'bodyText' => $template->renderBlock('body_text', $mailData),
            );

            $this->sendMail($email);
        }

        echo $this->accountTemplate->renderBlock('emailFound', array());
    }

    /**
     * Function the post for e-mail verification, and token emailing.
     *
     * @param string $tokenText The type of entity (account).
     *
     * @access private
     *
     * @throws \Pelagos\Exception\NotFoundException      When the Token is not found.
     * @throws \Pelagos\Exception\InvalidTokenException  When the Token is invalid (expired).
     * @throws \Pelagos\Exception\AccountExistsException When you already have an account.
     *
     * @return void
     */
    private function setPassword($tokenText)
    {
        $entityService = new EntityService($this->getEntityManager());
        $entity = $entityService->getBy('PersonToken', array('tokenText' => $tokenText));

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
            'tokenText' => $tokenText,
            'path' => $this->path,
        );

        echo $this->accountTemplate->renderBlock('setPassword', $twigData);
    }

    /**
     * Function that creates the Account.
     *
     * @param string $formData The form data.
     * @param string $token    The token string.
     *
     * @access private
     *
     * @throws \Pelagos\Exception\NotFoundException      When the Token is not found.
     * @throws \Pelagos\Exception\InvalidTokenException  When the Token is invalid (expired).
     * @throws \Pelagos\Exception\AccountExistsException When you already have an account.
     * @throws \Exception                                When password to not match.
     *
     * @return void
     */
    private function createAccount($formData, $token)
    {
        $entityService = new EntityService($this->getEntityManager());

        $entity = $entityService->getBy('PersonToken', array('tokenText' => $token));

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

            if ($formData['password'] !== $formData['verify_password']) {
                throw new \Exception('Password do not match!');
            }

            $userId = \Pelagos\Factory\UserIdFactory::generateUniqueUserId($person, $entityService);

            $account = new \Pelagos\Entity\Account($person, $userId, $formData['password']);

            $account->setCreator($userId);

            $account = $entityService->persist($account);

            $twigData = array(
                'Account' => $account,
            );

            echo $this->accountTemplate->renderBlock('accountCreated', $twigData);
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
        $message = \Swift_Message::newInstance('Wonderful Subject')
            ->setFrom(array('griidc@gomri.org' => 'GRIIDC'))
            ->setTo(array($email['toEmail'] => $email['toName']))
            ->setSubject($email['subject'])
            ->setBody($email['bodyText'], 'text/plain')
            ->addPart($email['bodyHTML'], 'text/html');

        // Send the message
        return $mailer->send($message);
    }
}
