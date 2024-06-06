<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Factory;

use Laminas\Code\Generator\ClassGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Autoloader\AutoloaderInterface;
use BookiesProxyManager\Configuration;
use BookiesProxyManager\Factory\NullObjectFactory;
use BookiesProxyManager\Generator\Util\UniqueIdentifierGenerator;
use BookiesProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use BookiesProxyManager\Inflector\ClassNameInflectorInterface;
use BookiesProxyManager\Signature\ClassSignatureGeneratorInterface;
use BookiesProxyManager\Signature\SignatureCheckerInterface;
use BookiesProxyManagerTestAsset\NullObjectMock;
use stdClass;

/**
 * @covers \BookiesProxyManager\Factory\AbstractBaseFactory
 * @covers \BookiesProxyManager\Factory\NullObjectFactory
 * @group Coverage
 */
final class NullObjectFactoryTest extends TestCase
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

    /**
     * {@inheritDoc}
     *
     * @covers \BookiesProxyManager\Factory\NullObjectFactory::__construct
     * @covers \BookiesProxyManager\Factory\NullObjectFactory::createProxy
     * @covers \BookiesProxyManager\Factory\NullObjectFactory::getGenerator
     */
    public function testWillSkipAutoGeneration(): void
    {
        $instance = new stdClass();

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with(stdClass::class)
            ->willReturn(NullObjectMock::class);

        (new NullObjectFactory($this->config))->createProxy($instance);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \BookiesProxyManager\Factory\NullObjectFactory::__construct
     * @covers \BookiesProxyManager\Factory\NullObjectFactory::createProxy
     * @covers \BookiesProxyManager\Factory\NullObjectFactory::getGenerator
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration(): void
    {
        $instance       = new stdClass();
        $proxyClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator      = $this->createMock(GeneratorStrategyInterface::class);
        $autoloader     = $this->createMock(AutoloaderInterface::class);

        $this->config->method('getGeneratorStrategy')->will(self::returnValue($generator));
        $this->config->method('getProxyAutoloader')->will(self::returnValue($autoloader));

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
                eval('class ' . $proxyClassName . ' extends \\BookiesProxyManagerTestAsset\\NullObjectMock {}');

                return true;
            });

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with(stdClass::class)
            ->willReturn($proxyClassName);

        $this
            ->inflector
            ->expects(self::once())
            ->method('getUserClassName')
            ->with(stdClass::class)
            ->willReturn(NullObjectMock::class);

        $this->signatureChecker->expects(self::atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects(self::once())->method('addSignature')->will(self::returnArgument(0));

        $factory = new NullObjectFactory($this->config);
        $factory->createProxy($instance);
    }
}
