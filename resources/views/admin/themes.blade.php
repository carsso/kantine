@extends('layouts.app-with-navbar')

@section('meta')
<script type="application/json" id="particle-themes">@json(config('tsparticles.config', []))</script>
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center mb-4">
        <div class="flex-none w-48 text-left">
            <a href="{{ route('admin.themes', ['tenantSlug' => $tenant->slug, 'year' => $year - 1]) }}" class="px-2 py-1 text-sm font-medium rounded-md shadow-xs text-white bg-gray-600 hover:bg-gray-700">
                <i class="fas fa-circle-arrow-left mr-2"></i>{{ $year - 1 }}
            </a>
        </div>
        <h1 class="flex-auto text-2xl text-center">Calendrier des thèmes {{ $year }} - {{ $tenant->name }}</h1>
        <div class="flex-none w-48 text-right">
            <a href="{{ route('admin.themes', ['tenantSlug' => $tenant->slug, 'year' => $year + 1]) }}" class="px-2 py-1 text-sm font-medium rounded-md shadow-xs text-white bg-gray-600 hover:bg-gray-700">
                {{ $year + 1 }}<i class="fas fa-circle-arrow-right ml-2"></i>
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-4 mb-4">
        @include('admin.includes.dashboard-style-mode', ['tenant' => $tenant])
    </div>

    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-4 mb-4 text-sm leading-relaxed">
        <h2 class="text-lg font-semibold mb-2">Comment le style d'un jour est choisi</h2>
        <ol class="list-decimal ml-5">
            <li>Le style choisi pour ce jour dans la modification du menu, s'il y en a un. « Aucun style » coupe l'automatique pour ce jour.</li>
            <li>Sinon, le style qui correspond au nom de l'événement / animation (« Tartiflette » donne Montagne, « Mexicain » donne Mexique…).</li>
            <li>Sinon, le style du calendrier ci-dessous. En dehors des événements, fêtes, périodes et débuts de saison, le dashboard reste bleu.</li>
        </ol>
        <p class="mt-2">La cantine étant fermée le week-end et les jours fériés, une fête qui tombe un de ces jours est affichée le jour ouvré d'avant. Les fériés eux-mêmes (8 mai, 11 novembre…) sont annoncés les jours qui les précèdent.</p>
        <p class="mt-2">
            Quand deux lignes du calendrier tombent le même jour, la priorité est : les événements ajoutés ci-dessous, puis les jours de fête (Chandeleur, Saint-Patrick…),
            puis les périodes de plusieurs jours (Pâques, Noël…), puis les débuts de saison.
            Le calendrier ci-dessous montre le résultat jour par jour, et le détail des dates indique les jours où une période cède la place.
        </p>
        <p class="mt-2 text-gray-500 dark:text-gray-400">
            En mode « Manuel », seule l'étape 1 s'applique ; en mode « Désactivé », aucun style n'est affiché.
            Aujourd'hui, le calendrier donne : <strong>{{ $todayStyle ? config('tsparticles.config.'.$todayStyle.'.name') : 'aucun style' }}</strong>.
        </p>
    </div>

    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-4 mb-4">
        <h2 class="text-lg font-semibold mb-1">Événements ajoutés</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
            Pour les événements dont la date change chaque année : Coupe du monde, Roland-Garros, Tour de France, Six Nations, Nouvel an lunaire, Diwali, séminaire…
            Ils passent devant le reste du calendrier pendant leurs dates.
        </p>
        <form action="{{ $editedEvent ? route('admin.themes.events.update', ['tenantSlug' => $tenant->slug, 'calendarEvent' => $editedEvent->id]) : route('admin.themes.events.store', ['tenantSlug' => $tenant->slug]) }}" method="POST" class="flex flex-wrap items-end gap-2 text-sm mb-3 @if($editedEvent) p-2 rounded-md bg-indigo-50 dark:bg-indigo-900 @endif">
            @csrf
            @if($editedEvent)
                @method('PUT')
            @endif
            <label class="flex flex-col">
                <span class="text-xs text-gray-500">Nom</span>
                <input type="text" name="label" value="{{ old('label', $editedEvent?->label) }}" required maxlength="100" placeholder="Coupe du monde 2026" class="px-2 py-1 border border-gray-200 rounded-md dark:bg-gray-800 dark:border-gray-500">
            </label>
            <div class="flex flex-col min-w-64">
                <label for="style" class="text-xs text-gray-500">Style</label>
                <style-picker name="style" value="{{ old('style', $editedEvent?->style ?? '') }}" preview-url="{{ route('dashboard', ['tenantSlug' => $tenant->slug]) }}" themes-only></style-picker>
            </div>
            <label class="flex flex-col">
                <span class="text-xs text-gray-500">Du</span>
                <input type="date" name="starts_on" value="{{ old('starts_on', $editedEvent?->starts_on->format('Y-m-d')) }}" required class="px-2 py-1 border border-gray-200 rounded-md dark:bg-gray-800 dark:border-gray-500">
            </label>
            <label class="flex flex-col">
                <span class="text-xs text-gray-500">Au</span>
                <input type="date" name="ends_on" value="{{ old('ends_on', $editedEvent?->ends_on->format('Y-m-d')) }}" required class="px-2 py-1 border border-gray-200 rounded-md dark:bg-gray-800 dark:border-gray-500">
            </label>
            @if($editedEvent)
                <button type="submit" class="px-3 py-1.5 rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fa-solid fa-check mr-1"></i>Enregistrer
                </button>
                <a href="{{ route('admin.themes', ['tenantSlug' => $tenant->slug, 'year' => $year]) }}" class="px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-500 hover:text-indigo-600">Annuler</a>
            @else
                <button type="submit" class="px-3 py-1.5 rounded-md text-white bg-gray-600 hover:bg-gray-700">
                    <i class="fa-solid fa-plus mr-1"></i>Ajouter
                </button>
            @endif
        </form>
        @if($errors->any())
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs text-red-800 dark:text-red-50 p-2 mb-3">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @if($events->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Aucun événement ajouté pour le moment.</p>
        @else
            <table class="w-full text-sm">
                @foreach($events as $event)
                    <tr class="border-b border-gray-100 dark:border-gray-600">
                        <td class="py-1 pr-2">{{ $event->label }}</td>
                        <td class="py-1 pr-2">{{ config('tsparticles.config.'.$event->style.'.name', $event->style) }}</td>
                        <td class="py-1 pr-2 whitespace-nowrap">du {{ \App\Lib\DashboardStyle::formatDate($event->starts_on, 'j F Y') }} au {{ \App\Lib\DashboardStyle::formatDate($event->ends_on, 'j F Y') }}</td>
                        <td class="py-1 text-right whitespace-nowrap">
                            <a href="{{ route('admin.themes', ['tenantSlug' => $tenant->slug, 'year' => $year, 'edit' => $event->id]) }}" class="text-gray-500 hover:text-indigo-600 mr-3" title="Modifier">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.themes.events.destroy', ['tenantSlug' => $tenant->slug, 'calendarEvent' => $event->id]) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-500 hover:text-red-600" title="Supprimer">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-4 mb-4">
        <h2 class="text-lg font-semibold mb-1">Calendrier {{ $year }}</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
            Chaque jour a la couleur du style affiché sur le dashboard, en comptant les styles choisis et les animations des menus (marqués d'un point) et le mode de la cantine.
            Survolez un jour pour le détail, cliquez pour ouvrir le dashboard de ce jour.
            Les week-ends et les jours fériés (soulignés en rouge) sont grisés, aujourd'hui est entouré.
        </p>
        @include('admin.includes.themes-calendar-year', ['months' => $months, 'legend' => $legend])
    </div>

    <details class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-4 mb-8">
        <summary class="text-lg font-semibold cursor-pointer">Détail des dates et des chevauchements</summary>
        <div class="mt-2">
            @include('admin.includes.themes-calendar-table', ['periods' => $periods])
        </div>
    </details>
</div>
@endsection
