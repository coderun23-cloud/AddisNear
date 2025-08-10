<?php

namespace App\Policies;

use App\Models\Place;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlacePolicy
{
   public function placecreate(User $user):Response{

    return $user->role==='owner'
    ? Response::allow()
    : Response::deny('Only Owners can register a place.');

   }
   public function placemodify(User $user,Place $place):Response{
    return ($user->id === $place->owner_id)?Response::allow():Response::deny('Only Owners can modify this product');
   }
}
