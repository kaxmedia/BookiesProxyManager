<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Generator\MethodGenerator;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator;
use BookiesProxyManagerTestAsset\BaseClass;
use BookiesProxyManagerTestAsset\VoidMethodTypeHintedInterface;
use ReflectionMethod;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator}
 *
 * @group Coverage
 * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator
 */
final class InterceptorGeneratorTest extends TestCase
{
    public function testInterceptorGenerator(): void
    {
        $method             = $this->createMock(MethodGenerator::class);
        $bar                = $this->createMock(ParameterGenerator::class);
        $baz                = $this->createMock(ParameterGenerator::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->method('getName')->willReturn('bar');
        $baz->method('getName')->willReturn('baz');
        $method->method('getName')->willReturn('fooMethod');
        $method->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        // @codingStandardsIgnoreStart
        $expected = <<<'PHP'
if (isset($this->pre['fooMethod'])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->pre['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnEarly);

    if ($returnEarly) {
        return $prefixReturnValue;
    }
}

$returnValue = "foo";

if (isset($this->post['fooMethod'])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->post['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnValue, $returnEarly);

    if ($returnEarly) {
        return $suffixReturnValue;
    }
}

return $returnValue;
PHP;
        // @codingStandardsIgnoreEnd

        self::assertSame(
            $expected,
            InterceptorGenerator::createInterceptedMethodBody(
                '$returnValue = "foo";',
                $method,
                $prefixInterceptors,
                $suffixInterceptors,
                null
            )
        );
    }

    public function testInterceptorGeneratorWithVoidReturnType(): void
    {
        $method             = $this->createMock(MethodGenerator::class);
        $bar                = $this->createMock(ParameterGenerator::class);
        $baz                = $this->createMock(ParameterGenerator::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->method('getName')->willReturn('bar');
        $baz->method('getName')->willReturn('baz');
        $method->method('getName')->willReturn('fooMethod');
        $method->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        // @codingStandardsIgnoreStart
        $expected = <<<'PHP'
if (isset($this->pre['fooMethod'])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->pre['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnEarly);

    if ($returnEarly) {
        $prefixReturnValue;
return;
    }
}

$returnValue = "foo";

if (isset($this->post['fooMethod'])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->post['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnValue, $returnEarly);

    if ($returnEarly) {
        $suffixReturnValue;
return;
    }
}

$returnValue;
return;
PHP;
        // @codingStandardsIgnoreEnd

        self::assertSame(
            $expected,
            InterceptorGenerator::createInterceptedMethodBody(
                '$returnValue = "foo";',
                $method,
                $prefixInterceptors,
                $suffixInterceptors,
                new ReflectionMethod(VoidMethodTypeHintedInterface::class, 'returnVoid')
            )
        );
    }

    public function testInterceptorGeneratorWithExistingNonVoidMethod(): void
    {
        $method             = $this->createMock(MethodGenerator::class);
        $bar                = $this->createMock(ParameterGenerator::class);
        $baz                = $this->createMock(ParameterGenerator::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->method('getName')->willReturn('bar');
        $baz->method('getName')->willReturn('baz');
        $method->method('getName')->willReturn('fooMethod');
        $method->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        // @codingStandardsIgnoreStart
        $expected = <<<'PHP'
if (isset($this->pre['fooMethod'])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->pre['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnEarly);

    if ($returnEarly) {
        return $prefixReturnValue;
    }
}

$returnValue = "foo";

if (isset($this->post['fooMethod'])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->post['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnValue, $returnEarly);

    if ($returnEarly) {
        return $suffixReturnValue;
    }
}

return $returnValue;
PHP;
        // @codingStandardsIgnoreEnd

        self::assertSame(
            $expected,
            InterceptorGenerator::createInterceptedMethodBody(
                '$returnValue = "foo";',
                $method,
                $prefixInterceptors,
                $suffixInterceptors,
                new ReflectionMethod(BaseClass::class, 'publicMethod')
            )
        );
    }

    public function testInterceptorGeneratorWithReferences(): void
    {
        $method             = $this->createMock(MethodGenerator::class);
        $bar                = $this->createMock(ParameterGenerator::class);
        $baz                = $this->createMock(ParameterGenerator::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->method('getName')->willReturn('bar');
        $bar->method('getPassedByReference')->willReturn(false);
        $baz->method('getName')->willReturn('baz');
        $baz->method('getPassedByReference')->willReturn(true);
        $method->method('getName')->willReturn('fooMethod');
        $method->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        // @codingStandardsIgnoreStart
        $expected = <<<'PHP'
if (isset($this->pre['fooMethod'])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->pre['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => &$baz), $returnEarly);

    if ($returnEarly) {
        return $prefixReturnValue;
    }
}

$returnValue = "foo";

if (isset($this->post['fooMethod'])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->post['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => &$baz), $returnValue, $returnEarly);

    if ($returnEarly) {
        return $suffixReturnValue;
    }
}

return $returnValue;
PHP;
        // @codingStandardsIgnoreEnd

        self::assertSame(
            $expected,
            InterceptorGenerator::createInterceptedMethodBody(
                '$returnValue = "foo";',
                $method,
                $prefixInterceptors,
                $suffixInterceptors,
                new ReflectionMethod(BaseClass::class, 'publicMethod')
            )
        );
    }
}
