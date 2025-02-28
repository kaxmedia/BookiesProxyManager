<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet;
use BookiesProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use BookiesProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet}
 *
 * @group Coverage
 */
final class MagicGetTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection         = new ReflectionClass(EmptyClass::class);
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);
        $publicProperties   = $this->createMock(PublicPropertiesMap::class);

        $valueHolder->method('getName')->willReturn('bar');
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');
        $publicProperties->method('isEmpty')->willReturn(false);

        $magicGet = new MagicGet(
            $reflection,
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors,
            $publicProperties
        );

        self::assertSame('__get', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());
        self::assertStringMatchesFormat('%A$returnValue = & $this->bar->$name;%A', $magicGet->getBody());
    }
}
