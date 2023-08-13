@extends('layouts.app-with-navbar')

@section('content')
@include('includes.upload', ['errors' => $errors])
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    @if(count($files))
        <div class="grid 2xl:grid-cols-4 lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1 gap-4 mb-8">
            @foreach($files as $file)
                @include('includes.file', ['file' => $file])
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-center mt-6">
            <i class="text-2xl text-gray-500">Aucun fichier</i>
        </div>
    @endif
</div>
@endsection
