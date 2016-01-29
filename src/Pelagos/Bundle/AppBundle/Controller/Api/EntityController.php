<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\FormTypeInterface;

use FOS\RestBundle\Controller\FOSRestController;

use Pelagos\Bundle\AppBundle\Exception\InvalidFormException;

use Pelagos\Entity\Entity;
use Pelagos\Entity\Account;

/**
 * The Entity api controller.
 */
abstract class EntityController extends FOSRestController
{
    /**
     * The type of entity this controller is for.
     *
     * @var string
     */
    private $entityType;

    /**
     * Constructor that sets $entityType based on the class name.
     */
    public function __construct()
    {
        preg_match('/([^\\\\]+)Controller$/', get_class($this), $matches);
        $this->entityType = $matches[1];
    }

    /**
     * Get all entities of a given type.
     *
     * @return array
     */
    public function cgetAction()
    {
        $entities = $this
            ->container
            ->get('pelagos.entity.handler')
            ->getAll($this->entityType);
        return $entities;
    }

    /**
     * Get a single entity of a given type identified by $id.
     *
     * @param integer $id The id of the entity to return.
     *
     * @throws BadRequestHttpException When the provided id is not a non-negative integer.
     * @throws NotFoundHttpException   When an entity of a given type identified by $id is not found.
     *
     * @return Entity
     */
    public function getAction($id)
    {
        if (!preg_match('/^\d+$/', $id)) {
            throw new BadRequestHttpException('id must be a non-negative integer');
        }
        $entity = $this
            ->container
            ->get('pelagos.entity.handler')
            ->get($this->entityType, $id);
        if ($entity === null) {
            throw $this->createNotFoundException('No ' . $this->entityType . " exists with id: $id");
        }
        return $entity;
    }

    /**
     * Create an entity from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @return Entity|FormTypeInterface
     */
    public function postAction(Request $request)
    {
        $entityClass = '\Pelagos\Entity\\' . $this->entityType;
        $entity = new $entityClass;
        $user = $this->getUser();
        $creator = 'anonymous';
        if ($user instanceof Account) {
            $creator = $user->getUsername();
        }
        $entity->setCreator($creator);
        try {
            $this
                ->container
                ->get('pelagos.entity.handler')
                ->post(
                    'Pelagos\Bundle\AppBundle\Form\\' . $this->entityType . 'Type',
                    $entity,
                    $request
                );
            return $entity;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Presents the form to use to create a new entity.
     *
     * @return FormTypeInterface
     */
    public function newAction()
    {
        return $this->container->get('form.factory')->createNamed(
            null,
            'Pelagos\Bundle\AppBundle\Form\\' . $this->entityType . 'Type',
            null,
            array(
                'action' => $this->generateUrl(
                    'pelagos_api_post_' . ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $this->entityType)), '_')
                ),
                'method' => 'POST',
            )
        );
    }

    /**
     * Validate a value for a property of an entity.
     *
     * This method will validate key/value pairs found in the query string against
     * properties of the given entity type.
     *
     * @param Request $request     The request object.
     * @param string  $entityClass The class of the type of entity to validate against.
     * @param string  $formType    The class of the type of form to use for validation.
     *
     * @access public
     *
     * @throws BadRequestHttpException When no property is supplied.
     * @throws BadRequestHttpException When more than one property is supplied.
     * @throws BadRequestHttpException The supplied property is not valid for the resource.
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validateProperty(Request $request, $entityClass, $formType)
    {
        // Get all the parameters from the query string.
        $params = $request->query->all();
        if (array_key_exists('q', $params)) {
            // Remove the 'q' parameter if it exists (this comes from Drupal).
            unset($params['q']);
        }
        if (count($params) == 0) {
            throw new BadRequestHttpException('Property to be validated not supplied.');
        }
        if (count($params) > 1) {
            throw new BadRequestHttpException('Only one property can be validated at a time.');
        }
        // Grab the property name from the parameter array.
        $property = array_keys($params)[0];
        // Instantiate a new entity.
        $entity = new $entityClass;
        // Create a form with this entity.
        $form = $this->get('form.factory')->createNamed(null, $formType, $entity, array('method' => 'GET'));
        // Process the request against the form.
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            // If the form does not contain the given property, it will not submit.
            throw new BadRequestHttpException("$property is not a valid property for this resource.");
        }
        $errors = $form->get($property)->getErrors();
        if ($errors->count() == 0) {
            // If there are no errors, return true.
            return true;
        }
        $errorMessages = array();
        foreach ($errors as $error) {
            // Get each error message.
            $errorMessages[] = $error->getMessage();
        }
        // Return the list of error messages.
        return implode(', ', $errorMessages);
    }
}
