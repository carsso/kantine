<?php

namespace App\Http\Controllers;

use App\Models\Menu;

class ApiController extends Controller
{
    public function home()
    {
        return [
            'today' => route('api.today'),
            'week' => route('api.week'),
        ];
    }

    public function today()
    {
        $todayMenu = Menu::where('date', date('Y-m-d'))->first();
        if(!$todayMenu) {
            return response()->json([
                'error' => 'Aucun menu trouvé pour aujourd\'hui',
            ], 404);
        }
        return $todayMenu;
    }

    public function week()
    {
        $calendarWeekFirstDay = date('Y-m-d', strtotime('monday this week'));
        $calendarWeekLastDay = date('Y-m-d', strtotime('sunday this week'));
        $weekMenus = Menu::whereBetween('date', [$calendarWeekFirstDay, $calendarWeekLastDay])->get();
        if(!$weekMenus) {
            return response()->json([
                'error' => 'Aucun menu trouvé pour cette semaine',
            ], 404);
        }
        return $weekMenus;
    }
}
