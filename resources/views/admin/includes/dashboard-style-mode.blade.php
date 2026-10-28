<form action="{{ route('admin.themes.mode', ['tenantSlug' => $tenant->slug]) }}" method="POST" class="flex flex-wrap items-center gap-2 text-sm">
    @csrf
    <label for="dashboard_style_mode_{{ $tenant->slug }}" class="font-medium">
        <i class="fa-solid fa-palette mr-1"></i>
        Styles du dashboard :
    </label>
    <select id="dashboard_style_mode_{{ $tenant->slug }}" name="dashboard_style_mode" class="px-2 py-1 border border-gray-200 shadow-xs rounded-md text-sm dark:bg-gray-700 dark:border-gray-500 dark:text-gray-300">
        @foreach(\App\Enums\DashboardStyleMode::cases() as $mode)
            <option value="{{ $mode->value }}" @selected($tenant->dashboard_style_mode === $mode)>{{ $mode->label() }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">
        Appliquer
    </button>
</form>
@if($tenant->dashboard_style_mode === \App\Enums\DashboardStyleMode::Disabled)
    <p class="mt-1 text-xs text-red-700 dark:text-red-400">
        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
        Aucun style n'est affiché sur les dashboards de cette cantine, même ceux choisis jour par jour.
    </p>
@endif
