<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\Generator\MagicMethodGenerator;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator;
use BookiesProxyManager\ProxyGenerator\Util\GetMethodIfExists;
use BookiesProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;

/**
 * Magic `__get` for lazy loading ghost objects
 */
class MagicGet extends MagicMethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct($originalClass, '__get', [new ParameterGenerator('name')]);

        $parent = GetMethodIfExists::get($originalClass, '__get');

        $callParent = '$returnValue = & parent::__get($name);';

        if (! $parent) {
            $callParent = PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_GET,
                'name',
                null,
                null,
                'returnValue',
                $originalClass
            );
        }

        $this->setBody(InterceptorGenerator::createInterceptedMethodBody(
            $callParent,
            $this,
            $prefixInterceptors,
            $suffixInterceptors,
            $parent
        ));
    }
}
