<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\FormInterface;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;

use Pelagos\Entity\Entity;
use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Exception\NotDeletableException;
use Pelagos\Exception\UnmappedPropertyException;

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
        if (array_key_exists('_permission', $params)) {
            $permission = $params['_permission'];
            unset($params['_permission']);
        }
        if (count($params) > 0) {
            $entities = $this->container->get('pelagos.entity.handler')->getBy($entityClass, $params);
        } else {
            $entities = $this->container->get('pelagos.entity.handler')->getAll($entityClass);
        }
        if (isset($permission)) {
            $authorizedEntities = array();
            foreach ($entities as $entity) {
                if ($this->isGranted($permission, $entity)) {
                    $authorizedEntities[] = $entity;
                }
            }
            $entities = $authorizedEntities;
        }
        return $entities;
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
     * @param Entity  $entity      An optional entity to use instead of creating a new one.
     *
     * @return Entity The newly created entity.
     */
    public function handlePost($formType, $entityClass, Request $request, Entity $entity = null)
    {
        if (null === $entity) {
            $entity = new $entityClass;
        }
        $this->processForm($formType, $entity, $request, 'POST');
        $this->container->get('pelagos.entity.handler')->create($entity);
        return $entity;
    }

    /**
     * Update an entity from the submitted data.
     *
     * @param string  $formType    The type of form.
     * @param string  $entityClass The type of entity.
     * @param integer $id          The id of the entity.
     * @param Request $request     The request object.
     * @param string  $method      The HTTP method (PUT or PATCH).
     *
     * @return Entity The updated entity.
     */
    public function handleUpdate($formType, $entityClass, $id, Request $request, $method)
    {
        $entity = $this->handleGetOne($entityClass, $id);
        $this->processForm($formType, $entity, $request, $method);
        $this->container->get('pelagos.entity.handler')->update($entity);
        return $entity;
    }

    /**
     * Delete an entity of a given type identified by $id.
     *
     * @param string  $entityClass The type of entity.
     * @param integer $id          The id of the entity.
     *
     * @throws BadRequestHttpException When the entity is not deletable.
     *
     * @return Entity The deleted entity.
     */
    public function handleDelete($entityClass, $id)
    {
        $entity = $this->handleGetOne($entityClass, $id);
        try {
            $this->container->get('pelagos.entity.handler')->delete($entity);
        } catch (NotDeletableException $exception) {
            throw new BadRequestHttpException(
                'This ' . $entity::FRIENDLY_NAME . ' is not deletable because ' .
                implode(', ', $exception->getReasons()) . '.'
            );
        }
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
     * @throws BadRequestHttpException When invalid data is submitted.
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
            throw new BadRequestHttpException(
                (string) $form->getErrors(true, true)
            );
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
     * @param string       $formType    The class of the type of form to use for validation.
     * @param string       $entityClass The class of the type of entity to validate against.
     * @param Request      $request     The request object.
     * @param integer|null $id          The id of the entity to validate against.
     *
     * @access public
     *
     * @throws BadRequestHttpException When no property is supplied.
     * @throws BadRequestHttpException When more than one property is supplied.
     * @throws BadRequestHttpException The supplied property is not valid for the resource.
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validateProperty($formType, $entityClass, Request $request, $id = null)
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
        // If we don't have an ID.
        if ($id === null) {
            // Instantiate a new entity.
            $entity = new $entityClass;
        } else {
            // Get the entity.
            $entity = $this->handleGetOne($entityClass, $id);
        }
        // Create a form with this entity.
        $form = $this->get('form.factory')->createNamed(null, $formType, $entity, array('method' => 'GET'));
        // Process the request against the form.
        $form->submit($params, false);
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

    /**
     * Get a property of an entity.
     *
     * @param string  $entityClass The type of entity.
     * @param integer $id          The id of the entity.
     * @param string  $property    The property to retrieve.
     *
     * @return Response A Response object containing the property or an empty body
     *                  and a "no content" status code if the property is not set.
     */
    public function getProperty($entityClass, $id, $property)
    {
        $entity = $this->handleGetOne($entityClass, $id);
        $getter = 'get' . ucfirst($property);
        $content = $entity->$getter();
        if ($content === null) {
            return new Response(null, Codes::HTTP_NO_CONTENT);
        }
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($info, $content);
        finfo_close($info);
        return new Response(
            $content,
            Codes::HTTP_OK,
            array(
                'Content-Type' => $mimeType,
                'Content-Length' => strlen($content),
            )
        );
    }

    /**
     * Set or replace a property of an entity via multipart/form-data POST.
     *
     * @param string  $entityClass The type of entity.
     * @param integer $id          The id of the entity.
     * @param string  $property    The property to set.
     * @param Request $request     The request object.
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function postProperty($entityClass, $id, $property, Request $request)
    {
        $entity = $this->handleGetOne($entityClass, $id);
        $file = $request->files->get($property);
        if (isset($file)) {
            $setter = 'set' . ucfirst($property);
            $entity->$setter(file_get_contents($file->getPathname()));
        }
        $this->container->get('pelagos.entity.handler')->update($entity);
        return new Response(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Set or replace a property of an entity via HTTP PUT file upload.
     *
     * @param string  $entityClass The type of entity.
     * @param integer $id          The id of the entity.
     * @param string  $property    The property to set.
     * @param Request $request     The request object.
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putProperty($entityClass, $id, $property, Request $request)
    {
        $entity = $this->handleGetOne($entityClass, $id);
        $setter = 'set' . ucfirst($property);
        $entity->$setter($request->getContent());
        $this->container->get('pelagos.entity.handler')->update($entity);
        return new Response(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Retrieves the list of distinct values for $property of $entityClass.
     *
     * @param string $entityClass The type of entity.
     * @param string $property    The property to request distinct values for.
     *
     * @throws BadRequestHttpException When $property is not a valid property for $entityClass.
     *
     * @return array The list of distinct values for $property of $entityClass.
     */
    public function getDistinctVals($entityClass, $property)
    {
        try {
            return $this->container->get('pelagos.entity.handler')->getDistinctVals($entityClass, $property);
        } catch (UnmappedPropertyException $e) {
            throw new BadRequestHttpException(
                "$property is not a valid property of " . $entityClass::FRIENDLY_NAME . '.'
            );
        }
    }

    /**
     * Creates and returns a Response object that indicates successful creation of a new resource.
     *
     * @param string  $locationRouteName The name of the route to put in the Location header.
     * @param integer $resourceId        The id of the newly created resource.
     * @param array   $additionalHeaders Array of additional headers to add to the response.
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Person to Research Group Association in the Location header.
     */
    protected function makeCreatedResponse($locationRouteName, $resourceId, array $additionalHeaders = array())
    {
        return new Response(
            null,
            Codes::HTTP_CREATED,
            array_merge(
                array(
                    'Content-Type' => 'application/x-empty',
                    'Location' => $this->generateUrl(
                        $locationRouteName,
                        ['id' => $resourceId]
                    ),
                    'X-Resource-Id' => $resourceId,
                ),
                $additionalHeaders
            )
        );
    }

    /**
     * Creates and returns a Response object with no content.
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    protected function makeNoContentResponse()
    {
        return new Response(
            null,
            Codes::HTTP_NO_CONTENT,
            array(
                'Content-Type' => 'application/x-empty',
            )
        );
    }
}
