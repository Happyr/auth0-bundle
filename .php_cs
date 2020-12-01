<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src')
    ->in('tests')
;
return PhpCsFixer\Config::create()
    ->setRules([
         '@Symfony' => true,
         'no_superfluous_phpdoc_tags' => true,
         'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
