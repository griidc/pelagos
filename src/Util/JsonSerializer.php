<?php

namespace App\Util;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * @param object $object An object to be serialized.
     *
     * @return self
     */
    public function serialize(object $object): self
    {
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);

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
     * @return JsonResponse
     */
    public function createJsonResponse(): JsonResponse
    {
        return new JsonResponse($this->json);
    }
}
