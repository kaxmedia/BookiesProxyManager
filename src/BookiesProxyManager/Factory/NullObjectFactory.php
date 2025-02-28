<?php

declare(strict_types=1);

namespace BookiesProxyManager\Factory;

use OutOfBoundsException;
use BookiesProxyManager\Configuration;
use BookiesProxyManager\Proxy\NullObjectInterface;
use BookiesProxyManager\ProxyGenerator\NullObjectGenerator;
use BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use BookiesProxyManager\Signature\Exception\InvalidSignatureException;
use BookiesProxyManager\Signature\Exception\MissingSignatureException;

use function is_object;

/**
 * Factory responsible of producing proxy objects
 */
class NullObjectFactory extends AbstractBaseFactory
{
    private NullObjectGenerator $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new NullObjectGenerator();
    }

    /**
     * @param object|string $instanceOrClassName the object to be wrapped or interface to transform to null object
     * @psalm-param RealObjectType|class-string<RealObjectType> $instanceOrClassName
     *
     * @psalm-return RealObjectType&NullObjectInterface
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy(object|string $instanceOrClassName): NullObjectInterface
    {
        $className      = is_object($instanceOrClassName) ? $instanceOrClassName::class : $instanceOrClassName;
        $proxyClassName = $this->generateProxy($className);

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design)
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor();
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }
}
