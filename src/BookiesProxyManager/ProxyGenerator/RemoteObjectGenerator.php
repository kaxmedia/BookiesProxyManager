<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use BookiesProxyManager\Exception\InvalidProxiedClassException;
use BookiesProxyManager\Generator\Util\ClassGeneratorUtils;
use BookiesProxyManager\Proxy\RemoteObjectInterface;
use BookiesProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicGet;
use BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset;
use BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet;
use BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicUnset;
use BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod;
use BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\StaticProxyConstructor;
use BookiesProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty;
use BookiesProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \BookiesProxyManager\Proxy\RemoteObjectInterface}
 *
 * {@inheritDoc}
 */
class RemoteObjectGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces = [RemoteObjectInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($adapter = new AdapterProperty());

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    static fn (ReflectionMethod $method): RemoteObjectMethod => RemoteObjectMethod::generateMethod(
                        new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                        $adapter,
                        $originalClass
                    ),
                    ProxiedMethodsFilter::getProxiedMethods(
                        $originalClass,
                        ['__get', '__set', '__isset', '__unset']
                    )
                ),
                [
                    new StaticProxyConstructor($originalClass, $adapter),
                    new MagicGet($originalClass, $adapter),
                    new MagicSet($originalClass, $adapter),
                    new MagicIsset($originalClass, $adapter),
                    new MagicUnset($originalClass, $adapter),
                ]
            )
        );
    }
}
