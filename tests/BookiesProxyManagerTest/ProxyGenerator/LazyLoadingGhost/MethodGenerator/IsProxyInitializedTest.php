<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\IsProxyInitialized;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\IsProxyInitialized}
 *
 * @group Coverage
 */
final class IsProxyInitializedTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\IsProxyInitialized::__construct
     */
    public function testBodyStructure(): void
    {
        $initializer = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('foo');

        $isProxyInitialized = new IsProxyInitialized($initializer);

        self::assertSame('isProxyInitialized', $isProxyInitialized->getName());
        self::assertCount(0, $isProxyInitialized->getParameters());
        self::assertSame('return ! $this->foo;', $isProxyInitialized->getBody());
        self::assertStringMatchesFormat('%A : bool%A', $isProxyInitialized->generate(), 'Return type hint is boolean');
    }
}
