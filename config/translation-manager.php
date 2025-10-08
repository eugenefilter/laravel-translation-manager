<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Load Package Assets
	|--------------------------------------------------------------------------
	|
	| This option controls whether the package should automatically load its
	| own CSS and JS assets (Tailwind, Alpine.js) from a CDN. Set this to
	| false if you want to include these assets in your own application's
	| build process.
	|
	*/
	'load_assets' => true,

	/*
	|--------------------------------------------------------------------------
	| Exclude from Global Management
	|--------------------------------------------------------------------------
	|
	| Specify files or directories that should be excluded from the "Global
	| Add Key" feature. This is useful for vendor translations that you
	| don't want to modify. Paths are relative to the lang directory.
	|
	| Example: ['vendor', 'auth.php']
	|
	*/
	'exclude_from_global' => [
		'vendor',
	],

	/*
	|--------------------------------------------------------------------------
	| Route Protection
	|--------------------------------------------------------------------------
	|
	| You can provide a list of middleware that must run before accessing any
	| Translation Manager route. Use this to plug in auth/permission checks.
	| By default, a permissive middleware is applied that allows all requests.
	|
	| Example:
	| 'middleware' => [ 'web', 'auth', 'can:manage-translations' ]
	|
	*/
	'middleware' => [
		'auth',
	],

	/*
	|--------------------------------------------------------------------------
	| Stateful Domains (API/UI Security)
	|--------------------------------------------------------------------------
	|
	| Comma-separated list of domains that are considered stateful when making
	| requests to this package (similar to Log Viewer). Requests coming from
	| other hosts will be rejected by a guard middleware.
	|
	| Env: LOG_VIEWER_API_STATEFUL_DOMAINS=app.test,localhost,127.0.0.1
	|
	*/
	'stateful_domains' => env('LOG_VIEWER_API_STATEFUL_DOMAINS', ''),

	/*
	|--------------------------------------------------------------------------
	| Locale Prefix Integration (optional)
	|--------------------------------------------------------------------------
	|
	| If your application uses mcamara/laravel-localization and you want this
	| package's routes to live under the locale prefix (e.g. /en/translations),
	| enable this option. When enabled and the package is present, all
	| Translation Manager routes will be prefixed with the current locale.
	|
	| Set via env: TRANSLATION_MANAGER_LOCALE_PREFIX=true
	*/
	'locale_prefix' => env('TRANSLATION_MANAGER_LOCALE_PREFIX', false),

	/*
	|--------------------------------------------------------------------------
	| Localization Middlewares (optional)
	|--------------------------------------------------------------------------
	|
	| If you are using mcamara/laravel-localization, you may also want to apply
	| its middlewares to the Translation Manager routes. Add any of your
	| localization middlewares here; they will be appended to the route group.
	|
	| Example: ['localize', 'localizationRedirect', 'localeSessionRedirect']
	*/
	'localization_middleware' => [],
];
