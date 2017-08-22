<?php

namespace Pelagos\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class for dataset download events.
 */
class DownloadEvent extends Event
{
    /**
     * The Dataset downloaded.
     *
     * @var Dataset
     */
    protected $dataset;

    /**
     * The user who is downloading data.
     *
     * @var string
     */
    protected $username;

    /**
     * Constructor.
     *
     * @param Entity $entity The Entity this event is for.
     */
    public function __construct(Dataset $dataset, $user)
    {
        $this->entity = $entity;
        $this->user = $user;
    }

    /**
     * Gets the Dataset this event is for.
     *
     * @return Dataset The Dataset this event is for.
     */
    public function getDataset()
    {
        return $this->dataset;
    }

    /**
     * Gets the username of the downloader.
     *
     * @return mixed The User this event is for.
     */
    public function getUsername()
    {
        return $this->username;
    }
}
