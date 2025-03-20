@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 mt-6 py-12">
        <div>
            <h1 class="text-center text-2xl leading-9 tracking-tight">{{ __('Dashboard') }}</h1>
        </div>
        <div class="mt-6 text-center">
            @if (session('status'))
                <div class="rounded-md bg-red-50 dark:bg-green-800 text-xs font-medium text-green-800 dark:text-green-50 p-4 mb-2">
                    {{ session('status') }}
                </div>
            @endif
            <div class="text-center mb-3">
                <img class="inline-block h-15 w-15 rounded-full" src="{{ auth()->user()->gravatar_url }}" alt="">
                <div class="mt-2">
                    <p class="font-medium">{{ auth()->user()->name }}</p>
                    <p class="text-xs font-medium text-gray-500">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <p>
                {{ __('You are logged in!') }}
            </p>
            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <div class="mt-6">
                    <button type="submit" class="rounded-md bg-red-600 dark:bg-red-800 px-2 py-0.5 text-xs leading-6 text-white shadow-xs hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                        {{ __('Logout') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 mt-6 py-12">
        <div>
            <h2 class="text-center text-xl leading-9 tracking-tight">{{ __('Tokens API') }}</h2>
        </div>
        <div class="mt-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 dark:bg-green-800 text-xs font-medium text-green-800 dark:text-green-50 p-4 mb-2">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('new_token'))
                <div class="rounded-md bg-yellow-50 dark:bg-yellow-800 text-xs font-medium text-yellow-800 dark:text-yellow-50 p-4 mb-2">
                    <strong class="font-bold">Nouveau token créé !</strong>
                    <div class="mt-2">
                        <code class="bg-gray-100 dark:bg-gray-800 p-2 rounded block">{{ session('new_token') }}</code>
                        <p class="mt-2 text-sm">Copiez ce token maintenant, il ne sera plus affiché !</p>
                    </div>
                </div>
            @endif

            @if($tokens->isEmpty())
                <p class="text-center text-gray-500 dark:text-gray-400">Aucun token n'a été généré.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Créé le</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dernière utilisation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($tokens as $token)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $token->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $token->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $token->last_used_at ? $token->last_used_at->format('d/m/Y H:i') : 'Jamais' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <form action="{{ route('account.tokens.destroy', $token->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Êtes-vous sûr de vouloir révoquer ce token ?')">
                                                Révoquer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-6 flex justify-center">
                <form action="{{ route('account.tokens.store') }}" method="POST" class="flex items-center space-x-4">
                    @csrf
                    <div class="flex items-center space-x-2">
                        <label for="token_name" class="whitespace-nowrap font-semibold">Nom du token :</label>
                        <input id="token_name" type="text" name="name" class="w-64 px-2 py-0.5 border border-gray-200 shadow-xs rounded-md text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400" required>
                    </div>
                    <button type="submit" class="rounded-md bg-blue-600 dark:bg-blue-800 px-2 py-0.5 text-xs leading-6 text-white shadow-xs hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        Générer un token API
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection