@php
    // One color per calendar style, seasons in their usual colors.
    $colors = [
        'new_year' => '#FDE68A', 'galette' => '#FCD34D', 'crepes' => '#FDBA74', 'valentine' => '#FDA4AF',
        'carnival' => '#F0ABFC', 'womens_day' => '#D8B4FE', 'st_patrick' => '#86EFAC', 'april_fool' => '#7DD3FC',
        'easter' => '#BEF264', 'earth_day' => '#5EEAD4', 'may_day' => '#A7F3D0', 'mothers_day' => '#F9A8D4',
        'fathers_day' => '#93C5FD', 'music_day' => '#C4B5FD', 'bastille_day' => '#A5B4FC', 'back_to_school' => '#67E8F9',
        'pink_october' => '#F472B6', 'halloween' => '#FB923C', 'st_nicolas' => '#A78BFA',
        'christmas_market' => '#FCA5A5', 'christmas' => '#EF4444', 'spring' => '#BBF7D0', 'summer' => '#FEF08A',
        'autumn' => '#FED7AA', 'winter' => '#BAE6FD', 'remembrance' => '#3B82F6',
        'pride' => '#E879F9', 'cyber_month' => '#38BDF8', 'disability_week' => '#FDE047',
        'blood_donation' => '#F43F5E', 'mobility_week' => '#4ADE80', 'health_week' => '#34D399',
        'europe_day' => '#2563EB', 'saint_jean' => '#F59E0B', 'telethon' => '#F87171', 'orange_day' => '#EA580C',
    ];
    // Styles of added events get a stable color of their own.
    $colorOf = fn (string $style): string => $colors[$style] ?? 'hsl('.(crc32($style) % 360).', 70%, 70%)';
    $noStyleBackground = 'background-image: repeating-linear-gradient(45deg, #d1d5db 0 2px, #f9fafb 2px 6px)';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @foreach($months as $month)
        <div>
            <h3 class="font-semibold capitalize mb-1">{{ $month['name'] }}</h3>
            <div class="grid grid-cols-7 gap-0.5 text-center text-xs">
                @foreach(['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $weekday)
                    <div class="text-gray-400">{{ $weekday }}</div>
                @endforeach
                @for($i = 0; $i < $month['offset']; $i++)
                    <div></div>
                @endfor
                @foreach($month['days'] as $day)
                    @php
                        $period = $day['period'];
                        $shown = $day['shown'];
                        $style = $shown['style'];
                        $fromMenu = in_array($shown['source'], ['chosen', 'event'], true);
                        $holiday = \App\Lib\DashboardStyle::publicHolidayName($day['date']);
                        $title = \App\Lib\DashboardStyle::formatDate($day['date'], 'l j F').($holiday ? ' (férié : '.$holiday.')' : '').(\App\Lib\DashboardStyle::isSolidarityDay($day['date']) ? ' (journée de solidarité : Lundi de Pentecôte)' : '').' : '.match (true) {
                            $shown['source'] === 'calendar' => \App\Lib\DashboardStyle::describe($period).' - '.config('tsparticles.config.'.$style.'.name'),
                            in_array($shown['source'], ['event', 'dishes'], true) => $shown['reason'].' - '.config('tsparticles.config.'.$style.'.name'),
                            $shown['source'] === 'chosen' && $style => 'style choisi dans le menu - '.config('tsparticles.config.'.$style.'.name'),
                            $shown['source'] === 'chosen' => 'aucun style choisi dans le menu',
                            default => 'pas de style',
                        };
                        if ($fromMenu && $period && $tenant->dashboard_style_mode === \App\Enums\DashboardStyleMode::Automatic) {
                            $title .= ' (à la place de : '.$period['label'].')';
                        }
                    @endphp
                    <a href="{{ route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => $day['date']->format('Y-m-d')]) }}" target="_blank"
                       title="{{ $title }}"
                       class="relative block py-1 rounded hover:ring-2 hover:ring-indigo-400 @if($style || $fromMenu) text-gray-900 @endif @if($day['date']->isWeekend() || $holiday) opacity-50 @endif @if($holiday) italic underline decoration-2 decoration-red-500 @endif @if($day['date']->isToday()) ring-2 ring-indigo-600 font-bold @endif"
                       @if($style) style="background-color: {{ $colorOf($style) }}" @elseif($fromMenu) style="{{ $noStyleBackground }}" @endif>
                        {{ $day['date']->day }}
                        @if($fromMenu)
                            <span class="absolute top-0.5 right-0.5 w-1.5 h-1.5 rounded-full bg-gray-900"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6">
    <h3 class="font-semibold mb-2">Légende</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-1 text-sm">
        @foreach($legend as $style => $labels)
            <div class="flex items-start gap-2">
                <span class="mt-1 inline-block w-4 h-4 flex-none rounded" style="background-color: {{ $colorOf($style) }}"></span>
                @php
                    $name = config('tsparticles.config.'.$style.'.name');
                    // Dates of the entries named like the style go on its line, not repeated below.
                    [$sameName, $others] = $labels->partition(fn (array $entry) => $entry['label'] === $name);
                @endphp
                <div>
                    <strong>{{ $name }}</strong>
                    @if($sameName->isNotEmpty())
                        <span class="text-gray-500 dark:text-gray-400">: {{ $sameName->pluck('when')->join(', ') }}</span>
                    @endif
                    @foreach($others as $entry)
                        <div class="text-gray-500 dark:text-gray-400">{{ $entry['label'] }} : {{ $entry['when'] }}</div>
                    @endforeach
                </div>
            </div>
        @endforeach
        <div class="flex items-start gap-2">
            <span class="mt-1 inline-block w-4 h-4 flex-none rounded border border-gray-300 dark:border-gray-500"></span>
            <span class="text-gray-500 dark:text-gray-400">Pas de style : fond bleu habituel</span>
        </div>
        <div class="flex items-start gap-2">
            <span class="mt-1 relative inline-block w-4 h-4 flex-none rounded border border-gray-300 dark:border-gray-500"><span class="absolute top-0.5 right-0.5 w-1.5 h-1.5 rounded-full bg-gray-900 dark:bg-gray-100"></span></span>
            <span class="text-gray-500 dark:text-gray-400">Jour réglé dans la modification du menu : style choisi ou nom d'animation</span>
        </div>
        <div class="flex items-start gap-2">
            <span class="mt-0.5 inline-block w-4 flex-none text-center text-xs opacity-50 italic underline decoration-2 decoration-red-500">1</span>
            <span class="text-gray-500 dark:text-gray-400">Jour férié : cantine fermée, comme le week-end</span>
        </div>
        <div class="flex items-start gap-2">
            <span class="mt-1 inline-block w-4 h-4 flex-none rounded" style="{{ $noStyleBackground }}"></span>
            <span class="text-gray-500 dark:text-gray-400">« Aucun style » choisi dans le menu</span>
        </div>
    </div>
</div>
