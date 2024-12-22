<?php

declare(strict_types=1);

test('blade directive', function (string $input) {
    $compiler = app('blade.compiler');

    $result = $compiler->compileString($input);

    expect($result)->toBe('<?php echo session_field(); ?>');
})->with([
    '@sessionToken',
    '@sessionToken()',
    '@sessionToken("abc")',
]);
