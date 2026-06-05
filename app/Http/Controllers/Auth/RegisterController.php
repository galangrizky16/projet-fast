<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'unique:users,email'],
            'nim_nip'          => ['nullable', 'string', 'max:50'],
            'no_telepon'       => ['nullable', 'string', 'max:20'],
            'role_id'          => ['required', 'exists:roles,id'],
            'program_studi_id' => ['nullable', 'exists:program_studis,id'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'             => $validated['name'],
            'email'            => $validated['email'],
            'nim_nip'          => $validated['nim_nip'] ?? null,
            'no_telepon'       => $validated['no_telepon'] ?? null,
            'role_id'          => $validated['role_id'],
            'program_studi_id' => $validated['program_studi_id'] ?? null,
            'password'         => Hash::make($validated['password']),
            'is_active'        => true,
            'email_verified_at'=> now(),
        ]);

        auth()->login($user);

        return redirect()->route('redirect.dashboard');
    }
}
