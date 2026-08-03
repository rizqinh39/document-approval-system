<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permohonan;
use Illuminate\Support\Facades\Cache;
use App\Helpers\QiHelp;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        
        $cacheKey = 'dashboard_summary_user_' . $user->id;
        
        $summary = Cache::remember($cacheKey, 300, function () use ($user) {
            $query = Permohonan::query();
            
            // If user is not admin/penilai, only show their own summary
            if ($user->hasRole('pemohon') && !$user->hasAnyRole(['admin', 'penilai'])) {
                $query->where('user_id', $user->id);
            }

            return [
                'total' => (clone $query)->count(),
                'draft' => (clone $query)->where('status', 'draft')->count(),
                'submitted' => (clone $query)->where('status', 'submitted')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'revision' => (clone $query)->where('status', 'revision')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
            ];
        });

        return QiHelp::apiResponse(true, 'Dashboard summary retrieved', $summary);
    }

    public function chartMonthly(Request $request)
    {
        $user = $request->user();
        
        $cacheKey = 'dashboard_chart_monthly_user_' . $user->id;
        
        $chartData = Cache::remember($cacheKey, 300, function () use ($user) {
            $query = Permohonan::query();
            
            if ($user->hasRole('pemohon') && !$user->hasAnyRole(['admin', 'penilai'])) {
                $query->where('user_id', $user->id);
            }

            // Group by month for the current year
            $data = $query->select(
                DB::raw('COUNT(id) as count'),
                DB::raw('MONTH(created_at) as month')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

            $formatted = array_fill(1, 12, 0);
            foreach ($data as $row) {
                $formatted[$row->month] = $row->count;
            }

            return array_values($formatted);
        });

        return QiHelp::apiResponse(true, 'Monthly chart data retrieved', $chartData);
    }
}
