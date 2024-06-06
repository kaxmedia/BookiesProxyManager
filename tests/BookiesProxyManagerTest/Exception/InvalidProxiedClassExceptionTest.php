<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Exception\InvalidProxiedClassException;
use BookiesProxyManagerTestAsset\BaseInterface;
use BookiesProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use BookiesProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use BookiesProxyManagerTestAsset\ClassWithProtectedMethod;
use BookiesProxyManagerTestAsset\FinalClass;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\Exception\InvalidProxiedClassException}
 *
 * @covers \BookiesProxyManager\Exception\InvalidProxiedClassException
 * @group Coverage
 */
final class InvalidProxiedClassExceptionTest extends TestCase
{
    public function testInterfaceNotSupported(): void
    {
        self::assertSame(
            'Provided interface "BookiesProxyManagerTestAsset\BaseInterface" cannot be proxied',
            InvalidProxiedClassException::interfaceNotSupported(
                new ReflectionClass(BaseInterface::class)
            )->getMessage()
        );
    }

    public function testFinalClassNotSupported(): void
    {
        self::assertSame(
            'Provided class "BookiesProxyManagerTestAsset\FinalClass" is final and cannot be proxied',
            InvalidProxiedClassException::finalClassNotSupported(
                new ReflectionClass(FinalClass::class)
            )->getMessage()
        );
    }

    public function testAbstractProtectedMethodsNotSupported(): void
    {
        self::assertSame(
            'Provided class "BookiesProxyManagerTestAsset\ClassWithAbstractProtectedMethod" has following protected abstract'
            . ' methods, and therefore cannot be proxied:' . "\n"
            . 'BookiesProxyManagerTestAsset\ClassWithAbstractProtectedMethod::protectedAbstractMethod',
            InvalidProxiedClassException::abstractProtectedMethodsNotSupported(
                new ReflectionClass(ClassWithAbstractProtectedMethod::class)
            )->getMessage()
        );
    }

    public function testProtectedMethodsNotSupported(): void
    {
        self::assertSame(
            'Provided class "BookiesProxyManagerTestAsset\ClassWithProtectedMethod" has following protected abstract'
            . ' methods, and therefore cannot be proxied:' . "\n",
            InvalidProxiedClassException::abstractProtectedMethodsNotSupported(
                new ReflectionClass(ClassWithProtectedMethod::class)
            )->getMessage()
        );
    }

    public function testAbstractPublicMethodsNotSupported(): void
    {
        self::assertSame(
            'Provided class "BookiesProxyManagerTestAsset\ClassWithAbstractPublicMethod" has following protected abstract'
            . ' methods, and therefore cannot be proxied:' . "\n",
            InvalidProxiedClassException::abstractProtectedMethodsNotSupported(
                new ReflectionClass(ClassWithAbstractPublicMethod::class)
            )->getMessage()
        );
    }
}
