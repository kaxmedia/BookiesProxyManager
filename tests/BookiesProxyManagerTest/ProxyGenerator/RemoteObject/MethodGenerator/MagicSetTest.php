<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet;
use BookiesProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet}
 *
 * @group Coverage
 */
final class MagicSetTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        $adapter    = $this->createMock(PropertyGenerator::class);
        $adapter->method('getName')->willReturn('foo');

        $magicGet = new MagicSet($reflection, $adapter);

        self::assertSame('__set', $magicGet->getName());
        self::assertCount(2, $magicGet->getParameters());
        self::assertStringMatchesFormat(
            '$return = $this->foo->call(\'BookiesProxyManagerTestAsset\\\EmptyClass\', \'__set\', array($name, $value));'
            . "\n\nreturn \$return;",
            $magicGet->getBody()
        );
    }
}
