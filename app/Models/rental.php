<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function details()
    {
        return $this->hasMany(RentalDetail::class, 'rental_id', 'id');
    }

    public function guarantees()
    {
        return $this->hasOne(Guarantee::class);
    }

    public function penalty()
    {
        return $this->hasMany(Penalty::class);
    }
}
