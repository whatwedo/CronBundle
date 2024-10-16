<?php
declare(strict_types=1);

use PHP_CodeSniffer\Standards\Squiz\Sniffs\Classes\ValidClassNameSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting\ClassCommentSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting\FileCommentSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting\FunctionCommentThrowTagSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
    $ecsConfig->import('vendor/whatwedo/php-coding-standard/config/whatwedo-symfony.php');
    $ecsConfig->skip([
        FileCommentSniff::class,
        ClassCommentSniff::class,
        FunctionCommentThrowTagSniff::class,
        ValidClassNameSniff::class => [
            __DIR__ . '/src/whatwedoCronBundle.php',
            __DIR__ . '/src/DependencyInjection/whatwedoCronExtension.php',
        ],
    ]);

    $ecsConfig->parallel();
};
