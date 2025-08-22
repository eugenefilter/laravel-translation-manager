@foreach($nodes as $node)
    <div class="ml-4">
        @if($node['type'] === 'folder')
            <div x-data="{ open: false }">
                <div @click="open = !open" class="flex items-center cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700 p-1 rounded">
                    <!-- Lucide: folder / folder-open -->
                    <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 mr-2 text-slate-500 tm-folder-icon">
                      <path d="M4 5h5l2 2h9a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/>
                    </svg>
                    <svg x-show="open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 mr-2 text-slate-500 tm-folder-icon">
                      <path d="M4 5h5l2 2h9a2 2 0 0 1 2 2v2"/>
                      <path d="M3 12h17a2 2 0 0 1 1.9 2.6l-1.5 4A2 2 0 0 1 18.5 20h-13A2 2 0 0 1 3.4 18l-1-3A2 2 0 0 1 3 12Z"/>
                    </svg>
                    <span class="font-medium">{{ $node['name'] }}</span>
                </div>
                <div x-show="open" x-transition class="mt-1">
                    @include('translation-manager::_file_tree', ['nodes' => $node['children'], 'lang' => $lang])
                </div>
            </div>
        @else
            <div class="flex items-center hover:bg-gray-100 dark:hover:bg-slate-700 p-1 rounded">
                <!-- Lucide: file -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 mr-2 text-slate-500 dark:text-slate-400">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                  <path d="M14 2v6h6"/>
                </svg>
                <a href="{{ route('translations.show', ['path' => ($lang === '_json' ? '_json/' . $node['path'] : $lang . '/' . $node['path'])]) }}" class="tm-file-link hover:underline">
                  {{ $node['name'] }}
                </a>
            </div>
        @endif
    </div>
@endforeach
