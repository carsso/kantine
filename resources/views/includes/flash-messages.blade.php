@php
    $name = !empty($name) ? $name . '_' : 'flash_';
@endphp

@if(session($name . 'success'))
    <div class="rounded-md bg-green-100 dark:bg-green-800 p-4 mb-4">
        <div class="text-sm font-medium text-green-800 dark:text-green-50">
            {{ session($name . 'success') }}
        </div>
    </div>
@endif

@if(session($name . 'error'))
    <div class="rounded-md bg-red-100 dark:bg-red-800 p-4 mb-4">
        <div class="text-sm font-medium text-red-800 dark:text-red-50">
            {{ session($name . 'error') }}

            @if(session($name . 'error_exception'))
                <br>
                {{ session($name . 'error_exception') }}
            @endif
        </div>
    </div>
@endif
