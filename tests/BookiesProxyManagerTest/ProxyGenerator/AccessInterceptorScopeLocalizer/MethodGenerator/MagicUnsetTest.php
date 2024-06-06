<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicUnset;
use BookiesProxyManagerTestAsset\ClassWithMagicMethods;
use BookiesProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicUnset}
 *
 * @group Coverage
 */
final class MagicUnsetTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicUnset::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection         = new ReflectionClass(EmptyClass::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $magicGet = new MagicUnset(
            $reflection,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('__unset', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());
        self::assertStringMatchesFormat('%a$accessor();%a', $magicGet->getBody());
    }

    /**
     * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicUnset::__construct
     */
    public function testBodyStructureWithInheritedMethod(): void
    {
        $reflection         = new ReflectionClass(ClassWithMagicMethods::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $magicGet = new MagicUnset(
            $reflection,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('__unset', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());
        self::assertStringMatchesFormat('%a$returnValue = & parent::__unset($name);%a', $magicGet->getBody());
    }
}
