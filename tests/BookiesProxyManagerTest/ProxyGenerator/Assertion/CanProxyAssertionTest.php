<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\Assertion;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Exception\InvalidProxiedClassException;
use BookiesProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use BookiesProxyManagerTestAsset\AccessInterceptorValueHolderMock;
use BookiesProxyManagerTestAsset\BaseClass;
use BookiesProxyManagerTestAsset\BaseInterface;
use BookiesProxyManagerTestAsset\CallableTypeHintClass;
use BookiesProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use BookiesProxyManagerTestAsset\ClassWithByRefMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithFinalMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithFinalMethods;
use BookiesProxyManagerTestAsset\ClassWithMethodWithDefaultParameters;
use BookiesProxyManagerTestAsset\ClassWithMixedProperties;
use BookiesProxyManagerTestAsset\ClassWithParentHint;
use BookiesProxyManagerTestAsset\ClassWithPrivateProperties;
use BookiesProxyManagerTestAsset\ClassWithProtectedProperties;
use BookiesProxyManagerTestAsset\ClassWithPublicArrayProperty;
use BookiesProxyManagerTestAsset\ClassWithPublicProperties;
use BookiesProxyManagerTestAsset\ClassWithSelfHint;
use BookiesProxyManagerTestAsset\EmptyClass;
use BookiesProxyManagerTestAsset\FinalClass;
use BookiesProxyManagerTestAsset\HydratedObject;
use BookiesProxyManagerTestAsset\LazyLoadingMock;
use BookiesProxyManagerTestAsset\NullObjectMock;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\Assertion\CanProxyAssertion}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\Assertion\CanProxyAssertion
 * @group Coverage
 */
final class CanProxyAssertionTest extends TestCase
{
    public function testDeniesFinalClasses(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(FinalClass::class));
    }

    public function testDeniesClassesWithAbstractProtectedMethods(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(
            ClassWithAbstractProtectedMethod::class
        ));
    }

    public function testAllowsInterfaceByDefault(): void
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(
            BaseInterface::class
        ));

        self::assertTrue(true); // not nice, but assertions are just fail-checks, no real code executed
    }

    public function testDeniesInterfaceIfSpecified(): void
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(BaseClass::class), false);

        $this->expectException(InvalidProxiedClassException::class);

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(BaseInterface::class), false);
    }

    /**
     * @psalm-param class-string $className
     *
     * @dataProvider validClasses
     */
    public function testAllowedClass(string $className): void
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass($className));

        self::assertTrue(true); // not nice, but assertions are just fail-checks, no real code executed
    }

    public function testDisallowsConstructor(): void
    {
        $this->expectException(BadMethodCallException::class);

        new CanProxyAssertion();
    }

    /**
     * @return string[][]
     */
    public function validClasses(): array
    {
        return [
            [AccessInterceptorValueHolderMock::class],
            [BaseClass::class],
            [BaseInterface::class],
            [CallableTypeHintClass::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithFinalMagicMethods::class],
            [ClassWithFinalMethods::class],
            [ClassWithMethodWithDefaultParameters::class],
            [ClassWithMixedProperties::class],
            [ClassWithPrivateProperties::class],
            [ClassWithProtectedProperties::class],
            [ClassWithPublicProperties::class],
            [ClassWithPublicArrayProperty::class],
            [ClassWithSelfHint::class],
            [ClassWithParentHint::class],
            [EmptyClass::class],
            [HydratedObject::class],
            [LazyLoadingMock::class],
            [NullObjectMock::class],
        ];
    }
}
