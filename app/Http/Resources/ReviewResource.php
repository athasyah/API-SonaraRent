<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rental_id' => $this->rental_id,
            'instrument_id' => $this->instrument_id,
            'instrument_name' => $this->instrument->name ?? null,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer->name ?? null,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'created_at' => $this->created_at,
        ];
    }
}
