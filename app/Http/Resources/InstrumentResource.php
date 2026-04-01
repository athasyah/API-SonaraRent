<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InstrumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'brand_id' => $this->brand_id,
            'brand_name' => $this->brandCategory?->name,
            'name' => $this->name,
            'price_per_day' => $this->price_per_day,
            'status' => $this->status,
            'description' => $this->description,
            'image' => $this->image
                ? Storage::disk('public')->url($this->image)
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Include computed stats when rentalDetails relationship is loaded (detail page)
        if ($this->relationLoaded('rentalDetails')) {
            $completedRentals = $this->rentalDetails->filter(function ($detail) {
                return $detail->rental && in_array($detail->rental->status, ['returned']);
            });

            $data['total_rented'] = $completedRentals->count();
            $data['total_revenue'] = $completedRentals->sum('subtotal');

            // Rental history for the history tab
            $data['rental_history'] = $this->rentalDetails
                ->filter(fn($detail) => $detail->rental)
                ->sortByDesc(fn($detail) => $detail->rental->created_at)
                ->take(20)
                ->values()
                ->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'rental_id' => $detail->rental_id,
                        'customer_name' => $detail->rental->customer?->name ?? '-',
                        'rent_date' => $detail->rental->rent_date,
                        'return_date' => $detail->rental->return_date,
                        'status' => $detail->rental->status,
                        'subtotal' => $detail->subtotal,
                        'day' => $detail->day,
                        'created_at' => $detail->rental->created_at,
                    ];
                });
        }

        // Include review stats when reviews relationship is loaded (detail page)
        if ($this->relationLoaded('reviews')) {
            $data['average_rating'] = $this->reviews->count() > 0
                ? round($this->reviews->avg('rating'), 1)
                : 0;
            $data['total_reviews'] = $this->reviews->count();

            // Recent reviews for display
            $data['recent_reviews'] = $this->reviews
                ->sortByDesc('created_at')
                ->take(5)
                ->values()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'customer_name' => $review->customer?->name ?? '-',
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'image' => $review->image
                            ? Storage::disk('public')->url($review->image)
                            : null,
                        'created_at' => $review->created_at,
                    ];
                });
        }

        return $data;
    }
}

