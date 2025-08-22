<ul class="tm-menu flex items-center">
  <li>
    <div
      x-on:click="window.dispatchEvent(new CustomEvent('tm:openModal', { detail: { type: 'create' } }))"
      class="cursor-pointer select-none px-2.5 py-1.5 rounded-md text-slate-600 hover:text-slate-900 hover:bg-gray-100 transition-colors dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-800"
      role="button" tabindex="0"
    >
      New File
    </div>
  </li>
  <li>
    <a
      href="{{ route('translations.global.create') }}"
      class="px-2.5 py-1.5 rounded-md text-slate-600 hover:text-slate-900 hover:bg-gray-100 transition-colors dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-800"
    >
      Global Add
    </a>
  </li>
  <li>
    <div
      x-on:click="window.dispatchEvent(new CustomEvent('tm:openModal', { detail: { type: 'folder' } }))"
      class="cursor-pointer select-none px-2.5 py-1.5 rounded-md text-slate-600 hover:text-slate-900 hover:bg-gray-100 transition-colors dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-800"
      role="button" tabindex="0"
    >
      New Folder
    </div>
  </li>
  <li>
    <div
      x-on:click="window.dispatchEvent(new CustomEvent('tm:openModal', { detail: { type: 'exclusions' } }))"
      class="cursor-pointer select-none px-2.5 py-1.5 rounded-md text-slate-600 hover:text-slate-900 hover:bg-gray-100 transition-colors dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-800"
      role="button" tabindex="0"
    >
      Exclusions
    </div>
  </li>
</ul>
