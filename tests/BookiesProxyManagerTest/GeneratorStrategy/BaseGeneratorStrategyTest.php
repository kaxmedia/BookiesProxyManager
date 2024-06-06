<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\GeneratorStrategy;

use Laminas\Code\Generator\ClassGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Generator\Util\UniqueIdentifierGenerator;
use BookiesProxyManager\GeneratorStrategy\BaseGeneratorStrategy;

use function strpos;

/**
 * Tests for {@see \BookiesProxyManager\GeneratorStrategy\BaseGeneratorStrategy}
 *
 * @group Coverage
 */
final class BaseGeneratorStrategyTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\GeneratorStrategy\BaseGeneratorStrategy::generate
     */
    public function testGenerate(): void
    {
        $strategy       = new BaseGeneratorStrategy();
        $className      = UniqueIdentifierGenerator::getIdentifier('Foo');
        $classGenerator = new ClassGenerator($className);
        $generated      = $strategy->generate($classGenerator);

        self::assertGreaterThan(0, strpos($generated, $className));
    }
}
