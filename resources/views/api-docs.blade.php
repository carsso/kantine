@extends('layouts.app-with-navbar')

@section('content')
@php
    $codeBlock = 'bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-md font-mono text-sm break-all';
    $link = 'underline hover:text-indigo-600';
@endphp
<div class="container mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-6 mt-6">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-8 py-10">
        <h1 class="text-center text-3xl tracking-tight">API &amp; MCP</h1>
        <p class="text-center text-gray-500 dark:text-gray-400 mt-2">
            Les menus de la cantine {{ $tenant->name }}, pour vos scripts, vos bots et vos assistants IA.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <a href="#api" class="group rounded-lg border border-gray-200 dark:border-gray-600 p-6 flex gap-4 hover:border-indigo-400 hover:shadow-md transition">
                <i class="fa-thin fa-brackets-curly text-4xl text-indigo-500"></i>
                <div>
                    <h2 class="text-xl group-hover:text-indigo-600">API JSON</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Des URLs publiques qui renvoient les menus, la recherche de plats et les événements en JSON.
                    </p>
                </div>
            </a>
            <a href="#mcp" class="group rounded-lg border border-gray-200 dark:border-gray-600 p-6 flex gap-4 hover:border-indigo-400 hover:shadow-md transition">
                <i class="fa-thin fa-robot text-4xl text-indigo-500"></i>
                <div>
                    <h2 class="text-xl group-hover:text-indigo-600">Serveur MCP</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Branchez Claude, ChatGPT ou Cursor sur la cantine et demandez-leur ce qu'on mange.
                    </p>
                </div>
            </a>
        </div>
        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
            <i class="fa-solid fa-lock-open"></i>
            Public, en lecture seule, sans compte.
        </p>
    </div>

    <section id="api" class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-8 py-10 flex flex-col gap-6 scroll-mt-4">
        <h2 class="text-2xl"><i class="fa-thin fa-brackets-curly text-indigo-500"></i> API JSON</h2>

        <div>
            <h3 class="text-lg mb-2">Adresse de base</h3>
            <div class="{{ $codeBlock }}">{{ $apiUrl }}</div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Toutes les réponses sont en JSON et les dates au format AAAA-MM-JJ. Limite : {{ $requestsPerMinute }} requêtes par minute.
            </p>
        </div>

        <div>
            <h3 class="text-lg mb-2">Points d'accès</h3>
            <div class="flex flex-col divide-y divide-gray-200 dark:divide-gray-600 rounded-lg border border-gray-200 dark:border-gray-600">
                @foreach ($endpoints as $endpoint)
                    <div class="p-4 flex flex-col gap-2">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 px-2 py-0.5 text-xs font-bold">GET</span>
                            <code class="font-mono">{{ $endpoint['path'] }}</code>
                            <a href="{{ $endpoint['example'] }}" target="_blank" class="ml-auto text-sm {{ $link }}">
                                Essayer <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                        <p>{{ $endpoint['description'] }}</p>
                        @if (! empty($endpoint['params']))
                            <ul class="text-sm flex flex-col gap-1">
                                @foreach ($endpoint['params'] as $param => $description)
                                    <li><code class="font-mono text-indigo-600 dark:text-indigo-300">{{ $param }}</code> : {{ $description }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="text-lg mb-2">Tags des plats</h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($tags as $key => $tag)
                    <span class="rounded-full border border-gray-200 dark:border-gray-600 px-3 py-1 text-sm">
                        {{ $tag['emoji'] }} {{ $tag['name'] }} <code class="font-mono text-gray-500 dark:text-gray-400">{{ $key }}</code>
                    </span>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="text-lg mb-2">Exemple : les prochaines frites</h3>
            <div class="{{ $codeBlock }}">curl "{{ $exampleUrl }}"</div>
            <pre class="bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-md font-mono text-sm overflow-x-auto mt-2">{{ json_encode($exampleResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </section>

    <section id="mcp" class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-8 py-10 flex flex-col gap-6 scroll-mt-4">
        <h2 class="text-2xl"><i class="fa-thin fa-robot text-indigo-500"></i> Serveur MCP</h2>

        <p>
            Le <a href="https://modelcontextprotocol.io" target="_blank" class="{{ $link }}">Model Context Protocol</a> (MCP)
            permet aux assistants IA d'utiliser des outils externes. Une fois ce serveur ajouté à votre assistant,
            il peut consulter les menus de la cantine {{ $tenant->name }} pour vous répondre.
        </p>

        <div>
            <h3 class="text-lg mb-2">Adresse du serveur</h3>
            <div class="{{ $codeBlock }}">{{ $mcpUrl }}</div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Limite : {{ $mcpRequestsPerMinute }} requêtes par minute.
            </p>
        </div>

        <div>
            <h3 class="text-lg mb-2">Ajouter le serveur</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded-lg border border-gray-200 dark:border-gray-600 p-4 flex flex-col gap-2">
                    <h4 class="font-semibold"><i class="fa-solid fa-terminal"></i> Claude Code</h4>
                    <div class="{{ $codeBlock }}">claude mcp add --transport http kantine-{{ $tenant->slug }} {{ $mcpUrl }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 dark:border-gray-600 p-4 flex flex-col gap-2">
                    <h4 class="font-semibold"><i class="fa-solid fa-browser"></i> Claude (web et desktop)</h4>
                    <p>Paramètres › Connecteurs › Ajouter un connecteur personnalisé, puis collez l'adresse du serveur.</p>
                </div>
                <div class="rounded-lg border border-gray-200 dark:border-gray-600 p-4 flex flex-col gap-2 lg:col-span-2">
                    <h4 class="font-semibold"><i class="fa-solid fa-plug"></i> Autres clients</h4>
                    <p>La plupart des clients acceptent une configuration de ce type.</p>
                    <pre class="bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-md font-mono text-sm overflow-x-auto">{{ json_encode(['mcpServers' => ['kantine-'.$tenant->slug => ['type' => 'http', 'url' => $mcpUrl]]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-lg mb-2">Outils disponibles</h3>
            <ul class="flex flex-col gap-2">
                @foreach ($mcpTools as $tool)
                    <li><code class="font-mono text-indigo-600 dark:text-indigo-300">{{ $tool['name'] }}</code> : {{ $tool['title'] }}.</li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 class="text-lg mb-2">Quelques idées de questions</h3>
            <div class="flex flex-wrap gap-2">
                @foreach ([
                    'Qu\'est-ce qu\'on mange aujourd\'hui ?',
                    'C\'est quand les prochaines frites ?',
                    'Quels plats végétariens cette semaine ?',
                    'Quand a-t-on eu du couscous pour la dernière fois ?',
                    'Il y a un événement prévu ce mois-ci ?',
                ] as $question)
                    <span class="rounded-full bg-indigo-50 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-100 px-3 py-1 text-sm">« {{ $question }} »</span>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection
