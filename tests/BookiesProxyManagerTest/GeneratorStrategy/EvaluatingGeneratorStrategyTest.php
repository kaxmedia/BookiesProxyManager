<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\GeneratorStrategy;

use Laminas\Code\Generator\ClassGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Generator\Util\UniqueIdentifierGenerator;
use BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;

use function class_exists;
use function ini_get;
use function strpos;
use function uniqid;

/**
 * Tests for {@see \BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy}
 *
 * @group Coverage
 */
final class EvaluatingGeneratorStrategyTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy::generate
     * @covers \BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy::__construct
     */
    public function testGenerate(): void
    {
        $strategy       = new EvaluatingGeneratorStrategy();
        $className      = UniqueIdentifierGenerator::getIdentifier('Foo');
        $classGenerator = new ClassGenerator($className);
        $generated      = $strategy->generate($classGenerator);

        self::assertGreaterThan(0, strpos($generated, $className));
        self::assertTrue(class_exists($className, false));
    }

    /**
     * @covers \BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy::generate
     * @covers \BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy::__construct
     */
    public function testGenerateWithDisabledEval(): void
    {
        if (! ini_get('suhosin.executor.disable_eval')) {
            self::markTestSkipped('Ini setting "suhosin.executor.disable_eval" is needed to run this test');
        }

        $strategy       = new EvaluatingGeneratorStrategy();
        $className      = 'Foo' . uniqid();
        $classGenerator = new ClassGenerator($className);
        $generated      = $strategy->generate($classGenerator);

        self::assertGreaterThan(0, strpos($generated, $className));
        self::assertTrue(class_exists($className, false));
    }
}
