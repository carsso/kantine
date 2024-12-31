<?php

namespace App\Http\Controllers;
use App\Http\Requests\UpdateMenuFormRequest;
use App\Libraries\WebexApi;
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
    public function menu($dateString = null)
    {
        $dateToday = strtotime('today 10 am');
        $date = $dateToday;
        if(date('H') >= 15) {
            $date = strtotime('+1 day', $date);
        }
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString.' 10 am');
        }
        $menu = Menu::where('date', '>=', date('Y-m-d', $date))->where('mains', '!=', '[]')->where('sides', '!=', '[]')->orderBy('date', 'asc')->first();
        if($menu) {
            $date = strtotime($menu->date.' 10 am');
        }
        $mondayTime = strtotime('monday this week 10 am', $date);
        $sundayTime = strtotime('sunday this week 10 am', $date);
        $calendarWeekFirstDay = date('Y-m-d', $mondayTime);
        $calendarWeekLastDay = date('Y-m-d', $sundayTime);
        $weekMenus = Menu::whereBetween('date', [$calendarWeekFirstDay, $calendarWeekLastDay])->get();
        if($weekMenus->count() == 0) {
            foreach(range(0, 4) as $day) {
                $menu = new Menu;
                $menu->date = date('Y-m-d', strtotime("+$day day", $mondayTime));
                $weekMenus->push($menu);
            }
        }
        $prevWeek = date('Y-m-d', strtotime('-1 week', $mondayTime));
        $nextWeek = date('Y-m-d', strtotime('+1 week', $mondayTime));
        $autocompleteDishes = [];
        foreach(['starters', 'liberos', 'mains', 'sides', 'cheeses', 'desserts'] as $type) {
            $autocompleteDishes[$type] = Menu::select($type)->orderBy('id', 'desc')->limit(300)->get()->pluck($type)->filter()->flatMap(function($item) {
                return $item;
            })->unique()->sort()->values()->toArray();

        }
        return view('admin.menu', ['menus' => $weekMenus, 'weekMonday' => Carbon::parse($mondayTime), 'weekSunday' => Carbon::parse($sundayTime), 'autocompleteDishes' => $autocompleteDishes, 'week' => $calendarWeekFirstDay, 'prevWeek' => $prevWeek, 'nextWeek' => $nextWeek]);
    }

    public function updateMenu(UpdateMenuFormRequest $request)
    {
        $validated = $request->validated();
        if($validated['date'] && count($validated['date']) > 0) {
            foreach($validated['date'] as $idx => $date)
            {
                $menu = Menu::where('date', $date)->first();
                if(!$menu) {
                    $menu = new Menu;
                }
                $ucfirst = function($s) {
                    return mb_strtoupper( mb_substr( $s, 0, 1 )) . mb_substr( $s, 1 );
                };
                foreach (['starters', 'liberos', 'mains', 'sides', 'cheeses', 'desserts'] as $type) {
                    if(isset($validated[$type][$idx])) {
                        $validated[$type][$idx] = array_map($ucfirst, array_values(array_filter($validated[$type][$idx])));
                    }
                }
                $menu->date = $date;
                $menu->event_name = $validated['event_name'][$idx] ?? '';
                $menu->information = $validated['information'][$idx] ? $validated['information'][$idx] : null;
                $menu->style = $validated['style'][$idx] ? $validated['style'][$idx] : null;
                $menu->starters = $validated['starters'][$idx] ?? [];
                $menu->liberos = $validated['liberos'][$idx] ?? [];
                $menu->mains = $validated['mains'][$idx] ?? [];
                $menu->sides = $validated['sides'][$idx] ?? [];
                $menu->cheeses = $validated['cheeses'][$idx] ?? [];
                $menu->desserts = $validated['desserts'][$idx] ?? [];
                $menu->file_id = $menu->file_id ?? null;
                $menu->save();
            }
            return $this->redirectWithSuccess('Menus mis à jour', redirect()->route('admin.menu', ['date' => $validated['date'][0]]));
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

    public function webexNotify()
    {
        $date = date('Y-m-d');
        Log::info('Sending Webex notifications for menu of ' . $date . ' to all rooms');
        $menu = Menu::where('date', $date)->where('mains', '!=', '[]')->where('sides', '!=', '[]')->first();

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
