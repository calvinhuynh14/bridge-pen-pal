<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\FailedLoginResponse as FailedLoginResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class FailedLoginResponse implements FailedLoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): Response
    {
        // Check if this is an Inertia request
        $isInertia = $request->header('X-Inertia') || $request->wantsJson() || $request->expectsJson();
        
        // For Inertia requests, return JSON with validation errors
        if ($isInertia) {
            return response()->json([
                'message' => __('These credentials do not match our records.'),
                'errors' => [
                    'email' => [__('These credentials do not match our records.')],
                ],
            ], 422);
        }

        // For regular requests, redirect back with error
        return back()->withErrors([
            Fortify::username() => __('These credentials do not match our records.'),
        ]);
    }
}

