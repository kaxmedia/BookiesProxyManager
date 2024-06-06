<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\Generator\MethodGenerator;

/**
 * Implementation for {@see \BookiesProxyManager\Proxy\LazyLoadingInterface::initializeProxy}
 * for lazy loading value holder objects
 */
class InitializeProxy extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty, PropertyGenerator $valueHolderProperty)
    {
        parent::__construct('initializeProxy');
        $this->setReturnType('bool');

        $initializer = $initializerProperty->getName();

        $this->setBody(
            'return $this->' . $initializer . ' && $this->' . $initializer
            . '->__invoke($this->' . $valueHolderProperty->getName()
            . ', $this, \'initializeProxy\', array(), $this->' . $initializer . ');'
        );
    }
}
