<?php

namespace App\Services;

use Carbon\Carbon;

class PenaltyService
{
    public function mappPenalty(array $data)
    {
        $data = [
            'user_id' => auth()->user()->id,
            'rental_id' => $data['rental_id'],
            'condition_id' => $data['condition_id'],
            'title' => $data['title'],
            'reason' => $data['reason'],
            'amount' => $data['amount'],
        ];

        return $data;
    }

    public function calculateLatePenalty($rental)
    {
        if (now()->lte($rental->return_date)) {
            return 0;
        }

        $lateDays = Carbon::parse($rental->return_date)
            ->startOfDay()
            ->diffInDays(now()->startOfDay());

        $pricePerDay = $rental->details->sum('price_per_day');

        return $lateDays * $pricePerDay;
    }
}
