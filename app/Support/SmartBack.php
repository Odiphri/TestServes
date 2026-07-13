<?php

namespace App\Support;

use Illuminate\Http\Request;

class SmartBack
{
    public static function url(Request $request, string $fallback): string
    {
        $previous = url()->previous();
        $current = $request->fullUrl();

        if (! $previous || $previous === $current) {
            return $fallback;
        }

        return $previous;
    }
}
