<?php

use Illuminate\Support\Facades\Route;
use Oleinykov\LaravelTranslationManager\Http\Controllers\TranslationController;
use Oleinykov\LaravelTranslationManager\Http\Middleware\EnsureAuthorized;
use Oleinykov\LaravelTranslationManager\Http\Middleware\EnsureStatefulDomains;

$userMiddleware = config('translation-manager.middleware', []);
if (!is_array($userMiddleware)) {
	$userMiddleware = [$userMiddleware];
}

$guardMiddleware = [EnsureStatefulDomains::class];

// Optional locale prefix integration with mcamara/laravel-localization
$localePrefix = '';
if (config('translation-manager.locale_prefix', false)
    && class_exists(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::class)) {
    $localePrefix = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::setLocale();
}

$prefix = trim(($localePrefix ? $localePrefix . '/' : '') . 'translations', '/');

// Merge middlewares: web + user + guard + optional localization ones
$localizationMiddleware = (array) config('translation-manager.localization_middleware', []);

Route::group([
    'prefix' => $prefix,
    // Use application's 'web' group to preserve auth/session behavior
    'middleware' => array_values(array_filter(array_merge(['web'], $userMiddleware, $guardMiddleware, $localizationMiddleware))),
], function () {

	Route::get('/', [TranslationController::class, 'index'])->name('translations.index');

	// --- Specific Routes ---
	// These should come before the dynamic routes to avoid being overridden.

	// Create a new translation file/folder
	Route::get('/create', [TranslationController::class, 'create'])->name('translations.create');
	Route::post('/', [TranslationController::class, 'store'])->name('translations.store');
	Route::post('/folder/create', [TranslationController::class, 'createFolder'])->name('translations.folder.create');

	// Global key management
	Route::get('/global/create', [TranslationController::class, 'globalCreate'])->name('translations.global.create');
	Route::post('/global', [TranslationController::class, 'globalStore'])->name('translations.global.store');
	Route::get('/global/edit/{key}', [TranslationController::class, 'globalEdit'])->name('translations.global.edit')->where('key', '.*');
	Route::post('/global/update/{key}', [TranslationController::class, 'globalUpdate'])->name('translations.global.update')->where('key', '.*');
	Route::delete('/global/delete/{key}', [TranslationController::class, 'globalDestroy'])->name('translations.global.destroy')->where('key', '.*');

	// Per-file cross-language matrix editor
	// IMPORTANT: place more specific routes BEFORE the catch-all /matrix/{path}
	// AJAX endpoints for matrix inline operations
	Route::post('/matrix/{path}/cell', [TranslationController::class, 'fileMatrixUpdateCell'])
	    ->name('translations.matrix.cell')->where('path', '.*');
	Route::post('/matrix/{path}/add', [TranslationController::class, 'fileMatrixAddKey'])
	    ->name('translations.matrix.add')->where('path', '.*');
	Route::get('/matrix/{path}', [TranslationController::class, 'fileMatrix'])
	    ->name('translations.matrix.show')->where('path', '.*');
	Route::post('/matrix/{path}', [TranslationController::class, 'fileMatrixUpdate'])
	    ->name('translations.matrix.update')->where('path', '.*');

	// Settings
	Route::get('/exclusions', [TranslationController::class, 'showExclusions'])->name('translations.exclusions.show');
	Route::post('/exclusions', [TranslationController::class, 'updateExclusions'])->name('translations.exclusions.update');


	Route::delete('/language/{lang}', [TranslationController::class, 'destroyLanguage'])->name('translations.language.destroy');

	// --- Dynamic Routes ---
	// These are the most generic and should come last.

	Route::get('/{path}', [TranslationController::class, 'show'])->name('translations.show')->where('path', '.*');
	Route::post('/{path}', [TranslationController::class, 'update'])->name('translations.update')->where('path', '.*');

});
