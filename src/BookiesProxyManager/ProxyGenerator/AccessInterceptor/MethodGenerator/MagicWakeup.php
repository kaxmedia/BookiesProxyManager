<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator;

use BookiesProxyManager\Generator\MagicMethodGenerator;
use BookiesProxyManager\ProxyGenerator\Util\Properties;
use BookiesProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use ReflectionClass;

/**
 * Magic `__wakeup` for lazy loading value holder objects
 */
class MagicWakeup extends MagicMethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(ReflectionClass $originalClass)
    {
        parent::__construct($originalClass, '__wakeup');

        $this->setBody(UnsetPropertiesGenerator::generateSnippet(
            Properties::fromReflectionClass($originalClass),
            'this'
        ));
    }
}
