<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelAgePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'infant_max_age',
        'child_max_age',
        'adult_min_age',
    ];

    protected $casts = [
        'infant_max_age' => 'integer',
        'child_max_age' => 'integer',
        'adult_min_age' => 'integer',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function stayTypes()
    {
        return $this->hasMany(StayType::class, 'hotel_age_policy_id');
    }
}
