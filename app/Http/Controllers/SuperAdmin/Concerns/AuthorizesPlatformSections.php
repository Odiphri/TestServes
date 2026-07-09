<?php

namespace App\Http\Controllers\SuperAdmin\Concerns;

use Illuminate\Support\Facades\Auth;

trait AuthorizesPlatformSections
{
    protected function platformAdmin()
    {
        return Auth::guard('platform_admin')->user();
    }

    protected function requireSuperAdmin(): void
    {
        abort_unless($this->platformAdmin()?->isSuperAdmin(), 403);
    }

    protected function isSuperAdmin(): bool
    {
        return (bool) $this->platformAdmin()?->isSuperAdmin();
    }
}
