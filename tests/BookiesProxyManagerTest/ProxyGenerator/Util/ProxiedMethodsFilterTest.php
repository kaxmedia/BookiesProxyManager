<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\Util;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use BookiesProxyManagerTestAsset\BaseClass;
use BookiesProxyManagerTestAsset\ClassWithAbstractMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use BookiesProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use BookiesProxyManagerTestAsset\ClassWithCounterConstructor;
use BookiesProxyManagerTestAsset\ClassWithFinalMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use BookiesProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use BookiesProxyManagerTestAsset\EmptyClass;
use BookiesProxyManagerTestAsset\HydratedObject;
use BookiesProxyManagerTestAsset\LazyLoadingMock;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function sort;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter
 * @group Coverage
 */
final class ProxiedMethodsFilterTest extends TestCase
{
    /**
     * @param array<int, string>|null $excludes
     * @param string[]                $expectedMethods
     *
     * @dataProvider expectedMethods
     */
    public function testFiltering(ReflectionClass $reflectionClass, ?array $excludes, array $expectedMethods): void
    {
        $filtered = ProxiedMethodsFilter::getProxiedMethods($reflectionClass, $excludes);

        $keys = array_map(
            static fn (ReflectionMethod $method): string => $method->getName(),
            $filtered
        );

        sort($keys);
        sort($expectedMethods);

        self::assertSame($keys, $expectedMethods);
    }

    /**
     * @param array<int, string>|null $excludes
     * @param string[]                $expectedMethods
     *
     * @dataProvider expectedAbstractPublicMethods
     */
    public function testFilteringOfAbstractPublic(
        ReflectionClass $reflectionClass,
        ?array $excludes,
        array $expectedMethods
    ): void {
        $filtered = ProxiedMethodsFilter::getAbstractProxiedMethods($reflectionClass, $excludes);

        $keys = array_map(
            static fn (ReflectionMethod $method): string => $method->getName(),
            $filtered
        );

        sort($keys);
        sort($expectedMethods);

        self::assertSame($keys, $expectedMethods);
    }

    /**
     * Data provider
     *
     * @return ReflectionClass[][]|null[][]|string[][][]
     */
    public function expectedMethods(): array
    {
        return [
            [
                new ReflectionClass(BaseClass::class),
                null,
                [
                    'privatePropertyGetter',
                    'protectedPropertyGetter',
                    'publicArrayHintedMethod',
                    'publicByReferenceMethod',
                    'publicByReferenceParameterMethod',
                    'publicMethod',
                    'publicPropertyGetter',
                    'publicTypeHintedMethod',
                ],
            ],
            [
                new ReflectionClass(EmptyClass::class),
                null,
                [],
            ],
            [
                new ReflectionClass(LazyLoadingMock::class),
                null,
                [
                    'getProxyInitializer',
                    'getWrappedValueHolderValue',
                    'initializeProxy',
                    'isProxyInitialized',
                    'setProxyInitializer',
                ],
            ],
            [
                new ReflectionClass(LazyLoadingMock::class),
                [],
                [
                    'getProxyInitializer',
                    'getWrappedValueHolderValue',
                    'initializeProxy',
                    'isProxyInitialized',
                    'setProxyInitializer',
                ],
            ],
            [
                new ReflectionClass(HydratedObject::class),
                ['doFoo'],
                ['__get'],
            ],
            [
                new ReflectionClass(HydratedObject::class),
                ['Dofoo'],
                ['__get'],
            ],
            [
                new ReflectionClass(HydratedObject::class),
                [],
                ['doFoo', '__get'],
            ],
            [
                new ReflectionClass(ClassWithAbstractProtectedMethod::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                null,
                ['publicAbstractMethod'],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                ['publicAbstractMethod'],
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                [],
                [
                    '__clone',
                    '__get',
                    '__isset',
                    '__set',
                    '__sleep',
                    '__unset',
                    '__wakeup',
                ],
            ],
            [
                new ReflectionClass(ClassWithMethodWithVariadicFunction::class),
                null,
                ['foo', 'buz'],
            ],
            [
                new ReflectionClass(ClassWithMethodWithByRefVariadicFunction::class),
                null,
                ['tuz'],
            ],
            'final magic methods' => [
                new ReflectionClass(ClassWithFinalMagicMethods::class),
                null,
                [],
            ],
            'non-final constructor is to be skipped' => [
                new ReflectionClass(ClassWithCounterConstructor::class),
                null,
                ['getAmount'],
            ],
        ];
    }

    /**
     * Data provider
     *
     * @return ReflectionClass[][]|null[][]|string[][][]
     */
    public function expectedAbstractPublicMethods(): array
    {
        return [
            [
                new ReflectionClass(BaseClass::class),
                null,
                [],
            ],
            [
                new ReflectionClass(EmptyClass::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractProtectedMethod::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                null,
                ['publicAbstractMethod'],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                ['publicAbstractMethod'],
                [],
            ],
            [
                new ReflectionClass(ClassWithMagicMethods::class),
                [],
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                [],
                [
                    '__clone',
                    '__get',
                    '__isset',
                    '__set',
                    '__sleep',
                    '__unset',
                    '__wakeup',
                ],
            ],
        ];
    }
}
