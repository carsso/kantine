@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    @include('includes.file', ['file' => $file, 'displayDetails' => true])
</div>
@if($file->state == 'todo' || $file->state == 'doing')
    <script>
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    </script>
@endif
@include('includes.upload', ['errors' => $errors])
@endsection
