<?php


namespace Jahondust\ModelLog\Policies;


use App\User;
use Jahondust\ModelLog\Models\ModelLog;

class ModelLogPolicy
{
    public function browse(User $user)
    {
        return $user->hasPermission('browse_model_log');
    }

    public function clear(User $user){
        return $user->hasPermission('clear_model_log');
    }
}
