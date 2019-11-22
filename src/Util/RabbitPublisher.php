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
     * File hasher rabbitmq producer instance.
     *
     * @var Producer
     */
    protected $datasetFileHasherProducer;

    /**
     * Doi rabbitmq producer instance.
     *
     * @var Producer
     */
    protected $doiProducer;

    /**
     * Create home dir producer instance.
     *
     * @var Producer
     */
    protected $createHomeDirProducer;

    const DOI_PRODUCER = 'doi';

    const FILE_HASHER_PRODUCER = 'file_hasher';

    const DATASET_SUBMISSION_PRODUCER = 'dataset_submission';

    const CREATE_HOMEDIR_PRODUCER = 'create_homedir';

    /**
     * RabbitPublisher constructor.
     *
     * @param Producer $datasetSubProducer        Dataset submission rabbitmq producer instance.
     * @param Producer $datasetFileHasherProducer File hasher rabbitmq producer instance.
     * @param Producer $doiProducer               Doi rabbitmq producer instance.
     * @param Producer $createHomeDirProducer     Create home dir producer instance.
     */
    public function __construct(
        Producer $datasetSubProducer,
        Producer $datasetFileHasherProducer,
        Producer $doiProducer,
        Producer $createHomeDirProducer
    ) {
        $this->datasetSubProducer = $datasetSubProducer;
        $this->datasetFileHasherProducer = $datasetFileHasherProducer;
        $this->doiProducer = $doiProducer;
        $this->createHomeDirProducer = $createHomeDirProducer;
    }

    /**
     * Utility publish method to call rabbitmq producers.
     *
     * @param mixed  $id           The id of the object that is being published.
     * @param string $producerName The producer name required to publish.
     * @param string $routingKey   The routing key for the message.
     *
     * @return void
     */
    public function publish($id, string $producerName, string $routingKey = '')
    {
        $publisher = null;
        if ($producerName === self::DATASET_SUBMISSION_PRODUCER) {
            $publisher = $this->datasetSubProducer;
        } elseif ($producerName === self::DOI_PRODUCER) {
            $publisher = $this->doiProducer;
        } elseif ($producerName === self::FILE_HASHER_PRODUCER) {
            $publisher = $this->datasetFileHasherProducer;
        } elseif ($producerName === self::CREATE_HOMEDIR_PRODUCER) {
            $publisher = $this->createHomeDirProducer;
        }

        if ($publisher) {
            $publisher->publish($id, $routingKey);
        }
    }
}
