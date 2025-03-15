@extends('layouts.app-with-navbar')

@section('content')
<div class="container-2xl 2xl:container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="lg:hidden text-2xl text-center mb-4">
        Menus de la semaine {{ $weekMonday->translatedFormat('W (Y)') }}
    </h1>
    <div class="flex mb-4">
        <div class="flex-none w-48 text-left">
            <a href="{{ route('menu', $prevWeek) }}" class="px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-xs text-white bg-gray-600 hover:bg-gray-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-circle-arrow-left mr-2"></i>
                Semaine précédente
            </a>
        </div>
        <div class="flex-auto">
            <h1 class="hidden lg:block text-2xl text-center">
                Menus de la semaine {{ $weekMonday->translatedFormat('W (Y)') }}
            </h1>
        </div>
        <div class="flex-none w-48 text-right">
            <a href="{{ route('menu', $nextWeek) }}" class="px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-xs text-white bg-gray-600 hover:bg-gray-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Semaine suivante
                <i class="fas fa-circle-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
    @if(count($menus))
        <div class="grid xl:grid-cols-5 lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1 gap-4 mb-6">
            @foreach($menus as $menu)
                @if(isset($menu['date']))
                    @include('includes.menu', ['day' => $menu])
                @else
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 py-5 text-center border-t-4 border-[#147DE8]">
                        <p class="text-2xl text-gray-500">
                            <i>Aucun menu pour ce jour</i>
                        </p>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-6 text-center mb-6">
            <p class="text-2xl text-gray-500">
                <i>Aucun menu pour cette semaine</i>
            </p>
        </div>
    @endif
</div>
@endsection
