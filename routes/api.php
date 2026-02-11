<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('imports/{contactImport}', [ImportController::class, 'show'])
    ->name('api.imports.show');
