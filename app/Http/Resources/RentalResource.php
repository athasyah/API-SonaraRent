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
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'guarantee_type' => $this->guarantee_type,
            'guarantee_image' => $this->guarantee_image ? url('storage/' . $this->guarantee_image) : null,
            'guarantee_taken_by_name' => $this->guarantee_taken_by_user?->name,
            'returned_by_name' => $this->returned_by_user_data?->name,
            'actual_return_date' => $this->actual_return_date,
            'details' => RentalDetailResource::collection(
                $this->whenLoaded('details')
            ),
            'penalty' => PenaltyResource::collection($this->whenLoaded('penalty')),
            'is_delivery' => (bool) $this->is_delivery,
            'delivery_address' => $this->delivery_address,
            'cancel_reason' => $this->cancel_reason,
        ];
    }
}
