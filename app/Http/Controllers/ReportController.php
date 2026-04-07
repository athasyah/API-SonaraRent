<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get report data for Sales and Revenue
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $dateFromReq = $request->date_from;
            $dateToReq = $request->date_to;

            if ($dateFromReq && (str_contains($dateFromReq, 'T') || str_contains($dateFromReq, ':'))) {
                $dateFrom = Carbon::parse($dateFromReq);
            } else {
                $dateFrom = $dateFromReq ? Carbon::parse($dateFromReq)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
            }

            if ($dateToReq && (str_contains($dateToReq, 'T') || str_contains($dateToReq, ':'))) {
                $dateTo = Carbon::parse($dateToReq);
            } else {
                $dateTo = $dateToReq ? Carbon::parse($dateToReq)->endOfDay() : Carbon::now()->endOfDay();
            }

            // 1. Summary Stats
            $totalSales = Rental::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'returned')
                ->count();

            $totalRevenue = Rental::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'returned')
                ->sum('total_price');

            $totalReturned = Rental::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'returned')
                ->count();

            // 2. Status Distribution in range
            $statusDistribution = Rental::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            // 3. Sales Trend (Daily counts)
            $salesTrendRaw = Rental::select(
                    DB::raw('DATE(created_at) as date_only'),
                    DB::raw('count(*) as count')
                )
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'returned')
                ->groupBy('date_only')
                ->orderBy('date_only')
                ->pluck('count', 'date_only');

            // 4. Revenue Trend (Daily sums - Returned only)
            $revenueTrendRaw = Rental::select(
                    DB::raw('DATE(created_at) as date_only'),
                    DB::raw('sum(total_price) as total')
                )
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'returned')
                ->groupBy('date_only')
                ->orderBy('date_only')
                ->pluck('total', 'date_only');

            // Fill gaps in dates
            $salesTrend = [];
            $revenueTrend = [];
            $currentDate = $dateFrom->copy();
            
            $isHourly = $dateFrom->diffInDays($dateTo) <= 2;

            if ($isHourly) {
                // Hour-by-hour aggregation
                $salesTrendRaw = Rental::select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour_only"),
                        DB::raw('count(*) as count')
                    )
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', 'returned')
                    ->groupBy('hour_only')
                    ->orderBy('hour_only')
                    ->pluck('count', 'hour_only');

                $revenueTrendRaw = Rental::select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour_only"),
                        DB::raw('sum(total_price) as total')
                    )
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', 'returned')
                    ->groupBy('hour_only')
                    ->orderBy('hour_only')
                    ->pluck('total', 'hour_only');

                while ($currentDate <= $dateTo) {
                    $dateStr = $currentDate->format('Y-m-d H:00:00');
                    
                    $salesTrend[] = [
                        'date' => $dateStr,
                        'count' => (int) ($salesTrendRaw[$dateStr] ?? 0)
                    ];
                    
                    $revenueTrend[] = [
                        'date' => $dateStr,
                        'total' => (float) ($revenueTrendRaw[$dateStr] ?? 0)
                    ];
                    
                    $currentDate->addHour();
                }
            } else {
                while ($currentDate <= $dateTo) {
                    $dateStr = $currentDate->toDateString();
                    
                    $salesTrend[] = [
                        'date' => $dateStr,
                        'count' => (int) ($salesTrendRaw[$dateStr] ?? 0)
                    ];
                    
                    $revenueTrend[] = [
                        'date' => $dateStr,
                        'total' => (float) ($revenueTrendRaw[$dateStr] ?? 0)
                    ];
                    
                    $currentDate->addDay();
                }
            }

            // 5. Recent Transactions in this range (returned only)
            $recentTransactions = Rental::with(['customer'])
                ->where('status', 'returned')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return Response::Ok('Berhasil mendapatkan data laporan', [
                'summary' => [
                    'total_sales' => $totalSales,
                    'total_revenue' => $totalRevenue,
                    'total_returned' => $totalReturned,
                ],
                'status_distribution' => $statusDistribution,
                'charts' => [
                    'sales_trend' => $salesTrend,
                    'revenue_trend' => $revenueTrend,
                ],
                'transactions' => $recentTransactions,
            ]);

        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data laporan', $th->getMessage());
        }
    }

    /**
     * Get Popular Instruments Report
     */
    public function popularInstruments(Request $request)
    {
        try {
            $limit = $request->limit ?? 10;
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfDay();

            $data = DB::table('rental_details')
                ->join('rentals', 'rental_details.rental_id', '=', 'rentals.id')
                ->join('instruments', 'rental_details.instrument_id', '=', 'instruments.id')
                ->join('categories', 'instruments.category_id', '=', 'categories.id')
                ->whereBetween('rentals.created_at', [$dateFrom, $dateTo])
                ->where('rentals.status', 'returned')
                ->select(
                    'instruments.id',
                    'instruments.name',
                    'categories.name as category_name',
                    DB::raw('count(*) as rental_count'),
                    DB::raw('sum(rental_details.subtotal) as total_revenue'),
                    DB::raw('avg(rental_details.price_per_day) as avg_price')
                )
                ->groupBy('instruments.id', 'instruments.name', 'categories.name')
                ->orderBy('rental_count', 'desc')
                ->limit($limit)
                ->get();

            return Response::Ok('Berhasil mendapatkan data instrumen populer', $data);
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data instrumen populer', $th->getMessage());
        }
    }
}
