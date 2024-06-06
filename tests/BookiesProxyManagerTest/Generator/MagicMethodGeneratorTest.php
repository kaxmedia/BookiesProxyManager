<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Generator;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Generator\MagicMethodGenerator;
use BookiesProxyManagerTestAsset\ClassWithByRefMagicMethods;
use BookiesProxyManagerTestAsset\ClassWithMagicMethods;
use BookiesProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\Generator\MagicMethodGenerator}
 *
 * @group Coverage
 * @covers \BookiesProxyManager\Generator\MagicMethodGenerator
 */
final class MagicMethodGeneratorTest extends TestCase
{
    public function testGeneratesCorrectByRefReturnValue(): void
    {
        $reflection  = new ReflectionClass(ClassWithByRefMagicMethods::class);
        $magicMethod = new MagicMethodGenerator($reflection, '__get', ['name']);

        self::assertStringMatchesFormat('%Apublic function & __get(%A', $magicMethod->generate());
    }

    public function testGeneratesCorrectByValReturnValue(): void
    {
        $reflection  = new ReflectionClass(ClassWithMagicMethods::class);
        $magicMethod = new MagicMethodGenerator($reflection, '__get', ['name']);

        self::assertStringMatchesFormat('%Apublic function __get(%A', $magicMethod->generate());
    }

    public function testGeneratesByRefReturnValueWithNonExistingGetMethod(): void
    {
        $reflection  = new ReflectionClass(EmptyClass::class);
        $magicMethod = new MagicMethodGenerator($reflection, '__get', ['name']);

        self::assertStringMatchesFormat('%Apublic function & __get(%A', $magicMethod->generate());
    }

    public function testGeneratesByValReturnValueWithNonExistingNonGetMethod(): void
    {
        $reflection  = new ReflectionClass(EmptyClass::class);
        $magicMethod = new MagicMethodGenerator($reflection, '__set', ['name']);

        self::assertStringMatchesFormat('%Apublic function __set(%A', $magicMethod->generate());
    }
}
