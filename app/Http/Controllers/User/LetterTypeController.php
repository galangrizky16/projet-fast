<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LetterTypeController extends Controller
{
    public function show(Request $request, JenisSurat $jenisSurat): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        return response()->json([
            'data' => [
                'id' => $jenisSurat->id,
                'nama' => $jenisSurat->nama,
                'deskripsi' => $jenisSurat->deskripsi,
                'field_config' => collect($jenisSurat->field_config ?? [])
                    ->filter(fn ($field): bool => is_array($field) && filled($field['name'] ?? null))
                    ->values()
                    ->all(),
            ],
        ]);
    }

}
