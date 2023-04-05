<?php

namespace App\Util;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serializes entities into Json.
 */
class JsonSerializer
{
    /**
     * Instance of the JMS Serializer.
     *
     * @var SerializerInterface $serializer
     */
    private $serializer;

    /**
     * The json string, containing the serialized string.
     *
     * @var string
     */
    private $json = '{}';

    /**
     * Class Constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serialize an Object into JSON.
     *
     * @param mixed $object An object to be serialized.
     * @param array $groups The serializer groups to use.
     *
     * @return self
     */
    public function serialize(mixed $object, array $groups = null): self
    {
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);
        if (!empty($groups)) {
            $context->setGroups($groups);
        }

        $this->json = $this->serializer->serialize($object, 'json', $context);

        return $this;
    }

    /**
     * Returns the serialized JSON.
     *
     * @return string
     */
    public function getJson(): string
    {
        return $this->json;
    }

    /**
     * Creates a response from JSON.
     *
     * @return Response
     */
    public function createJsonResponse(): Response
    {
        return new Response($this->json, Response::HTTP_OK, array('Content-Type', 'application/json'));
    }
}
