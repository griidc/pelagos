<?php

namespace Pelagos\Component\EntityApplication;

use \Pelagos\Entity\Entity;
use \Pelagos\Service\EntityService;

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
        $this->addJS(
            array(
                'static/js/account.js',
            )
        );

        $this->setTitle('Account Creation');

        $this->slim->render('Account.html');
    }

    public function handlePost($entityType)
    {
        $this->setTitle('Account Creation Result');

        $postValues = $this->slim->request->params();

        $entityService = new EntityService($this->getEntityManager());

        $entity = $entityService->getBy('Person', $this->slim->request->params());

        if ($entity) {
            // Instantiate Token Here
        }

        $twigData = array(
            "entity" => $entity
        );

        $this->slim->render('AccountRequestResponse.html', $twigData);
    }
}