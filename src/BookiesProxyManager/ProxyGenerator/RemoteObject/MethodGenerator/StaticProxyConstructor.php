<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\Factory\RemoteObject\AdapterInterface;
use BookiesProxyManager\Generator\MethodGenerator;
use BookiesProxyManager\ProxyGenerator\Util\Properties;
use BookiesProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use ReflectionClass;

/**
 * The `staticProxyConstructor` implementation for remote object proxies
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Constructor
     *
     * @param ReflectionClass   $originalClass Reflection of the class to proxy
     * @param PropertyGenerator $adapter       Adapter property
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $adapter)
    {
        $adapterName = $adapter->getName();

        parent::__construct(
            'staticProxyConstructor',
            [new ParameterGenerator($adapterName, AdapterInterface::class)],
            MethodGenerator::FLAG_PUBLIC | MethodGenerator::FLAG_STATIC,
            null,
            'Constructor for remote object control\n\n'
            . '@param \\BookiesProxyManager\\Factory\\RemoteObject\\AdapterInterface \$adapter'
        );

        $body = 'static $reflection;' . "\n\n"
            . '$reflection = $reflection ?? new \ReflectionClass(__CLASS__);' . "\n"
            . '$instance   = $reflection->newInstanceWithoutConstructor();' . "\n\n"
            . '$instance->' . $adapterName . ' = $' . $adapterName . ";\n\n"
            . UnsetPropertiesGenerator::generateSnippet(Properties::fromReflectionClass($originalClass), 'instance');

        $this->setBody($body . "\n\nreturn \$instance;");
    }
}
