<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'contacts');

Route::get('import', [ImportController::class, 'index'])->name('import.index');
Route::post('import', [ImportController::class, 'store'])->name('import.store');
Route::get('contacts/search', [ContactController::class, 'search'])->name('contacts.search');
Route::resource('contacts', ContactController::class);
