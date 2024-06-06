<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use BookiesProxyManager\Generator\Util\UniqueIdentifierGenerator;
use BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use BookiesProxyManager\Proxy\RemoteObjectInterface;
use BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use BookiesProxyManager\ProxyGenerator\RemoteObjectGenerator;
use BookiesProxyManagerTestAsset\BaseClass;
use BookiesProxyManagerTestAsset\BaseInterface;
use BookiesProxyManagerTestAsset\ClassWithByRefMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithMixedProperties;
use BookiesProxyManagerTestAsset\ClassWithMixedReferenceableTypedProperties;
use BookiesProxyManagerTestAsset\ClassWithMixedTypedProperties;
use BookiesProxyManagerTestAsset\ClassWithPhp80TypedMethods;
use ReflectionClass;

use function array_diff;

use const PHP_VERSION_ID;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\RemoteObjectGenerator}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\RemoteObjectGenerator
 * @group Coverage
 */
final class RemoteObjectGeneratorTest extends AbstractProxyGeneratorTest
{
    /**
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
     */
    public function testGeneratesValidCode(string $className): void
    {
        $generator          = $this->getProxyGenerator();
        $generatedClassName = UniqueIdentifierGenerator::getIdentifier('AbstractProxyGeneratorTest');
        $generatedClass     = new ClassGenerator($generatedClassName);
        $originalClass      = new ReflectionClass($className);
        $generatorStrategy  = new EvaluatingGeneratorStrategy();

        $generator->generate($originalClass, $generatedClass);
        $generatorStrategy->generate($generatedClass);

        $generatedReflection = new ReflectionClass($generatedClassName);

        if ($originalClass->isInterface()) {
            self::assertTrue($generatedReflection->implementsInterface($className));
        } else {
            self::assertEmpty(
                array_diff($originalClass->getInterfaceNames(), $generatedReflection->getInterfaceNames())
            );
        }

        self::assertSame($generatedClassName, $generatedReflection->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            self::assertTrue($generatedReflection->implementsInterface($interface));
        }
    }

    protected function getProxyGenerator(): ProxyGeneratorInterface
    {
        return new RemoteObjectGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [
            RemoteObjectInterface::class,
        ];
    }

    /** @return string[][] */
    public function getTestedImplementations(): array
    {
        $implementations = [
            [BaseClass::class],
            [ClassWithMagicMethods::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithMixedProperties::class],
            [ClassWithMixedTypedProperties::class],
            [ClassWithMixedReferenceableTypedProperties::class],
            [BaseInterface::class],
        ];

        if (PHP_VERSION_ID >= 80000) {
            $implementations[] = [ClassWithPhp80TypedMethods::class];
        }

        return $implementations;
    }
}
