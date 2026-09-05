<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Site-wide identity/branding settings (logo, navbar top bar, footer)
     * for the frontend's navbar and footer. Pass ?lang=en for English;
     * defaults to Indonesian.
     */
    public function show(Request $request): JsonResponse
    {
        $locale = $request->query('lang') === 'en' ? 'en' : 'id';

        return response()->json(['data' => Setting::current()->resolvedData($locale)]);
    }
}
