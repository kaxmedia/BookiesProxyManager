<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties;
use BookiesProxyManagerTestAsset\ClassWithMixedProperties;
use BookiesProxyManagerTestAsset\ClassWithMixedReferenceableTypedProperties;
use BookiesProxyManagerTestAsset\ClassWithPrivateProperties;
use BookiesProxyManagerTestAsset\ClassWithProtectedProperties;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties
 * @group Coverage
 */
final class BindProxyPropertiesTest extends TestCase
{
    /** @var PropertyGenerator&MockObject */
    private PropertyGenerator $prefixInterceptors;

    /** @var PropertyGenerator&MockObject */
    private PropertyGenerator $suffixInterceptors;

    protected function setUp(): void
    {
        $this->prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $this->suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $this->prefixInterceptors->method('getName')->willReturn('pre');
        $this->suffixInterceptors->method('getName')->willReturn('post');
    }

    public function testSignature(): void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithProtectedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );
        self::assertSame('bindProxyProperties', $method->getName());
        self::assertSame('private', $method->getVisibility());
        self::assertFalse($method->isStatic());

        $parameters = $method->getParameters();

        self::assertCount(3, $parameters);

        self::assertSame(
            ClassWithProtectedProperties::class,
            $parameters['localizedObject']->getType()
        );
        self::assertSame('array', $parameters['prefixInterceptors']->getType());
        self::assertSame('array', $parameters['suffixInterceptors']->getType());
    }

    public function testBodyStructure(): void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithMixedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $expectedCode = <<<'PHP'
$this->publicProperty0 = & $localizedObject->publicProperty0;

$this->publicProperty1 = & $localizedObject->publicProperty1;

$this->publicProperty2 = & $localizedObject->publicProperty2;

$this->protectedProperty0 = & $localizedObject->protectedProperty0;

$this->protectedProperty1 = & $localizedObject->protectedProperty1;

$this->protectedProperty2 = & $localizedObject->protectedProperty2;

\Closure::bind(function () use ($localizedObject) {
    $this->privateProperty0 = & $localizedObject->privateProperty0;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateProperty1 = & $localizedObject->privateProperty1;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateProperty2 = & $localizedObject->privateProperty2;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedProperties')->__invoke();

$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;
PHP;

        self::assertSame($expectedCode, $method->getBody());
    }

    public function testBodyStructureWithProtectedProperties(): void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithProtectedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        self::assertSame(
            '$this->property0 = & $localizedObject->property0;

$this->property1 = & $localizedObject->property1;

$this->property2 = & $localizedObject->property2;

$this->property3 = & $localizedObject->property3;

$this->property4 = & $localizedObject->property4;

$this->property5 = & $localizedObject->property5;

$this->property6 = & $localizedObject->property6;

$this->property7 = & $localizedObject->property7;

$this->property8 = & $localizedObject->property8;

$this->property9 = & $localizedObject->property9;

$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;',
            $method->getBody()
        );
    }

    public function testBodyStructureWithPrivateProperties(): void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithPrivateProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        self::assertSame(
            '\Closure::bind(function () use ($localizedObject) {
    $this->property0 = & $localizedObject->property0;
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property1 = & $localizedObject->property1;
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property2 = & $localizedObject->property2;
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property3 = & $localizedObject->property3;
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property4 = & $localizedObject->property4;
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property5 = & $localizedObject->property5;
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property6 = & $localizedObject->property6;
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property7 = & $localizedObject->property7;
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property8 = & $localizedObject->property8;
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property9 = & $localizedObject->property9;
}, $this, \'BookiesProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;',
            $method->getBody()
        );
    }

    public function testBodyStructureWithTypedProperties(): void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithMixedReferenceableTypedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        self::assertSame(
            <<<'PHP'
$this->publicUnTypedProperty = & $localizedObject->publicUnTypedProperty;

$this->publicBoolProperty = & $localizedObject->publicBoolProperty;

$this->publicNullableBoolProperty = & $localizedObject->publicNullableBoolProperty;

$this->publicIntProperty = & $localizedObject->publicIntProperty;

$this->publicNullableIntProperty = & $localizedObject->publicNullableIntProperty;

$this->publicFloatProperty = & $localizedObject->publicFloatProperty;

$this->publicNullableFloatProperty = & $localizedObject->publicNullableFloatProperty;

$this->publicStringProperty = & $localizedObject->publicStringProperty;

$this->publicNullableStringProperty = & $localizedObject->publicNullableStringProperty;

$this->publicArrayProperty = & $localizedObject->publicArrayProperty;

$this->publicNullableArrayProperty = & $localizedObject->publicNullableArrayProperty;

$this->publicIterableProperty = & $localizedObject->publicIterableProperty;

$this->publicNullableIterableProperty = & $localizedObject->publicNullableIterableProperty;

$this->protectedUnTypedProperty = & $localizedObject->protectedUnTypedProperty;

$this->protectedBoolProperty = & $localizedObject->protectedBoolProperty;

$this->protectedNullableBoolProperty = & $localizedObject->protectedNullableBoolProperty;

$this->protectedIntProperty = & $localizedObject->protectedIntProperty;

$this->protectedNullableIntProperty = & $localizedObject->protectedNullableIntProperty;

$this->protectedFloatProperty = & $localizedObject->protectedFloatProperty;

$this->protectedNullableFloatProperty = & $localizedObject->protectedNullableFloatProperty;

$this->protectedStringProperty = & $localizedObject->protectedStringProperty;

$this->protectedNullableStringProperty = & $localizedObject->protectedNullableStringProperty;

$this->protectedArrayProperty = & $localizedObject->protectedArrayProperty;

$this->protectedNullableArrayProperty = & $localizedObject->protectedNullableArrayProperty;

$this->protectedIterableProperty = & $localizedObject->protectedIterableProperty;

$this->protectedNullableIterableProperty = & $localizedObject->protectedNullableIterableProperty;

\Closure::bind(function () use ($localizedObject) {
    $this->privateUnTypedProperty = & $localizedObject->privateUnTypedProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateBoolProperty = & $localizedObject->privateBoolProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateNullableBoolProperty = & $localizedObject->privateNullableBoolProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateIntProperty = & $localizedObject->privateIntProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateNullableIntProperty = & $localizedObject->privateNullableIntProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateFloatProperty = & $localizedObject->privateFloatProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateNullableFloatProperty = & $localizedObject->privateNullableFloatProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateStringProperty = & $localizedObject->privateStringProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateNullableStringProperty = & $localizedObject->privateNullableStringProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateArrayProperty = & $localizedObject->privateArrayProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateNullableArrayProperty = & $localizedObject->privateNullableArrayProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateIterableProperty = & $localizedObject->privateIterableProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateNullableIterableProperty = & $localizedObject->privateNullableIterableProperty;
}, $this, 'BookiesProxyManagerTestAsset\\ClassWithMixedReferenceableTypedProperties')->__invoke();

$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;
PHP
            ,
            $method->getBody()
        );
    }
}
