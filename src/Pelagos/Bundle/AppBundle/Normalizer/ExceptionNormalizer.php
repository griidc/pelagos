<?php

namespace Pelagos\Bundle\AppBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * The ExceptionNormalizer to normalize serialized error messages.
 */
class ExceptionNormalizer implements NormalizerInterface
{
    /**
     * Normalize the serialized error message.
     *
     * @param string  $topic   The topic.
     * @param string  $format  The format.
     * @param array   $context The context.
     *
     * @return integer
     */
    public function normalize($topic, $format = null, array $context = array())
    {
        return array(
            'code' => $context['status_code'],
            'message' => $context['message'],
        );
    }

    /**
     * Count entities of a given type.
     *
     * @param string  $data    The the exception data.
     * @param string  $format  The format.
     *
     * @return integer
     */
    public function supportsNormalization($data, $format = null)
    {
        return false;
    }
}