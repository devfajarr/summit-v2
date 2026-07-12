<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'pendaki') {
            if (! $user->pendaki || $user->pendaki->status_verifikasi !== 'disetujui') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda harus melakukan verifikasi identitas (KYC) terlebih dahulu sebelum mengakses fitur ini.',
                ], 403);
            }
        }

        return $next($request);
    }
}
