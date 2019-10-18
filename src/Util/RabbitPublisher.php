<?php

namespace App\Util;

use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Utility Class RabbitPublisher to publish rabbitmq messages.
 * @package App\Util
 */
class RabbitPublisher
{
    /**
     * @var Producer
     */
    protected $datasetSubProducer;

    /**
     * RabbitPublisher constructor.
     *
     * @param Producer $datasetSubProducer
     */
    public function __construct(Producer $datasetSubProducer)
    {
        $this->datasetSubProducer = $datasetSubProducer;
    }

    /**
     * @param string $id         The id of the object that is being published.
     * @param string $routingKey The routing key for the message.
     */
    public function publish(string $id, string $routingKey)
    {
        $this->datasetSubProducer->publish($id, $routingKey);
    }
}
