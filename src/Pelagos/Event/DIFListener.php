<?php
namespace Pelagos\Event;

class DIFListener
{
    protected $twig;
    protected $mailer;

    public function __construct(\Twig_Environment $twig, \Swift_Mailer $mailer)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    public function onSubmitted(DIFEvent $event)
    {
        $dif = $event->getDIF();
        $template = $this->twig->loadTemplate('PelagosAppBundle:DIF:submit.email.twig');
        $mailData['udi'] = 'TESTUDI';
        $mailData['person'] = array('getFirstName' => 'John', 'getLastName' => 'Dough');
        $message = \Swift_Message::newInstance()
            ->setSubject('TEST DIF Submitted')
            ->setFrom('griidc@gomri.org')
            ->setTo('michael.williamson@tamucc.edu')
            ->setBody($template->renderBlock('body_html', $mailData), 'text/html')
            ->addPart($template->renderBlock('body_text', $mailData), 'text/plain');
        $this->mailer->send($message);
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
}

