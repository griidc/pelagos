<?php
namespace Pelagos\Event;

use Pelagos\Entity\Account;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class DIFListener
{
    protected $twig;
    protected $mailer;
    protected $currentUser;
    protected $tokenStorage;

    public function __construct(\Twig_Environment $twig, \Swift_Mailer $mailer, TokenStorage $tokenStorage)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
    }

    public function onSubmitted(DIFEvent $event)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser()->getPerson(); // getUser() is Account's method.

        // email DRPM(s)
        $drpms = $this->getDRPMs($event->getDIF());
        $template = $this->twig->loadTemplate('PelagosAppBundle:DIF:submit.drpm.email.twig');
        $this->sendMailMsg($drpms, $template, 'R9.x999.999.9999', 'DIF Submitted');

        // email User
        $template = $this->twig->loadTemplate('PelagosAppBundle:DIF:submit.email.twig');
        $this->sendMailMsg(array($currentUser), $template, 'R9.x999.999.9999', 'DIF Submitted');

    }
    public function onApproved(DIFEvent $event)
    {
        $dif = $event->getDIF();
    }
    public function onRejected(DIFEvent $event)
    {
        $dif = $event->getDIF();
    }
    public function onUnlocked(DIFEvent $event)
    {
        $dif = $event->getDIF();
    }

    protected function sendMailMsg($peopleObjs, $twigTemplate, $udi, $subject)
    {
        $mailData['udi'] = $udi;
        foreach ($peopleObjs as $person) {
            $mailData['person'] = $person;
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom('griidc@gomri.org')
                ->setTo($person->getEmailAddress())
                ->setBody($twigTemplate->renderBlock('body_html', $mailData), 'text/html')
                ->addPart($twigTemplate->renderBlock('body_text', $mailData), 'text/plain');
            $this->mailer->send($message);
        }
    }

    private function getDRPMs($dif)
    {
        $recepientPeople = array();
        foreach($dif->getDataset()->getDataRepository()->getPersonDataRepositories() as $pdr) {
            if ($pdr->getRole()->getName() == 'ROLE_DATA_REPOSITORY_MANAGER') {
                $recepientPeople[] = $pdr->getPerson();
            }
        }
        return $recepientPeople;
    }
}

