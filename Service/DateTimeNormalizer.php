<?php
namespace EveryCheck\TestApiRestBundle\Service;


use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

class DateTimeNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->format(\DateTime::ISO8601);
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return new $class($data);
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTime;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        $class = new \ReflectionClass($type);
        return $class->isSubclassOf('\DateTime');
    }
}