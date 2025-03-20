<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Services\DayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Tenant;

class MenuController extends Controller
{
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
            ->where('date', '>=', date('Y-m-d', $date   ))
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
        $categories = DishCategory::where('tenant_id', $tenant->id)
            ->whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');
        return view('menu', ['tenant' => $tenant, 'menus' => $menus, 'categories' => $categories, 'weekMonday' => Carbon::parse($mondayTime), 'prevWeek' => $prevWeek, 'nextWeek' => $nextWeek]);
    }

    public function dashboard(Request $request, DayService $dayService)
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

        $menu = $dayService->getDay($tenant, $day->format('Y-m-d'));
        $categories = DishCategory::where('tenant_id', $tenant->id)
            ->whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');
        $style = $menu ? $request->query('style', $menu['information']['style'] ?? 'default'): 'default';
        $particlesOptions = in_array($style, array_keys(config('tsparticles.config', []))) ? config('tsparticles.config.'.$style) : null;
        return view('dashboard', ['tenant' => $tenant, 'menu' => $menu, 'categories' => $categories, 'diff' => $diff, 'day' => $day, 'particlesOptions' => $particlesOptions, 'generationDate' => $generationDate]);
    }

    public function webexMenu(Request $request, DayService $dayService)
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

        $menu = $dayService->getDay($tenant, date('Y-m-d', $date));
        $categories = DishCategory::where('tenant_id', $tenant->id)
            ->whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');
        return view('webex.menu', ['tenant' => $tenant, 'menu' => $menu, 'date' => Carbon::parse($date), 'categories' => $categories]);
    }

   public function notifications(Request $request, DayService $dayService)
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

        $menu = $dayService->getDay($tenant, date('Y-m-d', $date));
        $categories = DishCategory::where('tenant_id', $tenant->id)
            ->whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');
        return view('notifications', ['tenant' => $tenant, 'menu' => $menu, 'date' => Carbon::parse($date), 'categories' => $categories]);
   }

   public function legal()
   {
       return view('legal');
   }

   public function home()
   {
       $tenants = Tenant::where('is_active', true)->get();
       return view('home', compact('tenants'));
   }
}
