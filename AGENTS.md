# AI Instruction & Project Context

**File Created:** August 16, 2025

## Project Overview

**Project Name:** Laravel Translation Manager

**Purpose:** This package provides a web-based user interface for managing translation files within a Laravel application. It aims to simplify the process of adding, editing, and managing translations for multilingual applications.

## Core Features

- **View Translations:** Displays all translation files, including PHP arrays and JSON files, grouped by language. It also supports nested folders.
- **Edit Translations:** Allows for in-place editing of translation keys and values.
- **Create Files/Folders:** Provides functionality to create new language files (`.php`) and new folders within language directories.
- **Global Key Creation:** A key feature that allows a user to add a new translation key simultaneously across the same file in all languages.
- **Search:** Users can search for translation keys or values across all files.

## Key Components

- `src/Http/Controllers/TranslationController.php`: The main controller that contains all the business logic for the package's features.
- `routes/web.php`: Defines all the necessary routes for the translation manager UI.
- `resources/views/`: Contains all the Blade templates used for rendering the UI. Key views include `index.blade.php`, `show.blade.php`, and `global-create.blade.php`.
- `config/translation-manager.php`: The package's configuration file. It allows users to configure settings, such as excluding certain files and folders from global management.

## Implemented Features (Based on User Requests)

- **Flexible Global Key Creation:** The "Global Create" feature was enhanced to allow users to either select an existing translation file or create a new one by typing a name.
- **Multi-Language Input:** The form for global key creation was updated to include separate input fields for each language, allowing for the simultaneous entry of all translations for a new key.
- **Exclusion List:** A configuration option (`exclude_from_global`) was added to prevent specific files or directories (e.g., `vendor` translations) from being modified by the global management tools.
- **Grouped File Display:** The list of existing translation files is now presented in a grouped format by language for better organization and readability.
- **Improved UI:** The user interface for entering multiple translations was changed from a simple list of inputs to a more structured and user-friendly table format.
- **Route Fixes:** Corrected the order of routes to prevent conflicts between specific routes (like `/global/create`) and dynamic, parameterized routes (like `/{lang}/{file}`).

---
**AI Restore Point:** August 16, 2025, 10:30 AM (GMT+0). This timestamp marks a point where the AI has a comprehensive understanding of the project's current state, including its structure, core functionalities, and recent modifications. This can be used to restore context if needed.

---
## AI Restore Point — 2025-08-16 16:58:08 +0200

**Scope:** Commit `5649d9e9939a382fe7ac3b48a581385f1d1ec977` on branch `dev` (tracking `origin/dev`).

**Commit Message:** feat(translations): per-language global update with inline success feedback

**Changed Files:**
- `resources/views/global-edit.blade.php`
  - Added a success alert banner that reads from `session('success')` or a passed `$success` variable to show inline feedback after updates.
- `src/Http/Controllers/TranslationController.php`
  - Method `globalUpdate(...)` updated to:
    - Validate `languages_to_update` as optional (`sometimes|array`), default `[]` if not provided.
    - Update only for explicitly selected languages; non-selected languages retain their existing values.
    - Rename request payload variable to `formTranslations` for clarity; collect `$updatedTranslations` for the view.
    - If the key name changes, remove the old key and set the new one.
    - Persist properly to both `.php` array files and `.json` translation files.
    - Return the `translation-manager::global-edit` view with `languageCodes`, `translations` (reflecting updated values), and a success message instead of redirecting, enabling immediate inline feedback.

**Behavior After Change:**
- Submitting the Global Edit form re-renders the same page with a green success alert and updated translations for selected languages only.

**Notes for Maintainers/Agents:**
- Ensure `Illuminate\Support\Arr` is correctly imported (the controller already imports `Arr`; calls use `Arr::has/get/set/forget`).
- Verify the `translations.global.update` route maps to `globalUpdate` and that the view alias `translation-manager::global-edit` resolves correctly.

**Quick Test Checklist:**
- Open Global Edit for an existing key across languages.
- Change the key and/or some values; select only certain languages; submit.
- Confirm: only selected languages change on disk; unselected remain unchanged; success banner appears; the form shows the new key and updated values.

**Rollback:**
- To revert this change: `git revert 5649d9e9939a382fe7ac3b48a581385f1d1ec977` on `dev`, then push.

---
## AI Restore Point — 2025-08-16 17:31:48 +0200

**Scope:** Commit `5f40fef2cc88e9692580cc990d26d39725e8fe72` on branch `dev` (tracking `origin/dev`).

**Commit Message:** feat(translations, ui): inline success alert and selective per-language updates

**Changed Files:**
- `resources/views/_file_tree.blade.php` (minor adjustments)
- `resources/views/copy.blade.php` (new view added)
- `resources/views/index.blade.php` (UI updates)
- `resources/views/layout.blade.php` (layout tweaks)
- `resources/views/show.blade.php` (UI updates)
- `routes/web.php` (route changes/alignments)
- `src/Http/Controllers/TranslationController.php` (logic updates; 154 lines touched)

**Summary of Changes:**
- UI: Added inline success alert patterns and refined views, including a new `copy.blade.php` view. Minor layout and tree view tweaks.
- Translations: Reinforced selective per-language update behavior and inline feedback in the controller flow; continued support for key rename and proper persistence to `.php` and `.json`.
- Routing: Updated routes to align with the UI flow changes.

**Behavior After Change:**
- After global updates, the page shows a success banner and displays only the updated language values while keeping others unchanged; new UI pieces support copying/management workflows.

**Notes for Maintainers/Agents:**
- Ensure the new view `resources/views/copy.blade.php` is wired in the UI if needed (route/controller method as applicable).
- Confirm route definitions in `routes/web.php` do not conflict with dynamic parameters.

**Quick Test Checklist:**
- Open the main index and file tree; verify UI renders correctly.
- Perform a global edit selecting a subset of languages; verify only those languages changed on disk and a success banner appears.
- Navigate to any new/copy-related UI and confirm expected rendering.

**Rollback:**
- To revert this change: `git revert 5f40fef2cc88e9692580cc990d26d39725e8fe72` on `dev`, then push.

---
## AI Restore Point — 2025-08-16 17:52:52 +0200

**Scope:** Commit `a47150d3be67be28cc1af2bbd6637266c9b2cbd2` on branch `dev` (tracking `origin/dev`).

**Commit Message:** feat(ui): move all actions to header, fix theme init, modernize buttons and icons, and finish dark-mode coverage

**Changed Files:**
- `resources/views/layout.blade.php`
- `resources/views/index.blade.php`
- `resources/views/show.blade.php`
- `resources/views/_file_tree.blade.php`
- `resources/views/copy.blade.php`
- `resources/views/create.blade.php`

**Summary of Changes:**
- Dark Mode: Applied theme class early (before Tailwind) to avoid flicker; ensured dark variants across all major views so the toggle reliably switches themes and persists via `localStorage` (`tm_dark`).
- Header Actions: Moved primary actions (New File, Global Add, New Folder, Exclusions) into the navbar via a Blade section `@section('translation-manager-actions')` for a modern, consistent toolbar.
- Buttons & Forms: Replaced legacy Tailwind `*-500/*-700` patterns with consistent modern variants (better focus rings, colors, and dark-mode contrast).
- Icons: Updated folder/file icons; folders show open/closed state; file icon simplified and dark-mode aware.
- Modals: “New Folder” modal opens via a simple window event, improving header-driven UX.

**Behavior After Change:**
- Theme toggle updates immediately on click and on page load; styling is consistent across dashboard, editors, global pages, and settings.
- Users access all primary actions from the header; UI feels cohesive and modern.

**Notes for Maintainers/Agents:**
- Tailwind is loaded via CDN with `darkMode: 'class'`; the early inline script adds `dark` class based on `localStorage`.
- Index view listens for `tm:openCreateFolder` to open the folder modal.
- If additional pages are added, prefer using the header actions section for controls and reuse the modern button styles.

**Quick Test Checklist:**
- Toggle dark mode; refresh page; confirm theme persists and applies everywhere.
- Use header buttons: open Global Add, New File, Exclusions; open Create Folder modal from the header.
- Expand/collapse folder items; verify icon state changes; open files and confirm editor uses updated styles.

---
## AI Restore Point — 2025-08-18 17:20:00 +0000

Security integration inspired by opcodesio/log-viewer; adds configurable middleware and a stateful-domain guard to protect all package routes.

Changed Files:
- `routes/web.php`
  - Routes now use the application's `web` middleware group to preserve session/auth behavior.
  - Middleware stack built as: `['web'] + config('translation-manager.middleware') + [EnsureStatefulDomains]`.

- `config/translation-manager.php`
  - Added `middleware`: array of user-defined middlewares to run before controllers (e.g., `['auth', 'verified']`).
  - Added `stateful_domains`: reads from `env('LOG_VIEWER_API_STATEFUL_DOMАINS', '')`; empty means allow all hosts.

  

- `src/Http/Middleware/EnsureStatefulDomains.php`
  - Parses comma-separated domains (supports wildcard `*.example.com`; normalizes protocol/port; treats `::1` as `127.0.0.1`).
  - Allows if config empty or host matches allowed list; otherwise aborts 403.

- `README.md`
  - Updated setup and security guide; clarified use of `web` group; troubleshooting for logout/session issues. Removed `authorize` mentions.

Behavior After Change:
- All `translations/*` routes are protected by: `web` → user `middleware` → `EnsureStatefulDomains` → controller.
- Recommended default: `'middleware' => ['auth']` in config; optionally add `verified`/`can:*`.
- If `LOG_VIEWER_API_STATEFUL_DOMAINS` is set, requests from non-listed hosts are rejected (403).

Quick Test Checklist:
- Set `.env`: `LOG_VIEWER_API_STATEFUL_DOMAINS=app.test,localhost,127.0.0.1,*.example.com`.
- In config set `'middleware' => ['auth']`.
- Logged out → `/translations` redirects to login (`auth`).
- Host not on allowed list → `/translations` returns 403.
  

Notes for Maintainers/Agents:
- After changing config, clear caches: `php artisan config:clear` and `php artisan route:clear`.
 
---
## AI Restore Point — 2025-08-18 18:30:00 +0000

UI, Theme, Icons, and Global Edit logic refinements; README/docs updated; vendor translations and JSON creation supported.

UI and Theme
- Replaced text buttons with compact icon buttons (Lucide):
  - Trash: `trash-2` for delete language, global delete, and row remove.
  - Edit: `edit-3` for Global Edit action.
- Swapped folder/file icons in the tree to Lucide (`folder`, `folder-open`, `file`).
- Introduced utility classes: `tm-icon-btn`, `tm-icon`, `tm-icon-btn--danger`, `tm-icon-btn--accent` for consistent icon styling.
- Set file link color to blue `#0084C7` via `tm-file-link` (works in light/dark).
- Dark theme danger palette: background `#571E30`, text/icons `#FB7285`. Hover/borders aligned.
- Dark is default; light theme is light‑gray (CSS overrides in layout).

Security and Routing
- Routes for `translations/*` run through app `web` group + configurable `middleware` + `EnsureStatefulDomains`.
- README updated with setup, pipeline, and troubleshooting; `authorize` removed from docs.

Global Edit Logic
- Decode keys from URL (`urldecode`) in `globalEdit` and `globalUpdate` to support Unicode keys.
- Correctly detect language for vendor paths `resources/lang/vendor/{package}/{locale}/...` when reading/updating.
- Search results show real locale for vendor files instead of `vendor`.
- When updating globally, if a selected locale lacks the file/key:
  - Create missing PHP file/entry mirroring either vendor structure or normal `lang/{locale}` structure.
  - When JSON is selected and a value provided, create/update `{locale}.json` across all locales with the new key.
- Fixed undefined variables in `globalUpdate` (initialized `$referenceNormal`, `$referenceVendor`, `$langsWithKey`).

Files Touched
- `resources/views/layout.blade.php`: theme overrides; icon utility classes; accent color.
- `resources/views/index.blade.php`: icon-only actions; Lucide trash for global and language delete; Global Edit icon.
- `resources/views/_file_tree.blade.php`: Lucide folder/file icons; file link class.
- `resources/views/show.blade.php`: row remove button → Lucide trash icon.
- `src/Http/Controllers/TranslationController.php`:
  - `globalEdit`/`globalUpdate`: `urldecode($key)`; vendor locale detection; create missing PHP files; optional JSON creation across locales; variable init fixes.
  - `collectTranslationsForSearch`: vendor locale display fix.
- `README.md`: middleware-only protection; updated guidance.

Quick Test Checklist
- Global Edit a Unicode key; values appear for locales that have it.
- Select a locale that lacks the file; provide value; save → file created and key written (vendor or normal path accordingly).
- Check “JSON” and value; all `{locale}.json` contain the key with given value.
- Search for a vendor key; languages display as real locales (en/ru/uk/...).

---
## UI Color Scheme (Canonical)

The project uses a unified design system with explicit light and dark palettes and a single primary accent. All future UI work should follow these colors and utilities.

- Light Theme (root variables):
  - Backgrounds: `--lm-bg: #f2f2f2`, `--lm-surface: #f7f7f7`, `--lm-surface-2: #f0f0f0`
  - Borders: `--lm-border: #dcdcdc`, `--lm-border-2: #cfcfcf`
  - Text: `--lm-text: #222222`, `--lm-text-2: #444444`
  - Overlays/Hover: `--lm-overlay: rgba(247,247,247,0.8)`, `--lm-hover: #ededed`
  - Accent (shared): `--tm-accent: #0084C7`, `--tm-accent-hover: #0A76AF`, `--tm-accent-ring: #0A76AF`

- Dark Theme (`.dark` variables):
  - Backgrounds: `--tm-bg: #222222`, `--tm-surface: #2a2a2a`, `--tm-surface-2: #242424`
  - Borders: `--tm-border: #3a3a3a`, `--tm-border-2: #303030`
  - Text: `--tm-text: #e8e8e8`, `--tm-text-200: #d8d8d8`, `--tm-text-300: #c8c8c8`, `--tm-text-400: #a0a0a0`
  - Overlays/Hover: `--tm-hover: #2f2f2f`, `--tm-overlay: rgba(34,34,34,0.80)`, `--tm-overlay-20: rgba(34,34,34,0.20)`
  - Primary Accent: `--tm-accent: #0084C7`, `--tm-accent-hover: #0A76AF`, `--tm-accent-ring: #0A76AF`
  - Info/Success: `--tm-info-bg: rgba(59,130,246,0.10)`, `--tm-info-border: rgba(59,130,246,0.35)`, `--tm-success-weak: rgba(16,185,129,0.12)`, `--tm-success-border: rgba(16,185,129,0.45)`
  - Danger (bright deep red): `--tm-danger: #7A0606`, `--tm-danger-hover: #650505`, `--tm-danger-text: #B40B0B`, `--tm-danger-weak: rgba(180,11,11,0.14)`, `--tm-danger-border: rgba(180,11,11,0.50)`
  - Folder Icon Accent: use `#DB9305` for folder icons via `.tm-folder-icon` in dark mode

- Accent Buttons (shared utilities):
  - Primary: `.tm-btn-accent` → background `#0084C7`, hover `#0A76AF`, text `#FFF` (dark mode uses same accent vars)
  - Outline: `.tm-btn-accent-outline` → border/text `#0084C7`, subtle hover bg; dark mode mirrors accent vars
  - Focus Rings: globally override Tailwind ring/border (blue/indigo/violet) to `--tm-accent-ring` for consistency

- Breadcrumbs: use `.tm-breadcrumb` with color `#a0a0a0` and `/` separators. Keep the final segment slightly more prominent if needed.

- Modals: compact container (max-w-xl) with consistent header; action buttons use accent styles; no white surfaces in dark theme (respect `dark:` classes).

- Dark Mode: Tailwind is configured with `darkMode: 'class'`. Theme is toggled via `localStorage('tm_dark')` and the `dark` class on `<html>`.

All new components and changes should adhere to this palette and utilities to maintain consistency across the app.
