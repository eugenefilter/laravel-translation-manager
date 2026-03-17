
@extends('translation-manager::layout')

@section('translation-manager-content')
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Create New Translation File</h1>

        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-6 rounded-lg shadow-md">
            @include('translation-manager::partials.create_file_form', ['modal' => false])
        </div>
    </div>
@endsection
