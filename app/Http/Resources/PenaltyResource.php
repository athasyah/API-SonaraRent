<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenaltyResource extends JsonResource
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
            'user_id' => $this->id,
            'user_name' => $this->rental?->customer?->name,
            'rental_id' => $this->rental_id,
            'condition_id' => $this->condition_id,
            'title' => $this->title,
            'reason' => $this->reason,
            'amount' => $this->amount,
            'instrument_name' => $this->condition?->instrument?->name,
            'created_at' => $this->created_at,
        ];
    }
}
