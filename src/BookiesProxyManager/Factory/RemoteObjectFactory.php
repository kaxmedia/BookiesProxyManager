<?php

declare(strict_types=1);

namespace BookiesProxyManager\Factory;

use OutOfBoundsException;
use BookiesProxyManager\Configuration;
use BookiesProxyManager\Factory\RemoteObject\AdapterInterface;
use BookiesProxyManager\Proxy\RemoteObjectInterface;
use BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use BookiesProxyManager\ProxyGenerator\RemoteObjectGenerator;
use BookiesProxyManager\Signature\Exception\InvalidSignatureException;
use BookiesProxyManager\Signature\Exception\MissingSignatureException;

use function is_object;

/**
 * Factory responsible of producing remote proxy objects
 */
class RemoteObjectFactory extends AbstractBaseFactory
{
    private ?RemoteObjectGenerator $generator;

    /**
     * {@inheritDoc}
     *
     * @param Configuration $configuration
     */
    public function __construct(protected AdapterInterface $adapter, ?Configuration $configuration = null)
    {
        parent::__construct($configuration);
        $this->generator = new RemoteObjectGenerator();
    }

    /**
     * @psalm-param RealObjectType|class-string<RealObjectType> $instanceOrClassName
     *
     * @psalm-return RealObjectType&RemoteObjectInterface
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy(string|object $instanceOrClassName): RemoteObjectInterface
    {
        $proxyClassName = $this->generateProxy(
            is_object($instanceOrClassName) ? $instanceOrClassName::class : $instanceOrClassName
        );

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design)
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor($this->adapter);
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator ?? $this->generator = new RemoteObjectGenerator();
    }
}
