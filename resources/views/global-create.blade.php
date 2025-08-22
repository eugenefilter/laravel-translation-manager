@extends('translation-manager::layout')

@section('translation-manager-content')
  <div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6">Global Translation Management</h1>

    @if(empty($languageCodes) && empty($existingFilesGrouped['_json']))
      <div class="bg-white p-6 rounded-lg shadow-md text-center border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <h2 class="text-2xl font-bold mb-4">No Translation Files Found</h2>
        <p class="text-gray-600 dark:text-slate-400 mb-6">You need to create at least one translation file before you can add a global
          key.</p>
        <a href="{{ route('translations.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
          Create a New Translation File
        </a>
      </div>
    @else
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{ selectedFile: '' }">
        <div class="lg:col-span-2">
          <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200 dark:bg-slate-800 dark:border-slate-700 flex flex-col min-h-0" style="height: calc(100vh - 180px);">
            <form action="{{ route('translations.global.store') }}" method="POST" class="flex flex-col min-h-0">
              @csrf
              <div class="mb-4">
                <label for="key" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Translation Key</label>
                <input type="text" name="key" id="key" class="tm-input" required placeholder="e.g., product.price">
              </div>

              <div class="mb-6">
                <label for="file" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">File Name</label>
                <input list="existing-files" name="file" id="file" x-ref="fileInput" x-on:input="selectedFile = $refs.fileInput.value" class="tm-input" required placeholder="Select or create a file (e.g., messages, auth)">
                <datalist id="existing-files">
                  @foreach($datalist as $file)
                    <option value="{{ $file }}">
                  @endforeach
                </datalist>
              </div>

              <div class="mb-4 flex-1 overflow-auto min-h-0 tm-scroll">
                <h3 class="text-lg font-semibold mb-2">Translations</h3>
                <div class="overflow-x-auto">
                  <table class="min-w-full border border-slate-200 dark:border-slate-700">
                    <thead class="bg-gray-50 dark:bg-slate-700/50">
                    <tr>
                      <th class="px-4 py-3 w-10"><input type="checkbox" class="tm-check align-middle" checked @click="document.querySelectorAll('.tm-lang-check').forEach(el => el.checked = $el.checked)"></th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">
                        Language
                      </th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">
                        Translation
                      </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                    @foreach($languageCodes as $langCode)
                      <tr>
                        <td class="px-4 py-3"><input type="checkbox" name="languages_to_update[]" value="{{ $langCode }}" class="tm-check tm-lang-check align-middle" checked></td>
                        <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-slate-100">{{ strtoupper($langCode) }}</td>
                        <td class="px-4 py-3">
                          <input type="text" name="translations[{{ $langCode }}]" id="translations_{{ $langCode }}" class="tm-input p-2">
                        </td>
                      </tr>
                    @endforeach
                    @if(isset($existingFilesGrouped['_json']))
                      <tr>
                        <td class="px-4 py-3"><input type="checkbox" name="languages_to_update[]" value="_json" class="tm-check tm-lang-check align-middle" checked></td>
                        <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-slate-100">JSON</td>
                        <td class="px-4 py-3">
                          <input type="text" name="translations[_json]" id="translations__json" class="tm-input p-2" placeholder="Value for JSON files">
                        </td>
                      </tr>
                    @endif
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="flex items-center justify-end pt-4">
                <a href="{{ route('translations.index') }}"
                   class="inline-block align-baseline font-bold text-sm text-gray-600 dark:text-slate-300 hover:text-gray-800 dark:hover:text-white mr-4">
                  Cancel
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                  Add Key Globally
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="lg:col-span-1">
          <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200 dark:bg-slate-800 dark:border-slate-700 tm-scroll" style="height: calc(100vh - 180px); overflow: auto;">
            <h2 class="text-2xl font-bold mb-4">Existing Files</h2>
            @php $fileInputId = 'file'; @endphp
            @forelse($existingFilesGrouped as $lang => $files)
              <div class="mb-4">
                <h3 class="text-lg font-semibold capitalize mb-2 border-b pb-1 border-slate-200 dark:border-slate-700">{{ $lang === '_json' ? 'JSON Files' : $lang }}</h3>
                <ul class="space-y-1 text-gray-700 dark:text-slate-300">
                  @foreach($files as $file)
                    <li>
                      <button type="button"
                              :class="selectedFile === '{{ $file }}' ? 'tm-file-option tm-file-option--selected w-full text-left' : 'tm-file-option w-full text-left'"
                              @click="$refs.fileInput.value='{{ $file }}'; $refs.fileInput.focus(); $refs.fileInput.dispatchEvent(new Event('input',{bubbles:true})); selectedFile='{{ $file }}'">
                        {{ $file }}
                      </button>
                    </li>
                  @endforeach
                </ul>
              </div>
            @empty
              <p class="text-gray-500 dark:text-slate-400">No existing translation files found.</p>
            @endforelse
            <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Click a file to auto-fill “File Name”.</p>
          </div>
        </div>
      </div>
    @endif
  </div>
@endsection
