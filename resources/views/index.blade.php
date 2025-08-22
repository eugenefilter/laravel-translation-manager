@extends('translation-manager::layout')

@section('translation-manager-content')

  <div class="container mx-auto p-4" x-data="translationManager({{ json_encode($allTranslations) }})"
       x-init="window.addEventListener('tm:openCreateFolder',()=>{ showCreateFolderModal = true });
               (function(){
                 try {
                   const usp = new URLSearchParams(window.location.search);
                   if (usp.get('newFolder') === '1') {
                     showCreateFolderModal = true;
                   }
                   const q = usp.get('q');
                   if (q && q.length) {
                     searchTerm = q;
                   }
                   if (usp.has('newFolder') || usp.has('q')) {
                     history.replaceState({}, '', window.location.pathname);
                   }
                 } catch (e) {}
               })()">

    <!-- Create Folder Modal -->
    <div x-show="showCreateFolderModal" class="fixed inset-0 bg-slate-900/60 flex items-center justify-center"
         style="display: none;">
      <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-6 rounded-lg shadow-md w-full max-w-md"
        @click.away="showCreateFolderModal = false">
        <h2 class="text-2xl font-bold mb-4">Create New Folder</h2>
        <form action="{{ route('translations.folder.create') }}" method="POST">
          @csrf
          <div class="mb-4">
            <label for="folder" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Folder
              Name</label>
            <input type="text" name="folder" id="folder" class="tm-input" placeholder="e.g., user, auth" required>
          </div>
          <div class="mb-4">
            <label for="locale"
                   class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Locale</label>
            <select name="locale" id="locale" class="tm-input">
              <option value="all">All Languages</option>
              @foreach(array_keys($languages) as $lang)
                @if($lang !== '_json')
                  <option value="{{ $lang }}">{{ $lang }}</option>
                @endif
              @endforeach
            </select>
          </div>
          <div class="flex items-center justify-between">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
              Create Folder
            </button>
            <button type="button" @click="showCreateFolderModal = false"
                    class="inline-block align-baseline font-semibold text-sm text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="mb-6">
      <input type="text" x-model="searchTerm" placeholder="Search keys or translations..." class="tm-input">
    </div>

    <template x-if="searchTerm.length > 0">
      <div
        class="bg-white p-6 rounded-lg shadow-md mb-6 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <h2 class="text-2xl font-bold mb-4">Search Results</h2>
        <div x-show="Object.keys(groupedTranslations).length === 0" class="text-gray-500 dark:text-slate-400">No
          results
          found.
        </div>
        <div class="space-y-4">
          <template x-for="(group, key) in groupedTranslations" :key="key">
            <div class="border-b border-gray-300 pb-4">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-semibold font-mono" x-text="key"></h3>
                <div class="flex space-x-2">
                  <a :href="`{{ route('translations.index') }}/global/edit/${encodeURIComponent(key)}`" class="tm-icon-btn tm-icon-btn--accent" title="Global edit" aria-label="Global edit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tm-icon">
                      <path d="M12 20h9"/>
                      <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                    </svg>
                  </a>
                  <form :action="`{{ route('translations.index') }}/global/delete/${encodeURIComponent(key)}`"
                        method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            x-on:click="window.__tmConfirm = () => $el.closest('form').submit(); window.dispatchEvent(new CustomEvent('tm:openModal', { detail: { type: 'confirmGeneric', message: 'Вы уверены, что хотите удалить этот ключ из всех файлов?' } }));"
                            class="tm-icon-btn tm-icon-btn--danger" title="Delete key everywhere" aria-label="Delete key everywhere">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tm-icon">
                        <path d="M3 6h18"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        <line x1="10" y1="11" x2="10" y2="17"/>
                        <line x1="14" y1="11" x2="14" y2="17"/>
                      </svg>
                    </button>
                  </form>
                </div>
              </div>
              <div class="pl-4 space-y-1">
                <template x-for="item in group" :key="item.lang + item.file">
                  <div class="flex items-center text-sm">
                    <span
                      class="inline-block rounded-full px-2 py-1 text-xs font-semibold mr-2 w-12 text-center bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-slate-200"
                      x-text="item.lang === '_json' ? 'JSON' : item.lang"></span>
                    <span class="text-gray-600 dark:text-slate-400 mr-2" x-text="item.file + ':'"></span>
                    <span class="text-gray-800 dark:text-slate-100" x-text="item.value"></span>
                  </div>
                </template>
              </div>
            </div>
          </template>
        </div>
      </div>
    </template>

    <template x-if="searchTerm.length === 0">
      @if(empty($languages))
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
          <p class="text-gray-500">No translation files found.</p>
        </div>
      @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($languages as $lang => $nodes)
            <div
              class="bg-white p-6 rounded-lg shadow-md border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold capitalize">
                  @if($lang === '_json')
                    JSON Files
                  @else
                    {{ $lang }}
                  @endif
                </h2>
                @if($lang !== '_json')
                  {{-- Не удаляем папку _json --}}
                  <button type="button"
                          onclick="window.dispatchEvent(new CustomEvent('tm:openModal', { detail: { type: 'deleteLang', action: '{{ route('translations.language.destroy', ['lang' => $lang]) }}', lang: '{{ $lang }}' } }))"
                          class="tm-icon-btn tm-icon-btn--danger" title="Delete language {{ $lang }}" aria-label="Delete language {{ $lang }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tm-icon">
                      <path d="M3 6h18"/>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                      <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                      <line x1="10" y1="11" x2="10" y2="17"/>
                      <line x1="14" y1="11" x2="14" y2="17"/>
                    </svg>
                  </button>
                @endif
              </div>
              <div class="space-y-1">
                @include('translation-manager::_file_tree', ['nodes' => $nodes, 'lang' => $lang])
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </template>
  </div>

  <script>
    function translationManager(allTranslations) {
      return {
        searchTerm: '',
        allTranslations: allTranslations,
        showCreateFolderModal: false,

        get groupedTranslations() {
          if (this.searchTerm.length < 2) {
            return {};
          }
          const lowerSearchTerm = this.searchTerm.toLowerCase();

          const filtered = this.allTranslations.filter(item => {
            return item.key.toLowerCase().includes(lowerSearchTerm) ||
              (item.value && typeof item.value === 'string' && item.value.toLowerCase().includes(lowerSearchTerm));
          });

          // Group by key
          return filtered.reduce((acc, item) => {
            if (!acc[item.key]) {
              acc[item.key] = [];
            }
            acc[item.key].push(item);
            return acc;
          }, {});
        }
      }
    }
  </script>
@endsection
