<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }
    public function orders(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager','support']);
    }
}
