<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet;
use BookiesProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use BookiesProxyManagerTestAsset\ClassWithMagicMethods;
use BookiesProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet}
 *
 * @group Coverage
 */
final class MagicGetTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection       = new ReflectionClass(EmptyClass::class);
        $initializer      = $this->createMock(PropertyGenerator::class);
        $valueHolder      = $this->createMock(PropertyGenerator::class);
        $publicProperties = $this->createMock(PublicPropertiesMap::class);

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');
        $publicProperties->method('isEmpty')->willReturn(false);
        $publicProperties->method('getName')->willReturn('bar');

        $magicGet = new MagicGet($reflection, $initializer, $valueHolder, $publicProperties);

        self::assertSame('__get', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());
        self::assertStringMatchesFormat(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, '__get', ['name' => \$name]"
            . ", \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return \$this->bar->\$name;\n}"
            . '%areturn %s;',
            $magicGet->getBody()
        );
        self::assertStringNotMatchesFormat('%Areturn $this->bar->__get($name);%A', $magicGet->getBody());
    }

    /**
     * @group 344
     */
    public function testBodyStructureWithPreExistingGetMethod(): void
    {
        $reflection       = new ReflectionClass(ClassWithMagicMethods::class);
        $initializer      = $this->createMock(PropertyGenerator::class);
        $valueHolder      = $this->createMock(PropertyGenerator::class);
        $publicProperties = $this->createMock(PublicPropertiesMap::class);

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');
        $publicProperties->method('isEmpty')->willReturn(false);
        $publicProperties->method('getName')->willReturn('bar');

        $magicGet = new MagicGet($reflection, $initializer, $valueHolder, $publicProperties);

        self::assertSame('__get', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());

        self::assertStringMatchesFormat(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, '__get', ['name' => \$name]"
            . ", \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return \$this->bar->\$name;\n}\n\n"
            . 'return $this->bar->__get($name);',
            $magicGet->getBody(),
            'The pre-existing magic `__get` is called, if the property is not accessible'
        );
    }
}
