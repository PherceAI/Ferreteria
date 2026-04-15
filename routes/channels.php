<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('branch.{branchId}', function (User $user, int $branchId) {
    return $user->canAccessBranch($branchId);
});
