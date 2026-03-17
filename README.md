Laravel Translation Manager

A simple, web-based UI for managing Laravel translation files (PHP array and JSON) with global key create/edit, search, and nested folders support.

Installation

- Require the package via Composer: `composer require oleinykov/laravel-translation-manager`
- The service provider auto-registers routes and views.
- Optionally publish config: `php artisan vendor:publish --tag=config --provider="Oleinykov\\LaravelTranslationManager\\TranslationManagerServiceProvider"`

Routes

- UI mounts under `translations/*`.
- Visit `/translations` for the dashboard.

Localization (Locale Prefix)

If your app is multilingual (e.g., using `mcamara/laravel-localization`), you can mount Translation Manager under a locale-aware prefix so routes look like `/{locale}/translations` (for example, `/en/translations`, `/uk/translations`).

Enable via environment or config:

- `.env`: set `TRANSLATION_MANAGER_LOCALE_PREFIX=true`
- Or `config/translation-manager.php`:
  - `'locale_prefix' => true` to activate locale-prefixed routes
  - Optionally add your localization middlewares so locale resolution stays consistent:
    - `'localization_middleware' => ['localize', 'localizationRedirect', 'localeSessionRedirect', 'localeViewPath']`

Notes:

- If `mcamara/laravel-localization` is installed, the current locale is used for the prefix.
- If disabled or the package is not present, routes remain under `/translations`.
- After changing configuration, clear caches if needed:
  - `php artisan route:clear && php artisan config:clear && php artisan cache:clear`

Configuration

- File: `config/translation-manager.php`
- `load_assets`: load Tailwind + Alpine from CDN for the UI.
- `exclude_from_global`: files/folders excluded from Global Add.
- `middleware`: array of middleware applied to all `translations/*` routes before controllers. Recommended: `['auth']`.
- `stateful_domains`: comma-separated list from `LOG_VIEWER_API_STATEFUL_DOMAINS` env used by a guard middleware.

Security (Log Viewer–style)

All package routes run through the application's `web` middleware group, your configurable middleware stack, and built-in guards inspired by `opcodesio/log-viewer`:

- `Oleinykov\\LaravelTranslationManager\\Http\\Middleware\\EnsureStatefulDomains`:
  - Reads allowed hosts from `env('LOG_VIEWER_API_STATEFUL_DOMАINS')` (exposed in config as `stateful_domains`).
  - If not set or empty, allows requests from any host.
  - Supports exact hosts and wildcard subdomains like `*.example.com`.
  - Rejects others with 403.

  

- User-defined middleware via `config('translation-manager.middleware')`:
  - Place your project’s middleware here (e.g., `auth`, `verified`, `can:manage-translations`).
  - These run alongside the base stack (cookies, session, CSRF, bindings) and before controllers.

Quick Setup Examples

- `.env` for host checks:
  - Set `LOG_VIEWER_API_STATEFUL_DOMAINS=app.test,localhost,127.0.0.1,*.example.com`

- Restrict access to authenticated and verified users:
  - In `config/translation-manager.php`:
    - `middleware` => `['auth', 'verified']`

  

Behavior Summary

- Request pipeline for any `translations/*` link:
  - Web group (`web`) → your `middleware` list → `EnsureStatefulDomains` → Controller.
  - If stateful domains are configured, host must match (exact or wildcard) or the request is denied.

Global Edit

- Select languages: only checked languages are updated.
- Auto-create files: if a selected locale lacks the target PHP file/key, the package creates the file and writes the key. For vendor paths, it mirrors `resources/lang/vendor/{package}/{locale}/...`.
- JSON option: if JSON is checked and a value is provided, `{locale}.json` is created/updated across all locales with the new key.
- Vendor translations: by default vendor is excluded via `exclude_from_global`. To manage vendor translations globally, remove `vendor` from `exclude_from_global` or adjust via the Exclusions UI.

Troubleshooting

- If adding `auth` logs you out on visit:
  - Clear caches: `php artisan route:clear && php artisan config:clear`.
  - Ensure `SESSION_DOMAIN` matches your host (and subdomain if used).
  - If using Sanctum SPA, set `SANCTUM_STATEFUL_DOMAINS` and `LOG_VIEWER_API_STATEFUL_DOMАINS` to include your domain.
  - Verify session driver is persistent (e.g., `file`, `redis`, not `array`).

UI Notes

- Dark theme is default; light theme is light-gray. No app code changes needed—this is applied via CSS overrides in the layout.

Development

- Config path: `config/translation-manager.php`
- Routes: `routes/web.php`
- Middleware: `src/Http/Middleware/EnsureStatefulDomains.php`
- Controller: `src/Http/Controllers/TranslationController.php`
- Views: `resources/views`

License

Proprietary. Do not distribute without permission.
