<?php

namespace App\Util;

use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Utility Class RabbitPublisher to publish rabbitmq messages.
 */
class RabbitPublisher
{
    /**
     * Dataset submission rabbitmq producer instance.
     *
     * @var Producer
     */
    protected $datasetSubProducer;

    /**
     * RabbitPublisher constructor.
     *
     * @param Producer $datasetSubProducer Dataset submission rabbitmq producer instance.
     */
    public function __construct(Producer $datasetSubProducer)
    {
        $this->datasetSubProducer = $datasetSubProducer;
    }

    /**
     * Utility publish method to call rabbitmq producers.
     *
     * @param string $id         The id of the object that is being published.
     * @param string $routingKey The routing key for the message.
     *
     * @return void
     */
    public function publish(string $id, string $routingKey)
    {
        $this->datasetSubProducer->publish($id, $routingKey);
    }
}
