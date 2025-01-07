<?php

namespace App\Controller\Api;

use Doctrine\ORM\Query;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use App\Handler\EntityHandler;
use App\Entity\Entity;
use App\Exception\NotDeletableException;
use App\Exception\UnmappedPropertyException;
use Doctrine\ORM\Internal\TopologicalSort\CycleDetectedException;

/**
 * The Entity api controller.
 */
abstract class EntityController extends AbstractFOSRestController
{
    /**
     * Entity Handler instance.
     *
     * @var EntityHandler
     */
    protected $entityHandler;

    /**
     * Form factory instance.
     *
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * EntityController constructor.
     *
     * @param EntityHandler        $entityHandler Entity Handler instance.
     * @param FormFactoryInterface $formFactory   Form factory instance.
     */
    public function __construct(EntityHandler $entityHandler, FormFactoryInterface $formFactory)
    {
        $this->entityHandler = $entityHandler;
        $this->formFactory = $formFactory;
    }

    /**
     * Count entities of a given type.
     *
     * @param string  $entityClass The type of entity.
     * @param Request $request     The request object.
     *
     * @return integer
     */
    protected function handleCount(string $entityClass, Request $request)
    {
        $params = $request->query->all();
        if (array_key_exists('q', $params)) {
            // Remove the 'q' parameter if it exists (this comes from Drupal).
            unset($params['q']);
        }
        foreach (array_keys($params) as $param) {
            str_replace('_', '.', $params[$param]);
        }
        return $this->entityHandler->count($entityClass, $params);
    }

    /**
     * Get all entities of a given type.
     *
     * @param string  $entityClass  The type of entity.
     * @param Request $request      The request object.
     * @param array   $subResources A list of properties that are sub-resources and the routes to access them.
     *
     * @return array
     */
    public function handleGetCollection(string $entityClass, Request $request, array $subResources = array())
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
        foreach (array_keys($params) as $param) {
            str_replace('_', '.', $params[$param]);
        }
        $orderBy = $this->getOrderBy($params);
        $properties = $this->getProperties($params);
        $hydrator = Query::HYDRATE_ARRAY;
        if (isset($permission)) {
            $hydrator = Query::HYDRATE_OBJECT;
        }
        $entities = $this->entityHandler->getBy(
            $entityClass,
            $params,
            $orderBy,
            $properties,
            $hydrator,
            distinct: false
        );
        if (isset($permission)) {
            $entities = $this->filterByPermission($entities, $permission);
        }
        if (
            count($subResources) > 0
            and (
                count($properties) === 0
                or count(array_intersect(array_keys($subResources), $properties)) > 0
            )
        ) {
            $this->processSubResources($entities, $subResources, Query::HYDRATE_OBJECT === $hydrator);
        }
        if (Query::HYDRATE_ARRAY === $hydrator) {
            return $this->makeJsonResponse($entities);
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
    public function handleGetOne(string $entityClass, int $id)
    {
        if (!preg_match('/^\d+$/', $id)) {
            throw new BadRequestHttpException('id must be a non-negative integer');
        }
        $entity = $this->entityHandler->get($entityClass, $id);
        if ($entity === null) {
            throw new NotFoundHttpException('No ' . $entityClass::FRIENDLY_NAME . " exists with id: $id");
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
    public function handlePost(string $formType, string $entityClass, Request $request, Entity $entity = null)
    {
        if (null === $entity) {
            $entity = new $entityClass();
        }
        $this->processForm($formType, $entity, $request, 'POST');
        $this->entityHandler->create($entity);
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
    public function handleUpdate(string $formType, string $entityClass, int $id, Request $request, string $method)
    {
        $entity = $this->handleGetOne($entityClass, $id);
        $this->processForm($formType, $entity, $request, $method);
        $this->entityHandler->update($entity);
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
    public function handleDelete(string $entityClass, int $id)
    {
        $entity = $this->handleGetOne($entityClass, $id);
        try {
            $this->entityHandler->delete($entity);
        } catch (NotDeletableException $exception) {

            throw new BadRequestHttpException(
                'This ' . $entity::FRIENDLY_NAME . ' is not deletable because ' .
                implode(', ', $exception->getReasons()) . '.'
            );
        } catch (CycleDetectedException $exception) {
            dump($exception->getCycle());
            throw $exception;
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
    private function processForm(string $formType, Entity $entity, Request $request, string $method = 'PUT')
    {
        $form = $this->formFactory->createNamed('', $formType, $entity, array('method' => $method));
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            throw new BadRequestHttpException(
                'You did not pass any valid parameters for a ' . $entity::FRIENDLY_NAME . '.'
            );
        }
        $validate = ($request->query->get('validate') == 'false') ? false : true;
        if ($validate and !$form->isValid()) {
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
     * @throws BadRequestHttpException When no property is supplied.
     * @throws BadRequestHttpException When more than one property is supplied.
     * @throws BadRequestHttpException The supplied property is not valid for the resource.
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validateProperty(string $formType, string $entityClass, Request $request, int $id = null)
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
            $entity = new $entityClass();
        } else {
            // Get the entity.
            $entity = $this->handleGetOne($entityClass, $id);
        }
        // Create a form with this entity.
        $form = $this->formFactory->createNamed('', $formType, $entity, array('method' => 'GET'));
        try {
            // Process the request against the form.
            $form->submit($params, false);
        } catch (\InvalidArgumentException $e) {
            return $e->getMessage();
        }
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
    public function getProperty(string $entityClass, int $id, string $property)
    {
        $entity = $this->handleGetOne($entityClass, $id);
        $getter = 'get' . ucfirst($property);
        $content = $entity->$getter();
        if ($content === null) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($info, $content);
        finfo_close($info);
        return new Response(
            $content,
            Response::HTTP_OK,
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
    public function postProperty(string $entityClass, int $id, string $property, Request $request)
    {
        $entity = $this->handleGetOne($entityClass, $id);
        $file = $request->files->get($property);
        if (isset($file)) {
            $setter = 'set' . ucfirst($property);
            $entity->$setter(file_get_contents($file->getPathname()));
        }
        $this->entityHandler->update($entity);
        return new Response(null, Response::HTTP_NO_CONTENT);
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
    public function putProperty(string $entityClass, int $id, string $property, Request $request)
    {
        $entity = $this->handleGetOne($entityClass, $id);
        $setter = 'set' . ucfirst($property);
        $entity->$setter($request->getContent());
        $this->entityHandler->update($entity);
        return new Response(null, Response::HTTP_NO_CONTENT);
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
    public function getDistinctVals(string $entityClass, string $property)
    {
        try {
            return $this->entityHandler->getDistinctVals($entityClass, $property);
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
    protected function makeCreatedResponse(string $locationRouteName, int $resourceId, array $additionalHeaders = array())
    {
        return new Response(
            null,
            Response::HTTP_CREATED,
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
            Response::HTTP_NO_CONTENT,
            array(
                'Content-Type' => 'application/x-empty',
            )
        );
    }

    /**
     * Get a resource URL for an Entity.
     *
     * @param string  $routeName The name of the route for the resource.
     * @param integer $id        The id of the Entity.
     *
     * @return string
     */
    protected function getResourceUrl(string $routeName, int $id)
    {
        return $this->generateUrl(
            $routeName,
            array('id' => $id),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * Make a JSON response.
     *
     * @param array $data The data to JSON encode.
     *
     * @return Response
     */
    protected function makeJsonResponse(array $data)
    {
        return new Response(
            json_encode($data),
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/json',
            )
        );
    }

    /**
     * Get an orderBy array from request parameters.
     *
     * @param array $params The request parameters.
     *
     * @return array
     */
    private function getOrderBy(array &$params)
    {
        $orderBy = array();
        if (array_key_exists('_orderBy', $params)) {
            foreach (preg_split('/[,\s]+/', $params['_orderBy']) as $propertyOrder) {
                $property = preg_split('/:/', $propertyOrder);
                $orderBy[$property[0]] = count($property) === 1 ? 'ASC' : $property[1];
            }
            unset($params['_orderBy']);
        }
        return $orderBy;
    }

    /**
     * Get a properties array from request parameters.
     *
     * @param array $params The request parameters.
     *
     * @return array
     */
    private function getProperties(array &$params)
    {
        $properties = array();
        if (array_key_exists('_properties', $params)) {
            $properties = preg_split('/[,\s]+/', $params['_properties']);
            unset($params['_properties']);
        }
        return $properties;
    }

    /**
     * Filter a list of entities by a permission.
     *
     * @param array  $entities   A list of entities.
     * @param string $permission A permission.
     *
     * @return array
     */
    private function filterByPermission(array $entities, string $permission)
    {
        $authorizedEntities = array();
        foreach ($entities as $entity) {
            if ($this->isGranted($permission, $entity)) {
                $authorizedEntities[] = $entity;
            }
        }
        return $authorizedEntities;
    }

    /**
     * Change values for properties in a list of entities that are sub-resources to urls to retrieve them.
     *
     * @param array   $entities     A list of entities.
     * @param array   $subResources A list of properties that are sub-resources and the routes to access them.
     * @param boolean $objects      Whether or not the list of entities contains objects or not.
     *
     * @return void
     */
    private function processSubResources(array &$entities, array $subResources, bool $objects = true)
    {
        if (true === $objects) {
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($entities as $entity) {
                foreach ($subResources as $subResource => $routeName) {
                    if (null !== $accessor->getValue($entity, $subResource)) {
                        $accessor->setValue(
                            $entity,
                            $subResource,
                            $this->getResourceUrl($routeName, $entity->getId())
                        );
                    }
                }
            }
        } else {
            foreach ($entities as $index => $entity) {
                foreach ($subResources as $subResource => $routeName) {
                    if (
                        array_key_exists($subResource, $entities[$index])
                        and null !== $entities[$index][$subResource]
                    ) {
                        $entities[$index][$subResource] = $this->getResourceUrl($routeName, $entity['id']);
                    }
                }
            }
        }
    }
}
