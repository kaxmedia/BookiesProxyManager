<?php

declare(strict_types=1);

namespace BookiesProxyManager\Factory;

use Laminas\Code\Generator\ClassGenerator;
use OutOfBoundsException;
use BookiesProxyManager\Configuration;
use BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use BookiesProxyManager\Signature\Exception\InvalidSignatureException;
use BookiesProxyManager\Signature\Exception\MissingSignatureException;
use BookiesProxyManager\Version;
use ReflectionClass;

use function array_key_exists;
use function assert;
use function class_exists;
use function is_a;

/**
 * Base factory common logic
 */
abstract class AbstractBaseFactory
{
    protected Configuration $configuration;

    /**
     * Cached checked class names
     *
     * @var array<string, string>
     * @psalm-var array<class-string, class-string>
     */
    private array $checkedClasses = [];

    public function __construct(?Configuration $configuration = null)
    {
        $this->configuration = $configuration ?? new Configuration();
    }

    /**
     * Generate a proxy from a class name
     *
     * @param array<string, mixed> $proxyOptions
     * @psalm-param class-string<RealObjectType> $className
     *
     * @psalm-return class-string<RealObjectType>
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     */
    protected function generateProxy(string $className, array $proxyOptions = []): string
    {
        if (array_key_exists($className, $this->checkedClasses)) {
            $generatedClassName = $this->checkedClasses[$className];

            assert(is_a($generatedClassName, $className, true));

            return $generatedClassName;
        }

        $proxyParameters = [
            'className'           => $className,
            'factory'             => static::class,
            'proxyManagerVersion' => Version::getVersion(),
            'proxyOptions'        => $proxyOptions,
        ];
        $proxyClassName  = $this
            ->configuration
            ->getClassNameInflector()
            ->getProxyClassName($className, $proxyParameters);

        if (! class_exists($proxyClassName)) {
            $this->generateProxyClass(
                $proxyClassName,
                $className,
                $proxyParameters,
                $proxyOptions
            );
        }

        $this
            ->configuration
            ->getSignatureChecker()
            ->checkSignature(new ReflectionClass($proxyClassName), $proxyParameters);

        return $this->checkedClasses[$className] = $proxyClassName;
    }

    abstract protected function getGenerator(): ProxyGeneratorInterface;

    /**
     * Generates the provided `$proxyClassName` from the given `$className` and `$proxyParameters`
     *
     * @param array<string, mixed> $proxyParameters
     * @param array<string, mixed> $proxyOptions
     * @psalm-param class-string $proxyClassName
     * @psalm-param class-string $className
     */
    private function generateProxyClass(
        string $proxyClassName,
        string $className,
        array $proxyParameters,
        array $proxyOptions = []
    ): void {
        $className = $this->configuration->getClassNameInflector()->getUserClassName($className);
        $phpClass  = new ClassGenerator($proxyClassName);

        /** @psalm-suppress TooManyArguments - generator interface was not updated due to BC compliance */
        $this->getGenerator()->generate(new ReflectionClass($className), $phpClass, $proxyOptions);

        $phpClass = $this->configuration->getClassSignatureGenerator()->addSignature($phpClass, $proxyParameters);

        /** @psalm-suppress TooManyArguments - generator interface was not updated due to BC compliance */
        $this->configuration->getGeneratorStrategy()->generate($phpClass, $proxyOptions);

        $autoloader = $this->configuration->getProxyAutoloader();

        $autoloader($proxyClassName);
    }
}
