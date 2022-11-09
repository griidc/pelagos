<?php

namespace App\Util;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment as TwigEnvironment;
use Twig\TemplateWrapper;

/**
 * A utility to send e-mails from templates.
 */
class MailSender
{
    /**
     * The twig templating engine instance.
     *
     * @var TwigEnvironment
     */
    protected $twig;

    /**
     * The Symfony Mailer instance.
     *
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * A string holding from email/name information.
     *
     * @var string
     */
    protected $fromEmailAddr;

    /**
     * Bcc email address to send all emails from the system.
     *
     * @var string
     */
    protected $bccAddress;

    /**
     * This is the class constructor to handle dependency injections.
     *
     * @param MailerInterface $mailer      Symfony mailer interface.
     * @param TwigEnvironment $twig        Twig engine.
     * @param string          $fromAddress Sender's email address.
     * @param string          $fromName    Sender's name to include in email.
     * @param string          $bccAddress  BCC Email address.
     */
    public function __construct(
        MailerInterface $mailer,
        TwigEnvironment $twig,
        string $fromAddress,
        string $fromName,
        string $bccAddress
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->fromEmailAddr = new Address($fromAddress, $fromName);
        $this->bccAddress = $bccAddress;
    }

    /**
     * Method to build and send an email.
     *
     * @param TemplateWrapper $emailTwigTemplate A twig template.
     * @param array           $mailData          Mail data array for email.
     * @param array           $toAddresses       Recipient's email addresses.
     *
     * @return void
     */
    public function sendEmailMessage(
        TemplateWrapper $emailTwigTemplate,
        array $mailData,
        array $toAddresses = array(),
    ) {
        $email = (new Email())
            ->from($this->fromEmailAddr)
            ->to(...$toAddresses)
            ->bcc($this->bccAddress)
            ->subject($emailTwigTemplate->renderBlock('subject', $mailData))
            ->text($emailTwigTemplate->renderBlock('body_text', $mailData))
            ->html($emailTwigTemplate->renderBlock('body_html', $mailData));

        $this->mailer->send($email);
    }
}
