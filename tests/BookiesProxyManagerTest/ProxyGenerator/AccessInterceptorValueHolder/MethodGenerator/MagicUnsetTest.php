<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset;
use BookiesProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use BookiesProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

use function strpos;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset}
 *
 * @group Coverage
 */
final class MagicUnsetTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset::__construct
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

        $magicUnset = new MagicUnset(
            $reflection,
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors,
            $publicProperties
        );

        self::assertSame('__unset', $magicUnset->getName());
        self::assertCount(1, $magicUnset->getParameters());
        self::assertGreaterThan(
            0,
            strpos($magicUnset->getBody(), 'unset($this->bar->$name);')
        );
    }
}
