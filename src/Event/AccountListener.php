<?php

namespace App\Event;

use App\Entity\Account;
use App\Entity\Person;
use App\Entity\ResearchGroupRole;
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

        // email User
        $this->sendMailMsg(
            $this->twig->load('Email/Account/user.account-created.email.twig'),
            array('account' => $account),
            array($account->getPerson())
        );

        // email Data Managers
        $template = $this->twig->load('Email/Account/data-managers.account-created.email.twig');
        $this->sendMailMsg($template, array('account' => $account), $this->getDMsFromAccount($account));
    }

    /**
     * Internal method to resolve Data Managers from an Account.
     *
     * @param Account $account An Account entity.
     *
     * @return array Array of Persons who are Data Managers for the Research Group tied back to the Account.
     */
    protected function getDMsFromAccount(Account $account)
    {
        $recipientPeople = array();
        $researchGroups = $account->getPerson()->getResearchGroups();

        foreach ($researchGroups as $rg) {
            $prgs = $rg->getPersonResearchGroups();
            foreach ($prgs as $prg) {
                if ($prg->getRole()->getName() == ResearchGroupRole::DATA) {
                    $recipientPeople[] = $prg->getPerson();
                }
            }
        }
        return $recipientPeople;
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

    /**
     * Method to send an email to the user when Forgot Username is requested.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onForgotUsername(EntityEvent $event)
    {
        $person = $event->getEntity();

        if ($person instanceof Person) {
            // email User
            $this->sendMailMsg(
                $this->twig->load('Email/Account/UsernameRetrieval.email.twig'),
                array('person' => $person),
                array($person)
            );
        }
    }
}
