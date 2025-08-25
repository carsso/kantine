<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\FailedJob;
use App\Models\SuccessfulJob;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class JobMonitorController extends Controller
{
    public function index()
    {
        return view('admin.jobs');
    }

    public function getJobs()
    {
        // Jobs en attente
        $pendingJobs = Job::orderBy('created_at', 'desc')->get();

        // Jobs échoués et réussis, limités à 20 au total
        $failedJobs = FailedJob::orderBy('failed_at', 'desc')->limit(20)->get();
        $successfulJobs = SuccessfulJob::orderBy('finished_at', 'desc')->limit(20)->get();

        // Récupérer tous les tenants
        $tenants = Tenant::all()->keyBy('id');

        // Combiner et trier par date
        $allJobs = collect()
            ->concat($failedJobs->map(function ($job) {
                $job->date = $job->failed_at;
                return $job;
            }))
            ->concat($successfulJobs->map(function ($job) {
                $job->date = $job->finished_at;
                return $job;
            }))
            ->sortByDesc('date')
            ->take(20);

        // Séparer les jobs réussis et échoués
        $failedJobs = $allJobs->filter(function ($job) {
            return isset($job->failed_at);
        })->values();

        $successfulJobs = $allJobs->filter(function ($job) {
            return isset($job->finished_at);
        })->values();

        // Statistiques
        $stats = [
            'pending' => Job::count(),
            'failed' => FailedJob::count(),
            'successful' => SuccessfulJob::count(),
            'failed_today' => FailedJob::whereDate('failed_at', today())->count(),
            'failed_week' => FailedJob::whereDate('failed_at', '>=', now()->subWeek())->count(),
            'successful_today' => SuccessfulJob::whereDate('finished_at', today())->count(),
            'successful_week' => SuccessfulJob::whereDate('finished_at', '>=', now()->subWeek())->count(),
        ];

        return response()->json([
            'pendingJobs' => $pendingJobs,
            'failedJobs' => $failedJobs,
            'successfulJobs' => $successfulJobs,
            'stats' => $stats,
            'tenants' => $tenants,
        ]);
    }
} 