<?php

namespace App\Serializer;

use App\Entity\Dataset;
use App\Util\DatasetLandingPageHost;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DatasetNormalizer implements NormalizerInterface
{
    public function __construct(
        private NormalizerInterface $normalizer,
        private DatasetLandingPageHost $landingPageHost,
    ) {
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array|bool|float|int|string|null
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if ($object instanceof Dataset && is_array($data)) {
            if (isset($data['landingPage'])) {
                $host = $this->landingPageHost->getHost();
                $udi = $object->getUdi();
                $data['landingPage'] = 'https://' . $host . '/data/' . $udi;
            }
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Dataset;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Dataset::class => true,
        ];
    }
}
