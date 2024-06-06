<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Factory;

use Laminas\Code\Generator\ClassGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Autoloader\AutoloaderInterface;
use BookiesProxyManager\Configuration;
use BookiesProxyManager\Factory\RemoteObject\AdapterInterface;
use BookiesProxyManager\Factory\RemoteObjectFactory;
use BookiesProxyManager\Generator\Util\UniqueIdentifierGenerator;
use BookiesProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use BookiesProxyManager\Inflector\ClassNameInflectorInterface;
use BookiesProxyManager\Signature\ClassSignatureGeneratorInterface;
use BookiesProxyManager\Signature\SignatureCheckerInterface;
use BookiesProxyManagerTestAsset\BaseInterface;
use BookiesProxyManagerTestAsset\RemoteProxy\RemoteObjectMock;
use stdClass;

/**
 * @covers \BookiesProxyManager\Factory\AbstractBaseFactory
 * @covers \BookiesProxyManager\Factory\RemoteObjectFactory
 * @group Coverage
 */
final class RemoteObjectFactoryTest extends TestCase
{
    /** @var ClassNameInflectorInterface&MockObject */
    private ClassNameInflectorInterface $inflector;

    /** @var SignatureCheckerInterface&MockObject */
    private SignatureCheckerInterface $signatureChecker;

    /** @var ClassSignatureGeneratorInterface&MockObject */
    private ClassSignatureGeneratorInterface $classSignatureGenerator;

    /** @var Configuration&MockObject */
    private Configuration $config;

    protected function setUp(): void
    {
        $this->config                  = $this->createMock(Configuration::class);
        $this->inflector               = $this->createMock(ClassNameInflectorInterface::class);
        $this->signatureChecker        = $this->createMock(SignatureCheckerInterface::class);
        $this->classSignatureGenerator = $this->createMock(ClassSignatureGeneratorInterface::class);

        $this
            ->config
            ->method('getClassNameInflector')
            ->willReturn($this->inflector);

        $this
            ->config
            ->method('getSignatureChecker')
            ->willReturn($this->signatureChecker);

        $this
            ->config
            ->method('getClassSignatureGenerator')
            ->willReturn($this->classSignatureGenerator);
    }

    public function testWillSkipAutoGeneration(): void
    {
        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with(BaseInterface::class)
            ->willReturn(RemoteObjectMock::class);

        $adapter = $this->createMock(AdapterInterface::class);
        $factory = new RemoteObjectFactory($adapter, $this->config);

        $proxy = $factory->createProxy(BaseInterface::class);

        self::assertInstanceOf(RemoteObjectMock::class, $proxy);
    }

    /**
     * {@inheritDoc}
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration(): void
    {
        $proxyClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator      = $this->createMock(GeneratorStrategyInterface::class);
        $autoloader     = $this->createMock(AutoloaderInterface::class);

        $this->config->method('getGeneratorStrategy')->willReturn($generator);
        $this->config->method('getProxyAutoloader')->willReturn($autoloader);

        $generator
            ->expects(self::once())
            ->method('generate')
            ->with(
                self::callback(
                    static fn (ClassGenerator $targetClass): bool => $targetClass->getName() === $proxyClassName
                )
            );

        // simulate autoloading
        $autoloader
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxyClassName)
            ->willReturnCallback(static function () use ($proxyClassName): bool {
                eval(
                    'class ' . $proxyClassName . ' implements \BookiesProxyManager\Proxy\RemoteObjectInterface {'
                    . 'public static function staticProxyConstructor() : self { return new static(); }'
                    . '}'
                );

                return true;
            });

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with(BaseInterface::class)
            ->willReturn($proxyClassName);

        $this
            ->inflector
            ->expects(self::once())
            ->method('getUserClassName')
            ->with(BaseInterface::class)
            ->willReturn(stdClass::class);

        $this->signatureChecker->expects(self::atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects(self::once())->method('addSignature')->will(self::returnArgument(0));

        $adapter = $this->createMock(AdapterInterface::class);
        $factory = new RemoteObjectFactory($adapter, $this->config);
        $factory->createProxy(BaseInterface::class);
    }
}
