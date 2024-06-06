<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Functional;

use Laminas\Code\Generator\ClassGenerator;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Exception\ExceptionInterface;
use BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use BookiesProxyManager\Proxy\ProxyInterface;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use BookiesProxyManager\ProxyGenerator\NullObjectGenerator;
use BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use BookiesProxyManager\ProxyGenerator\RemoteObjectGenerator;
use BookiesProxyManager\Signature\ClassSignatureGenerator;
use BookiesProxyManager\Signature\SignatureGenerator;
use ReflectionClass;
use ReflectionException;

use function array_filter;
use function array_map;
use function array_merge;
use function get_declared_classes;
use function realpath;
use function str_starts_with;
use function uniqid;

/**
 * Verifies that proxy-manager will not attempt to `eval()` code that will cause fatal errors
 *
 * @group Functional
 * @coversNothing
 */
final class FatalPreventionFunctionalTest extends TestCase
{
    /**
     * Verifies that code generation and evaluation will not cause fatals with any given class
     *
     * @param string $generatorClass an instantiable class (no arguments) implementing
     *                               the {@see \BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface}
     * @param string $className      a valid (existing/autoloadable) class name
     * @psalm-param class-string<ProxyGeneratorInterface> $generatorClass
     * @psalm-param class-string                          $className
     *
     * @dataProvider getTestedClasses
     */
    public function testCodeGeneration(string $generatorClass, string $className): void
    {
        $generatedClass          = new ClassGenerator(uniqid('generated', true));
        $generatorStrategy       = new EvaluatingGeneratorStrategy();
        $classGenerator          = new $generatorClass();
        $classSignatureGenerator = new ClassSignatureGenerator(new SignatureGenerator());

        try {
            $classGenerator->generate(new ReflectionClass($className), $generatedClass);
            $classSignatureGenerator->addSignature($generatedClass, ['key' => 'eval tests']);
            $generatorStrategy->generate($generatedClass);
        } catch (ExceptionInterface | ReflectionException $e) {
            // empty catch: this is actually a supported failure
        }

        self::assertTrue(true, 'Code generation succeeded: proxy is valid or couldn\'t be generated at all');
    }

    /**
     * @return string[][]
     * @psalm-return array<int, array<int, class-string<ProxyGeneratorInterface>|class-string>>
     */
    public function getTestedClasses(): array
    {
        return array_merge(
            [],
            ...array_map(
                fn ($generator): array => array_map(
                    static fn ($class): array => [$generator, $class],
                    $this->getProxyTestedClasses()
                ),
                [
                    AccessInterceptorScopeLocalizerGenerator::class,
                    AccessInterceptorValueHolderGenerator::class,
                    LazyLoadingGhostGenerator::class,
                    LazyLoadingValueHolderGenerator::class,
                    NullObjectGenerator::class,
                    RemoteObjectGenerator::class,
                ]
            )
        );
    }

    /**
     * @return string[]
     * @psalm-return array<int, class-string>
     *
     * @private (public only for PHP 5.3 compatibility)
     */
    private function getProxyTestedClasses(): array
    {
        $skippedPaths = [
            realpath(__DIR__ . '/../../../src'),
            realpath(__DIR__ . '/../../../vendor'),
            realpath(__DIR__ . '/../../BookiesProxyManagerTest'),
        ];

        return array_filter(
            get_declared_classes(),
            static function ($className) use ($skippedPaths): bool {
                $reflectionClass = new ReflectionClass($className);

                $fileName = $reflectionClass->getFileName();

                if (! $fileName) {
                    return false;
                }

                if ($reflectionClass->implementsInterface(ProxyInterface::class)) {
                    return false;
                }

                $realPath = realpath($fileName);

                self::assertIsString($realPath);

                foreach ($skippedPaths as $skippedPath) {
                    self::assertIsString($skippedPath);

                    if (str_starts_with($realPath, $skippedPath)) {
                        // skip classes defined within ProxyManager, vendor or the test suite
                        return false;
                    }
                }

                return true;
            }
        );
    }
}
