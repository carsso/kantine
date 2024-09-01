<div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-center mt-6">
    <h1 class="text-2xl">Fichier : {{ $file->name }}</h1>
    <p>
        <small title="{{ $file->created_at }}" class="text-gray-500">
            Uploadé le {{ $file->created_at->translatedFormat('j F Y à H:i') }}
            @if($file->user)
                par {{ $file->user->name }}
            @endif
        </small>
    </p>
    <p class="mt-2">
        Traitement du fichier :
        @if($file->state == 'todo')
            <span class="text-grey-700">
                <i class="fas fa-hourglass-start"></i>
                En attente
            </span>
        @elseif($file->state == 'doing')
            <span class="text-yellow-700">
                <i class="fas fa-spinner fa-spin-pulse"></i>
                En cours
            </span>
        @elseif($file->state == 'done')
            <span class="text-green-700">
                <i class="fas fa-check"></i>
                Terminé
            </span>
        @elseif($file->state == 'error')
            <span class="text-red-700">
                <i class="fas fa-times"></i>
                Erreur
            </span>
        @else
            {{ $file->state }}
        @endif
        @if($displayDetails ?? false)
            @if(auth()->user()->hasRole('Super Admin'))
                <br />
                <a href="{{ route('file.relaunch', $file->hash) }}" class="m-1 inline-flex items-center px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Relancer le traitement
                </a>
            @endif
        @endif
    </p>
    @if(count($file->menus))
        <p class="mt-2">
            Menus importés :<br />
            <small>
                @foreach($file->menus as $menu)
                    - <a href="{{ route('menu', $menu->date) }}" class="hover:text-indigo-500">{{ $menu->date_carbon->translatedFormat('l j F Y') }}</a>
                    <br />
                @endforeach
            </small>
        </p>
    @endif

    <p class="mt-2 text-gray-500"><small><small><small>SHA1 : {{ $file->hash }}</small></small></small></p>
    @if($file->datetime_carbon)
        <p class="text-gray-500"><small title="{{ $file->datetime_carbon }}">Document du {{ $file->datetime_carbon->translatedFormat('j F Y à H:i') }}</small></p>
    @endif
    <p class="mt-2">
        @if($displayDetails ?? false)
            <a href="{{ url($file->file_path_csv) }}" target="_blank" class="inline-flex items-center mx-2 px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-file-csv mr-2"></i>
                CSV
            </a>
            <a href="{{ url($file->file_path) }}" target="_blank" class="inline-flex items-center mx-2 px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-file-pdf mr-2"></i>
                PDF
            </a>
            @if($file->state == 'error' || auth()->user()->hasRole('Super Admin'))
                <form id="delete-form" class="text-center mt-4" action="{{ route('file.delete', $file->hash) }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-md bg-red-600 dark:bg-red-800 py-1 px-2 text-xs leading-1 text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?');">
                        <i class="fas fa-trash-can mr-2"></i> Supprimer
                    </button>
                </form>
            @endif
        @else
            <a href="{{ route('file', $file->hash) }}" class="inline-flex items-center mx-2 px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-magnifying-glass mr-2"></i>
                Détails
            </a>
        @endif
    </p>
</div>
        
@if($displayDetails ?? false)
    @if($file->message)
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-left mt-6">
            <h3 class="text-2xl">Output log : </h3>
            <p class="mt-2 text-left">
                <small><pre>{{ $file->message }}</pre></small>
            </p>
        </div>
    @endif
@endif