<?php

namespace Pelagos\Bundle\AppBundle\Handler;

use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;

/**
 * A handler to format exceptions.
 */
class ExceptionWrapperHandler implements ExceptionWrapperHandlerInterface
{
    /**
     * Re-format the exception into a new data structure suitable for serialization.
     *
     * @param mixed $data The exception data (actually an array, but ExceptionWrapperHandlerInterface
     *                    does not typehint it as such so we must say "mixed" here).
     *
     * @return array The re-formatted exception data structure.
     */
    public function wrap($data)
    {
        return array(
            'code' => $data['status_code'],
            'message' => $data['message'],
        );
    }
}
