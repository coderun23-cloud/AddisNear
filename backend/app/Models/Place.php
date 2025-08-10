<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    /** @use HasFactory<\Database\Factories\PlaceFactory> */
    use HasFactory;

    protected $fillable = [
            'name',
            'description',
            'category_id',
            'address',
            'latitude',
            'longitude',
            'phone_number',
            'website',
            'images',
            'owner_id'
        ];

        protected $casts = [
            'images' => 'array',
        ];
         public function owner()
{
    return $this->belongsTo(User::class, 'owner_id');
}

        public function category()
        {
            return $this->belongsTo(Category::class);
        }
        public function reservations()
        {
            return $this->hasMany(Reservation::class);
        }
        public function menus() {
        return $this->hasMany(Menu::class);
        }



}
