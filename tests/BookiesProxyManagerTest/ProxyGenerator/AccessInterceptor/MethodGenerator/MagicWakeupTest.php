<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\AccessInterceptor\MethodGenerator;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;
use BookiesProxyManagerTestAsset\ClassWithMixedProperties;
use BookiesProxyManagerTestAsset\EmptyClass;
use BookiesProxyManagerTestAsset\ProxyGenerator\LazyLoading\MethodGenerator\ClassWithTwoPublicProperties;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup}
 *
 * @group Coverage
 * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup
 */
final class MagicWakeupTest extends TestCase
{
    public function testBodyStructure(): void
    {
        $reflection = new ReflectionClass(
            ClassWithTwoPublicProperties::class
        );

        $magicWakeup = new MagicWakeup($reflection);

        self::assertSame('__wakeup', $magicWakeup->getName());
        self::assertCount(0, $magicWakeup->getParameters());
        self::assertSame("unset(\$this->bar, \$this->baz);\n\n", $magicWakeup->getBody());
    }

    public function testBodyStructureWithoutPublicProperties(): void
    {
        $magicWakeup = new MagicWakeup(new ReflectionClass(EmptyClass::class));

        self::assertSame('__wakeup', $magicWakeup->getName());
        self::assertCount(0, $magicWakeup->getParameters());
        self::assertEmpty($magicWakeup->getBody());
    }

    /**
     * @group 276
     */
    public function testWillUnsetPrivateProperties(): void
    {
        $magicWakeup = new MagicWakeup(new ReflectionClass(ClassWithMixedProperties::class));

        self::assertSame(
            'unset($this->publicProperty0, $this->publicProperty1, $this->publicProperty2, '
            . '$this->protectedProperty0, $this->protectedProperty1, $this->protectedProperty2);

\Closure::bind(function (\BookiesProxyManagerTestAsset\ClassWithMixedProperties $instance) {
    unset($instance->privateProperty0, $instance->privateProperty1, $instance->privateProperty2);
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithMixedProperties\')->__invoke($this);

',
            $magicWakeup->getBody()
        );
    }
}
