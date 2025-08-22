@extends('translation-manager::layout')

@section('translation-manager-content')
  <div class="container mx-auto p-4">

    @if(session('success'))
      <div class="p-4 mb-4 border-l-4 border-green-500 bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-200 dark:border-green-700" role="alert">
        <p class="font-bold">Success</p>
        <p>{{ session('success') }}</p>
      </div>
    @endif

    <!-- Breadcrumbs + right-aligned description -->
    <div class="mt-1 mb-3 text-base flex items-center justify-between gap-3">
      <nav class="tm-breadcrumb">
        <a href="{{ route('translations.index') }}" class="hover:underline">Translations</a>
        <span class="mx-1">/</span>
        <span>Matrix</span>
        <span class="mx-1">/</span>
        <span class="font-medium text-slate-800 dark:text-slate-100">{{ $path }}</span>
      </nav>
      <div class="text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
        @if($isJson)
          Редактирование JSON по локалям
        @elseif($isVendor)
          Редактирование: <span class="font-mono">{{ $vendorPackage }}/{{ $relative }}.php</span>
        @else
          Редактирование файла <span class="font-mono">{{ $relative }}.php</span> по локалям
        @endif
      </div>
    </div>

    <form action="{{ route('translations.matrix.update', ['path' => $path]) }}" method="POST" id="tm-matrix-form">
      @csrf
      <div class="mb-2 flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <input id="tm-search" type="text" class="tm-input w-80" placeholder="Поиск по ключам и переводам...">
        </div>
        <div class="flex items-center gap-4 text-sm text-slate-700 dark:text-slate-300">
          <div class="flex items-center gap-3">
            <span>Только неполные</span>
            <div class="relative inline-flex items-center">
              <input id="tm-filter-missing" type="checkbox" class="sr-only peer">
              <label for="tm-filter-missing" class="relative inline-flex h-6 w-11 cursor-pointer items-center rounded-full bg-slate-300 transition-colors peer-checked:bg-emerald-600">
                <span class="sr-only">Только неполные</span>
                <span class="absolute left-1 h-5 w-5 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
              </label>
            </div>
          </div>
          <button type="button" id="tm-addkey-btn" class="tm-btn-accent font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2">Добавить ключ</button>
        </div>
      </div>

      <div class="relative bg-white p-4 rounded-lg shadow-md border border-slate-200 dark:bg-slate-800 dark:border-slate-700 overflow-auto max-h-[70vh]" id="tm-matrix-scroll">
        <!-- Inline error toast for AJAX -->
        <div id="tm-error-toast" class="hidden absolute top-2 right-2 bg-red-600 text-white text-sm px-3 py-2 rounded shadow" role="alert"></div>
        <!-- Левый/правый индикатор прокрутки -->
        <div id="tm-scroll-left" class="hidden absolute inset-y-0 left-0 w-8 pointer-events-none flex items-center justify-start z-20">
          <div class="h-10 w-8 bg-gradient-to-r from-white/90 to-transparent dark:from-slate-800/90 flex items-center justify-start">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-slate-500 ml-1"><path d="m15 18-6-6 6-6"/></svg>
          </div>
        </div>
        <div id="tm-scroll-right" class="hidden absolute inset-y-0 right-0 w-8 pointer-events-none flex items-center justify-end z-20">
          <div class="h-10 w-8 bg-gradient-to-l from-white/90 to-transparent dark:from-slate-800/90 flex items-center justify-end">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-slate-500 mr-1"><path d="m9 18 6-6-6-6"/></svg>
          </div>
        </div>

        <!-- Липкая шапка: всегда видна при вертикальном и горизонтальном скролле -->
        <div class="sticky z-30 bg-white dark:bg-slate-800" style="top:-25px; height:60px;">
          <table class="min-w-full text-sm table-fixed">
            <colgroup>
              <col style="width:250px">
              @foreach($languageCodes as $code)
                <col style="width:256px">
              @endforeach
            </colgroup>
            <thead>
              <tr class="text-left border-b border-slate-200 dark:border-slate-700">
                <th class="px-3 py-2 h-[60px] align-middle font-semibold text-slate-700 dark:text-slate-200 sticky left-0 bg-white dark:bg-slate-800 z-30 w-[250px] min-w-[250px] max-w-[250px] border-r border-slate-200 dark:border-slate-700">Key</th>
                @foreach($languageCodes as $code)
                  <th class="px-3 py-2 h-[60px] align-middle font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $code }}</th>
                @endforeach
              </tr>
            </thead>
          </table>
        </div>
        <!-- Заполнитель высоты под шапку, чтобы контент не перекрывался -->
        <div class="h-[60px]"></div>

        <!-- Убрали отдельную шапку: теперь липкая шапка — это thead основной таблицы ниже -->

        <!-- Добавление нового ключа глобально (в общей области прокрутки): скроллятся только поля -->
        <div class="mb-4">
          <table class="min-w-full text-sm table-fixed">
            <colgroup>
              <col style="width:250px">
              @foreach($languageCodes as $code)
                <col style="width:256px">
              @endforeach
            </colgroup>
            <tbody>
              <tr class="align-top">
                <td class="px-3 py-2 sticky left-0 bg-white dark:bg-slate-800 z-10 border-r border-slate-200 dark:border-slate-700 w-[250px] min-w-[250px] max-w-[250px]">
                  <input type="text" name="new_key" id="tm-new-key" class="tm-input w-[230px]" placeholder="ключ.с.точками">
                </td>
                @foreach($languageCodes as $code)
                  <td class="px-3 py-2">
                    <input type="text" name="new_translations[{{ $code }}]" class="tm-input tm-input--dense w-full tm-addkey-input" placeholder="Значение" data-lang="{{ $code }}">
                  </td>
                @endforeach
              </tr>
            </tbody>
          </table>
        </div>

        <table class="min-w-full text-sm table-fixed" id="tm-main-table">
          <colgroup>
            <col style="width:250px">
            @foreach($languageCodes as $code)
              <col style="width:256px">
            @endforeach
          </colgroup>
          <!-- thead убран, шапка вынесена отдельно выше как sticky -->
          <tbody id="tm-main-tbody">
          @forelse($keys as $k)
            <tr class="border-t border-slate-200 dark:border-slate-700 align-top" data-key="{{ $k }}">
              <td class="px-3 py-2 font-mono text-xs text-slate-700 dark:text-slate-300 sticky left-0 bg-white dark:bg-slate-800 z-10 border-r border-slate-200 dark:border-slate-700 w-[250px] min-w-[250px] max-w-[250px] break-words">{{ $k }}</td>
              @foreach($languageCodes as $code)
                <td class="px-3 py-2">
                  <input type="text"
                         name="translations[{{ $code }}][{{ $k }}]"
                         value="{{ old("translations.$code.$k", $values[$code][$k] ?? '') }}"
                         class="tm-input tm-input--dense w-full tm-matrix-input" data-lang="{{ $code }}" data-key="{{ $k }}">
                </td>
              @endforeach
            </tr>
          @empty
            <tr>
              <td colspan="{{ 1 + count($languageCodes) }}" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">No keys found in these files.</td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>

      <!-- Кнопки сохранения и возврата убраны по просьбе пользователя (автосохранение по Enter/blur) -->
    </form>
  </div>
  <script>
    (function() {
      const form = document.getElementById('tm-matrix-form');
      const scroller = document.getElementById('tm-matrix-scroll');
      const left = document.getElementById('tm-scroll-left');
      const right = document.getElementById('tm-scroll-right');
      const token = form.querySelector('input[name="_token"]').value;
      const cellUrl = "{{ route('translations.matrix.cell', ['path' => $path]) }}";
      const addUrl = "{{ route('translations.matrix.add', ['path' => $path]) }}";

      function showError(msg) {
        const toast = document.getElementById('tm-error-toast');
        if (!toast) return;
        toast.textContent = msg || 'Ошибка сохранения';
        toast.classList.remove('hidden');
        clearTimeout(window.__tmErrTO);
        window.__tmErrTO = setTimeout(()=> toast.classList.add('hidden'), 2500);
      }

      function updateArrows() {
        const canScrollLeft = scroller.scrollLeft > 4;
        const canScrollRight = scroller.scrollLeft + scroller.clientWidth < scroller.scrollWidth - 4;
        left.classList.toggle('hidden', !canScrollLeft);
        right.classList.toggle('hidden', !canScrollRight);
      }

      scroller.addEventListener('scroll', updateArrows);
      window.addEventListener('resize', updateArrows);
      setTimeout(updateArrows, 0);

      // Динамическая высота скролл-контейнера, чтобы не было скролла всей страницы
      function resizeMatrix() {
        const rect = scroller.getBoundingClientRect();
        const viewport = window.innerHeight || document.documentElement.clientHeight;
        const padding = 16; // небольшой отступ снизу
        const h = Math.max(200, Math.floor(viewport - rect.top - padding));
        scroller.style.maxHeight = h + 'px';
        scroller.style.height = h + 'px';
      }
      resizeMatrix();
      window.addEventListener('resize', () => { resizeMatrix(); updateArrows(); });

      async function saveCell(input) {
        const lang = input.dataset.lang;
        const key = input.dataset.key;
        const value = input.value;
        if (!lang || !key) return;
        input.disabled = true;
        let res;
        try {
          const fd = new FormData();
          fd.append('_token', token);
          fd.append('lang', lang);
          fd.append('key', key);
          fd.append('value', value);
          res = await fetch(cellUrl, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
          if (!res.ok) throw new Error('Network error');
          try { const j = await res.json(); if (!j || j.status !== 'ok') throw new Error(j && j.message ? j.message : 'Save failed'); } catch(eJSON) { throw eJSON; }
          input.classList.add('ring-2','ring-emerald-500');
          setTimeout(()=>input.classList.remove('ring-2','ring-emerald-500'), 600);
          // После успешного сохранения считаем текущее значение базовым
          input.dataset.prev = input.value;
        } catch (e) {
          input.classList.add('ring-2','ring-red-500');
          try { if (res) { const t = await res.text(); if (t) showError(t); else showError('Ошибка сохранения'); } else { showError('Ошибка сохранения'); } } catch(e2) { showError('Ошибка сохранения'); }
          setTimeout(()=>input.classList.remove('ring-2','ring-red-500'), 1000);
        } finally {
          input.disabled = false;
        }
      }

      // Автосохранение: только по Enter
      form.addEventListener('keydown', function(e) {
        const target = e.target;
        if (!(target instanceof HTMLInputElement)) return;
        if (target.classList.contains('tm-matrix-input') && e.key === 'Enter') {
          e.preventDefault();
          saveCell(target);
        }
      });
      // Запоминаем исходное значение при фокусе и сохраняем по blur, если изменилось
      form.addEventListener('focusin', function(e) {
        const target = e.target;
        if (!(target instanceof HTMLInputElement)) return;
        if (target.classList.contains('tm-matrix-input')) {
          target.dataset.prev = target.value;
        }
      });
      form.addEventListener('blur', function(e) {
        const target = e.target;
        if (!(target instanceof HTMLInputElement)) return;
        if (target.classList.contains('tm-matrix-input')) {
          if ((target.dataset.prev ?? '') !== target.value) {
            saveCell(target);
          }
        }
      }, true);

      // Добавление нового ключа по кнопке (AJAX)
      const addBtn = document.getElementById('tm-addkey-btn');
      addBtn.addEventListener('click', async function() {
        const keyInput = document.getElementById('tm-new-key');
        const key = (keyInput.value || '').trim();
        if (!key) {
          keyInput.focus();
          keyInput.classList.add('ring-2','ring-red-500');
          setTimeout(()=>keyInput.classList.remove('ring-2','ring-red-500'), 800);
          return;
        }
        const values = {};
        document.querySelectorAll('.tm-addkey-input').forEach(inp => {
          values[inp.dataset.lang] = inp.value;
        });
        const fd = new FormData();
        fd.append('_token', token);
        fd.append('new_key', key);
        Object.keys(values).forEach(l => fd.append(`new_translations[${l}]`, values[l]));
        addBtn.disabled = true;
        try {
          const res = await fetch(addUrl, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
          if (!res.ok) throw new Error('Network error');
          const j = await res.json();
          if (!j || j.status !== 'ok') throw new Error(j && j.message ? j.message : 'Add failed');
          // Вставить новую строку в таблицу
          const tbody = scroller.querySelector('#tm-main-tbody');
          if (tbody) {
            const tr = document.createElement('tr');
            tr.className = 'border-t border-slate-200 dark:border-slate-700 align-top';
            tr.setAttribute('data-key', key);
            const tdKey = document.createElement('td');
            tdKey.className = 'px-3 py-2 font-mono text-xs text-slate-700 dark:text-slate-300 sticky left-0 bg-white dark:bg-slate-800 z-10 border-r border-slate-200 dark:border-slate-700 w-[250px] min-w-[250px] max-w-[250px]';
            tdKey.textContent = key;
            tr.appendChild(tdKey);
            @foreach($languageCodes as $code)
            {
              const td = document.createElement('td');
              td.className = 'px-3 py-2';
              const input = document.createElement('input');
              input.type = 'text';
              input.name = 'translations[{{ $code }}]['+key+']';
              input.value = values['{{ $code }}'] || '';
              input.className = 'tm-input tm-input--dense w-64 tm-matrix-input';
              input.setAttribute('data-lang', '{{ $code }}');
              input.setAttribute('data-key', key);
              td.appendChild(input);
              tr.appendChild(td);
            }
            @endforeach
            tbody.appendChild(tr);
          }
          // Очистить поля добавления
          keyInput.value = '';
          document.querySelectorAll('.tm-addkey-input').forEach(inp => inp.value = '');
        } catch (e) {
          addBtn.classList.add('ring-2','ring-red-500');
          setTimeout(()=>addBtn.classList.remove('ring-2','ring-red-500'), 800);
        } finally {
          addBtn.disabled = false;
        }
      });

      // Enter в полях добавления — триггер кнопки
      form.addEventListener('keydown', function(e) {
        const target = e.target;
        if (!(target instanceof HTMLInputElement)) return;
        if ((target.id === 'tm-new-key' || target.classList.contains('tm-addkey-input')) && e.key === 'Enter') {
          e.preventDefault();
          addBtn.click();
        }
      });
    })();
  </script>
  <script>
    // Поиск и фильтрация пустых переводов
    (function() {
      const search = document.getElementById('tm-search');
      const filterMissing = document.getElementById('tm-filter-missing');
      function applyFilter() {
        const term = (search?.value || '').toLowerCase();
        const rows = document.querySelectorAll('#tm-main-tbody > tr');
        rows.forEach(row => {
          const key = (row.getAttribute('data-key') || '').toLowerCase();
          const inputs = row.querySelectorAll('input.tm-matrix-input');
          let match = !term || key.includes(term);
          if (!match) {
            for (const inp of inputs) {
              if ((inp.value || '').toLowerCase().includes(term)) { match = true; break; }
            }
          }
          let missingOk = true;
          if (filterMissing && filterMissing.checked) {
            missingOk = false;
            for (const inp of inputs) {
              if ((inp.value || '').trim() === '') { missingOk = true; break; }
            }
          }
          row.style.display = (match && missingOk) ? '' : 'none';
        });
      }
      if (search) search.addEventListener('input', applyFilter);
      if (filterMissing) filterMissing.addEventListener('change', applyFilter);
      applyFilter();
    })();
  </script>
@endsection
