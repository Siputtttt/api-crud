<?php

use Illuminate\Support\Facades\Route;

Route::get('login', function () {
    return redirect()->to('/api/login');
});

Route::get('/', function () {
    return view('welcome');
});
