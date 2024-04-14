
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-center mt-6">
        <form action="{{ route('upload') }}" method="post" enctype="multipart/form-data">
            <h1 class="text-center text-2xl mb-2">Envoyer un menu (PDF)</h1>
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
            <div class="mt-2">
                <label for="file-input" class="sr-only">Envoyer un menu (PDF)</label>
                <input type="file" name="files[]" multiple id="file-input" class="block w-full border border-gray-200 shadow-sm rounded-md text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:border-gray-500 dark:text-gray-400
                    file:bg-transparent file:border-0
                    file:bg-gray-100 file:mr-4
                    file:py-3 file:px-4
                    dark:file:bg-gray-800 dark:file:text-gray-400">
            </div>
            <p class="mt-2 text-gray-500">
                <small>
                    <small>
                        Ces fichiers sont stockés de manière permanente sur le serveur. Veuillez vous assurer qu'ils ne contiennent pas de données personnelles.
                    </small>
                </small>
            </p>
            <div class="mt-2">
                <button type="submit" name="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Envoyer
                </button>
            </div>
        </form>
    </div>
</div>