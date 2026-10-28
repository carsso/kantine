<table class="w-full text-sm">
    <thead>
        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
            <th class="py-1 pr-2">Dates</th>
            <th class="py-1 pr-2">Événement</th>
            <th class="py-1 pr-2">Style</th>
            <th class="py-1 pr-2">Cède la place</th>
            <th class="py-1"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($periods as $period)
            <tr class="border-b border-gray-100 dark:border-gray-600 @if(now()->startOfDay()->between($period['start'], $period['end'])) bg-indigo-50 dark:bg-indigo-900 @endif">
                <td class="py-1 pr-2 whitespace-nowrap">
                    @if($period['start']->equalTo($period['end']))
                        {{ \App\Lib\DashboardStyle::formatDate($period['start'], 'l j F') }}
                    @else
                        du {{ \App\Lib\DashboardStyle::formatDate($period['start'], 'j F') }} au {{ \App\Lib\DashboardStyle::formatDate($period['end'], 'j F') }}
                    @endif
                </td>
                <td class="py-1 pr-2">{{ $period['label'] }}</td>
                <td class="py-1 pr-2">{{ config('tsparticles.config.'.$period['style'].'.name') }}</td>
                <td class="py-1 pr-2 text-xs text-orange-600">
                    @foreach($period['yields'] as $yield)
                        <div>{{ $yield['label'] }} le {{ \App\Lib\DashboardStyle::formatDate($yield['day'], 'j F') }}</div>
                    @endforeach
                </td>
                <td class="py-1 text-right">
                    <a href="{{ route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => $period['start']->format('Y-m-d'), 'style' => $period['style']]) }}" target="_blank" class="text-gray-500 hover:text-indigo-600" title="Aperçu">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
