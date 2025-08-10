<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /** @use HasFactory<\Database\Factories\MenuFactory> */
    use HasFactory;
      protected $fillable = [
        'place_id',
        'name',
        'description',
        'price',
        'images',
    ];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
