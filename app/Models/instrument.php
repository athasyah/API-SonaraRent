<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brandCategory()
    {
        return $this->belongsTo(Category::class, 'brand_id');
    }

    public function rentalDetails()
    {
        return $this->hasMany(RentalDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
