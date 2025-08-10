<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MenuPolicy
{
    public function menucreate(User $user):Response{

    return $user->role==='owner'
    ? Response::allow()
    : Response::deny('Only Owners can register a menu.');

   }
 
}
