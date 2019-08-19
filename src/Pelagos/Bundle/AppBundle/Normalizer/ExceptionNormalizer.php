<?php

namespace Pelagos\Bundle\AppBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ExceptionNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        return array('status_code' => 'foo');return array(
            'code' => $data['status_code'],
            'message' => $data['message'],
        );
    }

    public function supportsNormalization($data, $format = null)
    {
        //dump($data);
        return true;
    }
}