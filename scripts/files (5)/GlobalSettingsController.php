<?php
// app/Http/Controllers/Admin/GlobalSettingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateGlobalSetting;
use Illuminate\Http\Request;

class GlobalSettingsController extends Controller
{
    public function save(Request $request)
    {
        $settings = $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($settings['settings'] as $key => $value) {
            TemplateGlobalSetting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
