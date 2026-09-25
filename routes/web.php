<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    return response()->json([
        'service' => 'trivio-tech-task',
        'status' => 'ok',
    ]);
});
