<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Autoloader;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Autoloader\Autoloader;
use BookiesProxyManager\FileLocator\FileLocatorInterface;
use BookiesProxyManager\Generator\Util\UniqueIdentifierGenerator;
use BookiesProxyManager\Inflector\ClassNameInflectorInterface;

use function class_exists;
use function file_put_contents;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;

/**
 * Tests for {@see \BookiesProxyManager\Autoloader\Autoloader}
 *
 * @covers \BookiesProxyManager\Autoloader\Autoloader
 * @group Coverage
 */
final class AutoloaderTest extends TestCase
{
    private Autoloader $autoloader;

    /** @var FileLocatorInterface&MockObject */
    private FileLocatorInterface $fileLocator;

    /** @var ClassNameInflectorInterface&MockObject */
    private ClassNameInflectorInterface $classNameInflector;

    /**
     * @covers \BookiesProxyManager\Autoloader\Autoloader::__construct
     */
    protected function setUp(): void
    {
        $this->fileLocator        = $this->createMock(FileLocatorInterface::class);
        $this->classNameInflector = $this->createMock(ClassNameInflectorInterface::class);
        $this->autoloader         = new Autoloader($this->fileLocator, $this->classNameInflector);
    }

    /**
     * @covers \BookiesProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadUserClasses(): void
    {
        /** @var class-string $className */
        $className = 'Foo\\' . UniqueIdentifierGenerator::getIdentifier('Bar');
        $this
            ->classNameInflector
            ->expects(self::once())
            ->method('isProxyClassName')
            ->with($className)
            ->willReturn(false);

        self::assertFalse($this->autoloadWithoutFurtherAutoloaders($className));
    }

    /**
     * @covers \BookiesProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadNonExistingClass(): void
    {
        /** @var class-string $className */
        $className = 'Foo\\' . UniqueIdentifierGenerator::getIdentifier('Bar');
        $this
            ->classNameInflector
            ->expects(self::once())
            ->method('isProxyClassName')
            ->with($className)
            ->willReturn(true);
        $this
            ->fileLocator
            ->expects(self::once())
            ->method('getProxyFileName')
            ->willReturn(__DIR__ . '/non-existing');

        self::assertFalse($this->autoloadWithoutFurtherAutoloaders($className));
    }

    /**
     * @covers \BookiesProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadExistingClass(): void
    {
        self::assertFalse($this->autoloadWithoutFurtherAutoloaders(self::class));
    }

    /**
     * @covers \BookiesProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillAutoloadExistingFile(): void
    {
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        /** @var class-string $fqcn */
        $fqcn     = $namespace . '\\' . $className;
        $fileName = sys_get_temp_dir() . '/foo_' . uniqid('file', true) . '.php';

        file_put_contents($fileName, '<?php namespace ' . $namespace . '; class ' . $className . '{}');

        $this
            ->classNameInflector
            ->expects(self::once())
            ->method('isProxyClassName')
            ->with($fqcn)
            ->willReturn(true);
        $this
            ->fileLocator
            ->expects(self::once())
            ->method('getProxyFileName')
            ->willReturn($fileName);

        self::assertTrue($this->autoloadWithoutFurtherAutoloaders($fqcn));
        self::assertTrue(class_exists($fqcn, false));
    }

    /** @psalm-param class-string $className */
    private function autoloadWithoutFurtherAutoloaders(string $className): bool
    {
        $failingAutoloader = function (string $className): void {
            $this->fail(sprintf('Fallback autoloading was triggered to load "%s"', $className));
        };

        spl_autoload_register($failingAutoloader);

        $result = $this->autoloader->__invoke($className);

        spl_autoload_unregister($failingAutoloader);

        return $result;
    }
}
