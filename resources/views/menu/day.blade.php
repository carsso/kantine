@extends('layouts.app-with-navbar')

@section('meta')
<meta http-equiv="refresh" content="14400">
@endsection

@section('content')
<div class="container-2xl 2xl:container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="lg:hidden text-2xl text-center mb-4">
        Menu du {{ $day->translatedFormat('l d F') }}
    </h1>
    <div class="flex mb-4">
        <div class="flex-none w-48 text-left">
            <a href="{{ route('menu.day', $prevDay) }}" class="px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-circle-arrow-left mr-2"></i>
                Jour précédent
            </a>
        </div>
        <div class="flex-auto">
            <h1 class="hidden lg:block text-2xl text-center">
                Menu du {{ $day->translatedFormat('l d F') }}
            </h1>
        </div>
        <div class="flex-none w-48 text-right">
            <a href="{{ route('menu.day', $nextDay) }}" class="px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Jour suivant
                <i class="fas fa-circle-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
    @if($menu)
        <div class="mb-6">
            @include('includes.menu', ['menu' => $menu, 'displayDetails' => true])
        </div>
    @else
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6 text-center mb-6">
            <p class="text-2xl text-gray-500">
                <i>Aucun menu pour ce jour</i>
            </p>
        </div>
    @endif
</div>
@endsection
