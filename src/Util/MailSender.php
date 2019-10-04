<?php

namespace App\Util;

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
     * @var NamedAddress
     */
    protected $from;
    
    /**
     * This is the class constructor to handle dependency injections.
     *
     * @param \Swift_Mailer      $mailer        Symfony Mailer.
     * @param \Twig_Environment  $twig          Twig engine.
     * @param string             $fromAddress   Sender's email address.
     * @param string             $fromName      Sender's name to include in email.
     */
    public function __construct(
        \Swift_Mailer  $mailer,
        \Twig_Environment $twig,
        string $fromAddress,
        string $fromName
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->from = array($fromAddress => $fromName);
    }
    
    /**
     * Method to build and send an email.
     *
     * @param \Twig_Template $twigTemplate A twig template.
     * @param array          $mailData     Mail data array for email.
     * @param string|null    $peopleObjs   An optional recipient.
     * @param array          $attachments  An optional array of Swift_Message_Attachments to attach.
     *
     * @throws \InvalidArgumentException When any element of $attachments is not a Swift_Message_Attachment.
     *
     * @return void
     */
    public function sendEmailMessage(
        \Twig_Template $emailTwigTemplate,
        array $mailData,
        string $toAddresses  = null,
        array $attachments = array()
    ) {
         $message = new \Swift_Message();
         $message
            ->setSubject($emailTwigTemplate->renderBlock('subject', $mailData))
            ->setFrom($this->from)
            ->setTo($toAddresses)
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
