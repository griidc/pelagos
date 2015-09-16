<?php

namespace Doctrine\DBAL\Event\Listeners;

use Doctrine\DBAL\Events;
use Doctrine\Common\EventSubscriber;

/**
 * Should be used when Postgres Server default environment does not match the Doctrine requirements.
 */
class PostgresSessionInit implements EventSubscriber
{
    /**
     * An associative array of session variables as keys and values to set each to.
     *
     * @var array
     */
    protected $sessionVars = array();

    /**
     * Constructor that stores session variable array.
     *
     * @param array $sessionVars An associative array of session variables as keys and values to set each to.
     */
    public function __construct(array $sessionVars = array())
    {
        $this->sessionVars = array_merge($this->sessionVars, $sessionVars);
    }

    /**
     * Method for the postConnect action.
     *
     * @param \Doctrine\DBAL\Event\ConnectionEventArgs $args The connection event arguments.
     *
     * @return void
     */
    public function postConnect(\Doctrine\DBAL\Event\ConnectionEventArgs $args)
    {
        if (count($this->sessionVars)) {
            foreach ($this->sessionVars as $var => $value) {
                $args->getConnection()->exec("SET SESSION $var TO '$value'");
            }
        }
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array An array of events this subscriber wants to listen to.
     */
    public function getSubscribedEvents()
    {
        // @codingStandardsIgnoreStart
        return array(Events::postConnect);
        // @codingStandardsIgnoreEnd
    }
}
