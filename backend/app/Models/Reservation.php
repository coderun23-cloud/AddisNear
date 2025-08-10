<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;
        protected $fillable = [
        'user_id',
        'place_id',
        'reservation_date',
        'number_of_people',
        'status',
    ];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    public function place() {
        return $this->belongsTo(Place::class);
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }

}
