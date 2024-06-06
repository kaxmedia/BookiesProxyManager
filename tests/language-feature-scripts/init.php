<?php

declare(strict_types=1);

use BookiesProxyManager\Configuration;
use BookiesProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;

require_once __DIR__ . '/../../vendor/autoload.php';

$configuration = new Configuration();

$configuration->setGeneratorStrategy(new EvaluatingGeneratorStrategy());
