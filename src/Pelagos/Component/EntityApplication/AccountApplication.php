<?php

namespace Pelagos\Component\EntityApplication;

/**
 * Class for the person application class.
 */
class AccountApplication extends \Pelagos\Component\EntityApplication
{
    public function __construct(\Slim\Slim $slim)
    {
        parent::__construct($slim);
    }

    public function handleEntity($entityType)
    {
        $this->setTitle('Account Creation');
        $this->slim->render('Account.html');
    }
}