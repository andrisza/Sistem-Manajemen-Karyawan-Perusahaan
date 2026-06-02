<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Employee;

class SetSessionRole
{
    /**
     * Selalu sinkronkan session 'role' dan 'employee_id'
     * dari database setiap request auth — tidak ada hardcode nama role di sini.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $employee = Employee::with('role')->find(auth()->user()->employee_id);

            if ($employee && $employee->role) {
                $request->session()->put('role', $employee->role->title);
                $request->session()->put('employee_id', $employee->id);
            }
        }

        return $next($request);
    }
}
