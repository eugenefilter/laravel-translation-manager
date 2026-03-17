@extends('translation-manager::layout')

@section('translation-manager-content')
  <div class="container mx-auto p-4" x-data="translationEditor({{ json_encode($translations) }})">
    

    @if(session('success'))
      <div class="p-4 mb-4 border-l-4 border-green-500 bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-200 dark:border-green-700" role="alert">
        <p class="font-bold">Success</p>
        <p>{{ session('success') }}</p>
      </div>
    @endif

    <!-- Breadcrumbs -->
    <nav class="tm-breadcrumb mt-1 mb-5 text-base">
      <a href="{{ route('translations.index') }}" class="hover:underline">Translations</a>
      <span class="mx-1">/</span>
      <span class="capitalize">{{ $lang === '_json' ? 'JSON' : $lang }}</span>
      <span class="mx-1">/</span>
      <span class="font-medium text-slate-800 dark:text-slate-100">{{ $file }}</span>
    </nav>

    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-3xl font-bold">Edit Translations</h1>
      <a href="{{ route('translations.matrix.show', ['path' => ($lang === '_json' ? '_json/' . $file : $lang . '/' . $file)]) }}"
         class="tm-btn-accent-outline font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2">
        Edit across languages
      </a>
    </div>

    <div class="mb-6 flex justify-between items-center">
      <input type="text" x-model="searchTerm" placeholder="Search keys or translations..." class="tm-input mr-4">
      <button type="button" @click="showAddForm = !showAddForm"
              class="tm-btn-accent font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2 whitespace-nowrap">
        + Add New Key
      </button>
    </div>

    <form x-ref="translationForm" action="{{ route('translations.update', ['path' => ($lang === '_json' ? '_json/' . $file : $lang . '/' . $file)]) }}"
          method="POST">
      @csrf
      <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200 dark:bg-slate-800 dark:border-slate-700">

        <div x-show="showAddForm" class="mb-6 p-4 border border-blue-200 rounded-lg bg-blue-50">
          <h3 class="text-xl font-semibold mb-3">Add New Translation</h3>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
              <div class="md:col-span-2">
                <input type="text"
                       x-model="newKey"
                       @keydown.enter.prevent="addNewTranslation()"
                       class="mt-1 block w-full rounded-md border border-slate-200 bg-white text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                       placeholder="new.key.name">
              </div>
              <div class="md:col-span-2">
                <input type="text"
                       x-model="newValue"
                       @keydown.enter.prevent="addNewTranslation()"
                       class="mt-1 block w-full rounded-md border border-slate-200 bg-white text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                       placeholder="New Value">
              </div>
            <div class="md:col-span-1">
              <button type="button" @click="addNewTranslation()"
                      class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                Add
              </button>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <template x-for="(translation, index) in paginatedTranslations" :key="index">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
              <div class="md:col-span-2">
                <div class="flex items-center gap-2">
                  <input type="text"
                         :name="`translations[${index}][key]`"
                         x-model="translation.key"
                         @keydown.enter.prevent="saveChanges()" @change="saveChanges()"
                         class="tm-input p-2"
                         placeholder="key.with.dots">
                  <a :href="`{{ route('translations.index') }}?q=${encodeURIComponent(translation.key)}`"
                     class="tm-icon-btn tm-icon-btn--accent" title="Global search for this key" aria-label="Global search for this key">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tm-icon">
                      <circle cx="11" cy="11" r="8"/>
                      <path d="m21 21-4.3-4.3"/>
                    </svg>
                  </a>
                </div>
              </div>
              <div class="md:col-span-2">
                <input type="text"
                       :name="`translations[${index}][value]`"
                       x-model="translation.value"
                       @keydown.enter.prevent="saveChanges()" @change="saveChanges()"
                       class="tm-input p-2"
                       placeholder="Value">
              </div>
              <div class="md:col-span-1">
                <button type="button" @click="remove(index)" class="tm-icon-btn tm-icon-btn--danger" title="Remove" aria-label="Remove">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tm-icon">
                    <path d="M3 6h18"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                  </svg>
                </button>
              </div>
            </div>
          </template>
          <div x-show="filteredTranslations.length === 0 && searchTerm.length > 0"
               class="text-gray-500 dark:text-slate-400 text-center py-4">
            No matching translations found.
          </div>
          <div x-show="translations.length === 0 && searchTerm.length === 0" class="text-gray-500 dark:text-slate-400 text-center py-4">
            No translations found in this file.
          </div>
        </div>

        
      </div>
    </form>

    <div class="mt-8 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div class="flex items-center gap-3">
        <label for="perPage" class="text-slate-700 dark:text-slate-300">Items per page</label>
        <select x-model="itemsPerPage" id="perPage"
                class="rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 sm:text-sm">
          <template x-for="option in perPageOptions" :key="option">
            <option :value="option" x-text="option"></option>
          </template>
        </select>
      </div>
      <div class="flex items-center gap-3">
        <button type="button" @click="prevPage()" :disabled="currentPage === 1"
                class="tm-btn-accent-outline font-semibold py-2 px-3 rounded focus:outline-none focus:ring-2 disabled:opacity-40 disabled:cursor-not-allowed">
          Prev
        </button>
        <span class="text-slate-700 dark:text-slate-300">Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span></span>
        <button type="button" @click="nextPage()" :disabled="currentPage === totalPages"
                class="tm-btn-accent font-semibold py-2 px-3 rounded focus:outline-none focus:ring-2 disabled:opacity-40 disabled:cursor-not-allowed">
          Next
        </button>
      </div>
    </div>
  </div>

  <script>
    function translationEditor(translations) {
      return {
        translations: translations,
        searchTerm: '',
        currentPage: 1,
        itemsPerPage: 20,
        perPageOptions: [10, 20, 50, 100],
        newKey: '',
        newValue: '',
        showAddForm: false,

        init() {
          if (this.$el.dataset.initialized) {
            return;
          }
          this.$el.dataset.initialized = true;

          this.$watch('searchTerm', () => this.currentPage = 1);
          this.$watch('itemsPerPage', () => this.currentPage = 1);
        },

        get filteredTranslations() {
          if (this.searchTerm.length === 0) {
            return this.translations;
          }
          const lowerSearchTerm = this.searchTerm.toLowerCase();
          return this.translations.filter(item => {
            return item.key.toLowerCase().includes(lowerSearchTerm) ||
              item.value.toLowerCase().includes(lowerSearchTerm);
          });
        },

        get totalPages() {
          return Math.ceil(this.filteredTranslations.length / this.itemsPerPage);
        },

        get paginatedTranslations() {
          const start = (this.currentPage - 1) * this.itemsPerPage;
          const end = start + this.itemsPerPage;
          return this.filteredTranslations.slice(start, end);
        },

        addNewTranslation() {
          if (this.newKey.trim() === '') {
            window.dispatchEvent(new CustomEvent('tm:openModal', { detail: { type: 'alert', message: 'Key cannot be empty.' } }));
            return;
          }
          // Check if key already exists
          if (this.translations.some(t => t.key === this.newKey.trim())) {
            window.dispatchEvent(new CustomEvent('tm:openModal', { detail: { type: 'alert', message: 'Key already exists.' } }));
            return;
          }
          this.translations.push({key: this.newKey.trim(), value: this.newValue});
          this.newKey = '';
          this.newValue = '';
          this.showAddForm = false; // Hide form after adding
          this.currentPage = this.totalPages; // Go to the last page to see the new item
          this.$nextTick(() => {
            this.saveChanges(); // Trigger save after adding
          });
        },
        remove(index) {
          const self = this;
          window.__tmConfirm = function() {
            // Adjust index for pagination
            const globalIndex = (self.currentPage - 1) * self.itemsPerPage + index;
            self.translations.splice(globalIndex, 1);
            if (self.paginatedTranslations.length === 0 && self.currentPage > 1) {
              self.currentPage--;
            }
            self.$nextTick(() => { self.saveChanges(); });
          };
          window.dispatchEvent(new CustomEvent('tm:openModal', { detail: { type: 'confirmGeneric', message: 'Вы уверены, что хотите удалить эту запись?' } }));
        },
        saveChanges() {
          this.$refs.translationForm.submit();
        },
        nextPage() {
          if (this.currentPage < this.totalPages) {
            this.currentPage++;
          }
        },
        prevPage() {
          if (this.currentPage > 1) {
            this.currentPage--;
          }
        }
      }
    }
  </script>
@endsection
