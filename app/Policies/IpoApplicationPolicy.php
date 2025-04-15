<?php
namespace App\Policies;

use App\Models\IpoApplication;
use App\Models\User;

class IpoApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, IpoApplication $ipoApplication): bool
    {
        return $user->id === $ipoApplication->user_id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, IpoApplication $ipoApplication): bool
    {
        return $user->id === $ipoApplication->user_id;
    }

    public function delete(User $user, IpoApplication $ipoApplication): bool
    {
        return $user->id === $ipoApplication->user_id;
    }

    public function restore(User $user, IpoApplication $ipoApplication): bool
    {
        return false;
    }

    public function forceDelete(User $user, IpoApplication $ipoApplication): bool
    {
        return false;
    }
}
