<?php

$finder = PhpCsFixer\Finder::create()->in('plugins');

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@Symfony' => true,
        'align_multiline_comment' => true,
        'array_syntax' => ['syntax' => 'short'],
        'increment_style' => ['style' => 'post'],
        'list_syntax' => ['syntax' => 'short'],
        'yoda_style' => false,
    ])
    ->setFinder($finder);
