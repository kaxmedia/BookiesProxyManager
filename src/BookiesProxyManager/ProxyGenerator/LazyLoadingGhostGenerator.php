<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use BookiesProxyManager\Exception\InvalidProxiedClassException;
use BookiesProxyManager\Generator\MethodGenerator as ProxyManagerMethodGenerator;
use BookiesProxyManager\Generator\Util\ClassGeneratorUtils;
use BookiesProxyManager\Proxy\GhostObjectInterface;
use BookiesProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use BookiesProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\IsProxyInitialized;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicIsset;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSleep;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\SetProxyInitializer;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use BookiesProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use BookiesProxyManager\ProxyGenerator\Util\Properties;
use BookiesProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \BookiesProxyManager\Proxy\GhostObjectInterface}
 *
 * {@inheritDoc}
 */
class LazyLoadingGhostGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @psalm-param array{skippedProperties?: array<int, string>} $proxyOptions
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = [])
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $filteredProperties = Properties::fromReflectionClass($originalClass)
            ->filter($proxyOptions['skippedProperties'] ?? []);

        $publicProperties    = new PublicPropertiesMap($filteredProperties);
        $privateProperties   = new PrivatePropertiesMap($filteredProperties);
        $protectedProperties = new ProtectedPropertiesMap($filteredProperties);

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces([GhostObjectInterface::class]);
        $classGenerator->addPropertyFromGenerator($initializer           = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($initializationTracker = new InitializationTracker());
        $classGenerator->addPropertyFromGenerator($publicProperties);
        $classGenerator->addPropertyFromGenerator($privateProperties);
        $classGenerator->addPropertyFromGenerator($protectedProperties);

        $init = new CallInitializer($initializer, $initializationTracker, $filteredProperties);

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                $this->getAbstractProxiedMethods($originalClass),
                [
                    $init,
                    new StaticProxyConstructor($initializer, $filteredProperties),
                    new MagicGet(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties,
                        $initializationTracker
                    ),
                    new MagicSet(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicIsset(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicUnset(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicClone($originalClass, $initializer, $init),
                    new MagicSleep($originalClass, $initializer, $init),
                    new SetProxyInitializer($initializer),
                    new GetProxyInitializer($initializer),
                    new InitializeProxy($initializer, $init),
                    new IsProxyInitialized($initializer),
                ]
            )
        );
    }

    /**
     * Retrieves all abstract methods to be proxied
     *
     * @return MethodGenerator[]
     */
    private function getAbstractProxiedMethods(ReflectionClass $originalClass): array
    {
        return array_map(
            static function (ReflectionMethod $method): ProxyManagerMethodGenerator {
                $generated = ProxyManagerMethodGenerator::fromReflectionWithoutBodyAndDocBlock(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                );

                $generated->setAbstract(false);

                return $generated;
            },
            ProxiedMethodsFilter::getAbstractProxiedMethods($originalClass)
        );
    }
}
