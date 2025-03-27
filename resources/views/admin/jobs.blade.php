@extends('layouts.app-with-navbar')

@section('content')
<div class="container-2xl 2xl:container-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Monitoring des Jobs</h1>
    </div>

    <job-monitor></job-monitor>
</div>
@endsection