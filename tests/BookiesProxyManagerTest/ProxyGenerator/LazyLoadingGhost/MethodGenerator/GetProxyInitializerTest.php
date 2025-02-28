<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer}
 *
 * @group Coverage
 */
final class GetProxyInitializerTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer::__construct
     */
    public function testBodyStructure(): void
    {
        $initializer = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('foo');

        $getter = new GetProxyInitializer($initializer);

        self::assertEquals(TypeGenerator::fromTypeString('?\Closure'), $getter->getReturnType());
        self::assertSame('getProxyInitializer', $getter->getName());
        self::assertCount(0, $getter->getParameters());
        self::assertSame('return $this->foo;', $getter->getBody());
    }
}
