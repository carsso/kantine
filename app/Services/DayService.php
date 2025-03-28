<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\Information;
use App\Models\Tenant;
use Carbon\Carbon;

class DayService
{
    /**
     * Get information for the specified day
     *
     * @param Tenant $tenant
     * @param string|Carbon $date
     * @return array
     */
    public function getDay(Tenant $tenant, $dateString = null)
    {
        $date = strtotime('today 10 am');
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString.' 10 am');
        }

        // Get information for the day
        $information = Information::where('tenant_id', $tenant->id)
            ->where('date', date('Y-m-d', $date))
            ->first();

        // Get all dishes for the day
        $dishes = Dish::where('tenant_id', $tenant->id)
            ->where('date', date('Y-m-d', $date))
            ->get();

        // Organize dishes by category name and sort by sort_order
        $groupedDishes = $dishes->groupBy(function($dish) {
            return $dish->category->parent->type;
        })->map(function($typeGroup) {
            return $typeGroup->groupBy(function($dish) {
                return $dish->category->parent->name_slug;
            })->sortBy(function($group, $key) {
                return $group->first()->category->parent->sort_order;
            })->map(function($categoryGroup) {
                return $categoryGroup->groupBy(function($dish) {
                    return $dish->category->name_slug;
                })->sortBy(function($group, $key) {
                    return $group->first()->category->sort_order;
                });
            });
        });

        $result = [
            'date' => date('Y-m-d', $date),
            'date_carbon' => Carbon::parse($date),
            'dishes' => $groupedDishes,
            'information' => ($information && ($information->event_name || $information->information || $information->style)) ? $information : null,
            'tenant' => $tenant,
        ];
        $result['is_fries_day'] = $dishes->contains(function($dish) {
            return str_contains(strtolower($dish->name), 'frites');
        });
        $result['is_burgers_day'] = $dishes->contains(function($dish) {
            return str_contains(strtolower($dish->name), 'burger');
        });
        $result['is_antioxidants_day'] = $dishes->contains(function($dish) {
            $needles = ['haricots rouges', 'lentilles'];
            foreach ($needles as $needle){
                if (str_contains(strtolower($dish->name), $needle)) {
                    return true;
                }
            }
            return false;
        });
        $result['next_fries_day'] = Dish::where('tenant_id', $tenant->id)
            ->where('date', '>', date('Y-m-d', $date))
            ->where('name', 'like', '%Frites%')
            ->orderBy('date', 'asc')
            ->first();
        $result['next_burgers_day'] = Dish::where('tenant_id', $tenant->id)
            ->where('date', '>', date('Y-m-d', $date))
            ->where('name', 'like', '%Burger%')
            ->orderBy('date', 'asc')
            ->first();
        $result['next_antioxidants_day'] = Dish::where('tenant_id', $tenant->id)
            ->where('date', '>', date('Y-m-d', $date))
            ->where(function($query) {
                $query->where('name', 'like', '%Haricots rouges%')
                      ->orWhere('name', 'like', '%Lentilles%');
            })
            ->orderBy('date', 'asc')
            ->first();
        $result['next_event'] = Information::where('tenant_id', $tenant->id)
            ->where('date', '>', date('Y-m-d', $date))
            ->where('event_name', '!=', null)
            ->where('event_name', '!=', '')
            ->orderBy('date', 'asc')
            ->first();
        return $result;
    }
}