@extends('translation-manager::layout')

@section('translation-manager-content')
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6">Copy Translation File</h1>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Error</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="bg-white p-6 rounded-lg shadow-md border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <form action="{{ route('translations.copy.store') }}" method="POST">
            @csrf
            <input type="hidden" name="lang" value="{{ $lang }}">
            <input type="hidden" name="file" value="{{ $file }}">

            <div class="mb-4">
                <label for="source_file" class="block text-gray-700 text-sm font-bold mb-2">Source File:</label>
                <input type="text" id="source_file" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100" value="{{ $lang === '_json' ? $file : $lang . '/' . $file }}" readonly>
            </div>

            <div class="mb-4">
                <label for="new_file" class="block text-gray-700 text-sm font-bold mb-2">New File Name:</label>
                <input type="text" name="new_file" id="new_file" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="e.g., new_messages or new_en.json" required>
                <p class="text-gray-600 text-xs mt-1">Do not include file extension (.php or .json). It will be added automatically.</p>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Copy File
                </button>
                <a href="{{ route('translations.index') }}" class="inline-block align-baseline font-semibold text-sm text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
