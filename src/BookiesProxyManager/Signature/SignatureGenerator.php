<?php

declare(strict_types=1);

namespace BookiesProxyManager\Signature;

use BookiesProxyManager\Inflector\Util\ParameterEncoder;
use BookiesProxyManager\Inflector\Util\ParameterHasher;

final class SignatureGenerator implements SignatureGeneratorInterface
{
    private ParameterEncoder $parameterEncoder;
    private ParameterHasher $parameterHasher;

    public function __construct()
    {
        $this->parameterEncoder = new ParameterEncoder();
        $this->parameterHasher  = new ParameterHasher();
    }

    /**
     * {@inheritDoc}
     */
    public function generateSignature(array $parameters): string
    {
        return $this->parameterEncoder->encodeParameters($parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function generateSignatureKey(array $parameters): string
    {
        return $this->parameterHasher->hashParameters($parameters);
    }
}
