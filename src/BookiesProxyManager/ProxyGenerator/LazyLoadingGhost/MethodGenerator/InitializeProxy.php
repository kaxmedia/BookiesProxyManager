<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator as LaminasMethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\Generator\MethodGenerator;

/**
 * Implementation for {@see \BookiesProxyManager\Proxy\LazyLoadingInterface::initializeProxy}
 * for lazy loading ghost objects
 */
class InitializeProxy extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty, LaminasMethodGenerator $callInitializer)
    {
        parent::__construct('initializeProxy');
        $this->setReturnType('bool');

        $this->setBody(
            'return $this->' . $initializerProperty->getName() . ' && $this->' . $callInitializer->getName()
            . '(\'initializeProxy\', []);'
        );
    }
}
