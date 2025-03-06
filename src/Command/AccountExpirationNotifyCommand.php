<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use App\Util\MailSender;
use App\Entity\Account;

/**
 * Notify holders of accounts with soon to expire or expired passwords.
 *
 * @see Command
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:account-expiration-notify', description: 'Notify holder of accounts with soon to expire or expired passwords.')]
class AccountExpirationNotifyCommand extends Command
{
    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * A from array suitable for SwiftMailer.
     *
     * @var array
     */
    protected $from;

    /**
     * ISO 8601 interval representing the maximum allowable password age.
     *
     * @var string
     */
    protected $maximumPasswordAge;

    /**
     * Number of days for password expiration warning.
     *
     * @var string
     */
    protected $passwordExpiryWarn;

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Hostname of the current server.
     *
     * @var string
     */
    protected $hostName;

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
     * @param string                 $maximumPasswordAge ISO 8601 interval representing the maximum allowable password age.
     * @param string                 $passwordExpiryWarn Number of days for password expiry warn to start.
     * @param EntityManagerInterface $entityManager      An instance of entity manager.
     * @param string                 $hostName           Hostname of the current server.
     * @param MailSender             $mailer             Custom swiftmailer instance.
     * @param Environment            $twig               Twig environment variable.
     */
    public function __construct(
        string $maximumPasswordAge,
        string $passwordExpiryWarn,
        EntityManagerInterface $entityManager,
        string $hostName,
        MailSender $mailer,
        Environment $twig
    ) {
        $this->maximumPasswordAge = $maximumPasswordAge;
        $this->passwordExpiryWarn = $passwordExpiryWarn;
        $this->entityManager = $entityManager;
        $this->hostName = $hostName;
        $this->mailer = $mailer;
        $this->twig = $twig;
        parent::__construct();
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // This command takes no input.
        unset($input);

        // Save output object for use in other methods.
        $this->output = $output;

        // Create a time stamp for the maximum password age.
        $maxAgeTimeStamp = new \DateTime('now');
        $maxAgeTimeStamp->sub(new \DateInterval($this->maximumPasswordAge));

        // Create a time stamp for one day before the maximum password age.
        $minTimeStamp = clone $maxAgeTimeStamp;
        $minTimeStamp->sub(new \DateInterval('P1D'));

        $output->writeln('Notifying account holders of expired passwords' .
            ' (last changed ' . $this->maximumPasswordAge . ' ago' .
            ', between ' . $minTimeStamp->format('c') . ' and ' . $maxAgeTimeStamp->format('c') . ')');

        // Get expired accounts.
        $expiredAccounts = $this->getAccounts($minTimeStamp, $maxAgeTimeStamp);

        // Notify holders of expired accounts.
        $this->notifyAccountHolders(
            $expiredAccounts,
            $this->twig->load(
                'Account/NotifyExpired.email.twig'
            )
        );

        // For each expiration warning interval.
        foreach (explode(',', $this->passwordExpiryWarn) as $warnInterval) {
            // Create a time stamp $warnInterval after $maxAgeTimeStamp.
            $warnTimeStamp = clone $maxAgeTimeStamp;
            $warnTimeStamp->add(new \DateInterval($warnInterval));

            // Create a time stamp for one day before the warning time stamp.
            $minTimeStamp = clone $warnTimeStamp;
            $minTimeStamp->sub(new \DateInterval('P1D'));

            $output->writeln('Notifying account holders of passwords to expire in ' . $warnInterval .
                ' (last changed between ' . $minTimeStamp->format('c') . ' and ' . $warnTimeStamp->format('c') . ')');

            // Get soon to expire accounts.
            $soonToExpireAccounts = $this->getAccounts($minTimeStamp, $warnTimeStamp);

            // Notify holders of soon to expire accounts.
            $this->notifyAccountHolders(
                $soonToExpireAccounts,
                $this->twig->load(
                    'Account/NotifyExpiringSoon.email.twig'
                )
            );
        }

        return Command::SUCCESS;
    }

    /**
     * Get accounts with passwords last modified between $minTimeStamp and $maxTimeStamp.
     *
     * @param \DateTime $minTimeStamp The minimum time stamp for password modification.
     * @param \DateTime $maxTimeStamp The maximum time stamp for password modification.
     *
     * @return array Array of Accounts with passwords last modified between $minTimeStamp and $maxTimeStamp.
     */
    protected function getAccounts(\DateTime $minTimeStamp, \DateTime $maxTimeStamp)
    {
        // Create a query builder.
        $queryBuilder = $this->entityManager->createQueryBuilder();

        // Build a query to get all accounts with passwords last modified between $minTimeStamp and $maxTimeStamp.
        $queryBuilder
            ->select('account')
            ->from(Account::class, 'account')
            ->join('account.password', 'password')
            ->where(
                $queryBuilder->expr()->between(
                    'password.modificationTimeStamp',
                    '?1',
                    '?2'
                )
            )
            ->setParameter(1, $minTimeStamp)
            ->setParameter(2, $maxTimeStamp);
        // Return the query result.
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Notify account holders.
     *
     * @param array                 $accounts      Array of accounts to notify holders.
     * @param \Twig\TemplateWrapper $emailTemplate Twig email template to use for notification.
     *
     * @return void
     */
    protected function notifyAccountHolders(array $accounts, \Twig\TemplateWrapper $emailTemplate)
    {
        foreach ($accounts as $account) {
            // Get the Person this Account belongs to.
            $person = $account->getPerson();

            // Create a time stamp for when the password has expired or will expire.
            $expireTimeStamp = $account->getPasswordEntity()->getModificationTimeStamp();
            $expireTimeStamp->add(new \DateInterval($this->maximumPasswordAge));
            $expireTimeStamp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

            $this->output->write('  ' . $account->getUserId());
            $this->output->write(' (' . $person->getFirstName() . ' ' . $person->getLastName() . ')');
            $this->output->writeln(' expiration: ' . $expireTimeStamp->format('c'));

            // Construct an array for twig containing data needed for rendering the email template.
            $twigData = ['person' => $person, 'hostname' => $this->hostName, 'expireTimeStamp' => $expireTimeStamp];

            $mailData['recipient'] = $person;

            $this->mailer->sendEmailMessage(
                $emailTemplate,
                $twigData,
                [new Address($person->getEmailAddress(), $person->getFirstName() . ' ' . $person->getLastName())]
            );
        }
    }
}
