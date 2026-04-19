<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class RequireActiveBranch
{
    /**
     * Redirect authenticated users who have no branch assigned to a holding page.
     *
     * This middleware must run AFTER ResolveBranchContext so that branch_id
     * is already set in the Laravel Context.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        // Dueño / global access → never blocked, even without an active branch
        if ($user->hasGlobalBranchAccess()) {
            return $next($request);
        }

        $activeBranchId = Context::get('branch_id');

        if ($activeBranchId === null) {
            return Inertia::location(route('branch.required'));
        }

        return $next($request);
    }
}
