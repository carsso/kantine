@extends('layouts.app-with-navbar')

@section('content')
<div class="container-2xl 2xl:container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="lg:hidden text-2xl text-center mb-4">
        Menus de la semaine {{ $weekMonday->translatedFormat('W') }}
    </h1>
    <div class="flex mb-4">
        <div class="flex-none w-48 text-left">
            <a href="{{ route('menu', $prevWeek) }}" class="px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-circle-arrow-left mr-2"></i>
                Semaine précédente
            </a>
        </div>
        <div class="flex-auto">
            <h1 class="hidden lg:block text-2xl text-center">
                Menus de la semaine {{ $weekMonday->translatedFormat('W') }}
            </h1>
        </div>
        <div class="flex-none w-48 text-right">
            <a href="{{ route('menu', $nextWeek) }}" class="px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Semaine suivante
                <i class="fas fa-circle-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
    @if(count($menus))
        <div class="grid xl:grid-cols-5 lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1 gap-4 mb-6">
            @foreach($menus as $menu)
                @include('includes.menu', ['menu' => $menu])
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6 text-center mb-6">
            <p class="text-2xl text-gray-500">
                <i>Aucun menu pour cette semaine</i>
            </p>
            <p class="mt-8 text-xs">
                Tu as le fichier du menu ?
                <a href="{{ route('files') }}" class="leading-6 text-indigo-600 dark:text-indigo-500 hover:text-indigo-500 dark:hover:text-indigo-400">
                    Envoyer le menu
                </a>
            </p>
        </div>
    @endif
</div>
@endsection
