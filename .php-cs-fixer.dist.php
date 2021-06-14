<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('config')
    ->exclude('migrations')
;

return (new PhpCsFixer\Config())->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'binary_operator_spaces' => ['operators' => ['|' => null]], // TODO: remove after this is solved: https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/5495
    ])
    ->setFinder($finder)
;
