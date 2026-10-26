<?php

use Illuminate\Support\Facades\Route;

Route::get('/{path?}', function () {
    $index=public_path('app/index.html');
    abort_unless(is_file($index),503,'Compila el frontend con npm run build y npm run publish:local.');
    return response()->file($index);
})->where('path','.*');
