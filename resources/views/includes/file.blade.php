<div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-center mt-6">
    <h1 class="text-2xl">Fichier : {{ $file->name }}</h1>
    <p><small title="{{ $file->created_at }}" class="text-gray-500">Uploadé le {{ $file->created_at->translatedFormat('d F Y à H:i') }}</small></p>
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
                @if($displayDetails ?? false)
                    @if($file->message)
                        <br />
                        <small>{{ $file->message }}</small>
                    @endif
                @endif
            </span>
        @else
            {{ $file->state }}
        @endif
        
        @if($displayDetails ?? false)
            @if($file->state == 'error' || strtoupper(config('app.env')) != 'PRODUCTION')
                <br />
                <a href="{{ route('file.relaunch', $file->hash) }}" class="m-1 inline-flex items-center px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Relancer le traitement
                </a>
            @endif
            @if($file->message && $file->state != 'error')
                <br />
                <small>{{ $file->message }}</small>
            @endif
        @endif
    </p>
    @if($displayDetails ?? false)
        @if(count($file->menus))
            <p class="mt-2">
                Menus importés :<br />
                <small>
                    @foreach($file->menus as $menu)
                        - <a href="{{ route('menu.date', $menu->date) }}" class="hover:text-indigo-500">{{ $menu->date_carbon->translatedFormat('l d F') }}</a>
                        <br />
                    @endforeach
                </small>
            </p>
        @endif
    @endif

    <p class="mt-2 text-gray-500"><small><small><small>SHA1 : {{ $file->hash }}</small></small></small></p>
    <p class="text-gray-500"><small title="{{ $file->updated_at }}">Modification : {{ $file->updated_at->translatedFormat('d F Y à H:i') }}</small></p>
    <p class="mt-2">
        @if($displayDetails ?? false)
            <a href="{{ url($file->file_path_csv) }}" target="_blank" class="inline-flex items-center mx-2 px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-file-csv mr-2"></i>
                CSV
            </a>
            @if(strtoupper(config('app.env')) != 'PRODUCTION')
                <a href="{{ url($file->file_path) }}" target="_blank" class="inline-flex items-center mx-2 px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <i class="fas fa-file-pdf mr-2"></i>
                    PDF
                </a>
            @endif
            @if($file->state == 'error' || strtoupper(config('app.env')) != 'PRODUCTION')
                <a href="{{ route('file.delete', $file->hash) }}" class="ml-2 inline-flex items-center mx-2 px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fas fa-trash-can mr-2"></i>
                    Supprimer
                </a>
            @endif
        @else
            <a href="{{ route('file', $file->hash) }}" class="inline-flex items-center mx-2 px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-magnifying-glass mr-2"></i>
                Détails
            </a>
        @endif
    </p>
</div>