<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
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
            'user_id' => $this->user_id,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer?->name,
            'rent_date' => $this->rent_date,
            'return_date' => $this->return_date,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'details' => RentalDetailResource::collection(
                $this->whenLoaded('details')
            ),
            'penalty' => PenaltyResource::collection($this->whenLoaded('penalty')),
        ];
    }
}
