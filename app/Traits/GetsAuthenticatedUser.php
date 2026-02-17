<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait GetsAuthenticatedUser
{
    /**
     * Get the authenticated user with freelancer profile.
     *
     * @return User|null
     */
    protected function getAuthUser(): ?User
    {
        return Auth::user()?->load('freelancerProfile');
    }

    /**
     * Get the authenticated user ID.
     *
     * @return int|null
     */
    protected function getAuthUserId(): ?int
    {
        return Auth::id();
    }
}
