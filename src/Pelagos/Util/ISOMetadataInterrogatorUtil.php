<?php

namespace Pelagos\Util;

use Pelagos\Entity\Dataset;

/**
 * A utility class for extracting information from ISO metadata.
 */
class ISOMetadataInterrogatorUtil
{
    /**
     * Metadata file as SimpleXML object.
     *
     * @var SimpleXml
     */
    protected $xmlMetadata;

    /**
     * Constructor.
     *
     * @param SimpleXml $entityManager The entity manager to use.
     */
    public function __construct(SimpleXML $xmlMetadata)
    {
        $this->xmlMetadata = $xmlMetadata;
    }

}
