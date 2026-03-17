
<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('tm_dark') !== '0' }" x-init="document.documentElement.classList.toggle('dark', dark)">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translation Manager</title>
    @if(config('translation-manager.load_assets', true))
        <script>
          // Apply dark as default unless user explicitly set light ('0')
          try {
            if (localStorage.getItem('tm_dark') !== '0') {
              document.documentElement.classList.add('dark');
            } else {
              document.documentElement.classList.remove('dark');
            }
          } catch (e) {}
        </script>
        <script>
          // Tailwind CDN config (optional) + dark mode via class
          window.tailwind = window.tailwind || {}; 
          tailwind.config = { darkMode: 'class' };
        </script>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
          /* Custom utilities */
          .tm-icon-btn { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; border:1px solid transparent; background:transparent; cursor:pointer; }
          .tm-icon { width:16px; height:16px; }
          /* Light theme accents */
          html:not(.dark) .tm-file-link { color:#0084C7 !important; }
          html:not(.dark) .tm-icon-btn:hover { background:#ededed; }
          html:not(.dark) .tm-icon-btn--accent { color:#0084C7; border-color: transparent; }
          html:not(.dark) .tm-icon-btn--accent:hover { background:#ededed; }

          /* Light palette overrides (non-dark) to make UI light gray */
          :root {
            --lm-bg: #f2f2f2;          /* page background */
            --lm-surface: #f7f7f7;     /* cards, panels */
            --lm-surface-2: #f0f0f0;   /* secondary surface */
            --lm-border: #dcdcdc;      /* borders */
            --lm-border-2: #cfcfcf;    /* stronger borders */
            --lm-text: #222222;        /* primary text */
            --lm-text-2: #444444;      /* secondary text */
            --lm-overlay: rgba(247, 247, 247, 0.8);
            --lm-hover: #ededed;       /* subtle hover */
            /* Light accent variables to match site scheme */
            --tm-accent: #0084C7;
            --tm-accent-hover: #0A76AF;
            --tm-accent-ring: #0A76AF;
          }
          /* Apply to common Tailwind classes used across views */
          html:not(.dark) body,
          html:not(.dark) .bg-slate-50 { background-color: var(--lm-bg) !important; }
          html:not(.dark) .bg-white { background-color: var(--lm-surface) !important; }
          html:not(.dark) .bg-gray-50 { background-color: var(--lm-surface) !important; }
          html:not(.dark) .bg-white\/80 { background-color: var(--lm-overlay) !important; }
          html:not(.dark) .hover\:bg-gray-50:hover { background-color: var(--lm-hover) !important; }
          html:not(.dark) .hover\:bg-gray-100:hover { background-color: var(--lm-hover) !important; }
          /* Borders */
          html:not(.dark) .border-slate-200 { border-color: var(--lm-border) !important; }
          html:not(.dark) .border-slate-300 { border-color: var(--lm-border-2) !important; }
          html:not(.dark) .border-gray-300 { border-color: var(--lm-border) !important; }
          html:not(.dark) .divide-gray-200 > * + * { border-color: var(--lm-border) !important; }
          /* Text */
          html:not(.dark) .text-slate-800 { color: var(--lm-text) !important; }
          html:not(.dark) .text-gray-700 { color: var(--lm-text) !important; }
          html:not(.dark) .text-gray-600 { color: var(--lm-text-2) !important; }
          /* Dark palette overrides to match #222222 base */
          .dark {
            --tm-bg: #222222;
            --tm-surface: #2a2a2a;
            --tm-surface-2: #242424;
            --tm-border: #3a3a3a;
            --tm-border-2: #303030;
            --tm-text: #e8e8e8;
            --tm-text-200: #d8d8d8;
            --tm-text-300: #c8c8c8;
            --tm-text-400: #a0a0a0;
            --tm-hover: #2f2f2f;
            --tm-overlay: rgba(34, 34, 34, 0.80);
            --tm-overlay-20: rgba(34, 34, 34, 0.20);
            --tm-accent: #0084C7; /* primary blue */
            --tm-accent-hover: #0A76AF;
            --tm-accent-ring: #0A76AF;
            --tm-info-bg: rgba(59, 130, 246, 0.10);
            --tm-info-border: rgba(59, 130, 246, 0.35);
            /* Danger palette (brighter deep red) */
            --tm-danger: #7A0606;            /* bg for danger hover/surfaces */
            --tm-danger-hover: #650505;      /* deeper on hover */
            --tm-danger-text: #B40B0B;       /* brighter text/icon */
            --tm-danger-weak: rgba(180, 11, 11, 0.14); /* subtle alert bg */
            --tm-danger-border: rgba(180, 11, 11, 0.50); /* borders */
            --tm-success-weak: rgba(16, 185, 129, 0.12);
            --tm-success-border: rgba(16, 185, 129, 0.45);
          }
          /* Dark theme custom utilities */
          .dark .tm-file-link { color: var(--tm-accent) !important; }
          .dark .tm-icon-btn:hover { background: var(--tm-hover); }
          .dark .tm-icon-btn--accent { color: var(--tm-accent); }
          .dark .tm-icon-btn--accent:hover { background: var(--tm-hover); }
          .dark .tm-icon-btn--danger { color: var(--tm-danger-text); border-color: var(--tm-danger-border); }
          .dark .tm-icon-btn--danger:hover { background: var(--tm-danger); }
          .dark .tm-icon-btn--danger .tm-icon { color: var(--tm-danger-text); }
          /* Page background */
          .dark body,
          .dark .dark\:bg-slate-950 { background-color: var(--tm-bg) !important; }
          /* Surfaces and overlays */
          .dark .dark\:bg-slate-900 { background-color: var(--tm-surface-2) !important; }
          .dark .dark\:bg-slate-900\/80 { background-color: var(--tm-overlay) !important; }
          .dark .dark\:bg-slate-800 { background-color: var(--tm-surface) !important; }
          .dark .dark\:bg-slate-700 { background-color: var(--tm-surface) !important; }
          .dark .dark\:bg-slate-700\/50 { background-color: rgba(42, 42, 42, 0.5) !important; }
          .dark .dark\:hover\:bg-slate-800:hover { background-color: var(--tm-hover) !important; }
          .dark .dark\:hover\:bg-slate-900\/20:hover { background-color: var(--tm-overlay-20) !important; }
          /* Borders */
          .dark .dark\:border-slate-800 { border-color: var(--tm-border-2) !important; }
          .dark .dark\:border-slate-700 { border-color: var(--tm-border) !important; }
          .dark .dark\:divide-slate-700 > * + * { border-color: var(--tm-border) !important; }
          /* Text */
          .dark .dark\:text-slate-100 { color: var(--tm-text) !important; }
          .dark .dark\:text-slate-200 { color: var(--tm-text-200) !important; }
          .dark .dark\:text-slate-300 { color: var(--tm-text-300) !important; }
          .dark .dark\:text-slate-400 { color: var(--tm-text-400) !important; }
          .dark .dark\:text-blue-400 { color: #7FB0FF !important; }

          /* Primary buttons and links (blue) */
          .dark .bg-blue-600 { background-color: var(--tm-accent) !important; }
          .dark .hover\:bg-blue-700:hover { background-color: var(--tm-accent-hover) !important; }
          .dark .text-blue-600 { color: var(--tm-accent) !important; }
          .dark .hover\:text-blue-700:hover { color: var(--tm-accent-hover) !important; }
          .dark .focus\:ring-blue-500:focus { --tw-ring-color: var(--tm-accent-ring) !important; }
          .dark .focus\:border-blue-500:focus { border-color: var(--tm-accent-ring) !important; }
          .dark .focus\:ring-indigo-500:focus { --tw-ring-color: var(--tm-accent-ring) !important; }
          .dark .focus\:border-indigo-500:focus { border-color: var(--tm-accent-ring) !important; }

          /* Danger buttons and alerts (red) */
          .dark .bg-red-600 { background-color: var(--tm-danger) !important; }
          .dark .hover\:bg-red-700:hover { background-color: var(--tm-danger-hover) !important; }
          .dark .border-red-300 { border-color: var(--tm-danger-border) !important; }
          .dark .border-red-500 { border-color: var(--tm-danger) !important; }
          .dark .text-red-700 { color: var(--tm-danger-text) !important; }
          .dark .bg-red-100 { background-color: var(--tm-danger-weak) !important; }

          /* Info/Success alerts harmonized on dark */
          .dark .bg-blue-50 { background-color: var(--tm-info-bg) !important; }
          .dark .border-blue-200 { border-color: var(--tm-info-border) !important; }
          .dark .bg-green-50 { background-color: var(--tm-success-weak) !important; }
          .dark .border-green-200 { border-color: var(--tm-success-border) !important; }

          /* Inputs, fields and white surfaces inside dark */
          .dark .dark\:bg-slate-700 { background-color: var(--tm-surface) !important; }
          .dark .dark\:text-slate-100 { color: var(--tm-text) !important; }
          .dark .dark\:border-slate-600 { border-color: var(--tm-border) !important; }

          /* Accent button utilities using #0084C7 */
          .tm-btn-accent {
            background-color: #0084C7;
            color: #fff;
          }
          .tm-btn-accent:hover { background-color: #0A76AF; }
          .tm-btn-accent:focus { outline: none; --tw-ring-color: #0A76AF; }
          .dark .tm-btn-accent { background-color: var(--tm-accent); }
          .dark .tm-btn-accent:hover { background-color: var(--tm-accent-hover); }

          .tm-btn-accent-outline {
            background: transparent;
            color: #0084C7;
            border: 1px solid #0084C7;
          }
          .tm-btn-accent-outline:hover { background: rgba(0, 132, 199, 0.08); }
          .tm-btn-accent-outline:focus { outline: none; --tw-ring-color: #0A76AF; }
          .dark .tm-btn-accent-outline { color: var(--tm-accent); border-color: var(--tm-accent); }
          .dark .tm-btn-accent-outline:hover { background: rgba(0, 132, 199, 0.15); }

          /* Global focus ring overrides to use accent color (both themes) */
          .focus\:ring-blue-500:focus, .focus\:ring-indigo-500:focus, .focus\:ring-violet-500:focus { --tw-ring-color: var(--tm-accent-ring) !important; }
          .focus\:border-blue-500:focus, .focus\:border-indigo-500:focus, .focus\:border-violet-500:focus { border-color: var(--tm-accent-ring) !important; }

          /* Unified input style */
          .tm-input { width:100%; padding:0.75rem; border-radius:0.5rem; border:1px solid var(--lm-border); background:#fff; color:#1f2937; box-shadow:0 1px 2px 0 rgb(0 0 0 / 0.05); }
          .tm-input:focus { outline: none; box-shadow: 0 0 0 2px var(--tm-accent-ring); border-color: transparent; }
          .dark .tm-input { background: var(--tm-surface); color: var(--tm-text); border-color: var(--tm-border); }

          /* Breadcrumb utility */
          .tm-breadcrumb { color: #a0a0a0; }

          /* Scrollbar variables */
          :root { --tm-scroll-thumb: #cfcfcf; --tm-scroll-thumb-hover: #b6b6b6; --tm-scroll-track: transparent; }
          .dark { --tm-scroll-thumb: #3a3a3a; --tm-scroll-thumb-hover: #505050; --tm-scroll-track: transparent; }

          /* Global (Firefox) */
          * { scrollbar-width: thin; scrollbar-color: var(--tm-scroll-thumb) var(--tm-scroll-track); }
          /* WebKit default */
          ::-webkit-scrollbar { width: 8px; height: 8px; }
          ::-webkit-scrollbar-thumb { background-color: var(--tm-scroll-thumb); border-radius: 8px; }
          ::-webkit-scrollbar-thumb:hover { background-color: var(--tm-scroll-thumb-hover); }
          ::-webkit-scrollbar-track { background: var(--tm-scroll-track); }

          /* Hover-enlarge for designated scroll areas */
          .tm-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
          .tm-scroll:hover::-webkit-scrollbar { width: 10px; height: 10px; }

          /* Custom rounded checkboxes (TM style) */
          .tm-check {
            -webkit-appearance: none; appearance: none;
            width: 20px; height: 20px; border-radius: 5px; /* custom radius */
            display: inline-grid; place-content: center; cursor: pointer;
            border: 2px solid var(--lm-border); background: #ffffff;
            transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
            vertical-align: middle;
          }
          .dark .tm-check { border-color: var(--tm-border); background: var(--tm-surface); }
          .tm-check:hover { box-shadow: inset 0 0 0 9999px rgba(0,0,0,0.02); }
          .dark .tm-check:hover { box-shadow: inset 0 0 0 9999px rgba(255,255,255,0.02); }
          .tm-check:focus { outline: 2px solid var(--tm-accent-ring); outline-offset: 2px; }
          .tm-check:checked { background: #0084C7; border-color: #0084C7; }
          .dark .tm-check:checked { background: var(--tm-accent); border-color: var(--tm-accent); }
          .tm-check:checked::after {
            content: '';
            width: 6px; height: 10px;
            border-right: 2px solid #fff; border-bottom: 2px solid #fff;
            transform: rotate(45deg);
          }

          /* Folder icon color in dark mode */
          .dark .tm-folder-icon { color: #DB9305 !important; }
          /* Danger icon hover: make trash icon white */
          .tm-icon-btn--danger:hover .tm-icon { color: #ffffff !important; }
          /* Light mode danger hover background */
          html:not(.dark) .tm-icon-btn--danger:hover { background: #7A0606; }

          /* Selectable file options in sidebar */
          .tm-file-option { padding: 0.25rem 0.5rem; border-radius: 0.375rem; }
          html:not(.dark) .tm-file-option:hover { background: var(--lm-hover); }
          .dark .tm-file-option:hover { background: var(--tm-hover); }
          .tm-file-option--selected { border-left: 3px solid #DB9305; background: rgba(219,147,5,0.10); color: #6b4b00; }
          .dark .tm-file-option--selected { border-left-color: #DB9305; background: rgba(219,147,5,0.18); color: #ffd08a; }

          /* Highlight for selected File Name input */
          .tm-input-selected { background: #FFF7D1 !important; border-color: #F2C94C !important; }
          .dark .tm-input-selected { background: rgba(242,201,76,0.15) !important; border-color: #F2C94C !important; }

          /* Top navigation menu tweaks */
          .tm-menu { gap: 16px; }
          .tm-menu li { position: relative; }
          .tm-menu li + li { margin-left: 10px; }
          .tm-menu li + li::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 14px; /* short divider, not full height */
            background: var(--lm-border);
          }
          .dark .tm-menu li + li::before { background: var(--tm-border); }
        </style>
    @endif
</head>
<body class="bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100">
    @php $tm_embed = request()->boolean('embed'); @endphp
    @unless($tm_embed)
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur dark:bg-slate-900/80 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('translations.index') }}" class="flex items-center space-x-2 group">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white font-bold group-hover:bg-blue-700">TM</span>
                    <span class="font-semibold tracking-tight">Translation Manager</span>
                </a>
                <nav class="hidden md:flex items-center space-x-4 ml-6 text-sm">
                    <a href="{{ route('translations.index') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Dashboard</a>
                </nav>
                <div class="hidden md:flex items-center ml-4">
                    @include('translation-manager::partials.menu')
                </div>
            </div>
            <div class="flex items-center space-x-2">
                @if(Route::currentRouteName() !== 'translations.index')
                    <a href="{{ route('translations.index') }}" class="hidden sm:inline-flex items-center gap-1 rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        ← Back
                    </a>
                @endif
                <button
                    x-on:click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('tm_dark', dark ? '1' : '0')"
                    type="button"
                    class="inline-flex items-center rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    title="Toggle dark mode"
                >
                    <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6.76 4.84l-1.8-1.79-1.41 1.41 1.79 1.8 1.42-1.42zm10.45-1.79l-1.79 1.8 1.42 1.42 1.8-1.79-1.43-1.43zM12 4V1h-2v3h2zm0 19v-3h-2v3h2zm8-9h3v-2h-3v2zM1 12H4v-2H1v2zm15.24 7.16l1.8 1.79 1.41-1.41-1.79-1.8-1.42 1.42zM4.95 18.36l1.79-1.8-1.42-1.42-1.8 1.79 1.43 1.43zM17 12a5 5 0 11-5-5 5 5 0 015 5z"/></svg>
                    <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20.742 13.045A8.001 8.001 0 1110.955 3.258 7 7 0 0020.742 13.045z"/></svg>
                </button>
            </div>
        </div>
    </header>
    @endunless

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-0 pb-6">
        @yield('translation-manager-content')
    </main>

    @unless($tm_embed)
    <!-- Global Small Modal -->
    <div
      x-data="{
        open:false,
        title:'',
        mode:'iframe',
        src:'',
        confirm: { action: '', lang: '', message: '' },
        show(type){
          this.open = true;
          if(type === 'create'){
            this.title = 'Create New Translation File';
            this.mode = 'create';
            this.src = '';
          } else if(type === 'exclusions'){
            this.title = 'Manage Exclusions';
            this.mode = 'exclusions';
            this.src = '';
          } else if(type === 'folder'){
            this.title = 'Create New Folder';
            this.mode = 'folder';
            this.src = '';
          } else if(type === 'deleteLang'){
            this.title = 'Удаление языка';
            this.mode = 'confirm-delete-lang';
            this.src = '';
          } else if(type === 'confirmGeneric'){
            this.title = 'Удаление перевода';
            this.mode = 'confirm-generic';
            this.src = '';
          } else if(type === 'alert'){
            this.title = 'Уведомление';
            this.mode = 'alert';
            this.src = '';
          }
        },
        close(){ this.open = false; this.src = '' }
      }"
      x-init="window.addEventListener('tm:openModal', (e) => { if(e && e.detail && e.detail.type){ show(e.detail.type); if(e.detail.action){ confirm.action = e.detail.action } if(e.detail.lang){ confirm.lang = e.detail.lang } if(e.detail.message){ confirm.message = e.detail.message } } })"
    >
      <div
        x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
        aria-modal="true" role="dialog" aria-labelledby="tm-modal-title"
      >
        <div class="absolute inset-0 bg-black/40 dark:bg-black/60" x-on:click="close()"></div>
        <div class="relative w-full max-w-xl rounded-lg overflow-hidden bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl">
          <div class="flex items-center justify-between px-4 py-2 border-b border-slate-200 dark:border-slate-700">
            <h3 id="tm-modal-title" class="text-base font-semibold text-slate-800 dark:text-slate-100" x-text="title"></h3>
            <button class="tm-icon-btn" x-on:click="close()" aria-label="Close">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tm-icon"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <!-- Create New Translation File (inline) -->
          <template x-if="mode === 'create'">
            <div class="p-6">
              @include('translation-manager::partials.create_file_form', ['modal' => true])
            </div>
          </template>
          <!-- Simple Alert -->
          <template x-if="mode === 'alert'">
            <div class="p-6">
              <p class="text-sm text-slate-700 dark:text-slate-300 mb-4" x-text="confirm.message || 'Notice'"></p>
              <div class="flex items-center justify-end gap-2">
                <button type="button" x-on:click="close()" class="tm-btn-accent font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2">OK</button>
              </div>
            </div>
          </template>
          <!-- Generic confirm: prefer direct form submit when action provided; fallback to window.__tmConfirm -->
          <template x-if="mode === 'confirm-generic' && confirm.action">
            <form :action="confirm.action" method="POST" class="p-6">
              @csrf
              @method('DELETE')
              <p class="text-sm text-slate-700 dark:text-slate-300 mb-4" x-text="confirm.message || 'Are you sure?'"></p>
              <div class="flex items-center justify-end gap-2">
                <button type="button" x-on:click="close()" class="px-3 py-1.5 rounded-md text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
                <button type="submit" class="px-3 py-1.5 rounded-md text-sm bg-red-600 hover:bg-red-700 text-white font-semibold">Delete</button>
              </div>
            </form>
          </template>
          <template x-if="mode === 'confirm-generic' && !confirm.action">
            <div class="p-6">
              <p class="text-sm text-slate-700 dark:text-slate-300 mb-4" x-text="confirm.message || 'Are you sure?'"></p>
              <div class="flex items-center justify-end gap-2">
                <button type="button" x-on:click="close()" class="px-3 py-1.5 rounded-md text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
                <button type="button" class="px-3 py-1.5 rounded-md text-sm bg-red-600 hover:bg-red-700 text-white font-semibold" x-on:click="try { if (window.__tmConfirm) { window.__tmConfirm(); window.__tmConfirm = null } } finally { close() }">Delete</button>
              </div>
            </div>
          </template>
          <!-- Manage Exclusions (inline) -->
          <template x-if="mode === 'exclusions'">
            <div class="p-6">
              @include('translation-manager::partials.exclusions_form', ['modal' => true])
            </div>
          </template>
          <!-- Confirm Delete Language (inline) -->
          <template x-if="mode === 'confirm-delete-lang'">
            <div class="p-6">
              <form :action="confirm.action" method="POST">
                @csrf
                @method('DELETE')
                <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">
                  Вы уверены, что хотите удалить папку языка <span class="font-semibold" x-text="confirm.lang"></span> со всеми вложенными файлами и папками? Это действие необратимо!
                </p>
                <div class="flex items-center justify-end gap-2">
                  <button type="button" x-on:click="close()" class="px-3 py-1.5 rounded-md text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
                  <button type="submit" class="px-3 py-1.5 rounded-md text-sm bg-red-600 hover:bg-red-700 text-white font-semibold">Delete</button>
                </div>
              </form>
            </div>
          </template>
          <!-- Inline compact form for New Folder -->
          <template x-if="mode === 'folder'">
            <div class="p-6">
              <form action="{{ route('translations.folder.create') }}" method="POST">
                @csrf
                <div class="mb-3">
                  <label for="tm_modal_folder" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Folder Name</label>
                  <input id="tm_modal_folder" name="folder" type="text" required
                         class="mt-1 block w-full rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-500" placeholder="e.g., user, auth" />
                </div>
                <div class="mb-2">
                  <label for="tm_modal_locale" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Locale</label>
                  @php
                    $tm_dirs = \Illuminate\Support\Facades\File::directories(lang_path());
                    $tm_langs = array_map(fn($d) => basename($d), $tm_dirs);
                  @endphp
                  <select id="tm_modal_locale" name="locale"
                          class="mt-1 block w-full rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-500">
                    <option value="all">All Languages</option>
                    @foreach($tm_langs as $code)
                      @if($code !== '_json')
                        <option value="{{ $code }}">{{ $code }}</option>
                      @endif
                    @endforeach
                  </select>
                </div>
                <div class="flex items-center justify-end gap-2 mt-4">
                  <button type="button" x-on:click="close()" class="px-3 py-1.5 rounded-md text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
                  <button type="submit" class="tm-btn-accent font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2">Create Folder</button>
                </div>
              </form>
            </div>
          </template>
        </div>
      </div>
    </div>
    @endunless
</body>
</html>
