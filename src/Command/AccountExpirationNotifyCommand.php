<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Account;

/**
 * Notify holders of accounts with soon to expire or expired passwords.
 *
 * @see ContainerAwareCommand
 */
class AccountExpirationNotifyCommand extends ContainerAwareCommand
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
    protected $maxPasswordAge;

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('account:expiration-notify')
            ->setDescription('Notify holder of accounts with soon to expire or expired passwords.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This command takes no input.
        unset($input);

        // Save output object for use in other methods.
        $this->output = $output;

        // Construct a from array suitable for SwiftMailer.
        $this->from = array(
            $this->getContainer()->getParameter('mailer_from_addr') =>
            $this->getContainer()->getParameter('mailer_from_name')
        );

        // Get the password max age parameter.
        $this->maxPasswordAge = $this->getContainer()->getParameter('account_password_max_age');

        // Create a time stamp for the maximum password age.
        $maxAgeTimeStamp = new \DateTime('now');
        $maxAgeTimeStamp->sub(new \DateInterval($this->maxPasswordAge));

        // Create a time stamp for one day before the maximum password age.
        $minTimeStamp = clone $maxAgeTimeStamp;
        $minTimeStamp->sub(new \DateInterval('P1D'));

        $output->writeln('Notifying account holders of expired passwords' .
            ' (last changed ' . $this->maxPasswordAge . ' ago' .
            ', between ' . $minTimeStamp->format('c') . ' and ' . $maxAgeTimeStamp->format('c') . ')');

        // Get expired accounts.
        $expiredAccounts = $this->getAccounts($minTimeStamp, $maxAgeTimeStamp);

        // Notify holders of expired accounts.
        $this->notifyAccountHolders(
            $expiredAccounts,
            $this->getContainer()->get('twig')->loadTemplate(
                'PelagosAppBundle:Account:NotifyExpired.email.twig'
            )
        );

        // For each expiration warning interval.
        foreach ($this->getContainer()->getParameter('account_password_expiration_warn') as $warnInterval) {
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
                $this->getContainer()->get('twig')->loadTemplate(
                    'PelagosAppBundle:Account:NotifyExpiringSoon.email.twig'
                )
            );
        }

        // Return success.
        return 0;
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
        $queryBuilder = $this->getContainer()->get('doctrine.orm.entity_manager')->createQueryBuilder();

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
     * @param array          $accounts      Array of accounts to notify holders.
     * @param \Twig_Template $emailTemplate Twig email template to use for notification.
     *
     * @return void
     */
    protected function notifyAccountHolders(array $accounts, \Twig_Template $emailTemplate)
    {
        foreach ($accounts as $account) {
            // Get the Person this Account belongs to.
            $person = $account->getPerson();

            // Create a time stamp for when the password has expired or will expire.
            $expireTimeStamp = $account->getPasswordEntity()->getModificationTimeStamp();
            $expireTimeStamp->add(new \DateInterval($this->maxPasswordAge));
            $expireTimeStamp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

            $this->output->write('  ' . $account->getUserId());
            $this->output->write(' (' . $person->getFirstName() . ' ' . $person->getLastName() . ')');
            $this->output->writeln(' expiration: ' . $expireTimeStamp->format('c'));

            // Construct an array for twig containing data needed for rendering the email template.
            $twigData = array(
                'person' => $person,
                'hostname' => $this->getContainer()->getParameter('hostname'),
                'expireTimeStamp' => $expireTimeStamp,
            );

            // Create a new SwiftMailer message using the email template.
            $message = \Swift_Message::newInstance()
                ->setFrom($this->from)
                ->setTo(
                    array(
                        $person->getEmailAddress() => $person->getFirstName() . ' ' . $person->getLastName(),
                    )
                )
                ->setSubject(
                    $emailTemplate->renderBlock(
                        'subject',
                        array()
                    )
                )
                ->setBody(
                    $emailTemplate->renderBlock(
                        'body_text',
                        $twigData
                    ),
                    'text/plain'
                )
                ->addPart(
                    $emailTemplate->renderBlock(
                        'body_html',
                        $twigData
                    ),
                    'text/html'
                );

            // Send the message.
            $this->getContainer()->get('mailer')->send($message);
        }
    }
}
