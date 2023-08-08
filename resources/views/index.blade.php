@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2">
        <div class="text-left">
            <a href="{{ route('menu.date', $prevWeek) }}" class="px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-circle-arrow-left mr-2"></i>
                Semaine précédente
            </a>
        </div>
        <div class="text-right">
            <a href="{{ route('menu.date', $nextWeek) }}" class="px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Semaine suivante
                <i class="fas fa-circle-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
    @if(count($menus))
        <div class="grid 2xl:grid-cols-5 lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1 gap-4 mb-8">
            @foreach($menus as $menu)
                @include('includes.menu', ['menu' => $menu])
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-center mt-6">
            <i class="text-2xl text-gray-500">Aucun menu pour cette semaine</i>
        </div>
    @endif
</div>
@if(!count($menus))
    @include('includes.upload', ['errors' => $errors])
@endif
@endsection
