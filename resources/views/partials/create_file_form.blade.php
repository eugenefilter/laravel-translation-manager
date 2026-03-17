<form action="{{ route('translations.store') }}" method="POST">
  @csrf
  <div class="mb-4">
    <label for="lang" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Language Code (e.g., 'en', 'es', 'fr')</label>
    <input type="text" name="lang" id="lang" class="tm-input" required>
  </div>

  <div class="mb-4">
    <label for="file" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">File Path (e.g., 'messages', 'auth/login', 'nested/folder/file')</label>
    <input type="text" name="file" id="file" class="tm-input" placeholder="e.g., messages, auth/login, nested/folder/file" required>
  </div>

  <div class="flex items-center justify-between">
    <button type="submit" class="tm-btn-accent font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2">Create File</button>
    <a href="{{ route('translations.global.create') }}" class="tm-btn-accent-outline font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2">Global Add Key</a>
    @if(!($modal ?? false))
      <a href="{{ route('translations.index') }}" class="inline-block align-baseline font-semibold text-sm text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">Cancel</a>
    @else
      <button type="button" x-on:click="close()" class="inline-block align-baseline font-semibold text-sm text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">Cancel</button>
    @endif
  </div>
</form>
