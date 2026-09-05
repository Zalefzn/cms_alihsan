<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterSubscriptionController extends Controller
{
    /**
     * Captures an email from the frontend footer's newsletter card. An
     * already-subscribed email is treated as a success (not an error) —
     * there's nothing useful to tell the visitor otherwise, and it avoids
     * leaking whether an address is already on the list.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $locale = $request->query('lang') === 'en' ? 'en' : 'id';

        NewsletterSubscriber::query()->firstOrCreate(
            ['email' => $data['email']],
            ['locale' => $locale],
        );

        return response()->json(['message' => 'Berlangganan berhasil.'], 201);
    }
}
