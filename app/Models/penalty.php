<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
    public function condition()
    {
        return $this->belongsTo(InstrumentCondition::class, 'condition_id');
    }
}
