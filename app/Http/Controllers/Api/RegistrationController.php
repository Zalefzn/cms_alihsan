<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * Captures a PPDB (student admission) submission from the frontend's
     * registration form block — reviewed from App\Filament\Resources\RegistrationResource.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'child_name' => ['required', 'string', 'max:255'],
            'parent_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'unit' => ['required', 'string', 'in:'.implode(',', array_keys(Registration::UNITS))],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        Registration::create($data);

        return response()->json(['message' => 'Pendaftaran berhasil dikirim.'], 201);
    }
}
