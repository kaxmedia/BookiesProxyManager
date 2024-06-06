<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\Generator\MagicMethodGenerator;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator;
use BookiesProxyManager\ProxyGenerator\Util\GetMethodIfExists;
use ReflectionClass;

/**
 * Magic `__clone` for lazy loading ghost objects
 */
class MagicClone extends MagicMethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct($originalClass, '__clone');

        $parent = GetMethodIfExists::get($originalClass, '__clone');

        $this->setBody(InterceptorGenerator::createInterceptedMethodBody(
            $parent ? '$returnValue = parent::__clone();' : '$returnValue = null;',
            $this,
            $prefixInterceptors,
            $suffixInterceptors,
            $parent
        ));
    }
}
