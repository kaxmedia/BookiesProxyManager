<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use BookiesProxyManager\Exception\InvalidProxiedClassException;
use BookiesProxyManager\Generator\Util\ClassGeneratorUtils;
use BookiesProxyManager\Proxy\VirtualProxyInterface;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;
use BookiesProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use BookiesProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\GetProxyInitializer;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\InitializeProxy;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\IsProxyInitialized;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicClone;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSet;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSleep;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicUnset;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use BookiesProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use BookiesProxyManager\ProxyGenerator\Util\Properties;
use BookiesProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use BookiesProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use BookiesProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \BookiesProxyManager\Proxy\VirtualProxyInterface}
 *
 * {@inheritDoc}
 */
class LazyLoadingValueHolderGenerator implements ProxyGeneratorInterface
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

        $interfaces       = [VirtualProxyInterface::class];
        $publicProperties = new PublicPropertiesMap(Properties::fromReflectionClass($originalClass));

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($valueHolder = new ValueHolderProperty($originalClass));
        $classGenerator->addPropertyFromGenerator($initializer = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($publicProperties);

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildLazyLoadingMethodInterceptor($initializer, $valueHolder),
                    ProxiedMethodsFilter::getProxiedMethods($originalClass)
                ),
                [
                    new StaticProxyConstructor($initializer, Properties::fromReflectionClass($originalClass)),
                    Constructor::generateMethod($originalClass, $valueHolder),
                    new MagicGet($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicSet($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicIsset($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicUnset($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicClone($originalClass, $initializer, $valueHolder),
                    new MagicSleep($originalClass, $initializer, $valueHolder),
                    new MagicWakeup($originalClass),
                    new SetProxyInitializer($initializer),
                    new GetProxyInitializer($initializer),
                    new InitializeProxy($initializer, $valueHolder),
                    new IsProxyInitialized($valueHolder),
                    new GetWrappedValueHolderValue($valueHolder),
                ]
            )
        );
    }

    private function buildLazyLoadingMethodInterceptor(
        InitializerProperty $initializer,
        ValueHolderProperty $valueHolder
    ): callable {
        return static fn (ReflectionMethod $method): LazyLoadingMethodInterceptor => LazyLoadingMethodInterceptor::generateMethod(
            new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
            $initializer,
            $valueHolder
        );
    }
}
