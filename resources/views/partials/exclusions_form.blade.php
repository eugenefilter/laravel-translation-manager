@php
  if (!isset($exclusions)) {
    try {
      $configEx = config('translation-manager.exclude_from_global', []);
      $storage = storage_path('app/translation-manager-exclusions.json');
      $userEx = [];
      if (\Illuminate\Support\Facades\File::exists($storage)) {
        $json = json_decode(\Illuminate\Support\Facades\File::get($storage), true);
        if (is_array($json)) { $userEx = $json; }
      }
      $exclusions = array_unique(array_merge($configEx, $userEx));
    } catch (\Throwable $e) {
      $exclusions = $exclusions ?? [];
    }
  }
@endphp

<form action="{{ route('translations.exclusions.update') }}" method="POST">
  @csrf
  <div class="mb-4">
    <label for="exclusions" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">
      Exclusion List
    </label>
    <p class="text-sm text-gray-600 dark:text-slate-400 mb-2">
      Enter one file or directory path per line. These paths are relative to the `lang` directory and will be excluded from global operations. Example: `vendor` or `auth.php`.
    </p>
    <textarea name="exclusions" id="exclusions" rows="10"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-slate-100 bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 leading-tight focus:outline-none focus:ring-2 focus:ring-slate-500 font-mono">{{ implode("\n", $exclusions) }}</textarea>
  </div>

  <div class="flex items-center justify-end gap-2">
    @if(!($modal ?? false))
      <a href="{{ route('translations.index') }}"
         class="inline-block align-baseline font-semibold text-sm text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">
        Cancel
      </a>
    @else
      <button type="button" x-on:click="close()" class="px-3 py-1.5 rounded-md text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
    @endif
    <button type="submit" class="tm-btn-accent font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2">
      Save Exclusions
    </button>
  </div>
</form>
