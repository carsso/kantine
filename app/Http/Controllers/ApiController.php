<?php

namespace App\Http\Controllers;

use App\Models\Menu;

class ApiController extends Controller
{
    public function home()
    {
        return [
            'today' => route('api.today'),
            'day' => route('api.day', date('Y-m-d')),
            'week' => route('api.week'),
        ];
    }

    public function today()
    {
        return $this->day(date('Y-m-d'));
    }

    public function day($dateString)
    {
        $date = time();
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString);
        }
        $menu = Menu::where('date', date('Y-m-d', $date))->first();
        if(!$menu) {
            return response()->json([
                'error' => 'Aucun menu trouvé pour cette date '.date('Y-m-d', $date),
            ], 404);
        }
        return $menu;
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
