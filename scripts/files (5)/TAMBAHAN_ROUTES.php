<?php
// ============================================================
// TAMBAHKAN ke routes/fast.php
// Letakkan use statements di ATAS file (setelah <?php)
// Letakkan route groups di DALAM group admin yang sudah ada
// ============================================================

// ── USE STATEMENTS (tambah di bagian atas fast.php) ─────────
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\LetterIndexController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\QrManageController;
use App\Http\Controllers\Admin\GlobalSettingsController;
use App\Http\Controllers\Admin\ArchiveController;

// ── ROUTE TAMBAHAN (di dalam group admin) ───────────────────

// Semua Pengajuan
Route::get('/surat', [LetterIndexController::class, 'index'])
    ->name('surat.index');

// Arsip & Unduh
Route::get('/archive', [ArchiveController::class, 'index'])
    ->name('archive.index');

// Riwayat
Route::get('/history', [HistoryController::class, 'index'])
    ->name('history');

// Kategori Surat
Route::get('/categories', [CategoryController::class, 'index'])
    ->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])
    ->name('categories.store');
Route::put('/categories/{category}', [CategoryController::class, 'update'])
    ->name('categories.update');
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
    ->name('categories.destroy');

// Kelola QR Code
Route::get('/qr', [QrManageController::class, 'index'])
    ->name('qr.index');
Route::post('/qr/{suratQrCode}/revoke', [QrManageController::class, 'revoke'])
    ->whereNumber('suratQrCode')
    ->name('qr.revoke');

// Template — Duplikat & Toggle Active
Route::post('/templates/{jenisSurat}/duplicate', [TemplateController::class, 'duplicate'])
    ->name('templates.duplicate');
Route::patch('/templates/{jenisSurat}/toggle-active', [TemplateController::class, 'toggleActive'])
    ->name('templates.toggle-active');

// Global Settings
Route::post('/settings/template', [GlobalSettingsController::class, 'save'])
    ->name('settings.template');

// ── APPROVAL ROUTES (di dalam group approval) ───────────────
// Pastikan sudah ada di dalam:
// Route::middleware(['web'])->prefix('approval')->name('approval.')
Route::get('/dashboard', [ApprovalController::class, 'index'])
    ->name('dashboard');
Route::get('/surat/{id}', [ApprovalController::class, 'show'])
    ->whereNumber('id')
    ->name('surat.show');
Route::post('/surat/{id}/approve', [ApprovalController::class, 'approve'])
    ->whereNumber('id')
    ->name('surat.approve');
Route::post('/surat/{id}/reject', [ApprovalController::class, 'reject'])
    ->whereNumber('id')
    ->name('surat.reject');
