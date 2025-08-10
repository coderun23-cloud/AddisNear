<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id',
        'payer_id',
        'payee_id',
        'amount',
        'payment_method',
        'status',
        'tx_ref',
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    // Relationships

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
}
