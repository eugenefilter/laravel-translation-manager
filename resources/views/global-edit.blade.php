@extends('translation-manager::layout')

@section('translation-manager-content')
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Global Translation Edit</h1>

        @if(session('success') || isset($success))
            <div class="px-4 py-3 rounded mb-4 border bg-green-50 border-green-200 text-green-800 dark:bg-green-900/30 dark:text-green-200 dark:border-green-800" role="alert">
                <span class="block sm:inline">{{ session('success') ?? $success }}</span>
            </div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
            <form action="{{ route('translations.global.update', ['key' => $key]) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="new_key" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Translation Key</label>
                    <input type="text" name="new_key" id="new_key" class="tm-input" value="{{ $key }}">
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-semibold mb-2">Translations</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-slate-200 dark:border-slate-700">
                            <thead class="bg-gray-50 dark:bg-slate-700/50">
                                <tr>
                                    <th class="px-4 py-3 w-16"><input type="checkbox" class="tm-check align-middle" checked @click="document.querySelectorAll('.lang-checkbox').forEach(el => el.checked = $el.checked)"></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">Language</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">Translation</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                @foreach($languageCodes as $langCode)
                                    <tr>
                                        <td class="px-4 py-3"><input type="checkbox" name="languages_to_update[]" value="{{ $langCode }}" class="tm-check lang-checkbox align-middle" checked></td>
                                        <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-slate-100">{{ strtoupper($langCode) }}</td>
                                        <td class="px-4 py-3">
                                            <input type="text" name="translations[{{ $langCode }}]" id="translations_{{ $langCode }}" class="tm-input p-2" value="{{ $translations[$langCode] ?? '' }}">
                                        </td>
                                    </tr>
                                @endforeach
                                @if(isset($translations['_json']))
                                     <tr>
                                         <td class="px-4 py-3"><input type="checkbox" name="languages_to_update[]" value="_json" class="tm-check lang-checkbox align-middle" checked></td>
                                         <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-slate-100">JSON</td>
                                         <td class="px-4 py-3">
                                             <input type="text" name="translations[_json]" id="translations__json" class="tm-input p-2" value="{{ $translations['_json'] ?? '' }}">
                                         </td>
                                     </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <a href="{{ route('translations.index') }}"
                       class="inline-block align-baseline font-bold text-sm text-gray-600 dark:text-slate-300 hover:text-gray-800 dark:hover:text-white mr-4">
                        Cancel
                    </a>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Update Key Globally
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
