<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\DashboardInterface;
use App\Models\Instrument;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardInterface
{
    private Instrument $instrument;
    private Rental $rental;
    private RentalDetail $rentalDetail;
    private Review $review;
    private User $user;

    public function __construct(Instrument $instrument, Rental $rental, RentalDetail $rentalDetail, Review $review, User $user)
    {
        $this->instrument = $instrument;
        $this->rental = $rental;
        $this->rentalDetail = $rentalDetail;
        $this->review = $review;
        $this->user = $user;
    }
    /**
     * Get statistics and charts for Admin Dashboard
     */
    public function adminStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Revenue
        $currentRevenue = $this->rental->whereIn('status', ['ongoing', 'returned'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_price');
        $lastRevenue = $this->rental->whereIn('status', ['ongoing', 'returned'])
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total_price');
        $revenuePercentage = $lastRevenue > 0 ? round((($currentRevenue - $lastRevenue) / $lastRevenue) * 100, 1) : 0;

        // Total Rentals
        $currentRentals = $this->rental->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $lastRentals = $this->rental->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $rentalPercentage = $lastRentals > 0 ? round((($currentRentals - $lastRentals) / $lastRentals) * 100, 1) : 0;

        // Available Instruments
        $availableInstruments = $this->instrument->where('status', 'available')->count();
        $totalInstruments = $this->instrument->count();

        // Active Customers
        $activeCustomers = $this->user->role('customer')->count();
        $newCustomersThisMonth = $this->user->role('customer')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Weekly Rentals (last 7 days) - Optimized with single query & safe group by
        $sevenDaysAgo = $now->copy()->subDays(6)->startOfDay();
        $rawWeekly = $this->rental->select(DB::raw('DATE(created_at) as date_only'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $sevenDaysAgo)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date_only');

        $weeklyRentals = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->toDateString();
            $weeklyRentals[] = [
                'day' => Carbon::parse($date)->locale('id')->isoFormat('ddd'),
                'count' => $rawWeekly[$date] ?? 0,
            ];
        }

        // Monthly Revenue (last 12 months) - Optimized with single query & safe group by
        $twelveMonthsAgo = $now->copy()->subMonths(11)->startOfMonth();
        $rawMonthly = $this->rental->select(
                DB::raw('YEAR(created_at) as year_num'),
                DB::raw('MONTH(created_at) as month_num'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->whereIn('status', ['ongoing', 'returned'])
            ->where('created_at', '>=', $twelveMonthsAgo)
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->get();

        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $m = (int)$date->month;
            $y = (int)$date->year;
            
            $found = $rawMonthly->first(function($item) use ($m, $y) {
                return (int)$item->month_num === $m && (int)$item->year_num === $y;
            });

            $monthlyRevenue[] = [
                'month' => $date->locale('id')->isoFormat('MMM'),
                'revenue' => $found ? (float)$found->revenue : 0,
            ];
        }

        // Active rentals
        $activeRentals = $this->rental->where('status', 'ongoing')->count();

        // Average rating
        $avgRating = $this->review->avg('rating') ?? 0;
        $totalReviews = $this->review->count();

        // Rental status distribution
        $statusDistribution = $this->rental->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'stats' => [
                'total_revenue' => $currentRevenue,
                'revenue_percentage' => $revenuePercentage,
                'total_rentals' => $currentRentals,
                'rental_percentage' => $rentalPercentage,
                'available_instruments' => $availableInstruments,
                'total_instruments' => $totalInstruments,
                'active_customers' => $activeCustomers,
                'new_customers' => $newCustomersThisMonth,
            ],
            'weekly_rentals' => $weeklyRentals,
            'monthly_revenue' => $monthlyRevenue,
            'active_rentals' => $activeRentals,
            'avg_rating' => round($avgRating, 1),
            'total_reviews' => $totalReviews,
            'status_distribution' => $statusDistribution,
        ];
    }

    /**
     * Get statistics and charts for Staff Dashboard
     */
    public function staffStats(): array
    {
        $now = Carbon::now();

        // Rental counts by status
        $pendingCount = $this->rental->where('status', 'pending')->count();
        $approvedCount = $this->rental->where('status', 'approved')->count();
        $ongoingCount = $this->rental->where('status', 'ongoing')->count();
        $returnedCount = $this->rental->where('status', 'returned')->count();
        $cancelledCount = $this->rental->where('status', 'cancelled')->count();

        // Late rentals
        $lateRentals = $this->rental->where('status', 'ongoing')
            ->where('return_date', '<', $now)
            ->count();

        // Today's activity
        $todayRentals = $this->rental->whereDate('created_at', $now->toDateString())->count();
        $todayReturns = $this->rental->where('status', 'returned')
            ->whereDate('updated_at', $now->toDateString())
            ->count();

        // Weekly trend - Optimized with single query & safe group by
        $sevenDaysAgo = $now->copy()->subDays(6)->startOfDay();
        
        $newRentalsRaw = $this->rental->select(DB::raw('DATE(created_at) as date_only'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $sevenDaysAgo)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date_only');
            
        $returnsRaw = $this->rental->select(DB::raw('DATE(updated_at) as date_only'), DB::raw('count(*) as count'))
            ->where('status', 'returned')
            ->where('updated_at', '>=', $sevenDaysAgo)
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->pluck('count', 'date_only');

        $weeklyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dateStr = $date->toDateString();
            
            $weeklyTrend[] = [
                'day' => $date->locale('id')->isoFormat('ddd'),
                'date' => $dateStr,
                'new_rentals' => $newRentalsRaw[$dateStr] ?? 0,
                'returns' => $returnsRaw[$dateStr] ?? 0,
            ];
        }

        // upcoming returns (next 3 days)
        $upcomingReturns = $this->rental->where('status', 'ongoing')
            ->whereBetween('return_date', [$now, $now->copy()->addDays(3)])
            ->with(['customer', 'details' => function($query) {
                $query->with('instrument');
            }])
            ->orderBy('return_date')
            ->limit(10)
            ->get();

        return [
            'stats' => [
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'ongoing' => $ongoingCount,
                'returned' => $returnedCount,
                'cancelled' => $cancelledCount,
                'late' => $lateRentals,
                'today_rentals' => $todayRentals,
                'today_returns' => $todayReturns,
            ],
            'weekly_trend' => $weeklyTrend,
            'upcoming_returns' => $upcomingReturns,
        ];
    }

    /**
     * Get top popular instruments based on rental counts
     */
    public function getPopularInstruments(int $limit = 4): Collection
    {
        // Use a subquery to find popular instrument IDs and counts
        $popularData = $this->rentalDetail->select('instrument_id', DB::raw('count(*) as rental_count'))
            ->groupBy('instrument_id')
            ->orderByDesc('rental_count')
            ->limit($limit)
            ->get();
            
        $instrumentIds = $popularData->pluck('instrument_id');
        
        // Fetch full instrument models with relationships
        $instruments = $this->instrument->whereIn('id', $instrumentIds)
            ->with(['category', 'brandCategory'])
            ->get();
            
        // Map back the rental_count for each instrument
        return $instruments->map(function($instrument) use ($popularData) {
            $data = $popularData->firstWhere('instrument_id', $instrument->id);
            $instrument->rental_count = $data ? $data->rental_count : 0;
            return $instrument;
        })->sortByDesc('rental_count')->values();
    }
}
