<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Generator\Util\UniqueIdentifierGenerator;
use BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use BookiesProxyManagerTestAsset\BaseClass;
use BookiesProxyManagerTestAsset\BaseInterface;
use BookiesProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use BookiesProxyManagerTestAsset\ClassWithByRefMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithMixedProperties;
use BookiesProxyManagerTestAsset\ClassWithMixedReferenceableTypedProperties;
use BookiesProxyManagerTestAsset\ClassWithMixedTypedProperties;
use BookiesProxyManagerTestAsset\ClassWithPhp80TypedMethods;
use BookiesProxyManagerTestAsset\IterableMethodTypeHintedInterface;
use BookiesProxyManagerTestAsset\ObjectMethodTypeHintedInterface;
use BookiesProxyManagerTestAsset\ReturnTypeHintedClass;
use BookiesProxyManagerTestAsset\ReturnTypeHintedInterface;
use BookiesProxyManagerTestAsset\VoidMethodTypeHintedClass;
use BookiesProxyManagerTestAsset\VoidMethodTypeHintedInterface;
use ReflectionClass;

use const PHP_VERSION_ID;

/**
 * Base test for proxy generators
 *
 * @group Coverage
 */
abstract class AbstractProxyGeneratorTest extends TestCase
{
    /**
     * @psalm-param class-string $className
     *
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
            $parentClass = $generatedReflection->getParentClass();

            self::assertInstanceOf(ReflectionClass::class, $parentClass);
            self::assertSame($originalClass->getName(), $parentClass->getName());
        }

        self::assertSame($generatedClassName, $generatedReflection->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            self::assertTrue($generatedReflection->implementsInterface($interface));
        }
    }

    /**
     * Retrieve a new generator instance
     */
    abstract protected function getProxyGenerator(): ProxyGeneratorInterface;

    /**
     * Retrieve interfaces that should be implemented by the generated code
     *
     * @return string[]
     * @psalm-return list<class-string>
     */
    abstract protected function getExpectedImplementedInterfaces(): array;

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
            [ClassWithAbstractPublicMethod::class],
            [BaseInterface::class],
            [ReturnTypeHintedClass::class],
            [VoidMethodTypeHintedClass::class],
            [ReturnTypeHintedInterface::class],
            [VoidMethodTypeHintedInterface::class],
            [IterableMethodTypeHintedInterface::class],
            [ObjectMethodTypeHintedInterface::class],
        ];

        if (PHP_VERSION_ID >= 80000) {
            $implementations[] = [ClassWithPhp80TypedMethods::class];
        }

        return $implementations;
    }
}
