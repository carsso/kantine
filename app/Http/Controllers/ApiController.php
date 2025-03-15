<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Services\DayService;

class ApiController extends Controller
{
    public function home()
    {
        return [
            'today' => route('api.today'),
            'day' => route('api.day', date('Y-m-d')),
        ];
    }

    public function today(DayService $dayService)
    {
        return $this->day($dayService, date('Y-m-d'));
    }

    public function day(DayService $dayService, $dateString)
    {
        return $dayService->getDay($dateString);
    }
}
