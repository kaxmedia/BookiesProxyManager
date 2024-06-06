<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Functional;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use BookiesProxyManager\Factory\AccessInterceptorValueHolderFactory;
use BookiesProxyManager\Factory\LazyLoadingGhostFactory;
use BookiesProxyManager\Factory\LazyLoadingValueHolderFactory;
use BookiesProxyManagerTestAsset\BaseClass;
use BookiesProxyManagerTestAsset\ClassWithByRefMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use BookiesProxyManagerTestAsset\ClassWithFinalMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithFinalMethods;
use BookiesProxyManagerTestAsset\ClassWithMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use BookiesProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use BookiesProxyManagerTestAsset\ClassWithMixedProperties;
use BookiesProxyManagerTestAsset\ClassWithMixedReferenceableTypedProperties;
use BookiesProxyManagerTestAsset\ClassWithMixedTypedProperties;
use BookiesProxyManagerTestAsset\ClassWithParentHint;
use BookiesProxyManagerTestAsset\ClassWithPhp80TypedMethods;
use BookiesProxyManagerTestAsset\ClassWithPrivateProperties;
use BookiesProxyManagerTestAsset\ClassWithProtectedProperties;
use BookiesProxyManagerTestAsset\ClassWithPublicProperties;
use BookiesProxyManagerTestAsset\ClassWithSelfHint;
use BookiesProxyManagerTestAsset\EmptyClass;
use BookiesProxyManagerTestAsset\HydratedObject;
use BookiesProxyManagerTestAsset\IterableTypeHintClass;
use BookiesProxyManagerTestAsset\ObjectTypeHintClass;
use BookiesProxyManagerTestAsset\ReturnTypeHintedClass;
use BookiesProxyManagerTestAsset\ScalarTypeHintedClass;
use BookiesProxyManagerTestAsset\VoidMethodTypeHintedClass;

use const PHP_VERSION_ID;

/**
 * Verifies that proxy factories don't conflict with each other when generating proxies
 *
 * @link https://github.com/Ocramius/ProxyManager/issues/10
 *
 * @group Functional
 * @group issue-10
 * @coversNothing
 */
final class MultipleProxyGenerationTest extends TestCase
{
    /**
     * Verifies that proxies generated from different factories will retain their specific implementation
     * and won't conflict
     *
     * @dataProvider getTestedClasses
     */
    public function testCanGenerateMultipleDifferentProxiesForSameClass(object $object): void
    {
        $ghostProxyFactory                      = new LazyLoadingGhostFactory();
        $virtualProxyFactory                    = new LazyLoadingValueHolderFactory();
        $accessInterceptorFactory               = new AccessInterceptorValueHolderFactory();
        $accessInterceptorScopeLocalizerFactory = new AccessInterceptorScopeLocalizerFactory();
        $className                              = $object::class;
        $initializer                            = static fn (): bool => true;

        $generated = [
            $ghostProxyFactory->createProxy($className, $initializer),
            $virtualProxyFactory->createProxy($className, $initializer),
            $accessInterceptorFactory->createProxy($object),
        ];

        if ($className !== ClassWithMixedTypedProperties::class) {
            $generated[] = $accessInterceptorScopeLocalizerFactory->createProxy($object);
        }

        foreach ($generated as $key => $proxy) {
            self::assertInstanceOf($className, $proxy);

            foreach ($generated as $comparedKey => $comparedProxy) {
                if ($comparedKey === $key) {
                    continue;
                }

                self::assertNotSame($comparedProxy::class, $proxy::class);
            }

            $proxyClass = $proxy::class;

            /**
             * @psalm-suppress InvalidStringClass
             * @psalm-suppress MixedMethodCall
             */
            self::assertInstanceOf($proxyClass, new $proxyClass(), 'Proxy can be instantiated via normal constructor');
        }
    }

    /**
     * @return object[][]
     */
    public function getTestedClasses(): array
    {
        $objects = [
            [new BaseClass()],
            [new ClassWithMagicMethods()],
            [new ClassWithFinalMethods()],
            [new ClassWithFinalMagicMethods()],
            [new ClassWithByRefMagicMethods()],
            [new ClassWithMixedProperties()],
            [new ClassWithMixedTypedProperties()],
            [new ClassWithMixedReferenceableTypedProperties()],
            //            [new ClassWithPublicStringTypedProperty()],
            //            [new ClassWithPublicStringNullableTypedProperty()],
            [new ClassWithPrivateProperties()],
            [new ClassWithProtectedProperties()],
            [new ClassWithPublicProperties()],
            [new EmptyClass()],
            [new HydratedObject()],
            [new ClassWithSelfHint()],
            [new ClassWithParentHint()],
            [new ClassWithCollidingPrivateInheritedProperties()],
            [new ClassWithMethodWithVariadicFunction()],
            [new ClassWithMethodWithByRefVariadicFunction()],
            [new ScalarTypeHintedClass()],
            [new IterableTypeHintClass()],
            [new ObjectTypeHintClass()],
            [new ReturnTypeHintedClass()],
            [new VoidMethodTypeHintedClass()],
        ];

        if (PHP_VERSION_ID >= 80000) {
            $objects[] = [new ClassWithPhp80TypedMethods()];
        }

        return $objects;
    }
}
