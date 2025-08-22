@extends('translation-manager::layout')

@section('translation-manager-content')
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Manage Exclusions</h1>

        <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
            @include('translation-manager::partials.exclusions_form', ['modal' => false])
        </div>
    </div>
@endsection
