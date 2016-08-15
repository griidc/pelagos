<?php

namespace Pelagos\Event;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;
use Pelagos\Entity\Account;

/**
 * Listener class for Account-related events.
 */
class AccountListener extends EventListener
{
    /**
     * Method to send an email to DMs on an Account created event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onCreated(EntityEvent $event)
    {
        $account = $this->getAccount($event);

        // email Data Managers
        $template = $this->twig->loadTemplate('@Email/Account/data-managers.account-created.email.twig');
        $this->sendMailMsg($template, array('account' => $account), $this->getDMsFromAccount($account));
    }

    /**
     * Internal method to resolve Data Managers from an Account.
     *
     * @param Account $account An Account entity.
     *
     * @return Array of Persons who are Data Managers for the Research Group tied back to the Account.
     */
    protected function getDMsFromAccount(Account $account)
    {
        $recepientPeople = array();
        $researchGroups = $account->getPerson()->getResearchGroups();

        foreach ($researchGroups as $rg) {
            $prgs = $rg->getPersonResearchGroups();
            foreach ($prgs as $prg) {
                if ($prg->getRole()->getName() == ResearchGroupRoles::DATA) {
                    $recepientPeople[] = $prg->getPerson();
                }
            }
        }
        return $recepientPeople;
    }

    /**
     * Internal method to get an Account object from an Event.
     *
     * @param EntityEvent $event The event listener is for.
     *
     * @throws \Exception If event passed is not an instance of Account (bad usage).
     *
     * @return Account An Account object associated with the event being listened on.
     */
    protected function getAccount(EntityEvent $event)
    {
        $account = $event->getEntity();
        if (Account::class != get_class($account)) {
            throw new \Exception('Internal error: handler expects an Account');
        }
        return $account;
    }
}
