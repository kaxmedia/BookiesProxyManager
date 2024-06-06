<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSet;
use BookiesProxyManagerTestAsset\ClassWithMagicMethods;
use BookiesProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSet}
 *
 * @group Coverage
 */
final class MagicSetTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection         = new ReflectionClass(EmptyClass::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $magicGet = new MagicSet(
            $reflection,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('__set', $magicGet->getName());
        self::assertCount(2, $magicGet->getParameters());
        self::assertStringMatchesFormat('%a$returnValue = & $accessor();%a', $magicGet->getBody());
    }

    /**
     * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructureWithInheritedMethod(): void
    {
        $reflection         = new ReflectionClass(ClassWithMagicMethods::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $magicGet = new MagicSet(
            $reflection,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('__set', $magicGet->getName());
        self::assertCount(2, $magicGet->getParameters());
        self::assertStringMatchesFormat('%a$returnValue = & parent::__set($name, $value);%a', $magicGet->getBody());
    }
}
