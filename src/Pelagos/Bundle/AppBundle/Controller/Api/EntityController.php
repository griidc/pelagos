<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\FormInterface;

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
     * Get all entities of a given type.
     *
     * @param string  $entityClass The type of entity.
     * @param Request $request     The request object.
     *
     * @return array
     */
    public function handleGetCollection($entityClass, Request $request)
    {
        $params = $request->query->all();
        if (array_key_exists('q', $params)) {
            // Remove the 'q' parameter if it exists (this comes from Drupal).
            unset($params['q']);
        }
        if (count($params) > 0) {
            return $this->container->get('pelagos.entity.handler')->getBy($entityClass, $params);
        }
        return $this->container->get('pelagos.entity.handler')->getAll($entityClass);
    }

    /**
     * Get a single entity of a given type identified by $id.
     *
     * @param string  $entityClass The type of entity.
     * @param integer $id          The id of the entity.
     *
     * @throws BadRequestHttpException When the provided id is not a non-negative integer.
     * @throws NotFoundHttpException   When an entity of a given type identified by $id is not found.
     *
     * @return Entity
     */
    public function handleGetOne($entityClass, $id)
    {
        if (!preg_match('/^\d+$/', $id)) {
            throw new BadRequestHttpException('id must be a non-negative integer');
        }
        $entity = $this
            ->container
            ->get('pelagos.entity.handler')
            ->get($entityClass, $id);
        if ($entity === null) {
            throw $this->createNotFoundException('No ' . $entityClass::FRIENDLY_NAME . " exists with id: $id");
        }
        return $entity;
    }

    /**
     * Create an entity from the submitted data.
     *
     * @param string  $formType    The type of form.
     * @param string  $entityClass The type of entity.
     * @param Request $request     The request object.
     *
     * @return Entity|FormInterface
     */
    public function handlePost($formType, $entityClass, Request $request)
    {
        $entity = new $entityClass;
        $user = $this->getUser();
        $creator = 'anonymous';
        if ($user instanceof Account) {
            $creator = $user->getUsername();
        }
        $entity->setCreator($creator);
        try {
            $this->processForm($formType, $entity, $request, 'POST');
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
        $this->container->get('pelagos.entity.handler')->create($entity);
        return $entity;
    }

    /**
     * Update an entity from the submitted data.
     *
     * @param string  $formType The type of form.
     * @param Entity  $entity   The entity to update.
     * @param Request $request  The request object.
     * @param string  $method   The HTTP method (PUT or PATCH).
     *
     * @return Entity|FormInterface
     */
    public function handleUpdate($formType, Entity $entity, Request $request, $method)
    {
        $user = $this->getUser();
        $modifier = 'anonymous';
        if ($user instanceof Account) {
            $modifier = $user->getUsername();
        }
        $entity->setModifier($modifier);
        try {
            $this->processForm($formType, $entity, $request, $method);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
        $this->container->get('pelagos.entity.handler')->update($entity);
        return $entity;
    }

    /**
     * Processes the form.
     *
     * @param string  $formType The type of form to process.
     * @param Entity  $entity   The entity to populate.
     * @param Request $request  The request object.
     * @param string  $method   The HTTP method.
     *
     * @throws BadRequestHttpException When no valid parameters are passed.
     * @throws InvalidFormException    When invalid data is submitted.
     *
     * @return Entity The updated entity.
     */
    private function processForm($formType, Entity $entity, Request $request, $method = 'PUT')
    {
        $form = $this->get('form.factory')->createNamed(null, $formType, $entity, array('method' => $method));
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            throw new BadRequestHttpException(
                'You did not pass any valid parameters for a ' . $entity::FRIENDLY_NAME . '.'
            );
        }
        if (!$form->isValid()) {
            throw new InvalidFormException('Invalid submitted data', $form);
        }
        foreach ($request->files->all() as $property => $file) {
            if (isset($file)) {
                $setter = 'set' . ucfirst($property);
                $entity->$setter(file_get_contents($file->getPathname()));
            }
        }
        return $entity;
    }

    /**
     * Validate a value for a property of an entity.
     *
     * This method will validate key/value pairs found in the query string against
     * properties of the given entity type.
     *
     * @param string  $formType    The class of the type of form to use for validation.
     * @param string  $entityClass The class of the type of entity to validate against.
     * @param Request $request     The request object.
     *
     * @access public
     *
     * @throws BadRequestHttpException When no property is supplied.
     * @throws BadRequestHttpException When more than one property is supplied.
     * @throws BadRequestHttpException The supplied property is not valid for the resource.
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validateProperty($formType, $entityClass, Request $request)
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
