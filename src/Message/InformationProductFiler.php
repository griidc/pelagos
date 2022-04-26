<?php

namespace App\Message;

/**
 * The message for Information Product processor.
 */
final class InformationProductFiler
{
    /**
     * The ID of the Information Product.
     *
     * @var string
     */
    protected $informationProductId;

    /**
     * Constructor.
     *
     * @param int $informationProductId The ID of the Information Product.
     */
    public function __construct(int $informationProductId)
    {
        $this->informationProductId = $informationProductId;
    }

    /**
     * The Information Product ID getter.
     *
     * @return integer The Information Product ID.
     */
    public function getInformationProductId(): int
    {
        return $this->informationProductId;
    }
}
