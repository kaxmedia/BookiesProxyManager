<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\AccessInterceptor\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor}
 *
 * @group Coverage
 */
final class SetMethodPrefixInterceptorTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor::__construct
     */
    public function testBodyStructure(): void
    {
        $suffix = $this->createMock(PropertyGenerator::class);

        $suffix->expects(self::once())->method('getName')->willReturn('foo');

        $setter = new SetMethodPrefixInterceptor($suffix);

        self::assertEquals(TypeGenerator::fromTypeString('void'), $setter->getReturnType());
        self::assertSame('setMethodPrefixInterceptor', $setter->getName());
        self::assertCount(2, $setter->getParameters());
        self::assertSame('$this->foo[$methodName] = $prefixInterceptor;', $setter->getBody());
    }
}
