<?php

namespace App\Polices;

use App\Models\Peticione;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response\bool;


class PeticionePolicy
{
    use HandlesAuthorization;
    public function before(User $user, string $ability)
    {
        if ($user->role_id == 1) {
            return true;
        }
    }
    public function viewAny(User $user){

    }
    public function update(User $user, Peticione $peticione){
        return $user->role_id==2 && $peticione->user_id==$user->id;
    }

    public function cambiarEstado(User $user, Peticione $peticione)
    {
        return $user->role_id == 1;
    }

    public function delete(User $user, Peticione $peticione){
        return $user->role_id==2 && $peticione->user_id==$user->id;
    }

    public function forceDelete(User $user, Peticione $peticione)
    {

    }


    public function firmar(User $user, Peticione $peticione)
    {
        return $user->id != $peticione->user_id;

    }
}
