<?php

namespace App\Http\Controllers;
use App\Http\Requests\UpdateMenuFormRequest;
use App\Libraries\WebexApi;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        if(!$dateString) {
            $dateString = date('Y-m-d');
        }
        $date = time();
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString);
        }
        if(date('N', $date) >= 6) {
            $date = strtotime('+1 week', $date);
        }
        $mondayTime = strtotime('monday this week', $date);
        $sundayTime = strtotime('sunday this week', $date);
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
        foreach(['starters', 'mains', 'sides', 'cheeses', 'desserts'] as $type) {
            $autocompleteDishes[$type] = Menu::select($type)->orderBy('id', 'desc')->limit(300)->get()->pluck($type)->filter()->flatMap(function($item) {
                return $item;
            })->unique()->sort()->values()->toArray();

        }
        return view('admin.menu', ['menus' => $weekMenus, 'autocompleteDishes' => $autocompleteDishes, 'week' => $calendarWeekFirstDay, 'prevWeek' => $prevWeek, 'nextWeek' => $nextWeek]);
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
                foreach (['starters', 'mains', 'sides', 'cheeses', 'desserts'] as $type) {
                    if(isset($validated[$type][$idx])) {
                        $validated[$type][$idx] = array_values(array_filter($validated[$type][$idx]));
                    }
                }
                $menu->date = $date;
                $menu->event_name = $validated['event_name'][$idx] ?? '';
                $menu->starters = $validated['starters'][$idx] ?? $menu->starters ?? [];
                $menu->mains = $validated['mains'][$idx] ?? $menu->mains ?? [];
                $menu->sides = $validated['sides'][$idx] ?? $menu->sides ?? [];
                $menu->cheeses = $validated['cheeses'][$idx] ?? $menu->cheeses ?? [];
                $menu->desserts = $validated['desserts'][$idx] ?? $menu->desserts ?? [];
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
            $rooms[] = $room;
        }
        return view('admin.webex', ['rooms' => $rooms]);
    }
}
