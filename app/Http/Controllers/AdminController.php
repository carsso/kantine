<?php

namespace App\Http\Controllers;
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

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Super Admin']);
    }

    public function index()
    {
        return view('admin.index');
    }
    public function menu(DayService $dayService, $dateString = null)
    {
        $dateToday = strtotime('today 10 am');
        $date = $dateToday;
        if(date('H') >= 15) {
            $date = strtotime('+1 day', $date);
        }
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString.' 10 am');
        }
        $dish = Dish::where('date', '>=', date('Y-m-d', $date))->orderBy('date', 'asc')->first();
        if($dish) {
            $date = strtotime($dish->date.' 10 am');
        }
        $mondayTime = strtotime('monday this week 10 am', $date);
        $fridayTime = strtotime('friday this week 10 am', $date);
        $calendarWeekFirstDay = date('Y-m-d', $mondayTime);
        $calendarWeekLastDay = date('Y-m-d', $fridayTime);
        $menus = [];
        for($date = Carbon::parse($calendarWeekFirstDay); $date->lte(Carbon::parse($calendarWeekLastDay)); $date->addDay()) {
            $menu = $dayService->getDay($date->format('Y-m-d'));
            $menus[$date->format('Y-m-d')] = $menu;
        }
        $prevWeek = date('Y-m-d', strtotime('-1 week', $mondayTime));
        $nextWeek = date('Y-m-d', strtotime('+1 week', $mondayTime));
        $autocompleteDishes = Dish::orderBy('id', 'desc')->limit(300)->get()->pluck('name')->unique()->sort()->values()->toArray();
        $autocompleteDishesTags = collect(Dish::getTagsTranslations(false))
            ->map(function($value, $key) {
                return ['label' => $value, 'value' => $key];
            })->values()->all();
        $categories = DishCategory::whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');

        return view('admin.menu', ['menus' => $menus, 'categories' => $categories, 'weekMonday' => Carbon::parse($mondayTime), 'autocompleteDishes' => $autocompleteDishes, 'autocompleteDishesTags' => $autocompleteDishesTags, 'week' => $calendarWeekFirstDay, 'prevWeek' => $prevWeek, 'nextWeek' => $nextWeek]);
    }

    public function updateMenu(UpdateMenuFormRequest $request)
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
                        $dish = Dish::firstOrCreate([
                            'date' => $date,
                            'dishes_category_id' => $categoryId,
                            'name' => $dish,
                        ]);
                        $tags = $validated['dishes_tags'][$date][$categoryId][$idx] ?? '';
                        $tags = $tags ? explode(',', $tags) : [];
                        $dish->tags = $tags;
                        $dish->save();
                        $dishIds[] = $dish->id;
                    }
                }
                Dish::where('date', $date)
                    ->whereNotIn('id', values: $dishIds)
                    ->delete();
            }
            $fields = ['event_name', 'information', 'style'];
            foreach ($fields as $field) {
                if(isset($validated[$field])) {
                    foreach ($validated[$field] as $date => $value) {
                        $information = Information::firstOrCreate([
                            'date' => $date,
                        ]);
                        $information->$field = $value;
                        $information->save();
                    }
                }
            }
            return $this->redirectWithSuccess(
                'Menus mis à jour',
                redirect()->route('admin.menu', ['date' => $date])
            );
        }
        return $this->backWithError('Rien à mettre à jour');
   }

    public function webex()
    {
        if(!config('services.webex.bearer_token')) {
            throw new HttpException(500, 'Webex bearer token not set', null, []);
        }
        $api = new WebexApi;
        $rooms = [];
        $webexRooms = $api->getRooms();
        foreach($webexRooms['items'] as $room) {
            $room['memberships'] = $api->getRoomMemberships($room['id'])['items'];
            $room['messages'] = $api->getMessages($room['id'], 3)['items'];
            $rooms[] = $room;
        }
        return view('admin.webex', ['rooms' => $rooms]);
    }

    public function webexNotify(DayService $dayService)
    {
        $date = date('Y-m-d');
        Log::info('Sending Webex notifications for menu of ' . $date . ' to all rooms');
        $menu = $dayService->getDay($date);

        if(!config('services.webex.bearer_token')) {
            Log::info('Webex bearer token not set, aborting');
            return $this->redirectWithError('Token Webex non configuré', redirect()->route('admin'));
        }
        if(!$menu) {
            Log::info('No menu for date '.$date.', aborting');
            return $this->redirectWithError('Aucun menu pour le '.$date, redirect()->route('admin'));
        }
        $api = new WebexApi;
        Log::info('Listing Webex rooms');
        $rooms = $api->getRooms();
        foreach($rooms['items'] as $room) {
            Log::info('Adding Webex room notification task to room ' . $room['title'] .' ' . $room['id']);
            ProcessWebexMenuNotification::dispatch($room, $menu, $date, true);
        }
        return $this->redirectWithSuccess('Notifications Webex envoyées', redirect()->route('admin'));
    }
}
