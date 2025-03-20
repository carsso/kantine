@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto text-center">
        <h1 class="text-4xl font-bold mb-8">Bienvenue sur {{ config('app.name') }}</h1>
        <p class="text-lg mb-8">Choisissez votre kantine :</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
            @foreach($tenants as $tenant)
                <a href="{{ route('tenant.home', $tenant->slug) }}" 
                   class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                    <h2 class="text-xl font-semibold mb-2">{{ $tenant->name }}</h2>
                    <p class="text-gray-600 dark:text-gray-400">{{ $tenant->description }}</p>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection 