<?php

namespace App\Observers;

use App\User;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the user "creating" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function creating(User $user)
    {
        $uuid = Str::uuid()->toString();

        while (User::where('uuid', $uuid)->exists()) {
            $uuid = Str::uuid()->toString();
        }

        $user->uuid = $uuid;
    }
}
