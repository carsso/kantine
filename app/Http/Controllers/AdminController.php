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
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Services\WebexNotificationService;

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
        if(date('H') >= 15) {
            $date = strtotime('+1 day', $date);
        }
        $dish = Dish::where('tenant_id', $tenant->id)
            ->where('date', '>=', date('Y-m-d', $date))
            ->orderBy('date', 'asc')
            ->first();
        if($dish) {
            $date = strtotime($dish->date.' 10 am');
        }
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString.' 10 am');
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
        $autocompleteDishes = Dish::orderBy('id', 'desc')
            ->limit(300)
            ->get()
            ->pluck('name')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        $autocompleteDishesTags = collect(Dish::getTagsDefinitions())
            ->map(function($value, $key) {
                return ['label' => $value['name_short'], 'value' => $key];
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

    public function updateMenu(UpdateMenuFormRequest $request, DayService $dayService)
    {
        $tenant = $request->route('tenant');
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
                        $dish->tags = $tags;
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
                redirect()->route('admin.menu', ['tenantSlug' => $tenant->slug, 'date' => $date])
            );
        }
        return $this->backWithError('Rien à mettre à jour');
    }

    public function webex(Request $request)
    {
        $tenant = $request->route('tenant');
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

    public function webexNotify(Request $request, WebexNotificationService $webexService)
    {
        $tenant = $request->route('tenant');
        $date = date('Y-m-d');
        
        Log::info('Sending Webex notifications for menu of ' . $date . ' to all rooms');
        $result = $webexService->sendMenuNotifications($tenant, $date, true);
        
        if (!$result['success']) {
            Log::info($result['message']);
            return $this->redirectWithError($result['message'], redirect()->route('admin.webex', ['tenantSlug' => $tenant->slug]));
        }
        
        Log::info($result['message']);
        return $this->redirectWithSuccess('Notifications Webex envoyées avec succès', redirect()->route('admin.jobs'));
    }
}
