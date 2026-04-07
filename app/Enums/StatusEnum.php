<?php

namespace App\Enums;

enum StatusEnum: string
{
    case AVAILABLE = 'available';
    case PENDING = 'pending';
    case RESERVED = 'reserved';
    case ONGOING = 'ongoing';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';
    case RENTED = 'rented';
    case MAINTENANCE = 'maintenance';
    case DAMAGED = 'damaged';
    case RETURNING = 'returning';

}