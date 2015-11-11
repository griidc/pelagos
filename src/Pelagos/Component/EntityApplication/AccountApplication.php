<?php

namespace Pelagos\Component\EntityApplication;

use \Pelagos\Entity\Entity;
use \Pelagos\Service\EntityService;

/**
 * Class for the person application class.
 */
class AccountApplication extends \Pelagos\Component\EntityApplication
{
    /**
     * The instance of \Slim\Slim used by this application service.
     *
     * @var \Twig $twig
     *
     * @access private
     */
    private $twig;

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

        $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem('./templates'));

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
     */
    public function handleEntity($entityType)
    {
        $this->setTitle('Account Creation');
        $this->slim->render('Account.html');
    }

    /**
     * Function to handle entities and id or value.
     *
     * @param string  $entityType The type of entity to handle (account).
     * @param string $entityId   The hash verification value of the account.
     *
     * @access public
     */
    public function handleEntityInstance($entityType, $entityId)
    {
        $this->setTitle('Account Creation');
        // $entityService = new EntityService($this->getEntityManager());
        // $entity = $entityService->getBy('PersonToken', array('tokenText' => $entityId));

        // $goodToken = false;

        // foreach ($entity as $PersonToken) {
            // var_dump($PersonToken);

            // $goodToken = true;
        // }

        $goodToken = true;

        $twigData = array(
            "goodToken" => $goodToken,
            "token" => $entityId,
        );

        $this->slim->render('AccountApprovalResponse.html', $twigData);
    }

    /**
     * Function the post for e-mail verification, and token emailing.
     *
     * @param string  $entityType The type of entity (account).
     *
     * @access public
     */
    public function handlePost($entityType)
    {
        $postValues = $this->slim->request->params();

        var_dump($postValues);

        if (array_key_exists('action', $postValues)) {
            if ($postValues['action'] === 'accountrequest') {
                $this->verifyEmail($postValues['emailAddress']);
            }
            if ($postValues['action'] === 'establishaccount') {
                $this->setCredentials($postValues);
            }
        }
    }

    private function setCredentials($formData)
    {
        $entityService = new EntityService($this->getEntityManager());

        $personId = $formData['person'];


        $entity = $entityService->getBy('Person', array('id' => $personId));

    }

    private function verifyEmail($emailAddress)
    {
        $this->setTitle('Account Request Result');

        $entityService = new EntityService($this->getEntityManager());

        $entity = $entityService->getBy('Person', array('emailAddress' => $emailAddress));

        $knownEmail = false;

        foreach ($entity as $Person) {
            /*
            // Get PersonToken
            $PersonToken = $Person->getToken();

            // if $Person has Token, remove Token
            if ($PersonToken) {
                try {
                        $this->getEntityService()->delete($PersonToken);
                    } catch (\Exception $e) {
                        echo 'An error has occured: ' . $e->getMessage();
                    $this->quit();
                }
            }

            // Create new PersonToken
            $PersonToken = new \Pelagos\PersonToken($Person);

            // Persist PersonToken
            $PersonToken->setPerson($Person);
            $PersonToken = $this->entityService->persist($PersonToken);

            */

            $mailData = array(
                'Person' => $Person,
            );

            $template = $this->twig->loadTemplate('accountConfirmation.email.html.twig');

            $email = array(
                'toEmail'  => $Person->getEmailAddress(),
                'toName'   => $Person->getFirstName() . ' ' . $Person->getLastName(),
                'subject'  => $template->renderBlock('subject', $mailData),
                'bodyHTML' => $template->renderBlock('body_html', $mailData),
                'bodyText' => $template->renderBlock('body_text', $mailData),
            );

            //$this->sendMail($email);

            $knownEmail = true;
        }

        $twigData = array(
            "knownEmail" => $knownEmail,
            "emailAddress" => $emailAddress,
        );

        $this->slim->render('AccountRequestResponse.html', $twigData);
    }

    /**
     * A swift mailer function to send e-mail.
     *
     * @param $email Array An array of parameters used to send e-mail.
     *
     * @access private
     */
    private function sendMail($email)
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
            ->addPart($email['bodyHTML'], 'text/html')
            ;

        // Send the message
        return $mailer->send($message);
    }
}
