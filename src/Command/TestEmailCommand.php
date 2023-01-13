<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\Address;
use App\Util\MailSender;
use Twig\Environment;

/**
 * Command to send a test Pelagos email using .env system parameters.
 *
 * @see Command
 */
class TestEmailCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:send-test-email';

    /**
     * Custom swiftmailer instance.
     *
     * @var MailSender
     */
    protected $mailer;

    /**
     * Twig environment instance.
     *
     * @var Environment
     */
    protected $twig;

    /**
     * Class constructor for dependency injection.
     *
     * @param MailSender  $mailer Custom swiftmailer instance.
     * @param Environment $twig   Twig environment instance.
     */
    public function __construct(
        MailSender $mailer,
        Environment $twig,
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Send a test Pelagos email message.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->mailer->sendEmailMessage(
            $this->twig->load('Email/test.email.twig'),
            array(new Address('griidc@gomri.org', 'GRIIDC')),
        );

        return 0;
    }
}
