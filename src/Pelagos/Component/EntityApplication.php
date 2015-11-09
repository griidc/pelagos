<?php

namespace Pelagos\Component;

use \Pelagos\Entity\Entity;
use \Pelagos\Service\EntityService;
use \Pelagos\Exception\ArgumentException;
use \Pelagos\Exception\RecordNotFoundPersistenceException;

/**
 * Class for the entity application.
 */
class EntityApplication extends \Pelagos\Component
{
    /**
     * The instance of \Slim\Slim used by this application service.
     *
     * @var \Slim\Slim $slim
     *
     * @access protected
     */
    protected $slim;

    /**
     * Constructor for EntityApplicationService.
     *
     * @param \Slim\Slim $slim The instance of \Slim\Slim used by this application service.
     *
     * @access public
     */
    public function __construct(\Slim\Slim $slim)
    {
        // Call constructor for \Pelagos\Component
        parent::__construct();
        // Save the Slim instance
        $this->slim = $slim;

        $this->setTitle('Entity');

        $this->setJSGlobals();

        $this->addJS(
            array(
                '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',
                '//cdnjs.cloudflare.com/ajax/libs/jquery-noty/2.3.5/packaged/jquery.noty.packaged.min.js',
                '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js',
                '//cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.min.js',
                '/static/js/common.js',
                'static/js/entityForm.js',
            )
        );

        $this->addCSS(
            array(
                '//cdnjs.cloudflare.com/ajax/libs/animate.css/3.3.0/animate.min.css',
                '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css',
                'static/css/entity.css',
            )
        );

        $this->addLibrary(
            array(
                'ui.datepicker',
                'ui.dialog',
                'ui.tabs',
                'ui.widget',
                'ui.autocomplete',
            )
        );

        /* I loaded all the default stuff */
    }

    public function handleEntityInstance($entityType, $entityId)
    {

        $this->addJS(
            array(
                'static/js/entity.js',
            )
        );


        if (preg_match_all('/([A-Z][a-z]*)/', $entityType, $entityName)) {
            $this->setTitle(implode(' ', $entityName[1]) . ' Landing Page');
        }

        if (file_exists("static/js/$entityType" . '.js')) {
            $this->addJS("static/js/$entityType" . '.js');
        }

        $twigData = array(
            'userLoggedIn' => ($this->userIsLoggedIn()) ? 'true' : 'false',
        );
        $entityService = new EntityService($this->getEntityManager());
        $twigData['entityService'] = $entityService;
        if (isset($entityId)) {
            try {
                $entity = $entityService->get($entityType, $entityId);
                $this->slim->response->setStatus(200);
                } catch (ArgumentException $e) {
                $this->slim->response->setStatus(400);
                } catch (RecordNotFoundPersistenceException $e) {
                $this->slim->response->setStatus(404);
                } catch (\Exception $e) {
                $this->slim->response->setStatus(500);
            }
            if ($this->slim->response->getStatus() != 200) {
                $this->slim->render('error.html', array('errorMessage' => $e->getMessage()));
                return;
            }
            $twigData[$entityType] = $entity;
        }
        $this->slim->render($entityType . '.html', $twigData);

    }

    public function handleEntity($entityType)
    {
        $this->addJS(
            array(
                'static/js/entity.js',
            )
        );

        if (preg_match_all('/([A-Z][a-z]*)/', $entityType, $entityName)) {
            $this->setTitle('Create ' . implode(' ', $entityName[1]));
        }

        if (file_exists("static/js/$entityType" . '.js')) {
            $this->addJS("static/js/$entityType" . '.js');
        }

        $twigData = array(
            'userLoggedIn' => ($this->userIsLoggedIn()) ? 'true' : 'false',
        );
        $entityService = new EntityService($this->getEntityManager());
        $twigData['entityService'] = $entityService;

        $this->slim->render($entityType . '.html', $twigData);

    }

    public function handlePost($entityType)
    {
        $this->slim->render('error.html', array('errorMessage' => 'Post Not Allowed!'), 405);
    }
}