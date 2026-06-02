<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Cek apakah role karyawan yang login termasuk dalam daftar role yang diizinkan.
     * Session sudah di-set oleh SetSessionRole middleware sebelumnya.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $currentRole = session('role');

        if (empty($roles) || in_array($currentRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
