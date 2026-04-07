<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $review = Review::where('rental_id', $this->rental_id)
            ->where('instrument_id', $this->instrument_id)
            ->first();

        return [
            'id' => $this->id,
            'instrument_id' => $this->instrument_id,
            'instrument' => $this->instrument->name,
            'rental_id' => $this->rental_id,
            'price_per_day' => $this->price_per_day,
            'days' => $this->day,
            'subtotal' => $this->subtotal,
            'has_reviewed' => (bool)$review,
            'review_id' => $review?->id,
            'review_rating' => $review?->rating,
            'review_comment' => $review?->comment,
            'review_image' => $review?->image ? url('storage/' . $review->image) : null,
        ];
    }
}

