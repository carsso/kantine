<?php

namespace App\Http\Controllers;
use App\Events\MenuUpdatedEvent;
use App\Http\Requests\UpdateMenuFormRequest;
use App\Libraries\WebexApi;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\Information;
use App\Services\DayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Jobs\ProcessWebexMenuNotification;
use App\Models\Menu;
use Illuminate\Http\Request;
use App\Models\Tenant;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Super Admin']);
    }

    public function index()
    {
        $tenants = Tenant::all();
        return view('admin.index', ['tenants' => $tenants]);
    }

    public function store(Request $request, $tenant)
    {
        $request->validate([
            'date' => 'required|date',
            'dishes' => 'required|array',
            'dishes.*' => 'required|exists:dishes,id'
        ]);

        $date = Carbon::parse($request->date);
        $dishes = Dish::whereIn('id', $request->dishes)
            ->where('tenant_id', $tenant->id)
            ->get();

        foreach($dishes as $dish) {
            $dish->date = $date;
            $dish->save();
        }

        return redirect()->route('admin.index', ['tenant' => $tenant->slug, 'date' => $date->format('Y-m-d')])
            ->with('success', 'Menu mis à jour avec succès');
    }

    public function destroy($tenant, $id)
    {
        $dish = Dish::where('tenant_id', $tenant->id)
            ->findOrFail($id);
        $dish->date = null;
        $dish->save();

        return redirect()->back()->with('success', 'Plat retiré du menu avec succès');
    }

    public function menu(DayService $dayService, $tenant, $dateString = null)
    {
        $dateToday = strtotime('today 10 am');
        $date = $dateToday;
        if(date('H') >= 15) {
            $date = strtotime('+1 day', $date);
        }
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString.' 10 am');
        }
        $dish = Dish::where('tenant_id', $tenant->id)
            ->where('date', '>=', date('Y-m-d', $date))
            ->orderBy('date', 'asc')
            ->first();
        if($dish) {
            $date = strtotime($dish->date.' 10 am');
        }
        $mondayTime = strtotime('monday this week 10 am', $date);
        $fridayTime = strtotime('friday this week 10 am', $date);
        $calendarWeekFirstDay = date('Y-m-d', $mondayTime);
        $calendarWeekLastDay = date('Y-m-d', $fridayTime);
        $menus = [];
        for($date = Carbon::parse($calendarWeekFirstDay); $date->lte(Carbon::parse($calendarWeekLastDay)); $date->addDay()) {
            $menu = $dayService->getDay($tenant, $date->format('Y-m-d'));
            $menus[$date->format('Y-m-d')] = $menu;
        }
        $prevWeek = date('Y-m-d', strtotime('-1 week', $mondayTime));
        $nextWeek = date('Y-m-d', strtotime('+1 week', $mondayTime));
        $autocompleteDishes = Dish::where('tenant_id', $tenant->id)
            ->orderBy('id', 'desc')
            ->limit(300)
            ->get()
            ->pluck('name')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        $autocompleteDishesTags = collect(Dish::getTagsTranslations(false))
            ->map(function($value, $key) {
                return ['label' => $value, 'value' => $key];
            })->values()->all();
        $categories = DishCategory::where('tenant_id', $tenant->id)
            ->whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');

        return view('admin.menu', ['tenant' => $tenant, 'menus' => $menus, 'categories' => $categories, 'weekMonday' => Carbon::parse($mondayTime), 'autocompleteDishes' => $autocompleteDishes, 'autocompleteDishesTags' => $autocompleteDishesTags, 'week' => $calendarWeekFirstDay, 'prevWeek' => $prevWeek, 'nextWeek' => $nextWeek]);
    }

    public function updateMenu(DayService $dayService, UpdateMenuFormRequest $request, $tenant)
    {
        $validated = $request->validated();
        if($validated['date'] && count($validated['date']) > 0) {
            $date = array_values($validated['date'])[0];
            $ucfirst = function($s) {
                return mb_strtoupper( mb_substr( $s, 0, 1 )) . mb_substr( $s, 1 );
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
                    foreach($dishes as $idx => $dish) {
                        $tags = $validated['dishes_tags'][$date][$categoryId][$idx] ?? '';
                        $tags = $tags ? explode(',', $tags) : [];
                        $dish = Dish::firstOrCreate([
                            'date' => $date,
                            'dishes_category_id' => $categoryId,
                            'name' => $dish,
                            'tenant_id' => $tenant->id,
                            'tags' => $tags,
                        ]);
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
                if(isset($validated[$field])) {
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
                if($menu) {
                    try {
                        MenuUpdatedEvent::dispatch($menu);
                    } catch (\Exception $e) {
                        Log::error($e);
                    }
                }
            }
            return $this->redirectWithSuccess(
                'Menus mis à jour',
                redirect()->route('admin.menu', ['tenant' => $tenant->slug, 'date' => $date])
            );
        }
        return $this->backWithError('Rien à mettre à jour');
    }

    public function webex($tenant)
    {
        if (!$tenant->webex_bearer_token) {
            throw new HttpException(403, 'Webex configuration not found for tenant', null, []);
        }
        $api = new WebexApi($tenant);
        $rooms = [];
        $webexRooms = $api->getRooms();
        foreach($webexRooms['items'] as $room) {
            $room['memberships'] = $api->getRoomMemberships($room['id'])['items'];
            $room['messages'] = $api->getMessages($room['id'], 3)['items'];
            $rooms[] = $room;
        }
        return view('admin.webex', ['rooms' => $rooms, 'tenant' => $tenant]);
    }

    public function webexNotify(DayService $dayService, $tenant)
    {
        if (!$tenant->webex_bearer_token) {
            Log::info('Webex configuration not found for tenant, aborting');
            return $this->redirectWithError('Configuration Webex non trouvée', redirect()->route('admin.webex', ['tenant' => $tenant->slug]));
        }

        $date = date('Y-m-d');
        Log::info('Sending Webex notifications for menu of ' . $date . ' to all rooms');
        $menu = $dayService->getDay($tenant, $date);

        if(!$menu) {
            Log::info('No menu for date '.$date.', aborting');
            return $this->redirectWithError('Aucun menu pour le '.$date, redirect()->route('admin.webex', ['tenant' => $tenant->slug]));
        }
        $api = new WebexApi($tenant);
        Log::info('Listing Webex rooms');
        $rooms = $api->getRooms();
        foreach($rooms['items'] as $room) {
            Log::info('Adding Webex room notification task to room ' . $room['title'] .' ' . $room['id']);
            ProcessWebexMenuNotification::dispatch($tenant, $room, $menu, $date, true);
        }
        return $this->redirectWithSuccess('La mise à jour du menu va être envoyée à tous les canaux Webex. Cela peut prendre quelques instants.', redirect()->route('admin.webex', ['tenant' => $tenant->slug]));
    }
}
