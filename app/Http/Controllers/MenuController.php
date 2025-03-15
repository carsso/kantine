<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\Menu;
use App\Models\File;
use App\Http\Requests\UploadFormRequest;
use App\Jobs\ProcessFile;
use App\Services\DayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MenuController extends Controller
{
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
        $categories = DishCategory::whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');
        return view('menu', ['menus' => $menus, 'categories' => $categories, 'weekMonday' => Carbon::parse($mondayTime), 'prevWeek' => $prevWeek, 'nextWeek' => $nextWeek]);
    }

    public function dashboard(Request $request, DayService $dayService, $dateString = null)
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

        $day = Carbon::parse($date);
        $diff = $day->diffForHumans(
            Carbon::parse($dateToday),
            [
                'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                'options' => Carbon::JUST_NOW | Carbon::ONE_DAY_WORDS | Carbon::TWO_DAY_WORDS
            ],
        );
        if($date == $dateToday)
        {
            $diff = '';
        }

        $generationDate = Carbon::now();

        $menu = $dayService->getDay($day->format('Y-m-d'));
        $categories = DishCategory::whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');
        $style = $menu ? $request->query('style', $menu['information']['style'] ?? 'default'): 'default';
        $particlesOptions = in_array($style, array_keys(config('tsparticles.config', []))) ? config('tsparticles.config.'.$style) : null;
        return view('dashboard', ['menu' => $menu, 'categories' => $categories, 'diff' => $diff, 'day' => $day, 'particlesOptions' => $particlesOptions, 'generationDate' => $generationDate]);
    }

    public function webexMenu(DayService $dayService, $dateString = null)
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

        $menu = $dayService->getDay(date('Y-m-d', $date));
        $categories = DishCategory::whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');
        return view('webex.menu', ['menu' => $menu, 'date' => Carbon::parse($date), 'categories' => $categories]);
    }

   public function notifications(DayService $dayService, $dateString = null)
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

        $menu = $dayService->getDay(date('Y-m-d', $date));
        $categories = DishCategory::whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');
        return view('notifications', ['menu' => $menu, 'date' => Carbon::parse($date), 'categories' => $categories]);
   }

   public function legal()
   {
       return view('legal');
   }
}
