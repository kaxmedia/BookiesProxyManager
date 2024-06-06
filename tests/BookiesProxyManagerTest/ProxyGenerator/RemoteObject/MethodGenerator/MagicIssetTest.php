<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset;
use BookiesProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset}
 *
 * @group Coverage
 */
final class MagicIssetTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        $adapter    = $this->createMock(PropertyGenerator::class);
        $adapter->method('getName')->willReturn('foo');

        $magicGet = new MagicIsset($reflection, $adapter);

        self::assertSame('__isset', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());
        self::assertStringMatchesFormat(
            '$return = $this->foo->call(\'BookiesProxyManagerTestAsset\\\EmptyClass\', \'__isset\', array($name));'
            . "\n\nreturn \$return;",
            $magicGet->getBody()
        );
    }
}
