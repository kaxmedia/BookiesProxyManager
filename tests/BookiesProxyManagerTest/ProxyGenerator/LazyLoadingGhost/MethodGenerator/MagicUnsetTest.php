<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use BookiesProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use BookiesProxyManagerTestAsset\ClassWithMagicMethods;
use BookiesProxyManagerTestAsset\ProxyGenerator\LazyLoading\MethodGenerator\ClassWithTwoPublicProperties;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset}
 *
 * @group Coverage
 */
final class MagicUnsetTest extends TestCase
{
    /** @var PropertyGenerator&MockObject */
    private PropertyGenerator $initializer;

    /** @var MethodGenerator&MockObject */
    private MethodGenerator $initMethod;

    /** @var PublicPropertiesMap&MockObject */
    private PublicPropertiesMap $publicProperties;

    /** @var ProtectedPropertiesMap&MockObject */
    private ProtectedPropertiesMap $protectedProperties;

    /** @var PrivatePropertiesMap&MockObject */
    private PrivatePropertiesMap $privateProperties;

    private string $expectedCode = <<<'PHP'
$this->foo && $this->baz('__unset', array('name' => $name));

if (isset(self::$bar[$name])) {
    unset($this->$name);

    return;
}

if (isset(self::$baz[$name])) {
    // check protected property access via compatible class
    $callers      = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller       = isset($callers[1]) ? $callers[1] : [];
    $object       = isset($caller['object']) ? $caller['object'] : '';
    $expectedType = self::$baz[$name];

    if ($object instanceof $expectedType) {
        unset($this->$name);

        return;
    }

    $class = isset($caller['class']) ? $caller['class'] : '';

    if ($class === $expectedType || is_subclass_of($class, $expectedType) || $class === 'ReflectionProperty') {
        unset($this->$name);

        return;
    }
} elseif (isset(self::$tab[$name])) {
    // check private property access via same class
    $callers = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller  = isset($callers[1]) ? $callers[1] : [];
    $class   = isset($caller['class']) ? $caller['class'] : '';

    static $accessorCache = [];

    if (isset(self::$tab[$name][$class])) {
        $cacheKey = $class . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function ($instance) use ($name) {
                unset($instance->$name);
            }, null, $class);

        return $accessor($this);
    }

    if ('ReflectionProperty' === $class) {
        $tmpClass = key(self::$tab[$name]);
        $cacheKey = $tmpClass . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function ($instance) use ($name) {
                unset($instance->$name);
            }, null, $tmpClass);

        return $accessor($this);
    }
}
%A
PHP;

    protected function setUp(): void
    {
        $this->initializer         = $this->createMock(PropertyGenerator::class);
        $this->initMethod          = $this->createMock(MethodGenerator::class);
        $this->publicProperties    = $this->createMock(PublicPropertiesMap::class);
        $this->protectedProperties = $this->createMock(ProtectedPropertiesMap::class);
        $this->privateProperties   = $this->createMock(PrivatePropertiesMap::class);

        $this->initializer->method('getName')->willReturn('foo');
        $this->initMethod->method('getName')->willReturn('baz');
        $this->publicProperties->method('isEmpty')->willReturn(false);
        $this->publicProperties->method('getName')->willReturn('bar');
        $this->protectedProperties->method('getName')->willReturn('baz');
        $this->privateProperties->method('getName')->willReturn('tab');
    }

    /**
     * @covers \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset::__construct
     */
    public function testBodyStructure(): void
    {
        $magicIsset = new MagicUnset(
            new ReflectionClass(ClassWithTwoPublicProperties::class),
            $this->initializer,
            $this->initMethod,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties
        );

        self::assertSame('__unset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());
        self::assertStringMatchesFormat($this->expectedCode, $magicIsset->getBody());
    }

    /**
     * @covers \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset::__construct
     */
    public function testBodyStructureWithOverriddenMagicGet(): void
    {
        $magicIsset = new MagicUnset(
            new ReflectionClass(ClassWithMagicMethods::class),
            $this->initializer,
            $this->initMethod,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties
        );

        self::assertSame('__unset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());

        $body = $magicIsset->getBody();

        self::assertStringMatchesFormat($this->expectedCode, $body);
        self::assertStringMatchesFormat('%Areturn parent::__unset($name);', $body);
    }
}
