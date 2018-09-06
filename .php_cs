<?php

$config = PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'native_constant_invocation' => true,
        'native_function_invocation' => true,
    ));

$config->getFinder()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

$config
    ->setCacheFile(__DIR__ . '/.php_cs.cache');

return $config;
