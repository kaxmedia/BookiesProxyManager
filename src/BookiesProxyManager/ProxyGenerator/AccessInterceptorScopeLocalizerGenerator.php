<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use BookiesProxyManager\Exception\InvalidProxiedClassException;
use BookiesProxyManager\Generator\Util\ClassGeneratorUtils;
use BookiesProxyManager\Proxy\AccessInterceptorInterface;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\InterceptedMethod;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicClone;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicGet;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSet;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSleep;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicUnset;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor;
use BookiesProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use BookiesProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \BookiesProxyManager\Proxy\ValueHolderInterface}
 * and localizing scope of the proxied object at instantiation
 *
 * {@inheritDoc}
 */
class AccessInterceptorScopeLocalizerGenerator implements ProxyGeneratorInterface
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
        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces([AccessInterceptorInterface::class]);
        $classGenerator->addPropertyFromGenerator($prefixInterceptors = new MethodPrefixInterceptors());
        $classGenerator->addPropertyFromGenerator($suffixInterceptors = new MethodSuffixInterceptors());

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildMethodInterceptor($prefixInterceptors, $suffixInterceptors),
                    ProxiedMethodsFilter::getProxiedMethods(
                        $originalClass,
                        ['__get', '__set', '__isset', '__unset', '__clone', '__sleep']
                    )
                ),
                [
                    new StaticProxyConstructor($originalClass),
                    new BindProxyProperties($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new SetMethodPrefixInterceptor($prefixInterceptors),
                    new SetMethodSuffixInterceptor($suffixInterceptors),
                    new MagicGet($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicSet($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicIsset($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicUnset($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicSleep($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicClone($originalClass, $prefixInterceptors, $suffixInterceptors),
                ]
            )
        );
    }

    private function buildMethodInterceptor(
        MethodPrefixInterceptors $prefixInterceptors,
        MethodSuffixInterceptors $suffixInterceptors
    ): callable {
        return static fn (ReflectionMethod $method): InterceptedMethod => InterceptedMethod::generateMethod(
            new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
            $prefixInterceptors,
            $suffixInterceptors
        );
    }
}
