<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'instrument_id' => $this->instrument_id,
            'instrument' => $this->instrument->name,
            'rental_id' => $this->rental_id,
            'price_per_day' => $this->price_per_day,
            'days' => $this->day,
            'subtotal' => $this->subtotal,
        ];
    }
}
