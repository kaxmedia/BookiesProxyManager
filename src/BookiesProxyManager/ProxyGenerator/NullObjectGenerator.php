<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Reflection\MethodReflection;
use BookiesProxyManager\Exception\InvalidProxiedClassException;
use BookiesProxyManager\Generator\Util\ClassGeneratorUtils;
use BookiesProxyManager\Proxy\NullObjectInterface;
use BookiesProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use BookiesProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor;
use BookiesProxyManager\ProxyGenerator\NullObject\MethodGenerator\StaticProxyConstructor;
use BookiesProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;

/**
 * Generator for proxies implementing {@see \BookiesProxyManager\Proxy\NullObjectInterface}
 *
 * {@inheritDoc}
 */
class NullObjectGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator): void
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces = [NullObjectInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);

        foreach (ProxiedMethodsFilter::getProxiedMethods($originalClass, []) as $method) {
            $classGenerator->addMethodFromGenerator(
                NullObjectMethodInterceptor::generateMethod(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                )
            );
        }

        ClassGeneratorUtils::addMethodIfNotFinal(
            $originalClass,
            $classGenerator,
            new StaticProxyConstructor($originalClass)
        );
    }
}
