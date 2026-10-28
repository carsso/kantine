<?php

namespace App\Http\Controllers;

use App\Enums\DashboardStyleMode;
use App\Events\DashboardRefreshEvent;
use App\Events\MenuUpdatedEvent;
use App\Http\Requests\UpdateMenuFormRequest;
use App\Lib\DashboardStyle;
use App\Libraries\WebexApi;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\Information;
use App\Models\Tenant;
use App\Services\DayService;
use App\Services\WebexNotificationService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:admin']);
    }

    public function index()
    {
        $tenants = Tenant::all();

        return view('admin.index', ['tenants' => $tenants]);
    }

    public function menu(Request $request, DayService $dayService)
    {
        $tenant = $request->route('tenant');
        $dateString = $request->route('date');
        $dateToday = strtotime('today 10 am');
        $date = $dateToday;
        if (date('H') >= 15) {
            $date = strtotime('+1 day', $date);
        }
        $dish = Dish::where('tenant_id', $tenant->id)
            ->where('date', '>=', date('Y-m-d', $date))
            ->orderBy('date', 'asc')
            ->first();
        if ($dish) {
            $date = strtotime($dish->date.' 10 am');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString.' 10 am');
        }
        $mondayTime = strtotime('monday this week 10 am', $date);
        $fridayTime = strtotime('friday this week 10 am', $date);
        $calendarWeekFirstDay = date('Y-m-d', $mondayTime);
        $calendarWeekLastDay = date('Y-m-d', $fridayTime);
        $menus = [];
        for ($date = Carbon::parse($calendarWeekFirstDay); $date->lte(Carbon::parse($calendarWeekLastDay)); $date->addDay()) {
            $menu = $dayService->getDay($tenant, $date->format('Y-m-d'));
            $menus[$date->format('Y-m-d')] = $menu;
        }
        $prevWeek = date('Y-m-d', strtotime('-1 week', $mondayTime));
        $nextWeek = date('Y-m-d', strtotime('+1 week', $mondayTime));
        $autocompleteDishes = Dish::orderBy('id', 'desc')
            ->limit(300)
            ->get()
            ->pluck('name')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        $autocompleteDishesTags = collect(Dish::getTagsDefinitions())
            ->map(function ($value, $key) {
                return ['label' => $value['name_short'], 'value' => $key];
            })->values()->all();
        $autocompleteDishesAllergens = collect(Dish::getAllergensDefinitions())
            ->map(function ($value, $key) {
                return ['label' => $value['name'], 'value' => $key];
            })->values()->all();
        $categories = DishCategory::where('tenant_id', $tenant->id)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');

        return view('admin.menu', ['tenant' => $tenant, 'menus' => $menus, 'categories' => $categories, 'weekMonday' => Carbon::parse($mondayTime), 'autocompleteDishes' => $autocompleteDishes, 'autocompleteDishesTags' => $autocompleteDishesTags, 'autocompleteDishesAllergens' => $autocompleteDishesAllergens, 'week' => $calendarWeekFirstDay, 'prevWeek' => $prevWeek, 'nextWeek' => $nextWeek]);
    }

    public function updateMenu(UpdateMenuFormRequest $request, DayService $dayService)
    {
        $tenant = $request->route('tenant');
        $validated = $request->validated();
        if ($validated['date'] && count($validated['date']) > 0) {
            $date = array_values($validated['date'])[0];
            $ucfirst = function ($s) {
                return mb_strtoupper(mb_substr($s, 0, 1)).mb_substr($s, 1);
            };
            foreach ($validated['dishes'] as $date => $categories) {
                foreach ($categories as $categoryId => $dishes) {
                    $validated['dishes'][$date][$categoryId] = array_map($ucfirst, $dishes);
                    $validated['dishes'][$date][$categoryId] = array_filter($validated['dishes'][$date][$categoryId]);
                }
            }
            foreach ($validated['dishes'] as $date => $categories) {
                $dishIds = [];
                foreach ($categories as $categoryId => $dishes) {
                    foreach ($dishes as $idx => $dish) {
                        $tags = $validated['dishes_tags'][$date][$categoryId][$idx] ?? '';
                        $tags = $tags ? explode(',', $tags) : [];
                        $allergens = $validated['dishes_allergens'][$date][$categoryId][$idx] ?? '';
                        $allergens = Dish::normalizeAllergens($allergens ? explode(',', $allergens) : []);
                        $dish = Dish::firstOrCreate([
                            'date' => $date,
                            'dishes_category_id' => $categoryId,
                            'name' => $dish,
                            'tenant_id' => $tenant->id,
                            'tags' => $tags,
                        ]);
                        $dish->tags = $tags;
                        $dish->allergens = $allergens;
                        $dish->save();
                        $dishIds[] = $dish->id;
                    }
                }
                Dish::where('tenant_id', $tenant->id)
                    ->where('date', $date)
                    ->whereNotIn('id', $dishIds)
                    ->delete();
            }
            $fields = ['event_name', 'information', 'style'];
            foreach ($fields as $field) {
                if (isset($validated[$field])) {
                    foreach ($validated[$field] as $date => $value) {
                        $information = Information::firstOrCreate([
                            'date' => $date,
                            'tenant_id' => $tenant->id,
                        ]);
                        $information->$field = $value;
                        $information->save();
                    }
                }
            }
            foreach ($validated['date'] as $date) {
                $menu = $dayService->getDay($tenant, $date);
                if ($menu) {
                    try {
                        MenuUpdatedEvent::dispatch($menu);
                    } catch (\Exception $e) {
                        Log::error($e);
                    }
                }
            }

            return $this->redirectWithSuccess(
                'Menus mis à jour',
                redirect()->route('admin.menu', ['tenantSlug' => $tenant->slug, 'date' => $date])
            );
        }

        return $this->backWithError('Rien à mettre à jour');
    }

    public function themes(Request $request)
    {
        $tenant = $request->route('tenant');
        $year = (int) ($request->route('year') ?? now()->year);
        $calendar = DashboardStyle::calendar($year, $tenant);
        $periods = collect($calendar)->map(function (array $period, int $index) use ($calendar) {
            // Days of the period won by a period of higher priority (the calendar is ordered by priority).
            $yields = [];
            for ($day = $period['start']; $day->lte($period['end']); $day = $day->addDay()) {
                $winner = DashboardStyle::periodOn($day, calendar: $calendar);
                if ($winner && array_search($winner, $calendar) !== $index) {
                    $yields[] = ['day' => $day, 'label' => $winner['label']];
                }
            }

            return [...$period, 'yields' => $yields];
        });

        // Styles chosen and events announced in the menus, as the dashboard shows them.
        $informations = $tenant->information()->whereBetween('date', [$year.'-01-01', $year.'-12-31'])->get()->keyBy('date');
        $burgersAndFriesDays = Dish::with('category')->where('tenant_id', $tenant->id)->whereBetween('date', [$year.'-01-01', $year.'-12-31'])->get()
            ->groupBy('date')
            ->filter(fn ($dishes) => DayService::isBurgersDay($dishes) && DayService::isFriesDay($dishes));
        $months = [];
        foreach (range(1, 12) as $month) {
            $firstDay = CarbonImmutable::create($year, $month, 1);
            $days = [];
            for ($day = $firstDay; $day->month === $month; $day = $day->addDay()) {
                $information = $informations->get($day->format('Y-m-d'));
                $days[] = [
                    'date' => $day,
                    'period' => DashboardStyle::periodOn($day, calendar: $calendar),
                    'shown' => DashboardStyle::resolveWithReason($information?->style, $information?->event_name, $day, $tenant->dashboard_style_mode, $tenant, $calendar, $burgersAndFriesDays->has($day->format('Y-m-d'))),
                ];
            }
            $months[] = ['name' => $firstDay->translatedFormat('F'), 'offset' => $firstDay->dayOfWeekIso - 1, 'days' => $days];
        }

        // Styles shown at least one day, in order of appearance, with what shows them and when.
        $legend = collect($months)->pluck('days')->flatten(1)
            ->filter(fn (array $day) => $day['shown']['style'])
            ->groupBy(fn (array $day) => $day['shown']['style'])
            ->map(fn ($days) => $days->map(fn (array $day) => match ($day['shown']['source']) {
                'calendar' => ['label' => $day['period']['label'], 'when' => $day['period']['detail'] ?? DashboardStyle::formatRange($day['period']['start'], $day['period']['end'])],
                'event', 'dishes' => ['label' => $day['shown']['reason'], 'when' => DashboardStyle::formatDate($day['date'])],
                default => ['label' => 'Choisi dans le menu', 'when' => DashboardStyle::formatDate($day['date'])],
            })->unique()->values());

        return view('admin.themes', [
            'tenant' => $tenant,
            'year' => $year,
            'months' => $months,
            'legend' => $legend,
            'periods' => $periods->sortBy('start')->values(),
            'todayStyle' => DashboardStyle::fromCalendar(now(), $tenant),
            'events' => $tenant->calendarEvents()->orderByDesc('starts_on')->get(),
            'editedEvent' => $request->query('edit') ? $tenant->calendarEvents()->find($request->query('edit')) : null,
        ]);
    }

    public function storeCalendarEvent(Request $request)
    {
        $tenant = $request->route('tenant');
        $validated = $request->validate($this->calendarEventRules());

        $event = $tenant->calendarEvents()->create($validated);
        if (now()->startOfDay()->between($event->starts_on, $event->ends_on)) {
            DashboardRefreshEvent::dispatch();
        }

        return $this->redirectWithSuccess('Événement « '.$event->label.' » ajouté au calendrier', redirect()->route('admin.themes', ['tenantSlug' => $tenant->slug, 'year' => $event->starts_on->year]));
    }

    public function updateCalendarEvent(Request $request)
    {
        $tenant = $request->route('tenant');
        $event = $tenant->calendarEvents()->findOrFail($request->route('calendarEvent'));
        $validated = $request->validate($this->calendarEventRules());

        $wasToday = now()->startOfDay()->between($event->starts_on, $event->ends_on);
        $event->update($validated);
        if ($wasToday || now()->startOfDay()->between($event->starts_on, $event->ends_on)) {
            DashboardRefreshEvent::dispatch();
        }

        return $this->redirectWithSuccess('Événement « '.$event->label.' » modifié', redirect()->route('admin.themes', ['tenantSlug' => $tenant->slug, 'year' => $event->starts_on->year]));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function calendarEventRules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100'],
            'style' => ['required', Rule::in(array_keys(config('tsparticles.config', [])))],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
        ];
    }

    public function destroyCalendarEvent(Request $request)
    {
        $tenant = $request->route('tenant');
        $event = $tenant->calendarEvents()->findOrFail($request->route('calendarEvent'));
        $event->delete();
        if (now()->startOfDay()->between($event->starts_on, $event->ends_on)) {
            DashboardRefreshEvent::dispatch();
        }

        return $this->redirectWithSuccess('Événement « '.$event->label.' » supprimé du calendrier', redirect()->back());
    }

    public function updateDashboardStyleMode(Request $request)
    {
        $tenant = $request->route('tenant');
        $validated = $request->validate([
            'dashboard_style_mode' => ['required', Rule::enum(DashboardStyleMode::class)],
        ]);

        $tenant->update($validated);
        // Screens reload right away, the switch is meant for emergencies.
        DashboardRefreshEvent::dispatch();

        return $this->redirectWithSuccess('Styles du dashboard de '.$tenant->name.' : '.$tenant->dashboard_style_mode->label(), redirect()->back());
    }

    public function webex(Request $request)
    {
        $tenant = $request->route('tenant');
        if (! $tenant->webex_bearer_token) {
            throw new HttpException(403, 'Webex configuration not found for tenant', null, []);
        }
        $api = new WebexApi($tenant);
        $rooms = [];
        $webexRooms = $api->getRooms();
        foreach ($webexRooms['items'] as $room) {
            $room['memberships'] = $api->getRoomMemberships($room['id'])['items'];
            $room['messages'] = $api->getMessages($room['id'], 3)['items'];
            $rooms[] = $room;
        }

        return view('admin.webex', ['rooms' => $rooms, 'tenant' => $tenant]);
    }

    public function webexNotify(Request $request, WebexNotificationService $webexService)
    {
        $tenant = $request->route('tenant');
        $date = date('Y-m-d');

        Log::info('Sending Webex notifications for menu of '.$date.' to all rooms');
        $result = $webexService->sendMenuNotifications($tenant, $date, true);

        if (! $result['success']) {
            Log::info($result['message']);

            return $this->redirectWithError($result['message'], redirect()->route('admin.webex', ['tenantSlug' => $tenant->slug]));
        }

        Log::info($result['message']);

        return $this->redirectWithSuccess('Notifications Webex envoyées avec succès', redirect()->route('admin.jobs'));
    }
}
