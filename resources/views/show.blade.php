@extends('modelplus::layouts.app')

@section('content')
<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{ $modelName }}</h1>
        <p class="mt-2 text-sm text-gray-700">A list of all {{ strtolower($modelName) }} records in your database.</p>
    </div>
    @if(!isset($error))
    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Add {{ $modelName }}
        </button>
    </div>
    @endif
</div>

<div id="table-container">
    @include('modelplus::show-partial')
</div>
@endsection 