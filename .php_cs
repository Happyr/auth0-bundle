<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('Resources')
    ->exclude('vendor')
    ->in(__DIR__)
;
return PhpCsFixer\Config::create()
    ->setRules([
         '@Symfony' => true,
         'no_superfluous_phpdoc_tags' => true,
         'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
