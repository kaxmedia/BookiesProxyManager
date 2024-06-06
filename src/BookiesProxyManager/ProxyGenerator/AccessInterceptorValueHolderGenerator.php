<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use BookiesProxyManager\Exception\InvalidProxiedClassException;
use BookiesProxyManager\Generator\Util\ClassGeneratorUtils;
use BookiesProxyManager\Proxy\AccessInterceptorValueHolderInterface;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\InterceptedMethod;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicClone;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicSet;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor;
use BookiesProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use BookiesProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use BookiesProxyManager\ProxyGenerator\Util\Properties;
use BookiesProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use BookiesProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use BookiesProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;
use BookiesProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\MagicSleep;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \BookiesProxyManager\Proxy\ValueHolderInterface}
 * and {@see \BookiesProxyManager\Proxy\AccessInterceptorInterface}
 *
 * {@inheritDoc}
 */
class AccessInterceptorValueHolderGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @return void
     *
     * @throws InvalidArgumentException
     * @throws InvalidProxiedClassException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $publicProperties = new PublicPropertiesMap(Properties::fromReflectionClass($originalClass));
        $interfaces       = [AccessInterceptorValueHolderInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($valueHolder        = new ValueHolderProperty($originalClass));
        $classGenerator->addPropertyFromGenerator($prefixInterceptors = new MethodPrefixInterceptors());
        $classGenerator->addPropertyFromGenerator($suffixInterceptors = new MethodSuffixInterceptors());
        $classGenerator->addPropertyFromGenerator($publicProperties);

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildMethodInterceptor($prefixInterceptors, $suffixInterceptors, $valueHolder),
                    ProxiedMethodsFilter::getProxiedMethods($originalClass)
                ),
                [
                    Constructor::generateMethod($originalClass, $valueHolder),
                    new StaticProxyConstructor($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors),
                    new GetWrappedValueHolderValue($valueHolder),
                    new SetMethodPrefixInterceptor($prefixInterceptors),
                    new SetMethodSuffixInterceptor($suffixInterceptors),
                    new MagicGet(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicSet(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicIsset(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicUnset(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicClone($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors),
                    new MagicSleep($originalClass, $valueHolder),
                    new MagicWakeup($originalClass),
                ]
            )
        );
    }

    private function buildMethodInterceptor(
        MethodPrefixInterceptors $prefixes,
        MethodSuffixInterceptors $suffixes,
        ValueHolderProperty $valueHolder
    ): callable {
        return static fn (ReflectionMethod $method): InterceptedMethod => InterceptedMethod::generateMethod(
            new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
            $valueHolder,
            $prefixes,
            $suffixes
        );
    }
}
