<?php
/*
 * WellCommerce Open-Source E-Commerce Platform
 * 
 * This file is part of the WellCommerce package.
 *
 * (c) Adam Piotrowski <adam@wellcommerce.org>
 * 
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace WellCommerce\Bundle\ApiBundle\Serializer;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use WellCommerce\Bundle\ApiBundle\Metadata\Loader\SerializationMetadataLoaderInterface;
use WellCommerce\Bundle\DoctrineBundle\Helper\Doctrine\DoctrineHelperInterface;

/**
 * Class AbstractSerializer
 *
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
abstract class AbstractSerializer implements SerializerAwareInterface
{
    /**
     * @var DoctrineHelperInterface
     */
    protected $doctrineHelper;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var array
     */
    protected $cache;

    /**
     * @var SerializerInterface|NormalizerInterface
     */
    protected $serializer;

    /**
     * @var SerializationMetadataLoaderInterface
     */
    protected $serializationMetadataLoader;

    /**
     * @var \WellCommerce\Bundle\ApiBundle\Metadata\Collection\SerializationMetadataCollection
     */
    protected $serializationMetadataCollection;

    /**
     * AbstractSerializer constructor.
     *
     * @param DoctrineHelperInterface              $doctrineHelper
     * @param SerializationMetadataLoaderInterface $serializationMetadataLoader
     */
    public function __construct(DoctrineHelperInterface $doctrineHelper, SerializationMetadataLoaderInterface $serializationMetadataLoader)
    {
        $this->doctrineHelper                  = $doctrineHelper;
        $this->serializationMetadataLoader     = $serializationMetadataLoader;
        $this->propertyAccessor                = PropertyAccess::createPropertyAccessor();
        $this->serializationMetadataCollection = $this->getSerializationMetadataCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof NormalizerInterface) {
            throw new LogicException('Injected serializer must be a normalizer');
        }

        $this->serializer = $serializer;
    }

    /**
     * @return \WellCommerce\Bundle\ApiBundle\Metadata\Collection\SerializationMetadataCollection
     */
    protected function getSerializationMetadataCollection()
    {
        return $this->serializationMetadataLoader->loadMetadata();
    }

    /**
     * Returns the serialization metadata for given entity
     *
     * @param object $entity
     *
     * @return \WellCommerce\Bundle\ApiBundle\Metadata\SerializationClassMetadataInterface
     */
    protected function getSerializationMetadata($entity)
    {
        $className = $this->getRealClass($entity);

        return $this->serializationMetadataCollection->get($className);
    }

    /**
     * Returns the metadata for entity
     *
     * @param object $entity
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    protected function getEntityMetadata($entity)
    {
        return $this->doctrineHelper->getClassMetadataForEntity($entity);
    }

    /**
     * Builds property path in array-notation style from given attribute's name
     *
     * @param $attributeName
     *
     * @return PropertyPath
     */
    protected function getPropertyPath($attributeName)
    {
        $elements = explode('.', $attributeName);

        $wrapped = array_map(function ($element) {
            $name = Inflector::tableize($element);

            return "[{$name}]";
        }, $elements);

        return new PropertyPath(implode('', $wrapped));
    }

    /**
     * @param $fieldName
     *
     * @return mixed
     */
    protected function getPropertyNameForField($fieldName)
    {
        list($rootName,) = explode('.', $fieldName);

        return $rootName;
    }

    /**
     * Returns the real class name
     *
     * @param object $object
     *
     * @return string
     */
    private function getRealClass($object)
    {
        $className = get_class($object);

        return ClassUtils::getRealClass($className);
    }
}