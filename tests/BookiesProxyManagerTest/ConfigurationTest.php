<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Autoloader\AutoloaderInterface;
use BookiesProxyManager\Configuration;
use BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use BookiesProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use BookiesProxyManager\Inflector\ClassNameInflectorInterface;
use BookiesProxyManager\Signature\ClassSignatureGeneratorInterface;
use BookiesProxyManager\Signature\SignatureCheckerInterface;
use BookiesProxyManager\Signature\SignatureGeneratorInterface;

/**
 * Tests for {@see \BookiesProxyManager\Configuration}
 *
 * @group Coverage
 */
final class ConfigurationTest extends TestCase
{
    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
    }

    /**
     * @covers \BookiesProxyManager\Configuration::getProxiesNamespace
     * @covers \BookiesProxyManager\Configuration::setProxiesNamespace
     */
    public function testGetSetProxiesNamespace(): void
    {
        self::assertSame(
            'ProxyManagerGeneratedProxy',
            $this->configuration->getProxiesNamespace(),
            'Default setting check for BC'
        );

        $this->configuration->setProxiesNamespace('foo');
        self::assertSame('foo', $this->configuration->getProxiesNamespace());
    }

    /**
     * @covers \BookiesProxyManager\Configuration::getClassNameInflector
     * @covers \BookiesProxyManager\Configuration::setClassNameInflector
     */
    public function testSetGetClassNameInflector(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(ClassNameInflectorInterface::class, $this->configuration->getClassNameInflector());

        $inflector = $this->createMock(ClassNameInflectorInterface::class);

        $this->configuration->setClassNameInflector($inflector);
        self::assertSame($inflector, $this->configuration->getClassNameInflector());
    }

    /**
     * @covers \BookiesProxyManager\Configuration::getGeneratorStrategy
     */
    public function testDefaultGeneratorStrategyNeedToBeAInstanceOfEvaluatingGeneratorStrategy(): void
    {
        self::assertInstanceOf(EvaluatingGeneratorStrategy::class, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \BookiesProxyManager\Configuration::getGeneratorStrategy
     * @covers \BookiesProxyManager\Configuration::setGeneratorStrategy
     */
    public function testSetGetGeneratorStrategy(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(GeneratorStrategyInterface::class, $this->configuration->getGeneratorStrategy());

        $strategy = $this->createMock(GeneratorStrategyInterface::class);

        $this->configuration->setGeneratorStrategy($strategy);
        self::assertSame($strategy, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \BookiesProxyManager\Configuration::getProxiesTargetDir
     * @covers \BookiesProxyManager\Configuration::setProxiesTargetDir
     */
    public function testSetGetProxiesTargetDir(): void
    {
        self::assertDirectoryExists($this->configuration->getProxiesTargetDir());

        $this->configuration->setProxiesTargetDir(__DIR__);
        self::assertSame(__DIR__, $this->configuration->getProxiesTargetDir());
    }

    /**
     * @covers \BookiesProxyManager\Configuration::getProxyAutoloader
     * @covers \BookiesProxyManager\Configuration::setProxyAutoloader
     */
    public function testSetGetProxyAutoloader(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(AutoloaderInterface::class, $this->configuration->getProxyAutoloader());

        $autoloader = $this->createMock(AutoloaderInterface::class);

        $this->configuration->setProxyAutoloader($autoloader);
        self::assertSame($autoloader, $this->configuration->getProxyAutoloader());
    }

    /**
     * @covers \BookiesProxyManager\Configuration::getSignatureGenerator
     * @covers \BookiesProxyManager\Configuration::setSignatureGenerator
     */
    public function testSetGetSignatureGenerator(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(SignatureCheckerInterface::class, $this->configuration->getSignatureChecker());

        $signatureGenerator = $this->createMock(SignatureGeneratorInterface::class);

        $this->configuration->setSignatureGenerator($signatureGenerator);
        self::assertSame($signatureGenerator, $this->configuration->getSignatureGenerator());
    }

    /**
     * @covers \BookiesProxyManager\Configuration::getSignatureChecker
     * @covers \BookiesProxyManager\Configuration::setSignatureChecker
     */
    public function testSetGetSignatureChecker(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(SignatureCheckerInterface::class, $this->configuration->getSignatureChecker());

        $signatureChecker = $this->createMock(SignatureCheckerInterface::class);

        $this->configuration->setSignatureChecker($signatureChecker);
        self::assertSame($signatureChecker, $this->configuration->getSignatureChecker());
    }

    /**
     * @covers \BookiesProxyManager\Configuration::getClassSignatureGenerator
     * @covers \BookiesProxyManager\Configuration::setClassSignatureGenerator
     */
    public function testSetGetClassSignatureGenerator(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(
            ClassSignatureGeneratorInterface::class,
            $this->configuration->getClassSignatureGenerator()
        );
        $classSignatureGenerator = $this->createMock(ClassSignatureGeneratorInterface::class);

        $this->configuration->setClassSignatureGenerator($classSignatureGenerator);
        self::assertSame($classSignatureGenerator, $this->configuration->getClassSignatureGenerator());
    }
}
