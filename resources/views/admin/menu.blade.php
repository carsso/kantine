@extends('layouts.app-with-navbar')

@section('content')
<div class="container-2xl 2xl:container-full mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="lg:hidden text-2xl text-center mb-4">
        Menus de la semaine {{ $weekMonday->translatedFormat('W (Y)') }}
    </h1>
    <div class="flex mb-4">
        <div class="flex-none w-48 text-left">
            <a href="{{ route('admin.menu', $prevWeek) }}" class="px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-xs text-white bg-gray-600 hover:bg-gray-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
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
            <a href="{{ route('admin.menu', $nextWeek) }}" class="px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-xs text-white bg-gray-600 hover:bg-gray-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Semaine suivante
                <i class="fas fa-circle-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
    <form action="{{ route('admin.menu.update') }}" method="post" enctype="multipart/form-data">
        <button type="submit" onclick="return false;" disabled style="display: none" aria-hidden="true"></button>
        @csrf
        @if(count($errors) > 0)
            <div class="rounded-md bg-red-50 dark:bg-red-800 p-4 mb-4">
                <div class="text-sm font-medium text-red-800 dark:text-red-50">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="mb-2 text-right">
            <button type="submit" name="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-xs text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Sauvegarder le menu
            </button>
        </div>
        <div class="grid xl:grid-cols-5 lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1 gap-4 mb-8">
            @foreach($menus as $idx => $menu)
                @include('admin.includes.menu', ['idx' => $idx, 'menu' => $menu, 'autocompleteDishes' => $autocompleteDishes, 'autocompleteDishesTags' => $autocompleteDishesTags])
            @endforeach
        </div>
        <div class="mt-2 text-right">
            <button type="submit" name="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-xs text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Sauvegarder le menu
            </button>
        </div>
    </form>
</div>
@endsection
