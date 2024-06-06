<?php

declare(strict_types=1);

namespace BookiesProxyManager\Factory;

use Closure;
use OutOfBoundsException;
use BookiesProxyManager\Configuration;
use BookiesProxyManager\Proxy\AccessInterceptorInterface;
use BookiesProxyManager\Proxy\AccessInterceptorValueHolderInterface;
use BookiesProxyManager\Proxy\ValueHolderInterface;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use BookiesProxyManager\Signature\Exception\InvalidSignatureException;
use BookiesProxyManager\Signature\Exception\MissingSignatureException;

/**
 * Factory responsible of producing proxy objects
 */
class AccessInterceptorValueHolderFactory extends AbstractBaseFactory
{
    private AccessInterceptorValueHolderGenerator $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new AccessInterceptorValueHolderGenerator();
    }

    /**
     * @param object                 $instance           the object to be wrapped within the value holder
     * @param array<string, Closure> $prefixInterceptors an array (indexed by method name) of interceptor closures to be called
     *                                       before method logic is executed
     * @param array<string, Closure> $suffixInterceptors an array (indexed by method name) of interceptor closures to be called
     *                                       after method logic is executed
     * @psalm-param RealObjectType $instance
     * @psalm-param array<string, callable(
     *   RealObjectType&AccessInterceptorInterface<RealObjectType>=,
     *   RealObjectType=,
     *   string=,
     *   array<string, mixed>=,
     *   bool=
     * ) : mixed> $prefixInterceptors
     * @psalm-param array<string, callable(
     *   RealObjectType&AccessInterceptorInterface<RealObjectType>=,
     *   RealObjectType=,
     *   string=,
     *   array<string, mixed>=,
     *   mixed=,
     *   bool=
     * ) : mixed> $suffixInterceptors
     *
     * @psalm-return RealObjectType&AccessInterceptorInterface<RealObjectType>&ValueHolderInterface<RealObjectType>&AccessInterceptorValueHolderInterface<RealObjectType>
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy(
        object $instance,
        array $prefixInterceptors = [],
        array $suffixInterceptors = []
    ): AccessInterceptorValueHolderInterface {
        $proxyClassName = $this->generateProxy($instance::class);

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design)
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor($instance, $prefixInterceptors, $suffixInterceptors);
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }
}
