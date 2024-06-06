<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicClone;
use BookiesProxyManagerTestAsset\ClassWithMagicMethods;
use BookiesProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicClone}
 *
 * @group Coverage
 */
final class MagicCloneTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicClone::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection         = new ReflectionClass(EmptyClass::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $magicClone = new MagicClone($reflection, $prefixInterceptors, $suffixInterceptors);

        self::assertSame('__clone', $magicClone->getName());
        self::assertCount(0, $magicClone->getParameters());
        self::assertStringMatchesFormat("%a\n\n\$returnValue = null;\n\n%a", $magicClone->getBody());
    }

    /**
     * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicClone::__construct
     */
    public function testBodyStructureWithInheritedMethod(): void
    {
        $reflection         = new ReflectionClass(ClassWithMagicMethods::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $magicClone = new MagicClone($reflection, $prefixInterceptors, $suffixInterceptors);

        self::assertSame('__clone', $magicClone->getName());
        self::assertCount(0, $magicClone->getParameters());
        self::assertStringMatchesFormat("%a\n\n\$returnValue = parent::__clone();\n\n%a", $magicClone->getBody());
    }
}
