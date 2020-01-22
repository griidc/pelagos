<?php

namespace App\Util;

use Swift_Mailer;
use Twig\Environment;

/**
 * A utility to send e-mails from templates.
 */
class MailSender
{
    /**
     * The twig templating engine instance.
     *
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * The Symfony Mailer instance.
     *
     * @var Mailer
     */
    protected $mailer;

    /**
     * A NamedAddress holding email from name/email information.
     *
     * @var string
     */
    protected $from;

    /**
     * Bcc email address to send all emails from the system.
     *
     * @var string
     */
    protected $bccAddress;
    
    /**
     * This is the class constructor to handle dependency injections.
     *
     * @param \Swift_Mailer     $mailer      Symfony Mailer.
     * @param \Twig_Environment $twig        Twig engine.
     * @param string            $fromAddress Sender's email address.
     * @param string            $fromName    Sender's name to include in email.
     * @param string            $bccAddress  BCC Email address.
     */
    public function __construct(
        Swift_Mailer  $mailer,
        Environment $twig,
        string $fromAddress,
        string $fromName,
        string $bccAddress
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->from = array($fromAddress => $fromName);
        $this->bccAddress = $bccAddress;
    }

    /**
     * Method to build and send an email.
     *
     * @param \Twig\TemplateWrapper $emailTwigTemplate A twig template.
     * @param array                 $mailData          Mail data array for email.
     * @param array                 $toAddresses       Recipient's email addresses.
     * @param array                 $attachments       An optional array of Swift_Message_Attachments to attach.
     *
     * @throws \InvalidArgumentException When any element of $attachments is not a Swift_Message_Attachment.
     *
     * @return void
     */
    public function sendEmailMessage(
        \Twig\TemplateWrapper $emailTwigTemplate,
        array $mailData,
        array $toAddresses = array(),
        array $attachments = array()
    ) {
         $message = new \Swift_Message();
         $message
            ->setSubject($emailTwigTemplate->renderBlock('subject', $mailData))
            ->setFrom($this->from)
            ->setTo($toAddresses)
            ->setBcc($this->bccAddress)
            ->setBody($emailTwigTemplate->renderBlock('body_html', $mailData), 'text/html')
            ->addPart($emailTwigTemplate->renderBlock('body_text', $mailData), 'text/plain');
        foreach ($attachments as $attachment) {
            if (!$attachment instanceof \Swift_Attachment) {
                throw new \InvalidArgumentException('Attachment is not an instance of Swift_Attachment');
            }
            $message->attach($attachment);
        }

        $this->mailer->send($message);
    }
}
