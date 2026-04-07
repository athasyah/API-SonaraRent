<?php

namespace App\Listeners;

use App\Enums\StatusEnum;
use App\Events\RentalStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateInstrumentStatus
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RentalStatusUpdated $event): void
    {
        $rental = $event->rental;

        $statusMap = [
            StatusEnum::RESERVED->value  => StatusEnum::RESERVED->value,
            StatusEnum::ONGOING->value   => StatusEnum::RENTED->value,
            StatusEnum::RETURNED->value  => StatusEnum::AVAILABLE->value,
            StatusEnum::CANCELLED->value => StatusEnum::AVAILABLE->value,
        ];

        if (!isset($statusMap[$event->newStatus])) {
            return;
        }

        foreach ($rental->details as $detail) {
            $detail->instrument->update([
                'status' => $statusMap[$event->newStatus]
            ]);
        }
    }
}
