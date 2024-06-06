<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Functional;

use Generator;
use Laminas\Server\Client;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Factory\RemoteObject\Adapter\JsonRpc as JsonRpcAdapter;
use BookiesProxyManager\Factory\RemoteObject\Adapter\XmlRpc as XmlRpcAdapter;
use BookiesProxyManager\Factory\RemoteObject\AdapterInterface;
use BookiesProxyManager\Factory\RemoteObjectFactory;
use BookiesProxyManagerTestAsset\ClassWithPublicStringNullableNullDefaultTypedProperty;
use BookiesProxyManagerTestAsset\ClassWithPublicStringNullableTypedProperty;
use BookiesProxyManagerTestAsset\ClassWithPublicStringTypedProperty;
use BookiesProxyManagerTestAsset\ClassWithSelfHint;
use BookiesProxyManagerTestAsset\OtherObjectAccessClass;
use BookiesProxyManagerTestAsset\RemoteProxy\BazServiceInterface;
use BookiesProxyManagerTestAsset\RemoteProxy\Foo;
use BookiesProxyManagerTestAsset\RemoteProxy\FooServiceInterface;
use BookiesProxyManagerTestAsset\RemoteProxy\RemoteServiceWithDefaultsAndVariadicArguments;
use BookiesProxyManagerTestAsset\RemoteProxy\RemoteServiceWithDefaultsInterface;
use BookiesProxyManagerTestAsset\RemoteProxy\VariadicArgumentsServiceInterface;
use BookiesProxyManagerTestAsset\VoidCounter;
use ReflectionClass;

use function assert;
use function is_callable;
use function random_int;
use function ucfirst;
use function uniqid;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\RemoteObjectGenerator} produced objects
 *
 * @group Functional
 * @coversNothing
 */
final class RemoteObjectFunctionalTest extends TestCase
{
    /**
     * @param mixed[] $parametersExpectedByClient
     */
    protected function getXmlRpcAdapter(mixed $expectedValue, string $method, array $parametersExpectedByClient): XmlRpcAdapter
    {
        $client = $this->getMockBuilder(Client::class)->getMock();

        $client
            ->method('call')
            ->with(self::stringEndsWith($method), $parametersExpectedByClient)
            ->willReturn($expectedValue);

        return new XmlRpcAdapter(
            $client,
            ['BookiesProxyManagerTestAsset\RemoteProxy\Foo.foo' => 'BookiesProxyManagerTestAsset\RemoteProxy\FooServiceInterface.foo']
        );
    }

    /**
     * @param mixed[] $params
     */
    protected function getJsonRpcAdapter(mixed $expectedValue, string $method, array $params): JsonRpcAdapter
    {
        $client = $this->getMockBuilder(Client::class)->getMock();

        $client
            ->method('call')
            ->with(self::stringEndsWith($method), $params)
            ->willReturn($expectedValue);

        return new JsonRpcAdapter(
            $client,
            ['BookiesProxyManagerTestAsset\RemoteProxy\Foo.foo' => 'BookiesProxyManagerTestAsset\RemoteProxy\FooServiceInterface.foo']
        );
    }

    /**
     * @param array|mixed[] $passedParams
     * @param mixed[]       $callParametersExpectedByAdapter
     * @psalm-param class-string<OriginalClass>|OriginalClass $instanceOrClassName
     *
     * @dataProvider getProxyMethods
     * @psalm-template OriginalClass of object
     */
    public function testXmlRpcMethodCalls(
        string|object $instanceOrClassName,
        string $method,
        array $passedParams,
        array $callParametersExpectedByAdapter,
        mixed $expectedValue
    ): void {
        $proxy = (new RemoteObjectFactory($this->getXmlRpcAdapter($expectedValue, $method, $callParametersExpectedByAdapter)))
            ->createProxy($instanceOrClassName);

        $callback = [$proxy, $method];

        self::assertIsCallable($callback);
        self::assertSame($expectedValue, $callback(...$passedParams));
    }

    /**
     * @param array|mixed[] $passedParams
     * @param mixed[]       $parametersForProxy
     * @psalm-param class-string<OriginalClass>|OriginalClass $instanceOrClassName
     *
     * @dataProvider getProxyMethods
     * @psalm-template OriginalClass of object
     */
    public function testJsonRpcMethodCalls(
        string|object $instanceOrClassName,
        string $method,
        array $passedParams,
        array $parametersForProxy,
        mixed $expectedValue
    ): void {
        $proxy = (new RemoteObjectFactory($this->getJsonRpcAdapter($expectedValue, $method, $parametersForProxy)))
            ->createProxy($instanceOrClassName);

        $callback = [$proxy, $method];

        self::assertIsCallable($callback);
        self::assertSame($expectedValue, $callback(...$passedParams));
    }

    /**
     * @psalm-param class-string<OriginalClass>|OriginalClass $instanceOrClassName
     *
     * @dataProvider getPropertyAccessProxies
     * @psalm-template OriginalClass of object
     */
    public function testJsonRpcPropertyReadAccess(string|object $instanceOrClassName, string $publicProperty, mixed $propertyValue): void
    {
        $proxy = (new RemoteObjectFactory($this->getJsonRpcAdapter($propertyValue, '__get', [$publicProperty])))
            ->createProxy($instanceOrClassName);

        self::assertSame($propertyValue, $proxy->$publicProperty);
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result
     *
     * @return string[][]|bool[][]|object[][]|mixed[][][]
     */
    public function getProxyMethods(): array
    {
        $selfHintParam = new ClassWithSelfHint();

        return [
            [
                FooServiceInterface::class,
                'foo',
                [],
                [],
                'bar remote',
            ],
            [
                Foo::class,
                'foo',
                [],
                [],
                'bar remote',
            ],
            [
                new Foo(),
                'foo',
                [],
                [],
                'bar remote',
            ],
            [
                BazServiceInterface::class,
                'baz',
                ['baz'],
                ['baz'],
                'baz remote',
            ],
            [
                new ClassWithSelfHint(),
                'selfHintMethod',
                [$selfHintParam],
                [$selfHintParam],
                $selfHintParam,
            ],
            [
                VariadicArgumentsServiceInterface::class,
                'method',
                ['aaa', 1, 2, 3, 4, 5],
                ['aaa', 1, 2, 3, 4, 5],
                true,
            ],
            [
                RemoteServiceWithDefaultsInterface::class,
                'optionalNonNullable',
                ['aaa'],
                ['aaa', 'Optional parameter to be kept during calls'],
                200,
            ],
            [
                RemoteServiceWithDefaultsInterface::class,
                'optionalNullable',
                ['aaa'],
                ['aaa', null],
                200,
            ],
            'when passing only the required parameters' => [
                RemoteServiceWithDefaultsInterface::class,
                'manyRequiredWithManyOptional',
                ['aaa', 100],
                [
                    'aaa',
                    100,
                    'Optional parameter to be kept during calls',
                    100,
                    'Yet another optional parameter to be kept during calls',
                ],
                200,
            ],
            'when passing required params and one optional params' => [
                RemoteServiceWithDefaultsInterface::class,
                'manyRequiredWithManyOptional',
                ['aaa', 100, 'passed'],
                [
                    'aaa',
                    100,
                    'passed',
                    100,
                    'Yet another optional parameter to be kept during calls',
                ],
                200,
            ],
            'when passing required params and some optional params' => [
                RemoteServiceWithDefaultsInterface::class,
                'manyRequiredWithManyOptional',
                ['aaa', 100, 'passed', 90],
                [
                    'aaa',
                    100,
                    'passed',
                    90,
                    'Yet another optional parameter to be kept during calls',
                ],
                200,
            ],
            'when passing only required for method with optional and variadic params' => [
                RemoteServiceWithDefaultsAndVariadicArguments::class,
                'optionalWithVariadic',
                ['aaa'],
                [
                    'aaa',
                    'Optional param to be kept on proxy call',
                ],
                200,
            ],
            'when passing required, optional and variadic params' => [
                RemoteServiceWithDefaultsAndVariadicArguments::class,
                'optionalWithVariadic',
                ['aaa', 'Optional param to be kept on proxy call', 10, 20, 30, 50, 90],
                [
                    'aaa',
                    'Optional param to be kept on proxy call',
                    10,
                    20,
                    30,
                    50,
                    90,
                ],
                200,
            ],
        ];
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return string[][]
     */
    public function getPropertyAccessProxies(): array
    {
        return [
            [
                FooServiceInterface::class,
                'publicProperty',
                'publicProperty remote',
            ],
            [
                ClassWithPublicStringTypedProperty::class,
                'typedProperty',
                'typedProperty remote',
            ],
            [
                ClassWithPublicStringNullableTypedProperty::class,
                'typedNullableProperty',
                'typedNullableProperty remote',
            ],
            [
                ClassWithPublicStringNullableNullDefaultTypedProperty::class,
                'typedNullableNullDefaultProperty',
                'typedNullableNullDefaultProperty remote',
            ],
        ];
    }

    /**
     * @group        276
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     */
    public function testWillInterceptAccessToPropertiesViaFriendClassAccess(
        object $callerObject,
        object $realInstance,
        string $method,
        string $expectedValue,
        string $propertyName
    ): void {
        $adapter = $this->createMock(AdapterInterface::class);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with($realInstance::class, '__get', [$propertyName])
            ->willReturn($expectedValue);

        $proxy = (new RemoteObjectFactory($adapter))
            ->createProxy($realInstance);

        $accessor = [$callerObject, $method];
        assert(is_callable($accessor));

        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group        276
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     */
    public function testWillInterceptAccessToPropertiesViaFriendClassAccessEvenIfCloned(
        object $callerObject,
        object $realInstance,
        string $method,
        string $expectedValue,
        string $propertyName
    ): void {
        $adapter = $this->createMock(AdapterInterface::class);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with($realInstance::class, '__get', [$propertyName])
            ->willReturn($expectedValue);

        $proxy = clone (new RemoteObjectFactory($adapter))
            ->createProxy($realInstance);

        $accessor = [$callerObject, $method];
        assert(is_callable($accessor));

        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group 327
     */
    public function testWillExecuteLogicInAVoidMethod(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);

        $increment = random_int(10, 1000);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with(VoidCounter::class, 'increment', [$increment])
            ->willReturn(random_int(10, 1000));

        $proxy = clone (new RemoteObjectFactory($adapter))
            ->createProxy(VoidCounter::class);

        $proxy->increment($increment);
    }

    public function getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope(): Generator
    {
        foreach ((new ReflectionClass(OtherObjectAccessClass::class))->getProperties() as $property) {
            $property->setAccessible(true);

            $propertyName  = $property->getName();
            $realInstance  = new OtherObjectAccessClass();
            $expectedValue = uniqid('', true);

            $property->setValue($realInstance, $expectedValue);

            yield OtherObjectAccessClass::class . '#$' . $propertyName => [
                new OtherObjectAccessClass(),
                $realInstance,
                'get' . ucfirst($propertyName),
                $expectedValue,
                $propertyName,
            ];
        }
    }
}
